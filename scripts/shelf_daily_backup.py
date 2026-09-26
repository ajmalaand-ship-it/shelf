#!/usr/bin/python3
"""Back up Shelf, verify the artifacts, then retain the newest 14 backups."""

import fcntl
import hashlib
import json
import os
from pathlib import Path
import re
import shutil
import subprocess
import sys


ROOT = Path('/home/ajmalaand/backups/shelf-auto')
APP = Path(__file__).resolve().parent.parent
STAMP = re.compile(r'[0-9]{8}-[0-9]{6}')


def backup_folders(root):
    return sorted(
        (p for p in root.iterdir()
         if STAMP.fullmatch(p.name) and not p.is_symlink() and p.is_dir()),
        key=lambda p: p.name,
        reverse=True,
    )


def retain_backups(root):
    # Never traverse a symlink, including links contained within old backups.
    if root.is_symlink() or root.resolve() != root:
        raise RuntimeError('Backup root must be a real, canonical directory.')
    if not shutil.rmtree.avoids_symlink_attacks:
        raise RuntimeError('Safe recursive deletion is unavailable.')
    for folder in backup_folders(root)[14:]:
        shutil.rmtree(folder)


def verify(folder):
    manifest = json.loads((folder / 'manifest.json').read_text())
    for name, key in [('database.sql', 'database_sha256'), ('media.zip', 'media_sha256')]:
        with (folder / name).open('rb') as stream:
            digest = hashlib.sha256()
            for chunk in iter(lambda: stream.read(1024 * 1024), b''):
                digest.update(chunk)
        if digest.hexdigest() != manifest[key]:
            raise RuntimeError(f'Checksum mismatch: {name}')
    # Inspect structure and completion without printing private database contents.
    schema = data = complete = False
    with (folder / 'database.sql').open('rb') as stream:
        for line in stream:
            schema |= line.startswith(b'CREATE TABLE ')
            data |= line.startswith(b'INSERT INTO ')
            complete |= line.startswith(b'-- Dump completed on ')
    if not (schema and data and complete):
        raise RuntimeError('SQL dump lacks schema, data, or completion marker.')
    subprocess.run(['/usr/bin/unzip', '-t', str(folder / 'media.zip')],
                   check=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)


def main():
    os.umask(0o077)
    if ROOT.is_symlink() or ROOT.resolve() != ROOT:
        raise RuntimeError('Refusing a redirected backup root.')
    ROOT.mkdir(mode=0o700, parents=True, exist_ok=True)
    with (ROOT / '.backup.lock').open('a') as lock:
        fcntl.flock(lock, fcntl.LOCK_EX | fcntl.LOCK_NB)
        before = set(backup_folders(ROOT))
        # Runtime override also works when Laravel configuration is cached.
        php = r'''
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
config(['poetry.backup_path' => $argv[1]]);
exit($kernel->call('poetry:backup'));
'''
        result = subprocess.run(['/usr/local/bin/php', '-r', php, str(ROOT)],
                                cwd=APP, stdout=subprocess.DEVNULL,
                                stderr=subprocess.DEVNULL)
        if result.returncode:
            raise RuntimeError('Backup command failed; retention was not run.')
        created = set(backup_folders(ROOT)) - before
        if len(created) != 1:
            raise RuntimeError('Expected exactly one new backup folder.')
        folder = created.pop()
        verify(folder)
        retain_backups(ROOT)
        print(f'Verified SQL, ZIP (unzip -t), and SHA-256: {folder}', flush=True)
        print(f'Retention complete: {len(backup_folders(ROOT))} backup folders.', flush=True)


if __name__ == '__main__':
    try:
        main()
    except Exception as error:
        # Do not print command output or exception details that may contain secrets.
        print(f'Shelf backup failed ({type(error).__name__}); inspect privately. '
              'No further actions taken.', file=sys.stderr)
        sys.exit(1)
