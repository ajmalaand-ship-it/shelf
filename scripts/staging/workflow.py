#!/usr/bin/env python3
"""Private staging lifecycle. Secrets stay in memory/private files, never output."""
import argparse
import base64
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
import urllib.error
import urllib.request
from urllib.parse import quote

sys.dont_write_bytecode = True
PRODUCTION = Path('/home/shelf/apps/shelf')
STAGING = Path('/home/shelf/apps/shelf-staging')
RUNTIME = Path('/home/shelf/staging-runtime')
RELEASES = Path('/home/shelf/apps/shelf-staging-releases')
STATE = RUNTIME / 'release-state.json'
SOURCE = Path(__file__).resolve().parents[2]
SHARES = '2026_10_01_040000_record_owner_book_shares.php'


def run(args, cwd=SOURCE, input=None):
    result = subprocess.run(args, cwd=cwd, input=input, capture_output=True, text=True)
    if result.returncode:
        # Provider/process output can contain passwords or personal data.
        raise RuntimeError('Command failed: ' + Path(args[0]).name + ' (details withheld; no later live steps run).')
    return result.stdout


def php(app, code, *args):
    return run(['php', '-r', 'require $argv[1]."/vendor/autoload.php";'+code, str(app), *map(str, args)], app)


def settings(app):
    return json.loads(php(app, 'echo json_encode(Dotenv\\Dotenv::parse(file_get_contents($argv[1]."/.env")));'))


def artisan(app, *args):
    output = run(['php', 'artisan', *args], app)
    if args[0].startswith('shelf:check-'):
        print(output, end='', flush=True)
    return output


def private_write(path, content):
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(content)
    path.chmod(0o600)


def save_state(data):
    private_write(STATE.with_suffix('.new'), json.dumps(data, indent=2)+'\n')
    STATE.with_suffix('.new').replace(STATE)


def current_state():
    return json.loads(STATE.read_text()) if STATE.exists() else {}


def backup(app):
    result = run(['python3', '-B', 'scripts/shelf_daily_backup.py'], app)
    print(result, end='', flush=True)
    return Path(next(line.split(': ', 1)[1] for line in result.splitlines()
                     if line.startswith('Verified SQL, ZIP (unzip -t), and SHA-256: ')))


def uapi(module, method, **values):
    result = json.loads(run(['/usr/local/cpanel/bin/uapi', '--input=json', '--output=json', module, method],
                            input=json.dumps(values)))['result']
    if result['status'] != 1:
        raise RuntimeError('cPanel '+module+'.'+method+' failed; response withheld. Stop and inspect privately.')
    print('PASS: cPanel '+module+'.'+method, flush=True)
    return result.get('data')


def commit_id(value):
    commit = run(['git', 'rev-parse', '--verify', value+'^{commit}']).strip()
    if not re.fullmatch('[0-9a-f]{40}', commit):
        raise RuntimeError('Invalid commit.')
    return commit


def prepare_release(commit):
    destination = RELEASES / commit
    if destination.exists():
        raise RuntimeError('Release already exists; use a new commit or inspect the retained release.')
    destination.mkdir(parents=True)
    RELEASES.chmod(0o755)
    destination.chmod(0o755)
    with tempfile.TemporaryDirectory(prefix='staging-release-', dir='/home/shelf/tmp') as temporary:
        archive = Path(temporary)/'code.tar'
        with archive.open('wb') as output:
            subprocess.run(['git', 'archive', commit], cwd=SOURCE, stdout=output, check=True)
        with tarfile.open(archive) as source:
            source.extractall(destination, filter='data')
    (destination/'bootstrap/cache').mkdir(parents=True,exist_ok=True)
    # Each release owns its dependencies; no links to production code or vendor.
    if (destination/'composer.lock').read_bytes() == (PRODUCTION/'composer.lock').read_bytes():
        shutil.copytree(PRODUCTION/'vendor', destination/'vendor')
    else:
        run(['composer', 'install', '--no-interaction', '--prefer-dist'], destination)
    run(['composer', 'dump-autoload', '--no-scripts'], destination)
    shutil.rmtree(destination/'storage')  # only newly extracted empty placeholders
    (destination/'storage').symlink_to(RUNTIME/'storage', target_is_directory=True)
    (destination/'.env').symlink_to(RUNTIME/'.env')
    (destination/'.shelf-staging').write_text('Private Shelf staging boundary\n')
    (destination/'REVISION').write_text(commit+'\n')
    return destination


