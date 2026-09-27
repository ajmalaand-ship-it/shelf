#!/usr/bin/python3
"""Back up Shelf, verify the artifacts, then retain the newest 14 backups."""

from datetime import datetime, timezone
import fcntl
import hashlib
import json
import os
from pathlib import Path
import re
import shutil
import subprocess
import sys
import tempfile
import zipfile


APP = Path(__file__).resolve().parent.parent
STAMP = re.compile(r'[0-9]{8}-[0-9]{6}')
MEDIA_PATHS = ('public', 'private', 'source')


def read_settings(app):
    # Parse the actual .env with Laravel's dotenv library, without booting Laravel
    # or using cached configuration / inherited database credentials.
    php = r'''
require $argv[1].'/vendor/autoload.php';
$values = Dotenv\Dotenv::parse(file_get_contents($argv[1].'/.env'));
$keys = ['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME',
         'DB_PASSWORD', 'DB_SOCKET', 'POETRY_BACKUP_PATH'];
echo json_encode(array_intersect_key($values, array_flip($keys)), JSON_THROW_ON_ERROR);
'''
    result = subprocess.run(
        ['php', '-d', 'display_errors=stderr', '-r', php, str(app)],
        check=True, capture_output=True, text=True,
    )
    settings = json.loads(result.stdout)
    required = ('DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'POETRY_BACKUP_PATH')
    if settings.get('DB_CONNECTION') != 'mysql' or any(
        not settings.get(key) for key in required
    ) or 'DB_PASSWORD' not in settings:
        raise RuntimeError('Missing or unsupported backup configuration.')
    if not settings['DB_PORT'].isdigit() or not 1 <= int(settings['DB_PORT']) <= 65535:
        raise RuntimeError('Invalid database port.')
    if any('${' in value for value in settings.values()):
        raise RuntimeError('Backup settings must use explicit values in .env.')
    return settings


def backup_root(settings, app):
    root = Path(settings['POETRY_BACKUP_PATH'])
    if not root.is_absolute() or root.is_symlink() or root.resolve() != root:
        raise RuntimeError('Backup root must be a real, absolute canonical directory.')
    # Keep backups and retention away from application data and filesystem roots.
    if root == Path(root.anchor) or root == app or app in root.parents or root in app.parents:
        raise RuntimeError('Backup root must be separate from the application.')
    return root


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


def option_value(value):
    # MySQL option-file escaping; credentials never appear in process arguments.
    return '"' + value.replace('\\', '\\\\').replace('"', '\\"').replace(
        '\n', '\\n').replace('\r', '\\r').replace('\t', '\\t') + '"'


def dump_database(folder, settings):
    credentials = folder / '.mysql.cnf'
    options = {'host': settings['DB_HOST'], 'port': settings['DB_PORT'],
               'user': settings['DB_USERNAME'], 'password': settings['DB_PASSWORD']}
    if settings.get('DB_SOCKET'):
        options['socket'] = settings['DB_SOCKET']
    try:
        with credentials.open('x', encoding='utf-8') as stream:
            os.chmod(credentials, 0o600)
            stream.write('[client]\n')
            for key, value in options.items():
                stream.write(f'{key}={option_value(value)}\n')
        env = os.environ.copy()
        env.pop('MYSQL_PWD', None)
        with (folder / 'database.sql').open('xb') as stream:
            subprocess.run(
                ['mysqldump', f'--defaults-file={credentials}', '--single-transaction',
                 '--quick', '--skip-lock-tables', '--skip-add-locks', '--comments',
                 '--dump-date', '--', settings['DB_DATABASE']],
                check=True, stdout=stream, stderr=subprocess.DEVNULL, env=env,
            )
    finally:
        credentials.unlink(missing_ok=True)


def write_media(app, archive):
    storage = app / 'storage'
    with zipfile.ZipFile(archive, 'x', compression=zipfile.ZIP_DEFLATED,
                         allowZip64=True) as output:
        for name in MEDIA_PATHS:
            source = storage / 'app' / name
            if not source.is_dir() or source.resolve() != source:
                raise RuntimeError('Required media directory is missing or redirected.')
            output.write(source, source.relative_to(storage).as_posix() + '/')
            for directory, dirs, files in os.walk(source, followlinks=False):
                for entry in sorted(dirs + files):
                    path = Path(directory) / entry
                    if path.is_symlink() or not (path.is_dir() or path.is_file()):
                        raise RuntimeError('Unsupported media entry; refusing an incomplete backup.')
                    output.write(path, path.relative_to(storage).as_posix())


def sha256(path):
    digest = hashlib.sha256()
    with path.open('rb') as stream:
        for chunk in iter(lambda: stream.read(1024 * 1024), b''):
            digest.update(chunk)
    return digest.hexdigest()


def verify(folder):
    manifest = json.loads((folder / 'manifest.json').read_text())
    for name, key in [('database.sql', 'database_sha256'), ('media.zip', 'media_sha256')]:
        if sha256(folder / name) != manifest[key]:
            raise RuntimeError(f'Checksum mismatch: {name}')
    # Inspect completion without printing private database contents. Empty tables
    # are valid: do not require INSERT statements for a successful backup.
    with (folder / 'database.sql').open('rb') as stream:
        complete = any(line.startswith(b'-- Dump completed on ') for line in stream)
    if not complete:
        raise RuntimeError('SQL dump lacks a completion marker.')
    subprocess.run(['unzip', '-t', str(folder / 'media.zip')],
                   check=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)


def main():
    os.umask(0o077)
    settings = read_settings(APP)
    root = backup_root(settings, APP)
    root.mkdir(mode=0o700, parents=True, exist_ok=True)
    lock_fd = os.open(root / '.backup.lock', os.O_CREAT | os.O_WRONLY | os.O_NOFOLLOW, 0o600)
    with os.fdopen(lock_fd, 'a') as lock:
        fcntl.flock(lock, fcntl.LOCK_EX | fcntl.LOCK_NB)
        now = datetime.now(timezone.utc)
        folder = root / now.strftime('%Y%m%d-%H%M%S')
        if folder.exists() or folder.is_symlink():
            raise RuntimeError('Backup timestamp already exists.')
        # Failed/incomplete backups never enter the retention set.
        with tempfile.TemporaryDirectory(prefix='.incomplete-', dir=root) as temporary:
            staging = Path(temporary)
            dump_database(staging, settings)
            write_media(APP, staging / 'media.zip')
            manifest = {
                'created_at': now.isoformat(), 'system': 'Shelf',
                'database': 'database.sql', 'database_sha256': sha256(staging / 'database.sql'),
                'media': 'media.zip', 'media_sha256': sha256(staging / 'media.zip'),
                'paths': ['app/' + name for name in MEDIA_PATHS],
            }
            (staging / 'manifest.json').write_text(json.dumps(manifest, indent=2) + '\n')
            verify(staging)
            staging.rename(folder)
        retain_backups(root)
        print(f'Verified SQL, ZIP (unzip -t), and SHA-256: {folder}', flush=True)
        print(f'Retention complete: {len(backup_folders(root))} backup folders.', flush=True)


if __name__ == '__main__':
    try:
        main()
    except Exception as error:
        # Do not print command output or exception details that may contain secrets.
        print(f'Shelf backup failed ({type(error).__name__}); inspect privately. '
              'No further actions taken.', file=sys.stderr)
        sys.exit(1)
