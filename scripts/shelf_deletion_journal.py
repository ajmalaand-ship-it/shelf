#!/usr/bin/env python3
"""Encrypted independent deletion journal. No profiles, emails, tokens or secrets."""
import argparse
import fcntl
import hashlib
import json
import os
from pathlib import Path
import secrets
import tempfile
from datetime import datetime, timezone
import shelf_offsite_backup as backup

ROOT = Path('/home/shelf/backups/shelf/deletion-journal')
REMOTE = backup.REMOTE + '/DeletionRecords'


def validate(record):
    deletion = {'reader_id', 'created_at', 'deleted_at', 'record_id'}
    claim = {'reader_id', 'created_at', 'claimed_at', 'record_id', 'purchase_id', 'transaction_hash', 'owner_id', 'case_reference', 'proof_hash'}
    if set(record) not in (deletion, claim):
        raise RuntimeError('Invalid journal record.')
    for key in ('reader_id',) + (() if set(record) == deletion else ('purchase_id', 'owner_id')):
        if type(record[key]) is not int or record[key] < 1:
            raise RuntimeError('Invalid journal identity.')
    for key in ('created_at', 'deleted_at' if set(record) == deletion else 'claimed_at'):
        if not isinstance(record[key], str) or datetime.fromisoformat(record[key].replace('Z', '+00:00')).tzinfo is None:
            raise RuntimeError('Invalid journal date.')
    for key in ('created_at', 'deleted_at' if set(record) == deletion else 'claimed_at'):
        record[key] = datetime.fromisoformat(record[key].replace('Z', '+00:00')).astimezone(timezone.utc).isoformat()
    for key, length in [('record_id', 32)] + ([] if set(record) == deletion else [('transaction_hash', 64), ('proof_hash', 64)]):
        if not isinstance(record[key], str) or len(record[key]) != length or any(c not in '0123456789abcdef' for c in record[key]):
            raise RuntimeError('Invalid journal proof.')
    if set(record) == claim:
        import re
        if not re.fullmatch(r'[A-Za-z0-9_-]{3,80}', record['case_reference']):
            raise RuntimeError('Invalid case reference.')
    return record


def directory():
    ROOT.mkdir(mode=0o700, parents=True, exist_ok=True)
    if ROOT.is_symlink() or ROOT.resolve() != ROOT or ROOT.stat().st_mode & 0o077:
        raise RuntimeError('Unsafe deletion-journal directory.')


def encode(value, target, workspace):
    source = workspace / ('plain-' + secrets.token_hex(8))
    source.write_text(json.dumps(value, separators=(',', ':')))
    source.chmod(0o600)
    backup.crypt(source, target)


def decode(source, workspace):
    target = workspace / ('decoded-' + secrets.token_hex(8))
    backup.crypt(source, target, decrypt=True)
    return json.loads(target.read_text())


def fetch(workspace):
    head = workspace / 'head.gpg'
    backup.rclone('copyto', REMOTE + '/head.gpg', head)
    index = decode(head, workspace)
    if set(index) != {'version', 'records'} or index['version'] != 1 or not isinstance(index['records'], dict):
        raise RuntimeError('Deletion journal index unavailable or invalid; recovery blocked.')
    records = []
    for name, digest in index['records'].items():
        if not isinstance(name, str) or len(name) != 36 or not name.endswith('.gpg') or any(c not in '0123456789abcdef' for c in name[:-4]):
            raise RuntimeError('Invalid journal path.')
        local = workspace / name
        backup.rclone('copyto', REMOTE + '/' + name, local, '--immutable')
        if hashlib.sha256(local.read_bytes()).hexdigest() != digest:
            raise RuntimeError('Missing or corrupt deletion record; recovery blocked.')
        record = validate(decode(local, workspace))
        if name != record['record_id'] + '.gpg':
            raise RuntimeError('Deletion identity mismatch.')
        records.append(record)
    return index, records


def publish(index, workspace):
    encrypted = workspace / 'new-head.gpg'
    encode(index, encrypted, workspace)
    backup.rclone('copyto', encrypted, REMOTE + '/head.gpg')
    downloaded = workspace / 'head-confirm.gpg'
    backup.rclone('copyto', REMOTE + '/head.gpg', downloaded)
    if downloaded.read_bytes() != encrypted.read_bytes():
        raise RuntimeError('Deletion journal publication could not be verified.')


def initialize():
    directory()
    with tempfile.TemporaryDirectory(prefix='shelf-deletion-', dir='/home/shelf/tmp') as temp:
        work = Path(temp)
        # A read failure is never treated as an empty journal. Creation is allowed
        # only after a successful scoped listing proves head is absent.
        listing = json.loads(backup.rclone('lsjson', backup.REMOTE).decode())
        if any(item.get('Name') == 'DeletionRecords' for item in listing):
            fetch(work)
        else:
            publish({'version': 1, 'records': {}}, work)
        (ROOT / 'initialized').touch(mode=0o600)