def switch_staging(destination):
    # Static files are served by Apache; PHP and private runtime stay account-only.
    (destination/'public').chmod(0o755)
    for path in (destination/'public').rglob('*'):
        if path.is_symlink(): raise RuntimeError('Unexpected staging public symlink.')
        path.chmod(0o755 if path.is_dir() else 0o644)
    temporary = STAGING.with_name('shelf-staging-next')
    if temporary.exists() or temporary.is_symlink():
        raise RuntimeError('Unexpected pending staging switch.')
    temporary.symlink_to(destination, target_is_directory=True)
    temporary.replace(STAGING)


def initialize_runtime():
    if RUNTIME.exists() or STAGING.exists() or STAGING.is_symlink():
        raise RuntimeError('Staging already exists; use deploy/refresh, not setup.')
    RUNTIME.mkdir(mode=0o700)
    for path in ['app/private/covers', 'app/private/authors', 'app/private/artwork', 'app/private/audio',
                 'app/public', 'app/source', 'framework/cache/data', 'framework/views', 'framework/sessions', 'logs']:
        (RUNTIME/'storage'/path).mkdir(parents=True, exist_ok=True)
    # The web server runs PHP as Shelf. Public release directories remain traversable.
    password = secrets.token_urlsafe(36)
    gate = secrets.token_urlsafe(48)
    hash_value = run(['php', '-r', 'echo password_hash(stream_get_contents(STDIN), PASSWORD_BCRYPT);'], input=password)
    values = {
        'APP_NAME': 'Shelf Test', 'APP_ENV': 'staging', 'APP_DEBUG': 'false',
        'APP_URL': 'https://staging.shelf.services', 'APP_KEY': 'base64:'+base64.b64encode(secrets.token_bytes(32)).decode(),
        'DB_CONNECTION': 'mysql', 'DB_HOST': 'localhost', 'DB_PORT': '3306',
        'DB_DATABASE': 'shelf_staging', 'DB_USERNAME': 'shelf_staging', 'DB_PASSWORD': secrets.token_urlsafe(40),
        'CACHE_STORE': 'file', 'CACHE_PREFIX': 'shelf_staging_', 'SESSION_DRIVER': 'file',
        'SESSION_COOKIE': 'shelf_staging_session', 'SESSION_SECURE_COOKIE': 'true',
        'QUEUE_CONNECTION': 'null', 'DB_QUEUE': 'staging-disabled', 'MAIL_MAILER': 'log',
        'MAIL_FROM_ADDRESS': 'noreply@staging.shelf.services', 'MAIL_FROM_NAME': 'Shelf Test',
        'READER_ACCOUNTS_ENABLED': 'true', 'SHELF_PLAY_SYNC_ENABLED': 'false',
        'SHELF_PURCHASES_ENABLED': 'false', 'SHELF_STAGING_ACCESS_KEY': gate,
        'SHELF_REVENUECAT_WEBHOOK_AUTH': secrets.token_urlsafe(48),
        'SHELF_STAGING_BASIC_PASSWORD_HASH': hash_value,
        'POETRY_BACKUP_PATH': '/home/shelf/backups/shelf-staging',
    }
    private_write(RUNTIME/'.env', ''.join(k+'='+json.dumps(v)+'\n' for k,v in values.items()))
    private_write(Path('/home/shelf/secrets/staging/access.txt'), 'Browser user: owner\nBrowser password: '+password+'\n')
    Path('/home/shelf/secrets/staging').chmod(0o700)
    uapi('Mysql', 'create_database', name='shelf_staging')
    uapi('Mysql', 'create_user', name='shelf_staging', password=values['DB_PASSWORD'])
    uapi('Mysql', 'set_privileges_on_database', user='shelf_staging', database='shelf_staging', privileges='ALL PRIVILEGES')


