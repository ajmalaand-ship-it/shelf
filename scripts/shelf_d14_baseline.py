"""Inventory all retained Shelf backup identities; suppress already-absent profiles."""
from datetime import datetime, timezone
import json
from pathlib import Path
import secrets
import tarfile
import tempfile
import shelf_deletion_journal as journal
import shelf_daily_backup as local
import shelf_offsite_backup as backup


def current_readers():
    code = r'''require $argv[1].'/vendor/autoload.php';
$app=require $argv[1].'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo json_encode(App\Models\Reader::orderBy('id')->get(['id','created_at'])->map(fn($r)=>[$r->id,$r->created_at->format('Y-m-d\\TH:i:s\\Z')])->all());'''
    return {tuple(row) for row in json.loads(backup.run(['php', '-r', code, backup.PRODUCTION]).decode())}


def baseline():
    live = current_readers()
    identities = set()
    local_paths = local.backup_folders(Path('/home/shelf/backups/shelf'))
    listing = json.loads(backup.rclone('lsjson', backup.REMOTE, '--files-only').decode())
    remote_names = sorted(item['Name'] for item in listing if backup.NAME.fullmatch(item.get('Name', '')) and item['Name'].startswith('shelf-production-'))
    # Never assume local offsite spool is the entire retained remote inventory.
    for path in local_paths:
        local.verify(path)
        with tempfile.TemporaryDirectory(prefix='shelf-d14-inventory-', dir='/home/shelf/tmp') as temp:
            work = Path(temp)
            backup.restore_database(path, work, identity_inventory=identities)
    for name in remote_names:
        with tempfile.TemporaryDirectory(prefix='shelf-d14-inventory-', dir='/home/shelf/tmp') as temp:
            work = Path(temp)
            encrypted, side = work / name, work / 'metadata.json'
            backup.rclone('copyto', backup.REMOTE + '/' + name, encrypted, '--immutable')
            backup.rclone('copyto', backup.REMOTE + '/' + name[:-4] + '.json', side)
            metadata = json.loads(side.read_text())
            if local.sha256(encrypted) != metadata['sha256']:
                raise RuntimeError('Baseline ciphertext mismatch.')
            plain = work / 'package.tar'
            backup.crypt(encrypted, plain, decrypt=True)
            target = work / 'database'
            target.mkdir(mode=0o700)
            with tarfile.open(plain) as archive:
                manifest = json.load(archive.extractfile('recovery-manifest.json'))
                sql = archive.extractfile('database.sql').read()
            if __import__('hashlib').sha256(sql).hexdigest() != manifest['files']['database.sql']:
                raise RuntimeError('Baseline SQL checksum mismatch.')
            (target / 'database.sql').write_bytes(sql)
            backup.restore_database(target, work, identity_inventory=identities)
    # These identities are already absent, not live readers deleted for testing.
    # Timestamp records baseline suppression, not an invented original deletion date.
    absent = identities - live
    recorded = datetime.now(timezone.utc).isoformat()
    for reader, born in sorted(absent):
        journal.append({'reader_id': reader, 'created_at': born, 'deleted_at': recorded, 'record_id': secrets.token_hex(16)})
    backup.write_json(journal.ROOT / 'baseline.json', {'inventoried_at': recorded, 'local_sets': len(local_paths),
        'remote_sets': len(remote_names), 'absent_identities_recorded': len(absent), 'complete': True})
    print('Retained backup identity inventory complete: ' + json.dumps({'local_sets': len(local_paths), 'remote_sets': len(remote_names), 'already_absent': len(absent)}))
