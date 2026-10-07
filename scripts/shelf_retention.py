#!/usr/bin/env python3
"""Scoped D14 inventory/expiry. Dry-run first; never touches credentials/journal."""
import argparse
from datetime import datetime, timedelta, timezone
import json
import os
from pathlib import Path
import re
import tempfile
import shelf_offsite_backup as backup

LOGS = Path('/home/shelf/apps/shelf/storage/logs')
EXCEPTIONS = Path('/home/shelf/backups/shelf/security-retention-exceptions.json')
STATE = backup.PRIVATE / 'd14-retention.json'
LOG_NAME = re.compile(r'(?:laravel|daily-backup|offsite-backup|play-price-sync|play-refunds|d14-maintenance)-(?:legacy-)?(\d{4}-\d{2}-\d{2})\.log')


def expired(name, now):
    match = backup.NAME.fullmatch(name)
    if not match:
        return False
    date = datetime.strptime(name.split('-')[2], '%Y%m%dT%H%M%SZ').replace(tzinfo=timezone.utc)
    return date < now - timedelta(days=90)


def incident_holds(now):
    if not EXCEPTIONS.exists():
        return {}
    backup.private_file(EXCEPTIONS)
    entries = json.loads(EXCEPTIONS.read_text())
    result = {}
    for item in entries:
        if set(item) != {'file', 'case_reference', 'review_at', 'owner_id', 'reviewed_at'} or not LOG_NAME.fullmatch(item['file']):
            raise RuntimeError('Invalid incident exception; cleanup blocked.')
        if not re.fullmatch('[A-Za-z0-9_-]{3,80}', item['case_reference']) or type(item['owner_id']) is not int:
            raise RuntimeError('Invalid incident review.')
        review = datetime.fromisoformat(item['review_at'])
        if review.tzinfo is None or datetime.fromisoformat(item['reviewed_at']).tzinfo is None:
            raise RuntimeError('Invalid review date.')
        result[item['file']] = 'review_overdue' if review <= now else 'incident_hold'
    return result


def inventory(now=None):
    now = now or datetime.now(timezone.utc)
    listing = json.loads(backup.rclone('lsjson', backup.REMOTE, '--files-only').decode())
    files = {item['Name']: item for item in listing if isinstance(item.get('Name'), str) and '/' not in item['Name']}
    proofs = []
    # Only verified download/restore evidence pins a recovery set. Evidence is
    # read from local private records and the independently copied remote proof.
    for path in backup.ROOT.glob('recovery-shelf-production-*.json'):
        if path.is_symlink():
            raise RuntimeError('Unsafe recovery evidence.')
        record = json.loads(path.read_text())
        if record.get('download_checksum') is True and record.get('database_schema_rows_identical') is True and backup.NAME.fullmatch(record.get('package', '')) and record['package'] in files:
            proofs.append(record)
    if not proofs:
        raise RuntimeError('No verified remote recoverable set; cleanup blocked.')
    pin = max(proofs, key=lambda r: r['restored_at'])['package']
    with tempfile.TemporaryDirectory(prefix='shelf-retention-', dir='/home/shelf/tmp') as temp:
        side = Path(temp) / 'pinned.json'
        backup.rclone('copyto', backup.REMOTE + '/' + pin[:-4] + '.json', side)
        metadata = json.loads(side.read_text())
        proof = next(r for r in proofs if r['package'] == pin)
        if metadata.get('sha256') != proof.get('sha256') or metadata.get('bytes') != files[pin].get('Size'):
            raise RuntimeError('Pinned recovery evidence mismatch; cleanup blocked.')
    candidates, exceptions = [], []
    for name in files:
        if not backup.NAME.fullmatch(name) or not expired(name, now):
            continue
        if name == pin:
            exceptions.append({'file': name, 'reason': 'last_verified_recovery_set'})
            continue
        side = name[:-4] + '.json'
        if side not in files:
            exceptions.append({'file': name, 'reason': 'incomplete_set_review_required'})
            continue
        candidates += [name, side]
    local_delete = []
    spool = sorted((p for p in backup.ROOT.iterdir() if backup.NAME.fullmatch(p.name) and not p.is_symlink()), key=lambda p: p.name, reverse=True)
    for path in spool[14:]:
        if path.name == pin:
            exceptions.append({'file': path.name, 'reason': 'last_verified_local_recovery_set'})
            continue
        backup.valid_package(path)
        local_delete += [path.name, path.with_suffix('.json').name]
    holds = incident_holds(now)
    logs = []
    for path in LOGS.iterdir():
        match = LOG_NAME.fullmatch(path.name)
        if not match:
            continue
        if path.is_symlink() or path.resolve().parent != LOGS.resolve():
            raise RuntimeError('Unsafe log path; cleanup blocked.')
        # Whole dated day is retained until its final instant is older than 90d.
        end = datetime.fromisoformat(match[1]).replace(tzinfo=timezone.utc) + timedelta(days=1)
        if end >= now - timedelta(days=90):
            continue
        if path.name in holds:
            exceptions.append({'file': path.name, 'reason': holds[path.name]})
        else:
            logs.append(path.name)
    return {'inventoried_at': now.isoformat(), 'remote_delete': sorted(candidates), 'log_delete': sorted(logs), 'local_offsite_delete': sorted(local_delete), 'pinned': pin,
            'exceptions': exceptions, 'ignored_unrelated_files': True}


def execute(enabled=False):
    record = inventory()
    if not enabled:
        backup.write_json(backup.ROOT / 'd14-retention-inventory.json', record)
        print(json.dumps(record, indent=2))
        return
    backup.private_file(STATE)
    if json.loads(STATE.read_text()).get('enabled') is not True:
        raise RuntimeError('Dry-run and activation required before cleanup.')
    # Recompute immediately; never execute a stale saved file list.
    for name in record['remote_delete']:
        backup.rclone('deletefile', backup.REMOTE + '/' + name)
    for name in record['local_offsite_delete']:
        path = backup.ROOT / name
        if path.is_symlink() or path.resolve().parent != backup.ROOT:
            raise RuntimeError('Unsafe local offsite expiry path.')
        path.unlink()
    for name in record['log_delete']:
        path = LOGS / name
        if path.is_symlink() or not LOG_NAME.fullmatch(name):
            raise RuntimeError('Log changed during cleanup.')
        path.unlink()
    backup.write_json(backup.ROOT / 'd14-retention-last-run.json', record)
    print(json.dumps({'expired_remote_files': len(record['remote_delete']), 'expired_log_files': len(record['log_delete']), 'expired_local_offsite_files': len(record['local_offsite_delete']), 'exceptions': record['exceptions']}))


def activate():
    if backup.APP != backup.PRODUCTION:
        raise RuntimeError('Activation requires promoted production code.')
    # Successful scoped dry-run is mandatory in the same activation run.
    execute(False)
    backup.write_json(STATE, {'enabled': True, 'offsite_days': 90, 'security_log_days': 90, 'local_copies': 14,
                              'activated_at': datetime.now(timezone.utc).isoformat()})


def main():
    os.umask(0o077)
    parser = argparse.ArgumentParser()
    parser.add_argument('action', choices=['inventory', 'activate', 'cleanup'])
    action = parser.parse_args().action
    if action == 'activate':
        activate()
    else:
        execute(action == 'cleanup')

if __name__ == '__main__':
    try:
        main()
    except Exception:
        raise SystemExit('Shelf retention stopped; no further cleanup. Inspect privately.')