def source_catalogue(destination):
    # Bootstrap this commit with production's private dotenv values, without
    # deploying its files or executing migrations on production. Only SELECTs.
    code = r'''Dotenv\Dotenv::createImmutable('/home/shelf/apps/shelf')->load();
$app=require $argv[1].'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$snapshot=app(App\Services\StagingCatalogue::class)->capture(Illuminate\Support\Facades\DB::connection());
file_put_contents($argv[2],json_encode($snapshot,JSON_THROW_ON_ERROR)); chmod($argv[2],0600);'''
    # Use the development checkout without a staging marker/.env, so it cannot
    # confuse the source with the target or trigger staging side effects.
    php(SOURCE, code, destination)


def refresh_data(app, initial=False):
    state = current_state()
    state.pop('checks', None)  # refreshing invalidates previous approval evidence
    if state: save_state(state)
    if not initial: backup(app)
    before = json.loads(php(app, r'''$app=require $argv[1].'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo json_encode(app(App\Services\StagingCatalogue::class)->capture(Illuminate\Support\Facades\DB::connection(),true));'''))
    with tempfile.TemporaryDirectory(prefix='staging-catalogue-', dir='/home/shelf/tmp') as temporary:
        export = Path(temporary)/'catalogue.json'
        source_catalogue(export)
        after = json.loads(export.read_text())
    name = datetime.now(timezone.utc).strftime('%Y_%m_%d_%H%M%S')+'_refresh_catalogue'
    folder = RUNTIME/'storage/app/private/catalogue-refresh'/name
    private_write(folder/'before.json', json.dumps(before, ensure_ascii=False))
    private_write(folder/'after.json', json.dumps(after, ensure_ascii=False))
    shutil.copyfile(app/'scripts/staging/catalogue_migration.php', folder/(name+'.php'))
    artisan(app, 'migrate', '--force', '--realpath', '--path='+str(folder))
    # Copy only files referenced by catalogue records. No owner APKs, mail,
    # session files, account uploads, source-import metadata or provider secrets.
    refs = [('collections', 'cover_image', 'covers'), ('authors', 'image_path', 'authors'),
            ('poems', 'audio_path', 'audio'), ('poems', 'artwork_path', 'artwork')]
    count = 0
    for table, field, disk in refs:
        for row in after[table]:
            path = row.get(field)
            if not path: continue
            relative = Path(path)
            if relative.is_absolute() or '..' in relative.parts:
                raise RuntimeError('Unsafe catalogue media path.')
            source = PRODUCTION/'storage/app/private'/disk/relative
            if source.is_symlink() or not source.is_file():
                raise RuntimeError('Referenced catalogue media missing or redirected.')
            target = RUNTIME/'storage/app/private'/disk/relative
            target.parent.mkdir(parents=True, exist_ok=True)
            shutil.copyfile(source,target); target.chmod(0o600)
            if hashlib.sha256(source.read_bytes()).digest() != hashlib.sha256(target.read_bytes()).digest():
                raise RuntimeError('Media copy verification failed.')
            if source.stat().st_ino == target.stat().st_ino: raise RuntimeError('Media files are shared.')
            count += 1
    print('PASS: catalogue-only reversible refresh; '+str(count)+' referenced media files copied and verified.',flush=True)


