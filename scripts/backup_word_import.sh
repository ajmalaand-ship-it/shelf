#!/usr/bin/env bash
# OWNER-RUN: verified pre-migration backup. Never invokes migrations.
set -euo pipefail
export TMPDIR=/home/shelf/tmp TMP=/home/shelf/tmp TEMP=/home/shelf/tmp
mkdir -p -- "$TMPDIR"
cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.."
python3 - <<'PY'
import json
import os
import shutil
import sys
import tempfile
from datetime import datetime, timezone
from pathlib import Path

sys.dont_write_bytecode = True
sys.path.insert(0, 'scripts')
import shelf_daily_backup as backup

try:
    os.umask(0o077)
    settings = backup.read_settings(backup.APP)
    if settings['DB_DATABASE'] != 'shelf_app':
        raise RuntimeError('Unexpected database target')
    root = backup.backup_root(settings, backup.APP)
    if root != Path('/home/shelf/backups/shelf'):
        raise RuntimeError('Unexpected backup destination')
    root.mkdir(mode=0o700, parents=True, exist_ok=True)
    now = datetime.now(timezone.utc)
    destination = root / ('before-word-import-' + now.strftime('%Y%m%d-%H%M%S'))
    with tempfile.TemporaryDirectory(prefix='word-import-backup-', dir='/home/shelf/tmp') as directory:
        staging = Path(directory)
        backup.dump_database(staging, settings)
        backup.write_media(backup.APP, staging / 'media.zip')
        manifest = {
            'created_at': now.isoformat(), 'system': 'Shelf',
            'database': 'database.sql', 'database_sha256': backup.sha256(staging / 'database.sql'),
            'media': 'media.zip', 'media_sha256': backup.sha256(staging / 'media.zip'),
            'paths': ['app/' + name for name in backup.MEDIA_PATHS],
        }
        (staging / 'manifest.json').write_text(json.dumps(manifest, indent=2) + '\n')
        backup.verify(staging)
        shutil.copytree(staging, destination)
        backup.verify(destination)
        (destination / 'backup-note.txt').write_text('Before Task 5b migration. Verified backup: ' + str(destination) + '\n')
    print('Verified backup and path note: ' + str(destination))
except Exception:
    print('Backup failed. STOP: do not run the migration. Details withheld to protect credentials.', file=sys.stderr)
    sys.exit(1)
PY
