#!/usr/bin/env python3
"""Shelf-only encrypted copy and isolated recovery; never sync or delete remotely."""
import argparse
import configparser
from datetime import datetime, timezone
import fcntl
import hashlib
import json
import os
from pathlib import Path
import re
import secrets
import shutil
import subprocess
import sys
import tarfile
import tempfile
import time
import zipfile
import urllib.request
import urllib.error
import urllib.parse
import selectors

sys.dont_write_bytecode = True
import shelf_daily_backup as local

APP = Path(__file__).resolve().parent.parent
PRODUCTION = Path('/home/shelf/apps/shelf')
PRIVATE = Path('/home/shelf/secrets/backup')
ROOT = Path('/home/shelf/backups/shelf/offsite')
RCLONE = Path('/home/shelf/tools/rclone-v1.75.1/rclone')
REMOTE = 'shelf_onedrive:Shelf-Backups'
KEY = PRIVATE / 'recovery-key.secret'
CONFIG = PRIVATE / 'rclone.conf'
NAME = re.compile(r'shelf-(production|staging)-[0-9]{8}T[0-9]{6}Z-[0-9a-f]{16}\.tar\.gpg')


def run(args, **kwargs):
    result = subprocess.run(list(map(str, args)), capture_output=True, **kwargs)
    if result.returncode:
        raise RuntimeError('Command failed; private process output withheld.')
    return result.stdout


def private_file(path):
    if path.is_symlink() or not path.is_file() or path.stat().st_mode & 0o077:
        raise RuntimeError('Missing or insecure private file.')


def write_json(path, value):
    temporary = path.with_suffix('.new')
    with temporary.open('w') as output:
        os.chmod(temporary, 0o600)
        json.dump(value, output, indent=2)
        output.write('\n')
    temporary.replace(path)


def rclone(*args):
    private_file(CONFIG)
    return run([RCLONE, '--config', CONFIG, '--log-level', 'ERROR',
                '--retries', '3', '--low-level-retries', '5', '--contimeout', '20s',
                '--timeout', '5m', '--stats', '0', *args])


def prepare():
    PRIVATE.mkdir(mode=0o700, parents=True, exist_ok=True)
    ROOT.mkdir(mode=0o700, parents=True, exist_ok=True)
    for path in (PRIVATE, ROOT):
        if path.is_symlink() or path.resolve() != path or path.stat().st_mode & 0o077:
            raise RuntimeError('Unsafe private backup directory.')
    (PRIVATE / 'gnupg').mkdir(mode=0o700, exist_ok=True)
    if not KEY.exists():
        with KEY.open('x') as output:
            output.write(secrets.token_hex(32) + '\n')
    private_file(KEY)
    if not (PRIVATE / 'settings.json').exists():
        write_json(PRIVATE / 'settings.json', {'remote': REMOTE, 'retention': None,
                                               'schedule_enabled': False})
    print('Prepared private key and configuration; no secrets displayed. Scheduling disabled.')


class OneDriveSetupError(RuntimeError):
    """Only fixed, credential-free messages may be exposed to the owner."""


def graph_get(token, path):
    request = urllib.request.Request('https://graph.microsoft.com/v1.0/' + path,
                                     headers={'Authorization': 'Bearer ' + token})
    try:
        with urllib.request.urlopen(request, timeout=20) as response:
            return json.load(response)
    except urllib.error.HTTPError as error:
        if error.code == 401:
            raise OneDriveSetupError('Saved Microsoft credential needs refresh; no new sign-in attempted.') from None
        raise OneDriveSetupError('Microsoft drive verification failed (HTTP ' + str(error.code) +
                                '); stopped without retrying or changing credentials.') from None
    except (OSError, ValueError):
        raise OneDriveSetupError('Microsoft drive verification timed out or returned invalid data; stopped without retrying.') from None