def https_checks():
    gate = settings(STAGING)['SHELF_STAGING_ACCESS_KEY']
    base = 'https://staging.shelf.services'
    def request(path, auth=False, method='GET', data=None):
        headers = {'Accept':'application/json'}
        if auth: headers['X-Shelf-Test-Key']=gate
        req=urllib.request.Request(base+path,headers=headers,method=method,data=data)
        try:
            with urllib.request.urlopen(req,timeout=20) as response:
                return response.status,{k.lower():v for k,v in response.headers.items()},response.read()
        except urllib.error.HTTPError as response:
            return response.code,{k.lower():v for k,v in response.headers.items()},response.read()
    assert request('/api/app-config')[0]==401
    status,headers,body=request('/api/app-config',True)
    assert status==200 and json.loads(body)['environment']=='staging'
    assert headers.get('x-shelf-environment')=='staging' and 'noindex' in headers.get('x-robots-tag','')
    assert request('/api/purchases/webhook',True,'POST',b'{}')[0]==403
    assert request('/api/auth/config',True)[0]==200
    assert request('/api/library',True)[0]==401
    assert request('/.env',True)[0] in (403,404)
    assert request('/storage/private/catalogue-refresh/',True)[0] in (403,404)
    assert b'TEST COPY' in request('/admin/login',True)[2]
    status,_,body=request('/api/collections',True)
    books=json.loads(body)['data']; assert status==200 and len(books)>=6
    status,_,body=request('/api/collections/'+quote(books[0]['slug'],safe='')+'/poems',True)
    items=json.loads(body)['data']; assert status==200
    locked=next(item for item in items if item['locked'])
    status,_,body=request('/api/poems/'+str(locked['id']),True)
    data=json.loads(body)['data']
    assert status==200 and data['locked'] is True and data['body'] is None
    cover=books[0].get('cover_url')
    if cover:
        from urllib.parse import urlparse
        assert request(urlparse(cover).path,True)[0]==200
    with urllib.request.urlopen('https://shelf.services/api/app-config',timeout=20) as response:
        assert json.load(response).get('environment','production')=='production'
    print('PASS: real HTTPS/private gate, noindex, API marker, banner, catalogue/cover, locked paid text, accounts, locked Library, denied webhook/private files; production healthy.',flush=True)