def append(record):
    validate(record)
    directory()
    if not (ROOT / 'initialized').is_file():
        raise RuntimeError('Deletion journal not initialized; deletion unavailable.')
    lockfd = os.open(ROOT / '.lock', os.O_CREAT | os.O_WRONLY | os.O_NOFOLLOW, 0o600)
    with os.fdopen(lockfd, 'a') as lock:
        fcntl.flock(lock, fcntl.LOCK_EX)
        with tempfile.TemporaryDirectory(prefix='shelf-deletion-', dir='/home/shelf/tmp') as temp:
            work = Path(temp)
            index, records = fetch(work)
            deletion = 'deleted_at' in record
            existing = next((r for r in records if deletion and 'deleted_at' in r and r['reader_id'] == record['reader_id'] and r['created_at'] == record['created_at']), None)
            if not deletion:
                claims = [r for r in records if r.get('purchase_id') == record['purchase_id']]
                if claims:
                    last = claims[-1]
                    deleted = any('deleted_at' in r and r['reader_id'] == last['reader_id'] and r['created_at'] == last['created_at'] for r in records)
                    if not deleted:
                        raise RuntimeError('Purchase already durably claimed; support review required.')
            if existing:
                return existing
            encrypted = work / (record['record_id'] + '.gpg')
            encode(record, encrypted, work)
            backup.rclone('copyto', encrypted, REMOTE + '/' + encrypted.name, '--immutable')
            index['records'][encrypted.name] = hashlib.sha256(encrypted.read_bytes()).hexdigest()
            publish(index, work)
            # Full read-back proves each committed record is obtainable off-server.
            check = work / 'check'
            check.mkdir(mode=0o700)
            _, verified = fetch(check)
            if record not in verified:
                raise RuntimeError('Deletion durability unverified.')
            backup.write_json(ROOT / (record['record_id'] + '.json'), record)
            return record


def current():
    with tempfile.TemporaryDirectory(prefix='shelf-deletion-', dir='/home/shelf/tmp') as temp:
        return fetch(Path(temp))[1]


def replay_sql(records):
    # Replay minimal journal against the verified disposable recovery database.
    lines = ['START TRANSACTION;']
    def birth(record):
        return datetime.fromisoformat(record['created_at'].replace('Z', '+00:00')).astimezone(timezone.utc).strftime('%Y-%m-%d %H:%M:%S')
    deletions = [validate(r) for r in records if 'deleted_at' in r]
    for record in deletions:
        reader = record['reader_id']
        where = f"id={reader} AND created_at='{birth(record)}'"
        lines += [f"DELETE FROM personal_access_tokens WHERE tokenable_type='App\\\\Models\\\\Reader' AND tokenable_id IN (SELECT id FROM readers WHERE {where});",
                  f"DELETE FROM purchase_recovery_claims WHERE reader_id IN (SELECT id FROM readers WHERE {where});",
                  f"DELETE FROM readers WHERE {where};",
                  f"INSERT IGNORE INTO provider_deletions (reader_id,receipt_id,status,created_at,updated_at) VALUES ({reader},'{record['record_id']}','pending',UTC_TIMESTAMP(),UTC_TIMESTAMP());"]
    for record in records:
        validate(record)
        if 'purchase_id' not in record:
            continue
        pid, reader = record['purchase_id'], record['reader_id']
        lines += [f"INSERT IGNORE INTO purchase_recoveries (purchase_id,reader_id,owner_id,case_reference,proof_hash,journal_id,created_at) SELECT {pid},{reader},{record['owner_id']},'{record['case_reference']}','{record['proof_hash']}','{record['record_id']}','{datetime.fromisoformat(record['claimed_at']).strftime('%Y-%m-%d %H:%M:%S')}' FROM purchases WHERE id={pid} AND SHA2(transaction_id,256)='{record['transaction_hash']}';",
                  f"INSERT INTO purchase_recovery_claims (purchase_id,reader_id,recovery_id) SELECT {pid},{reader},a.id FROM purchase_recoveries a JOIN readers r ON r.id={reader} AND r.created_at='{birth(record)}' WHERE a.journal_id='{record['record_id']}' ON DUPLICATE KEY UPDATE reader_id=VALUES(reader_id),recovery_id=VALUES(recovery_id);"]
    lines.append('COMMIT;')
    if records:
        lines.append('ALTER TABLE readers AUTO_INCREMENT=' + str(max(r['reader_id'] for r in records) + 1) + ';')
    return '\n'.join(lines).encode()


def main():
    os.umask(0o077)
    os.environ.update(TMPDIR='/home/shelf/tmp', TMP='/home/shelf/tmp', TEMP='/home/shelf/tmp',
                      GNUPGHOME='/home/shelf/secrets/backup/gnupg')
    parser = argparse.ArgumentParser()
    parser.add_argument('action', choices=['initialize', 'append', 'status'])
    args = parser.parse_args()
    if args.action == 'initialize':
        initialize()
        print('Off-server encrypted deletion journal verified; no reader deleted.')
    elif args.action == 'append':
        import sys
        print(json.dumps(append(json.load(sys.stdin))))
    else:
        print(json.dumps({'records': len(current()), 'offserver_verified': True}))

if __name__ == '__main__':
    try:
        main()
    except Exception:
        raise SystemExit('Deletion journal unavailable; no reopening/deletion permitted. Details withheld.')