def finish_authorization():
    private_file(CONFIG)
    parser = configparser.ConfigParser(interpolation=None)
    parser.read(CONFIG)
    section = parser['shelf_onedrive']
    try:
        token = json.loads(section.get('token', '{}'))['access_token']
    except (KeyError, ValueError):
        raise OneDriveSetupError('No saved OAuth credential; owner browser authorization is required.') from None
    # /me/drives can include handles whose /root fails. Microsoft's default
    # /me/drive is authoritative for the owner's existing OneDrive.
    drive = graph_get(token, 'me/drive')
    drive_id, drive_type = drive.get('id'), drive.get('driveType')
    if not drive_id or drive_type not in ('personal', 'business'):
        raise OneDriveSetupError('Default OneDrive ID/type is missing; no configuration changed.')
    existing = section.get('drive_id')
    if existing and existing != drive_id:
        raise OneDriveSetupError('Saved drive differs from the default OneDrive; owner selection required, credentials preserved.')
    root = graph_get(token, 'drives/' + urllib.parse.quote(drive_id, safe='') + '/root')
    parent = root.get('parentReference', {})
    quota_value = drive.get('quota', {})
    if ('folder' not in root or not root.get('id') or parent.get('driveId') != drive_id
            or parent.get('driveType') != drive_type):
        raise OneDriveSetupError('Default drive root identity could not be verified; no configuration changed.')
    if not isinstance(quota_value.get('remaining'), int) or quota_value['remaining'] < 0:
        raise OneDriveSetupError('Default drive quota could not be verified; no configuration changed.')
    section['drive_id'], section['drive_type'] = drive_id, drive_type
    temporary = CONFIG.with_suffix('.verified-new')
    with temporary.open('x') as output:
        os.chmod(temporary, 0o600)
        parser.write(output)
    temporary.replace(CONFIG)
    print('Saved OAuth preserved; default ' + drive_type + ' OneDrive root verified. Quota bytes: ' +
          json.dumps({k: quota_value[k] for k in ('total', 'used', 'remaining') if k in quota_value}))


def authorize():
    prepare()
    if CONFIG.exists():
        parser = configparser.ConfigParser(interpolation=None)
        parser.read(CONFIG)
        if parser.get('shelf_onedrive', 'token', fallback=''):
            finish_authorization()
            return
    # Filter output: forward only the local browser link, never provider errors,
    # configuration dumps or tokens. Stop on the first configuration error.
    process = subprocess.Popen([str(RCLONE), '--config', str(CONFIG), 'config', 'create',
        'shelf_onedrive', 'onedrive', 'config_is_local', 'true',
        'auth_no_open_browser', 'true', 'access_scopes', 'Files.ReadWrite offline_access',
        '--no-output', '--retries', '1', '--low-level-retries', '1',
        '--contimeout', '10s', '--timeout', '20s'], stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT, stdin=subprocess.DEVNULL)
    deadline, buffer, shown = time.monotonic() + 300, '', False
    try:
        with selectors.DefaultSelector() as selector:
            selector.register(process.stdout, selectors.EVENT_READ)
            while process.poll() is None:
                if time.monotonic() >= deadline:
                    raise OneDriveSetupError('Browser authorization timed out; saved credentials preserved.')
                for key, _ in selector.select(timeout=1):
                    buffer = (buffer + os.read(key.fd, 4096).decode(errors='replace'))[-16384:]
                    link = re.search(r'http://127\.0\.0\.1:53682/[^\s]+', buffer)
                    if link and not shown:
                        print('Open on your PC: ' + link.group(0), flush=True)
                        shown = True
                    if 'Failed to query' in buffer or 'ObjectHandle is Invalid' in buffer:
                        process.terminate()
                        process.wait(timeout=5)
                        finish_authorization()
                        return
            if process.returncode:
                raise OneDriveSetupError('Browser setup stopped; saved credentials preserved. Resume authorize to verify them.')
        finish_authorization()
    finally:
        if process.poll() is None:
            process.kill()
            process.wait()


def quota():
    value = json.loads(rclone('about', 'shelf_onedrive:', '--json'))
    if not isinstance(value.get('free'), int) or value['free'] < 0:
        raise RuntimeError('OneDrive did not report usable free storage.')
    return {k: value[k] for k in ('total', 'used', 'free') if k in value}