def checked(app, commit, initial=False):
    previous = current_state().get('production_commit')
    changes = run(['git', 'diff', '--name-only', previous, commit], SOURCE).splitlines() if previous else []
    android_purchase_scope = {'mobile/lib/services/api_config.dart',
        'mobile/lib/purchases/book_purchase_provider.dart', 'mobile/lib/purchases/library_controller.dart',
        'mobile/lib/purchases/purchase_screen.dart', 'mobile/test/book_purchases_test.dart', 'mobile/test/library_ux_test.dart',
        'app/Http/Controllers/LibraryController.php', 'tests/Feature/BookPurchasesTest.php',
        'scripts/build_shelf_release.sh', 'scripts/build_owner_preview.sh', 'scripts/run_tests.sh',
        'scripts/staging/workflow.py', 'docs/MASTER_RECORD.md', 'docs/PRODUCTION_PURCHASE_VERIFICATION.md', 'AGENTS.md'}
    if changes and set(changes) <= android_purchase_scope and 'mobile/lib/purchases/library_controller.dart' in changes:
        log = RUNTIME / ('checks-' + commit + '.log')
        test_env = os.environ.copy()
        test_env['SHELF_PHP_TEST_FILTER'] = 'BookPurchasesTest|VoidedPurchasesTest|AccountingTest|PlayPriceSyncTest'
        test_env['SHELF_MOBILE_TEST_SCOPE'] = 'purchases'
        with log.open('w') as output:
            result = subprocess.run(['bash', 'scripts/run_tests.sh', '--mobile'], cwd=app,
                                    stdout=output, stderr=subprocess.STDOUT, env=test_env)
        if result.returncode:
            raise RuntimeError('Staged Android purchase checks failed; private log: ' + str(log))
        artisan(app, 'shelf:check-staging')
        artisan(app, 'shelf:check-purchases')
        https_checks()
        state = current_state()
        state.update(staging_commit=commit, checks={'commit': commit, 'passed': True,
            'checked_at': datetime.now(timezone.utc).isoformat(), 'mobile_tests': True,
            'scope': 'android-release-purchases', 'log_sha256': hashlib.sha256(log.read_bytes()).hexdigest()})
        save_state(state)
        print('PASS: focused Android release checkout, restore and server verification checks.', flush=True)
        return
    purchase_scope = {'config/purchases.php', 'app/Services/Purchases/RevenueCatClient.php',
                      'app/Services/Purchases/PurchaseService.php', 'app/Http/Controllers/LibraryController.php',
                      'app/Providers/AppServiceProvider.php', 'app/Console/Commands/CheckBookPurchases.php',
                      'tests/Feature/BookPurchasesTest.php', 'scripts/staging/workflow.py', 'AGENTS.md',
                      'docs/MASTER_RECORD.md', 'docs/PRODUCTION_PURCHASE_VERIFICATION.md',
                      'docs/PURCHASES_TEST_SETUP.md', 'docs/STATUS_RECONCILIATION_2026-10-02.md',
                      'docs/MANUAL_PLAY_PRODUCTS.md', 'docs/ACCOUNTING.md'}
    if changes and set(changes) <= purchase_scope and 'config/purchases.php' in changes:
        log = RUNTIME / ('checks-' + commit + '.log')
        test_env = os.environ.copy()
        test_env['SHELF_PHP_TEST_FILTER'] = 'BookPurchasesTest|VoidedPurchasesTest|AccountingTest|PlayPriceSyncTest'
        with log.open('w') as output:
            result = subprocess.run(['bash', 'scripts/run_tests.sh'], cwd=app,
                                    stdout=output, stderr=subprocess.STDOUT, env=test_env)
        if result.returncode:
            raise RuntimeError('Staged production verification checks failed; private log: ' + str(log))
        artisan(app, 'shelf:check-staging')
        artisan(app, 'shelf:check-purchases')
        https_checks()
        state = current_state()
        state.update(staging_commit=commit, checks={'commit': commit, 'passed': True,
            'checked_at': datetime.now(timezone.utc).isoformat(), 'mobile_tests': False,
            'scope': 'production-verification', 'log_sha256': hashlib.sha256(log.read_bytes()).hexdigest()})
        save_state(state)
        print('PASS: staged production verification, sandbox, refunds, accounting and manual sync regressions.', flush=True)
        return
    manual_scope = {'config/play_sync.php', 'app/Services/Play/GooglePlayClient.php',
                    'app/Services/Play/PlayPriceSync.php', 'app/Console/Commands/SyncPlayPrices.php',
                    'app/Filament/Resources/Collections/Schemas/CollectionForm.php',
                    'app/Filament/Resources/Collections/Tables/CollectionsTable.php',
                    'tests/Feature/PlayPriceSyncTest.php', 'scripts/staging/workflow.py', 'AGENTS.md',
                    'docs/MASTER_RECORD.md', 'docs/ACCOUNTING.md', 'docs/OFFSERVER_BACKUP.md',
                    'docs/PLAY_PRICE_SYNC_VERIFICATION.md', 'docs/PURCHASES_TEST_SETUP.md',
                    'docs/STATUS_RECONCILIATION_2026-10-02.md', 'docs/MANUAL_PLAY_PRODUCTS.md'}
    if changes and set(changes) <= manual_scope and 'config/play_sync.php' in changes:
        # Owner authorized focused checks for the first-100-books manual amendment.
        log = RUNTIME / ('checks-' + commit + '.log')
        test_env = os.environ.copy()
        test_env['SHELF_PHP_TEST_FILTER'] = 'PlayPriceSyncTest|BookPurchasesTest|VoidedPurchasesTest|AccountingTest'
        with log.open('w') as output:
            result = subprocess.run(['bash', 'scripts/run_tests.sh'], cwd=app,
                                    stdout=output, stderr=subprocess.STDOUT, env=test_env)
        if result.returncode:
            raise RuntimeError('Staged manual Play checks failed; private log: ' + str(log))
        artisan(app, 'shelf:check-staging')
        https_checks()
        state = current_state()
        state.update(staging_commit=commit, checks={'commit': commit, 'passed': True,
            'checked_at': datetime.now(timezone.utc).isoformat(), 'mobile_tests': False,
            'scope': 'manual-play', 'log_sha256': hashlib.sha256(log.read_bytes()).hexdigest()})
        save_state(state)
        print('PASS: identical staged commit focused manual Play/purchase/refund/accounting checks.', flush=True)
        return
    backup_scope = {'scripts/shelf_offsite_backup.py', 'scripts/test_shelf_offsite_backup.py', 'scripts/shelf_daily_backup.py',
                    'scripts/run_tests.sh', 'scripts/staging/workflow.py', 'docs/OFFSERVER_BACKUP.md',
                    'docs/MASTER_RECORD.md', 'AGENTS.md', 'docs/ACCOUNTING.md',
                    'docs/STATUS_RECONCILIATION_2026-10-02.md'}
    if changes and set(changes) <= backup_scope and any(name in changes for name in ('scripts/shelf_offsite_backup.py', 'docs/OFFSERVER_BACKUP.md')):
        # This task explicitly excludes broad PHP/phone reruns. The new transfer
        # code never boots Laravel; test it at the identical staged commit.
        log = RUNTIME / ('checks-' + commit + '.log')
        with log.open('w') as output:
            result = subprocess.run(['bash', 'scripts/run_tests.sh', '--backup'], cwd=app,
                                    stdout=output, stderr=subprocess.STDOUT)
        if result.returncode:
            raise RuntimeError('Staged backup checks failed; private log: ' + str(log))
        https_checks()
        state = current_state()
        state.update(staging_commit=commit, checks={'commit': commit, 'passed': True,
            'checked_at': datetime.now(timezone.utc).isoformat(), 'mobile_tests': False,
            'scope': 'backup-only', 'log_sha256': hashlib.sha256(log.read_bytes()).hexdigest()})
        save_state(state)
        print('PASS: identical staged commit focused backup checks; no broad PHP/phone rerun.', flush=True)
        return
    artisan(app,'shelf:check-owner-shares')
    artisan(app,'shelf:check-staging',*(['--initial'] if initial else []))
    artisan(app,'shelf:check-play-refunds','--simulate-after-backup')
    artisan(app,'shelf:check-accounting','--simulate-after-backup')
    # Snapshot source before/after staging mutation check proves no source change.
    with tempfile.TemporaryDirectory(prefix='staging-isolation-',dir='/home/shelf/tmp') as temporary:
        one,two=Path(temporary)/'one.json',Path(temporary)/'two.json'
        source_catalogue(one)
        artisan(app,'shelf:check-staging')
        source_catalogue(two)
        if one.read_bytes()!=two.read_bytes(): raise RuntimeError('Production catalogue changed during isolation check; stop and review.')
    https_checks()
    # Only run Flutter when this release changed mobile/, per the owner rule.
    previous = run(['git', 'rev-parse', 'HEAD'], PRODUCTION).strip()
    changed = subprocess.run(['git', 'diff', '--quiet', previous, commit, '--', 'mobile/'], cwd=SOURCE)
    if changed.returncode not in (0, 1): raise RuntimeError('Could not determine mobile test scope.')
    test_command = ['bash', 'scripts/run_tests.sh'] + (['--mobile'] if changed.returncode == 1 else [])
    log=RUNTIME/('checks-'+commit+'.log')
    with log.open('w') as output:
        test_env=os.environ.copy(); test_env.pop('SHELF_PHP_TEST_FILTER',None)
        result=subprocess.run(test_command,cwd=app,stdout=output,stderr=subprocess.STDOUT,env=test_env)
    if result.returncode: raise RuntimeError('Staging automated checks failed; private log: '+str(log))
    state=current_state(); state.update(staging_commit=commit,checks={'commit':commit,'passed':True,
        'checked_at':datetime.now(timezone.utc).isoformat(),'mobile_tests':changed.returncode == 1,'log_sha256':hashlib.sha256(log.read_bytes()).hexdigest()})
    if initial: state['initial_isolation_verified']=True
    save_state(state)
    print('PASS: deployed commit automated checks (Flutter only when mobile changed). Staging approval evidence recorded.',flush=True)


