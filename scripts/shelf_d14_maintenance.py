#!/usr/bin/env python3
"""Production-only approved D14 activation and retry/expiry schedule."""
import argparse
from datetime import datetime, timezone
import json
import os
from pathlib import Path
import tempfile
import shelf_deletion_journal as journal
import shelf_offsite_backup as backup
import shelf_retention as retention

CRON = '45 3 * * * PATH=/usr/local/bin:/usr/bin:/bin TMPDIR=/home/shelf/tmp TMP=/home/shelf/tmp TEMP=/home/shelf/tmp /usr/bin/python3 -B /home/shelf/apps/shelf/scripts/shelf_d14_maintenance.py run >> /home/shelf/apps/shelf/storage/logs/d14-maintenance.log 2>&1'
LEGACY = ('laravel', 'daily-backup', 'offsite-backup', 'play-price-sync', 'play-refunds', 'd14-maintenance')


def rotate():
    day = datetime.now(timezone.utc).strftime('%Y-%m-%d')
    for name in LEGACY:
        path = retention.LOGS / (name + '.log')
        if not path.exists():
            continue
        if path.is_symlink() or not path.is_file():
            raise RuntimeError('Unsafe routine log.')
        target = retention.LOGS / (name + '-legacy-' + day + '.log')
        # Conservatively date pre-policy logs on archival day. Never overwrite.
        if target.exists():
            continue
        path.chmod(0o600)
        path.rename(target)
        # Preserve live writers' inode? Cron opens new file on next invocation;
        # application daily logger opens dated files after promoted config.


def run():
    records = journal.current() # Cannot proceed if server-loss recovery journal is unavailable.
    with tempfile.TemporaryDirectory(prefix='shelf-d14-', dir='/home/shelf/tmp') as temp:
        path = Path(temp) / 'journal.json'
        path.write_text(json.dumps(records))
        path.chmod(0o600)
        result = backup.run(['php', 'artisan', 'shelf:d14-process', path], cwd=backup.PRODUCTION)
        print(result.decode().strip()) # Fixed counts/codes only, never identifiers.
    rotate()
    retention.execute(True)


def activate():
    # Fresh verified local backup in this same run, before private config/cron changes.
    print(backup.run(['python3', '-B', 'scripts/shelf_daily_backup.py'], cwd=backup.PRODUCTION).decode().strip())
    journal.initialize()
    import shelf_d14_baseline
    shelf_d14_baseline.baseline()
    retention.activate() # Mandatory dry-run before enabling any expiry.
    rotate()
    current = backup.run(['crontab', '-l']).decode()
    existing = [line for line in current.splitlines() if 'scripts/shelf_d14_maintenance.py run' in line]
    if existing and existing != [CRON]:
        raise RuntimeError('Unexpected D14 schedule; stop.')
    if not existing:
        with tempfile.TemporaryDirectory(prefix='shelf-d14-cron-', dir='/home/shelf/tmp') as temp:
            path = Path(temp) / 'crontab'
            path.write_text(current.rstrip('\n') + '\n' + CRON + '\n')
            backup.run(['crontab', path])
            if backup.run(['crontab', '-l']).decode() != path.read_text():
                raise RuntimeError('D14 schedule verification failed.')
    print('D14 encrypted journal, dry-run-first retention and 03:45 retries activated; other jobs preserved. No reader deleted.')


def main():
    os.umask(0o077)
    os.environ.update(TMPDIR='/home/shelf/tmp', TMP='/home/shelf/tmp', TEMP='/home/shelf/tmp',
                      GNUPGHOME='/home/shelf/secrets/backup/gnupg')
    if backup.APP != backup.PRODUCTION or (backup.PRODUCTION / '.shelf-staging').exists():
        raise RuntimeError('D14 maintenance requires promoted production code.')
    parser = argparse.ArgumentParser()
    parser.add_argument('action', choices=['activate', 'run'])
    if parser.parse_args().action == 'activate':
        activate()
    else:
        run()

if __name__ == '__main__':
    try:
        main()
    except Exception:
        raise SystemExit('D14 maintenance failed; no further action. Journal/expiry must be reviewed before recovery.')
