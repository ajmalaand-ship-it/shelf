#!/usr/bin/env python3
"""Backup-first owner-only purchase activation and one-shot Google Play sync.

No catalogue prices, readers or financial history are edited. Sync status/queue
updates use the existing application service. A Google denial leaves scheduled
sync disabled, so the owner can retry this script after credential propagation.
"""
import fcntl
import json
import os
from pathlib import Path
import subprocess
import sys
import urllib.request

sys.dont_write_bytecode = True
from setup_revenuecat_test import APP, php_json, update_env


def main():
    if (APP / '.shelf-staging').is_file():
        raise RuntimeError('Production purchase connection is forbidden on staging.')
    os.umask(0o077)
    os.environ.update(TMPDIR='/home/shelf/tmp', TMP='/home/shelf/tmp', TEMP='/home/shelf/tmp')
    settings = php_json(r'''require $argv[1].'/vendor/autoload.php';
echo json_encode(Dotenv\Dotenv::parse(file_get_contents($argv[1].'/.env')));''')
    assert settings['SHELF_REVENUECAT_APP_ID'] == 'appf83cd58c5c'
    # Keep the remote setup credential out of runtime purchase verification.
    key = Path(settings['SHELF_REVENUECAT_V2_SECRET_KEY_PATH']).read_text().strip()
    request = urllib.request.Request(
        'https://api.revenuecat.com/v2/projects/projbbce26da/integrations/webhooks',
        headers={'Authorization': 'Bearer ' + key})
    with urllib.request.urlopen(request, timeout=20) as response:
        hooks = json.load(response)['items']
    matches = [w for w in hooks if w['url'] == 'https://shelf.services/api/purchases/webhook']
    assert len(matches) == 1
    assert matches[0]['environment'] == 'sandbox' and matches[0]['app_id'] == settings['SHELF_REVENUECAT_APP_ID']
    assert set(matches[0]['event_types']) == {'non_renewing_purchase', 'cancellation', 'expiration'}
    path = Path(settings['SHELF_REVENUECAT_SECRET_KEY_PATH'])
    assert path.resolve() == path and APP not in path.parents and not path.stat().st_mode & 0o077
    request = urllib.request.Request('https://api.revenuecat.com/v1/subscribers/shelf-setup-verifier-probe',
                                     headers={'Authorization': 'Bearer ' + path.read_text().strip()})
    with urllib.request.urlopen(request, timeout=20) as response:
        assert isinstance(json.load(response)['subscriber'], dict)
    with (APP / 'storage/framework/play-price-sync.lock').open('a') as lock:
        fcntl.flock(lock, fcntl.LOCK_EX | fcntl.LOCK_NB)
        result = subprocess.run(['python3', str(APP / 'scripts/shelf_daily_backup.py')],
                                capture_output=True, text=True)
        if result.returncode:
            raise RuntimeError('Backup failed. No activation or database changes attempted.')
        print(result.stdout, end='', flush=True)
        backup_path = next(line.split(': ', 1)[1] for line in result.stdout.splitlines()
                           if line.startswith('Verified SQL, ZIP (unzip -t), and SHA-256: '))
        notes = Path(backup_path) / 'purchase-test-connection-notes.txt'
        notes.write_text('Owner-approved 30 September 2026: sandbox purchase connection.\n'
                         'Rollback: disable SHELF_PURCHASES_ENABLED and SHELF_PLAY_SYNC_ENABLED, then php artisan optimize:clear.\n'
                         'No catalogue price/source edits or financial deletions. Remote products/webhook remain for tests.\n')
        update_env({'SHELF_PURCHASES_ENABLED': 'true', 'SHELF_PLAY_SYNC_ENABLED': 'false'})
        result = subprocess.run(['php', str(APP / 'artisan'), 'optimize:clear'], capture_output=True)
        if result.returncode:
            raise RuntimeError('Live config refresh failed; stop and inspect privately.')
        result = subprocess.run(['php', str(APP / 'artisan'), 'shelf:check-purchases', '--simulate-after-backup'],
                                capture_output=True, text=True)
        print(result.stdout, end='', flush=True)
        if result.returncode:
            raise RuntimeError('Live purchase simulation failed; stop and inspect privately.')
        # Enable only this process until all books succeed. Cron cannot retry a denied key.
        data = php_json(r'''require $argv[1].'/vendor/autoload.php';
$app=require $argv[1].'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['play_sync.enabled'=>true]);
try {
    $books=App\Models\Collection::where('status','published')->where('price_usd','>',0)->orderBy('id')->get();
    $output=''; $ok=true;
    foreach($books as $book) {
        $code=Illuminate\Support\Facades\Artisan::call('shelf:sync-play-prices',['book'=>(string)$book->id,'--once'=>true]);
        $output.=Illuminate\Support\Facades\Artisan::output();
        if($code!==0) { $ok=false; break; }
    }
    echo json_encode(['ok'=>$ok,'output'=>$output]);
} catch(Throwable $e) { echo json_encode(['fatal'=>true]); }''')
        if data.get('fatal'):
            raise RuntimeError('Live sync encountered an unexpected failure; stop and inspect privately.')
        print(data['output'], end='', flush=True)
        if data['ok']:
            update_env({'SHELF_PLAY_SYNC_ENABLED': 'true'})
            print('All sellable books synced. Scheduled admin price sync enabled.', flush=True)
            with notes.open('a') as stream:
                stream.write('All sellable books synced; scheduled sync enabled.\n')
        else:
            print('Google Play sync stopped. Scheduled sync remains disabled; no retry loop.', flush=True)
            with notes.open('a') as stream:
                stream.write('Google sync stopped at first unsuccessful book; scheduled sync remains disabled.\n')
        print('Sandbox purchase configuration active; owner-only agreement gate and independent V1 verifier retained.', flush=True)


if __name__ == '__main__':
    try:
        main()
    except Exception as error:
        print(str(error) if isinstance(error, RuntimeError) else type(error).__name__ + '; stopped safely.', file=sys.stderr)
        sys.exit(1)