def setup(commit):
    if shutil.disk_usage('/home/shelf').free < 5*1024**3: raise RuntimeError('Insufficient disk space.')
    backup(PRODUCTION)
    initialize_runtime()
    app=prepare_release(commit)
    # Empty schema first; catalogue data migrations are run after the safe copy.
    paths=[str(p.relative_to(app)) for p in sorted((app/'database/migrations').glob('*.php')) if p.name!=SHARES]
    artisan(app,'migrate','--force',*['--path='+p for p in paths])
    refresh_data(app,initial=True)
    artisan(app,'migrate','--force')
    artisan(app,'filament:assets')
    (app/'public/robots.txt').write_text('User-agent: *\nDisallow: /\n')
    switch_staging(app)
    uapi('SubDomain','addsubdomain',domain='staging',rootdomain='shelf.services',dir='apps/shelf-staging/public')
    # cPanel restricts document roots to public_html. Keep its dedicated entry
    # but serve the independent staging release through a public-only symlink.
    configure_document_root()
    uapi('SSL','start_autossl_check')
    save_state({'staging_commit':commit,'production_commit':run(['git','rev-parse','HEAD'],PRODUCTION).strip(),
                'checks':None,'setup_at':datetime.now(timezone.utc).isoformat()})
    print('Staging created. Run checks after DNS/AutoSSL completes; no production code or database changed.',flush=True)