def crypt(source, target, decrypt=False):
    private_file(KEY)
    if target.exists():
        raise RuntimeError('Encryption/decryption target already exists.')
    # The key is read by file descriptor, never an argument, environment or log.
    with KEY.open('rb') as key:
        run(['gpg', '--no-options', '--batch', '--yes', '--no-symkey-cache',
             '--pinentry-mode', 'loopback', '--passphrase-fd', str(key.fileno()),
             '--output', target, *(['--decrypt'] if decrypt else
             ['--symmetric', '--cipher-algo', 'AES256', '--compress-algo', 'none']), source],
            pass_fds=(key.fileno(),))


def tree_files(path, exclude=()):
    if path.is_symlink() or not path.is_dir():
        raise RuntimeError('Unexpected recovery directory.')
    for directory, dirs, files in os.walk(path, followlinks=False):
        if Path(directory) == path:
            dirs[:] = [name for name in dirs if name not in exclude]
        for name in sorted(dirs + files):
            entry = Path(directory) / name
            if entry.is_symlink() or not (entry.is_dir() or entry.is_file()):
                raise RuntimeError('Unsupported recovery file.')
            if entry.is_file():
                yield entry


def add_file(archive, path, name, hashes):
    if path.is_symlink() or not path.is_file():
        raise RuntimeError('Required recovery file missing or redirected.')
    before = local.sha256(path)
    archive.add(path, arcname=name, recursive=False)
    if local.sha256(path) != before:
        raise RuntimeError('File changed while being backed up; retry later.')
    hashes[name] = before


def build_package(app, workspace, output_root):
    stage = (app / '.shelf-staging').is_file()
    if app.resolve() != PRODUCTION and not stage:
        raise RuntimeError('Only established Shelf production/staging may be backed up.')
    settings = local.read_settings(app)
    local.dump_database(workspace, settings, full_objects=True)
    local.write_media(app, workspace / 'media.zip')
    manifest = {'database_sha256': local.sha256(workspace / 'database.sql'),
                'media_sha256': local.sha256(workspace / 'media.zip')}
    (workspace / 'manifest.json').write_text(json.dumps(manifest))
    local.verify(workspace)
    if stage:
        revision = (app / 'REVISION').read_text().strip()
        code_repository = PRODUCTION
    else:
        revision = run(['git', 'rev-parse', 'HEAD'], cwd=app).decode().strip()
        code_repository = app
    with (workspace / 'code.tar').open('xb') as output:
        subprocess.run(['git', 'archive', revision], cwd=code_repository,
                       stdout=output, stderr=subprocess.DEVNULL, check=True)
    with zipfile.ZipFile(workspace / 'media.zip') as media:
        media_hashes = {i.filename: hashlib.sha256(media.read(i)).hexdigest()
                        for i in media.infolist() if not i.is_dir()}
    hashes = {}
    archive_path = workspace / 'recovery.tar'
    with tarfile.open(archive_path, 'w') as archive:
        for name in ('database.sql', 'media.zip', 'manifest.json', 'code.tar'):
            add_file(archive, workspace / name, name, hashes)
        env = app / '.env'
        if stage:
            env = env.resolve()
            if env != Path('/home/shelf/staging-runtime/.env'):
                raise RuntimeError('Unexpected staging environment.')
        add_file(archive, env, 'configuration/app.env', hashes)
        secret_root = Path('/home/shelf/secrets/staging') if stage else Path('/home/shelf/secrets')
        for file in tree_files(secret_root, () if stage else ('backup', 'staging')):
            relative = file.relative_to(secret_root)
            if not stage and relative.parts[0] in ('backup', 'staging'):
                continue  # recovery key/OAuth are separate; staging is a separate backup
            add_file(archive, file, 'configuration/secrets/' + relative.as_posix(), hashes)
        if not stage:
            for file in tree_files(Path('/home/shelf/android/keys')):
                add_file(archive, file, 'configuration/android-keys/' + file.name, hashes)
            add_file(archive, app / 'public/.htaccess', 'configuration/public.htaccess', hashes)
            crontab = run(['crontab', '-l'])
            (workspace / 'crontab').write_bytes(crontab)
            add_file(archive, workspace / 'crontab', 'configuration/crontab', hashes)
        record = {'format': 1, 'system': 'Shelf', 'environment': 'staging' if stage else 'production',
                  'revision': revision, 'created_at': datetime.now(timezone.utc).isoformat(),
                  'files': hashes, 'media_files': media_hashes,
                  'exclusions': ['server-global configuration', 'TLS (reissue)', 'runtime caches/logs',
                                 'OneDrive OAuth and recovery key (retain separately)']}
        (workspace / 'recovery-manifest.json').write_text(json.dumps(record, indent=2))
        archive.add(workspace / 'recovery-manifest.json', arcname='recovery-manifest.json')
    name = 'shelf-' + record['environment'] + '-' + datetime.now(timezone.utc).strftime('%Y%m%dT%H%M%SZ')
    target = output_root / (name + '-' + secrets.token_hex(8) + '.tar.gpg')
    crypt(archive_path, target)
    write_json(target.with_suffix('.json'), {'sha256': local.sha256(target), 'bytes': target.stat().st_size,
                                           'environment': record['environment'], 'revision': revision})
    return target


