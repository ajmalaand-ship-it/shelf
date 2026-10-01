#!/usr/bin/env python3
"""Install only Shelf's dedicated Play sync runner after a fresh verified backup."""

import argparse
from datetime import datetime, timezone
import os
from pathlib import Path
import subprocess
import sys
import tempfile

sys.dont_write_bytecode = True
from shelf_daily_backup import verify

ENTRY = '* * * * * PATH=/usr/local/bin:/usr/bin:/bin /bin/bash /home/shelf/apps/shelf/scripts/run_play_price_sync.sh >> /home/shelf/apps/shelf/storage/logs/play-price-sync.log 2>&1'


def main():
    if (Path(__file__).resolve().parent.parent / '.shelf-staging').is_file():
        raise RuntimeError('Production scheduler installation is forbidden on staging.')
    parser = argparse.ArgumentParser()
    parser.add_argument('--after-backup', required=True)
    args = parser.parse_args()
    os.umask(0o077)
    backup = Path(args.after_backup)
    if backup.parent != Path('/home/shelf/backups/shelf') or backup.is_symlink() or backup.resolve() != backup:
        raise RuntimeError('Invalid backup path')
    stamp = datetime.strptime(backup.name, '%Y%m%d-%H%M%S').replace(tzinfo=timezone.utc)
    if not 0 <= (datetime.now(timezone.utc) - stamp).total_seconds() < 3600:
        raise RuntimeError('A fresh backup is required')
    verify(backup)
    result = subprocess.run(['crontab', '-l'], capture_output=True, text=True)
    if result.returncode not in (0, 1):
        raise RuntimeError('Could not read Shelf crontab')
    current = result.stdout if result.returncode == 0 else ''
    matches = [line for line in current.splitlines() if 'scripts/run_play_price_sync.sh' in line]
    if matches:
        if matches != [ENTRY]:
            raise RuntimeError('Unexpected existing Play sync entry')
        print('Dedicated Shelf Play sync cron already installed; other entries preserved.')
        return
    snapshot = backup / 'shelf-crontab-before-play-sync.txt'
    with snapshot.open('x', encoding='utf-8') as stream:
        stream.write(current)
    updated = current.rstrip('\n') + '\n' + ENTRY + '\n'
    with tempfile.TemporaryDirectory(prefix='shelf-play-cron-', dir='/home/shelf/tmp') as directory:
        path = Path(directory) / 'crontab'
        path.write_text(updated)
        subprocess.run(['crontab', str(path)], check=True, capture_output=True)
    installed = subprocess.run(['crontab', '-l'], check=True, capture_output=True, text=True).stdout
    if installed != updated:
        raise RuntimeError('Crontab verification failed; inspect before continuing')
    print('Dedicated Shelf Play sync cron installed and verified; daily backup preserved.')
    print(f'Previous Shelf crontab: {snapshot}')


if __name__ == '__main__':
    try:
        main()
    except Exception as error:
        print(f'Cron installation stopped ({type(error).__name__}); no further actions.')
        raise SystemExit(1)