def configure_document_root():
    entry=PRODUCTION/'public/apps/shelf-staging/public'
    if entry.is_symlink():
        if entry.readlink()!=STAGING/'public': raise RuntimeError('Unexpected staging document-root target.')
        return
    if not entry.is_dir(): raise RuntimeError('Expected cPanel staging document root is missing.')
    retained=RUNTIME/'original-cpanel-document-root'
    if retained.exists(): raise RuntimeError('Original cPanel document root already retained; inspect before proceeding.')
    entry.rename(retained)
    entry.symlink_to(STAGING/'public',target_is_directory=True)
    entry.parent.chmod(0o755);entry.parent.parent.chmod(0o755)
    print('PASS: dedicated cPanel public entry points to independent staging public files.',flush=True)


def deploy(commit):
    backup(STAGING)
    app=prepare_release(commit)
    artisan(app,'migrate','--force')
    artisan(app,'filament:assets')
    (app/'public/robots.txt').write_text('User-agent: *\nDisallow: /\n')
    switch_staging(app)
    state=current_state();state.update(staging_commit=commit,checks=None);save_state(state)
    checked(app,commit,initial=current_state().get('initial_isolation_verified') is not True)


def promotion_allowed(state,commit,actual):
    evidence=state.get('checks') or {}
    return state.get('staging_commit')==commit==actual and evidence.get('commit')==commit and evidence.get('passed') is True


def production_history_snapshot(agreement_limit=None):
    # Only aggregate fingerprints leave PHP; no reader or money data is printed.
    return json.loads(php(PRODUCTION,r'''$app=require $argv[1].'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$out=[];
foreach (['readers','reader_account_actions','personal_access_tokens','purchases','purchase_events','sales_ledger','book_entitlements','purchase_consents','author_share_agreements','accounting_entries','accounting_shares'] as $table) {
 $q=Illuminate\Support\Facades\DB::table($table)->orderBy('id');
 if ($table==='author_share_agreements' && $argv[2]!=='all') {$q->where('id','<=',(int)$argv[2]);}
 $rows=Illuminate\Support\Facades\Schema::hasTable($table) ? $q->get() : collect();$out[$table]=['count'=>$rows->count(),'sha256'=>hash('sha256',json_encode($rows,JSON_THROW_ON_ERROR))];
}
$out['agreement_limit']=$argv[2]==='all' ? (int)Illuminate\Support\Facades\DB::table('author_share_agreements')->max('id') : (int)$argv[2];
echo json_encode($out,JSON_THROW_ON_ERROR);''','all' if agreement_limit is None else agreement_limit))