def valid_package(path):
    if not NAME.fullmatch(path.name) or path.is_symlink() or path.resolve().parent != ROOT:
        raise RuntimeError('Expected encrypted Shelf package in the dedicated offsite directory.')
    private_file(path)
    info = json.loads(path.with_suffix('.json').read_text())
    if info['sha256'] != local.sha256(path) or info['bytes'] != path.stat().st_size:
        raise RuntimeError('Encrypted package checksum mismatch.')
    return info


def upload(path):
    info = valid_package(path)
    space = quota()
    if space['free'] < info['bytes'] + 100 * 1024**2:
        raise RuntimeError('Insufficient OneDrive free space; local backups retained.')
    rclone('copyto', path, REMOTE + '/' + path.name, '--immutable')
    rclone('copyto', path.with_suffix('.json'), REMOTE + '/' + path.with_suffix('.json').name, '--immutable')
    write_json(ROOT / 'last-upload.json', {'package': path.name, 'sha256': info['sha256'],
                                          'uploaded_at': datetime.now(timezone.utc).isoformat(), 'quota': space})


def safe_extract(archive, target):
    members = archive.getmembers()
    seen = set()
    for member in members:
        name = member.name
        if name in seen or Path(name).is_absolute() or '..' in Path(name).parts or not (member.isfile() or member.isdir()):
            raise RuntimeError('Unsafe recovery archive member.')
        seen.add(name)
    archive.extractall(target, members=members, filter='data')


def restore_files(encrypted, expected, workspace):
    if local.sha256(encrypted) != expected:
        raise RuntimeError('Downloaded ciphertext differs from uploaded backup.')
    crypt(encrypted, workspace / 'recovery.tar', decrypt=True)
    target = workspace / 'restored'
    target.mkdir(mode=0o700)
    with tarfile.open(workspace / 'recovery.tar') as archive:
        safe_extract(archive, target)
    manifest = json.loads((target / 'recovery-manifest.json').read_text())
    for name, digest in manifest['files'].items():
        if Path(name).is_absolute() or '..' in Path(name).parts or local.sha256(target / name) != digest:
            raise RuntimeError('Restored file failed its checksum.')
    local.verify(target)
    with zipfile.ZipFile(target / 'media.zip') as media:
        for info in media.infolist():
            if Path(info.filename).is_absolute() or '..' in Path(info.filename).parts or (info.external_attr >> 16) & 0o170000 == 0o120000:
                raise RuntimeError('Unsafe media archive member.')
        media.extractall(target / 'storage')
    for name, digest in manifest['media_files'].items():
        if Path(name).is_absolute() or '..' in Path(name).parts or local.sha256(target / 'storage' / name) != digest:
            raise RuntimeError('Restored source/media differs.')
    (target / 'code').mkdir()
    with tarfile.open(target / 'code.tar') as archive:
        safe_extract(archive, target / 'code')
    return target, manifest