def promote(commit):
    state=current_state()
    if not promotion_allowed(state,commit,(STAGING/'REVISION').read_text().strip()):
        raise RuntimeError('Promotion refused: exact commit must already be on staging with passing checks.')
    log=RUNTIME/('checks-'+commit+'.log')
    if hashlib.sha256(log.read_bytes()).hexdigest()!=state['checks']['log_sha256']:
        raise RuntimeError('Staging check evidence changed.')
    # The cPanel-created public entry is runtime, excluded by the staged .gitignore.
    entry=PRODUCTION/'public/apps/shelf-staging/public'
    if not entry.is_symlink() or entry.readlink()!=STAGING/'public':
        raise RuntimeError('Staging public entry changed.')
    dirty = run(['git','status','--porcelain','--','.',':!public/apps/'],PRODUCTION).strip()
    runtime_handler = (PRODUCTION/'public/.htaccess').read_bytes()
    if dirty:
        original = run(['git', 'show', 'HEAD:public/.htaccess'], PRODUCTION).encode()
        handler = b"\n# php -- BEGIN cPanel-generated handler, do not edit\n# Set the \u201cea-php83\u201d package as the default \u201cPHP\u201d programming language.\n<IfModule mime_module>\n  AddHandler application/x-httpd-ea-php83 .php .php8 .phtml\n</IfModule>\n# php -- END cPanel-generated handler, do not edit\n"
        handler = handler.decode('unicode_escape').encode('utf-8')
        if dirty != 'M public/.htaccess' or runtime_handler != original + handler:
            raise RuntimeError('Production checkout has changes beyond the verified cPanel handler.')
        if run(['git', 'diff', '--name-only', 'HEAD', commit, '--', 'public/.htaccess'], PRODUCTION).strip():
            raise RuntimeError('Release changes the runtime cPanel handler; stop.')
    previous=run(['git','rev-parse','HEAD'],PRODUCTION).strip()
    run(['git','merge-base','--is-ancestor',previous,commit],PRODUCTION)
    saved=backup(PRODUCTION)
    private_write(saved/'promotion-notes.txt','From '+previous+' to '+commit+'\nRollback code: git switch --detach '+previous+'\nDatabase: inspect migration batch; do not restore over reader/money changes. Use retained SQL/media backup only with owner approval.\n')
    artisan(PRODUCTION,'down')
    try:
        unchanged=production_history_snapshot()
        run(['git','merge','--ff-only',commit],PRODUCTION)
        if (PRODUCTION/'public/.htaccess').read_bytes() != runtime_handler:
            raise RuntimeError('Runtime cPanel handler changed unexpectedly.')
        # Keep dependency setup out of maintenance where possible; same lock
        # copies the already tested stage dependencies if it changed.
        if (PRODUCTION/'composer.lock').read_bytes()!=(STAGING/'composer.lock').read_bytes():
            raise RuntimeError('Staging/production lock mismatch.')
        # The staged vendor tree is already installed and tested at this lock.
        # Copy dependency files rather than contacting the network in maintenance.
        shutil.copytree(STAGING/'vendor', PRODUCTION/'vendor', dirs_exist_ok=True)
        run(['composer','dump-autoload','--no-scripts'],PRODUCTION)
        artisan(PRODUCTION,'optimize:clear')
        artisan(PRODUCTION,'migrate','--force')
        artisan(PRODUCTION,'filament:assets')
        artisan(PRODUCTION,'shelf:check-purchases')
        artisan(PRODUCTION,'shelf:check-accounting')
        if unchanged!=production_history_snapshot(unchanged['agreement_limit']):
            raise RuntimeError('Past reader/payment/agreement records changed unexpectedly; stop and review.')
        print('PASS: all previous reader, purchase, refund, ledger and agreement records unchanged.',flush=True)
    finally:
        artisan(PRODUCTION,'up')
    artisan(PRODUCTION,'shelf:check-owner-shares')
    https_checks()
    state.update(production_commit=commit,last_backup=str(saved),previous_production_commit=previous)
    save_state(state)
    print('PASS: staged commit promoted with backup/migrations/health checks; history preserved.',flush=True)


def main():
    os.umask(0o077)
    os.environ.update(TMPDIR='/home/shelf/tmp',TMP='/home/shelf/tmp',TEMP='/home/shelf/tmp',
                      COMPOSER_CACHE_DIR='/home/shelf/tmp/composer-cache')
    parser=argparse.ArgumentParser()
    parser.add_argument('action',choices=['setup','deploy','refresh','check','promote','status'])
    parser.add_argument('commit',nargs='?',default='HEAD')
    args=parser.parse_args()
    if args.action=='status': print(json.dumps(current_state(),indent=2));return
    Path('/home/shelf/tmp').mkdir(exist_ok=True)
    with Path('/home/shelf/tmp/shelf-staging-workflow.lock').open('a') as lock:
        fcntl.flock(lock,fcntl.LOCK_EX|fcntl.LOCK_NB)
        commit=commit_id(args.commit)
        if args.action=='setup': setup(commit)
        elif args.action=='deploy': deploy(commit)
        elif args.action=='refresh':
            refresh_data(STAGING);print('Refresh complete; run check before promotion.',flush=True)
        elif args.action=='check': checked(STAGING,(STAGING/'REVISION').read_text().strip(),
                                          initial=current_state().get('initial_isolation_verified') is not True)
        elif args.action=='promote': promote(commit)


if __name__=='__main__':
    try: main()
    except Exception as error:
        print(str(error) if isinstance(error,RuntimeError) else type(error).__name__+'; stopped safely, no private details printed.',file=sys.stderr)
        sys.exit(1)