def restore_database(target, workspace):
    # Disposable server, no TCP port and no production/staging credentials.
    data = workspace / 'mysql'
    data.mkdir(mode=0o700)
    socket = workspace / 'mysql.sock'
    run(['mariadb-install-db', '--no-defaults', '--datadir=' + str(data),
         '--auth-root-authentication-method=normal', '--skip-test-db'])
    process = subprocess.Popen(['mariadbd', '--no-defaults', '--datadir=' + str(data),
                                '--socket=' + str(socket), '--skip-networking', '--event-scheduler=OFF', '--skip-slave-start',
                                '--pid-file=' + str(workspace / 'mysql.pid'),
                                '--log-error=' + str(workspace / 'mysql.log')],
                               stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    client = ['mysql', '--no-defaults', '--protocol=SOCKET', '--socket=' + str(socket), '-u', 'root']
    try:
        for _ in range(100):
            if process.poll() is not None:
                raise RuntimeError('Isolated MariaDB stopped.')
            if socket.exists() and subprocess.run(client + ['-e', 'SELECT 1'], stdout=subprocess.DEVNULL,
                                                  stderr=subprocess.DEVNULL).returncode == 0:
                break
            time.sleep(0.1)
        else:
            raise RuntimeError('Isolated MariaDB startup timed out.')
        run(client + ['-e', 'CREATE DATABASE shelf_recovery CHARACTER SET utf8mb4;'])
        with (target / 'database.sql').open('rb') as sql:
            run(client + ['shelf_recovery'], stdin=sql)
        # Re-dump canonical rows/schema and compare with the original dump. Header
        # timestamps/version metadata are omitted; private data never leaves here.
        canonical = run(['mysqldump', '--no-defaults', '--protocol=SOCKET', '--socket=' + str(socket),
                         '-u', 'root', '--skip-comments', '--skip-add-locks', '--skip-lock-tables',
                         '--routines', '--events', '--triggers',
                         '--', 'shelf_recovery'])
        original = (target / 'database.sql').read_bytes()
        normalize = lambda content: b'\n'.join(line for line in content.splitlines()
                                              if line and not line.startswith(b'--'))
        if normalize(canonical) != normalize(original):
            raise RuntimeError('Recovered database differs from backup schema/rows.')
        tables = run(client + ['-N', '-e', 'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema="shelf_recovery";'])
        return int(tables.strip())
    finally:
        process.terminate()
        try:
            process.wait(timeout=30)
        except subprocess.TimeoutExpired:
            process.kill()
            process.wait()


def drill(path):
    info = valid_package(path)
    with tempfile.TemporaryDirectory(prefix='shelf-recover-', dir='/home/shelf/tmp') as temporary:
        workspace = Path(temporary)
        downloaded = workspace / path.name
        rclone('copyto', REMOTE + '/' + path.name, downloaded, '--immutable')
        target, manifest = restore_files(downloaded, info['sha256'], workspace)
        tables = restore_database(target, workspace)
        evidence = {'package': path.name, 'sha256': info['sha256'], 'revision': manifest['revision'],
                    'environment': manifest['environment'], 'restored_at': datetime.now(timezone.utc).isoformat(),
                    'download_checksum': True, 'decryption': True, 'database_schema_rows_identical': True,
                    'table_count': tables, 'media_file_count': len(manifest['media_files']),
                    'configuration_checksums': True, 'code_extracted': True, 'isolated_socket_no_network': True}
        write_json(ROOT / ('recovery-' + path.name + '.json'), evidence)
        print(json.dumps(evidence, indent=2))


def local_drill(path):
    info = valid_package(path)
    with tempfile.TemporaryDirectory(prefix='shelf-recover-', dir='/home/shelf/tmp') as temporary:
        workspace = Path(temporary)
        target, manifest = restore_files(path, info['sha256'], workspace)
        tables = restore_database(target, workspace)
        print('PASS: LOCAL ONLY encrypted recovery; ' + str(tables) + ' tables, '
              + str(len(manifest['media_files'])) + ' media/source files, config/code hashes. '
              'OneDrive recovery remains unverified.')


def report_failure():
    write_json(ROOT / 'failure.json', {'failed_at': datetime.now(timezone.utc).isoformat(),
                                      'message': 'Shelf off-server backup failed. Inspect status privately; local backups preserved.'})
    # The task authorizes backup failure reporting. Only a generic alert is sent
    # to Shelf's marked owner, never reader addresses or process/provider output.
    code = r'''require $argv[1].'/vendor/autoload.php';
$app=require $argv[1].'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo App\Models\User::where('is_owner',true)->sole()->email;'''
    address = run(['php', '-r', code, PRODUCTION]).decode().strip()
    if not re.fullmatch(r'[A-Za-z0-9.!#$%&\'*+/=?^_`{|}~-]+@[A-Za-z0-9.-]+', address):
        raise RuntimeError('Invalid owner alert address.')
    message = ('To: ' + address + '\nSubject: Shelf backup needs attention\n'
               'Content-Type: text/plain; charset=utf-8\n\n'
               'Shelf could not finish its encrypted OneDrive backup/recovery check. '
               'Existing local backups remain. Ask Codex to inspect '
               '/home/shelf/backups/shelf/offsite/failure.json and last-upload.json privately.\n')
    run(['/usr/sbin/sendmail', '-t'], input=message.encode())


def scheduled():
    if APP != PRODUCTION or (APP / '.shelf-staging').exists():
        raise RuntimeError('Offsite scheduling is production-only.')
    settings = json.loads((PRIVATE / 'settings.json').read_text())
    if settings.get('schedule_enabled') is not True or settings.get('retention') is None:
        raise RuntimeError('Connection, quota/retention and recovery gates are incomplete.')
    lock_fd = os.open(ROOT / '.lock', os.O_CREAT | os.O_WRONLY | os.O_NOFOLLOW, 0o600)
    with os.fdopen(lock_fd, 'a') as lock:
        fcntl.flock(lock, fcntl.LOCK_EX | fcntl.LOCK_NB)
        try:
            with tempfile.TemporaryDirectory(prefix='shelf-offsite-', dir='/home/shelf/tmp') as temporary:
                package = build_package(APP, Path(temporary), ROOT)
            upload(package)
            drill(package)
            write_json(ROOT / 'last-success.json', {'package': package.name,
                'finished_at': datetime.now(timezone.utc).isoformat(), 'recovery_verified': True})
        except Exception:
            report_failure()
            raise


CRON = '30 3 * * * PATH=/usr/local/bin:/usr/bin:/bin TMPDIR=/home/shelf/tmp TMP=/home/shelf/tmp TEMP=/home/shelf/tmp /usr/bin/python3 -B /home/shelf/apps/shelf/scripts/shelf_offsite_backup.py scheduled >> /home/shelf/apps/shelf/storage/logs/offsite-backup.log 2>&1'


def install_schedule():
    if APP != PRODUCTION:
        raise RuntimeError('Scheduler installation requires promoted production code.')
    settings = json.loads((PRIVATE / 'settings.json').read_text())
    evidence = list(ROOT.glob('recovery-shelf-production-*.json'))
    if settings.get('retention') is None or not evidence:
        raise RuntimeError('Quota-based retention and production recovery evidence are required.')
    latest = max(evidence, key=lambda item: item.stat().st_mtime)
    record = json.loads(latest.read_text())
    if not record.get('database_schema_rows_identical') or not record.get('download_checksum'):
        raise RuntimeError('Incomplete recovery evidence.')
    # Fresh existing local backup first; existing local retention is unchanged.
    result = run(['python3', '-B', 'scripts/shelf_daily_backup.py'], cwd=PRODUCTION).decode()
    saved = Path(next(line.split(': ', 1)[1] for line in result.splitlines()
                      if line.startswith('Verified SQL, ZIP (unzip -t), and SHA-256: ')))
    current = run(['crontab', '-l']).decode()
    matches = [line for line in current.splitlines() if 'scripts/shelf_offsite_backup.py scheduled' in line]
    if matches and matches != [CRON]:
        raise RuntimeError('Unexpected existing offsite cron; stop.')
    if not matches:
        (saved / 'shelf-crontab-before-offsite.txt').write_text(current)
        updated = current.rstrip('\n') + '\n' + CRON + '\n'
        with tempfile.TemporaryDirectory(prefix='shelf-offsite-cron-', dir='/home/shelf/tmp') as temp:
            file = Path(temp) / 'crontab'
            file.write_text(updated)
            run(['crontab', file])
        if run(['crontab', '-l']).decode() != updated:
            raise RuntimeError('Cron verification failed; stop.')
    settings['schedule_enabled'] = True
    write_json(PRIVATE / 'settings.json', settings)
    print('03:30 offsite backup/recovery schedule verified; other jobs preserved. Backup: ' + str(saved))


def recover(path, destination):
    # For a replacement server: copy the encrypted backup and its secret-free JSON
    # from OneDrive, supply the separately retained key at KEY, and recover only
    # into a NEW private directory. No application is booted or live DB contacted.
    if destination is None or destination.exists() or destination.is_symlink() or destination.parent.resolve() != Path('/home/shelf/tmp'):
        raise RuntimeError('Recovery destination must be a new directory directly under /home/shelf/tmp.')
    if not NAME.fullmatch(path.name) or path.is_symlink():
        raise RuntimeError('Invalid recovery package.')
    info = json.loads(path.with_suffix('.json').read_text())
    with tempfile.TemporaryDirectory(prefix='shelf-recover-', dir='/home/shelf/tmp') as temporary:
        workspace = Path(temporary)
        restored, manifest = restore_files(path, info['sha256'], workspace)
        tables = restore_database(restored, workspace)
        shutil.copytree(restored, destination)
        destination.chmod(0o700)
    print('Isolated recovery verified and retained at ' + str(destination) + '; '
          + str(tables) + ' tables. No live application/database changed.')


def main():
    os.umask(0o077)
    os.environ.update(TMPDIR='/home/shelf/tmp', TMP='/home/shelf/tmp', TEMP='/home/shelf/tmp',
                      GNUPGHOME='/home/shelf/secrets/backup/gnupg')
    parser = argparse.ArgumentParser()
    parser.add_argument('action', choices=['prepare', 'authorize', 'quota', 'package', 'upload', 'drill', 'local-drill', 'scheduled', 'install-schedule', 'recover'])
    parser.add_argument('--package', type=Path)
    parser.add_argument('--destination', type=Path)
    args = parser.parse_args()
    if args.action == 'prepare':
        prepare()
    elif args.action == 'authorize':
        authorize()
    elif args.action == 'quota':
        print(json.dumps(quota(), indent=2))
    elif args.action == 'scheduled':
        scheduled()
    elif args.action == 'install-schedule':
        install_schedule()
    elif args.action == 'recover':
        if args.package is None:
            parser.error('--package is required')
        recover(args.package, args.destination)
    elif args.action == 'package':
        prepare()
        lock_fd = os.open(ROOT / '.lock', os.O_CREAT | os.O_WRONLY | os.O_NOFOLLOW, 0o600)
        with os.fdopen(lock_fd, 'a') as lock:
            fcntl.flock(lock, fcntl.LOCK_EX | fcntl.LOCK_NB)
            with tempfile.TemporaryDirectory(prefix='shelf-offsite-', dir='/home/shelf/tmp') as temporary:
                print(build_package(APP, Path(temporary), ROOT))
    else:
        if args.package is None:
            parser.error('--package is required')
        {'upload': upload, 'drill': drill, 'local-drill': local_drill}[args.action](args.package)


if __name__ == '__main__':
    try:
        main()
    except OneDriveSetupError as error:
        print(str(error), file=sys.stderr)
        sys.exit(1)
    except Exception as error:
        print('Shelf off-server backup stopped (' + type(error).__name__ + '); details withheld. Local backups preserved.', file=sys.stderr)
        sys.exit(1)
