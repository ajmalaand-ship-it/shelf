#!/usr/bin/env python3
"""Shelf-only disabled Apple configuration; provider creation only after deployment.
No product/credential/Google integration changes. Never emit keys or response bodies.
"""
import argparse
import hashlib
import importlib.util
import io
import json
import os
from pathlib import Path
import pwd
import re
import secrets
import stat
import subprocess
import tarfile
import urllib.error
import urllib.request

ROOT = Path(__file__).resolve().parents[2]
PRODUCTION = Path('/home/shelf/apps/shelf')
PROJECT = 'projbbce26da'
APPLE_APP = 'app8ef018b95b'
PRODUCT = 'prod0d084e901f'
GOOGLE_WEBHOOK = 'whintgr75a4892970'
AUTH_FILE = Path('/home/shelf/secrets/revenuecat-apple-sandbox-webhook-auth.txt')
RECORD = Path('/home/shelf/secrets/revenuecat-apple-sandbox-webhook.json')
V2_KEY = Path('/home/shelf/secrets/revenuecat-v2-secret-key.txt')
EVENTS = ['non_renewing_purchase', 'cancellation', 'expiration', 'refund_reversed']
# Distinct integration URL; query only distinguishes configuration, never authorizes events.
URL = 'https://shelf.services/api/purchases/webhook?source=apple'


def workflow():
    spec = importlib.util.spec_from_file_location('shelf_workflow', ROOT/'scripts/staging/workflow.py')
    module = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(module)
    return module


def private(path):
    s = path.lstat()
    if path.resolve() != path or not stat.S_ISREG(s.st_mode) or s.st_uid != os.getuid() or stat.S_IMODE(s.st_mode) != 0o600:
        raise RuntimeError('Private file ownership/type/mode invalid; details withheld.')
    return path.read_bytes()


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *args, **kwargs):
        return None


def request(route, body=None):
    if not route.startswith('/projects/'+PROJECT+'/'):
        raise RuntimeError('Unexpected provider scope.')
    key = private(V2_KEY).decode().strip()
    req = urllib.request.Request('https://api.revenuecat.com/v2'+route,
        data=json.dumps(body).encode() if body is not None else None,
        headers={'Authorization': 'Bearer '+key, 'Content-Type': 'application/json', 'Accept': 'application/json'})
    try:
        with urllib.request.build_opener(NoRedirect).open(req, timeout=25) as response:
            result = json.load(response)
            print(('POST' if body is not None else 'GET')+' RevenueCat scoped configuration: HTTP '+str(response.status), flush=True)
            return result
    except urllib.error.HTTPError as error:
        raise RuntimeError('RevenueCat HTTP '+str(error.code)+'; stopped without retry; private details withheld.') from None


def items(route):
    result = []
    seen = set()
    for _ in range(10):
        if route in seen:
            raise RuntimeError('Repeated provider page.')
        seen.add(route)
        data = request(route)
        result.extend(data['items'])
        route = data.get('next_page')
        if not route:
            return result
        if not route.startswith('/v2/projects/'+PROJECT+'/'):
            raise RuntimeError('Unexpected provider pagination.')
        route = route[3:]
    raise RuntimeError('Bounded pagination exceeded.')


def exclusive(path, content, private_parent=True):
    if path.parent.resolve() != path.parent or path.parent.stat().st_uid != os.getuid() or path.parent.stat().st_mode & (0o077 if private_parent else 0o022):
        raise RuntimeError('Unsafe private parent.')
    fd = os.open(path, os.O_WRONLY|os.O_CREAT|os.O_EXCL|os.O_NOFOLLOW, 0o600)
    with os.fdopen(fd, 'wb') as output:
        output.write(content)
        output.flush()
        os.fsync(output.fileno())
    if private(path) != content:
        raise RuntimeError('Private file read-back mismatch.')


def prepare(resume_checkpoint=None):
    w = workflow()
    before = w.settings(PRODUCTION)
    if before.get('SHELF_APPLE_SANDBOX_ENABLED', 'false') != 'false' or before.get('SHELF_REAL_PURCHASES_ENABLED', 'false') != 'false':
        raise RuntimeError('Purchase controls must already be OFF.')
    keys = items('/projects/'+PROJECT+'/apps/'+APPLE_APP+'/public_api_keys')
    matches = [x['key'] for x in keys if x.get('app_id') == APPLE_APP and x.get('key','').startswith('appl_')]
    if len(matches) != 1:
        raise RuntimeError('Exactly one existing Apple SDK key required.')
    # SQL/media verification and an encrypted exact configuration checkpoint precede changes.
    saved = w.backup(PRODUCTION)
    checkpoint = saved/'apple-purchase-checkpoint'
    checkpoint.mkdir(mode=0o700)
    for path in ('docs/MASTER_RECORD.md', 'docs/IOS_SIGN_IN.md', 'public/.htaccess'):
        w.private_write(checkpoint/Path(path).name, (PRODUCTION/path).read_text())
    w.private_write(checkpoint/'production.diff', w.run(['git','diff','--','docs/MASTER_RECORD.md','docs/IOS_SIGN_IN.md','public/.htaccess'], PRODUCTION))
    w.private_write(checkpoint/'release-state.json', w.STATE.read_text())
    original = private(PRODUCTION/'.env')
    archive = io.BytesIO()
    with tarfile.open(fileobj=archive, mode='w') as output:
        entry = tarfile.TarInfo('production.env')
        entry.size = len(original)
        entry.mode = 0o600
        output.addfile(entry, io.BytesIO(original))
    clear = archive.getvalue()
    destination = checkpoint/'private-configuration.tar.gpg'
    gpg = ['gpg','--batch','--pinentry-mode','loopback','--passphrase-file','/home/shelf/secrets/backup/recovery-key.secret']
    r = subprocess.run([*gpg,'--symmetric','--cipher-algo','AES256','--output',str(destination)],input=clear,capture_output=True)
    if r.returncode or subprocess.run([*gpg,'--decrypt',str(destination)],capture_output=True).stdout != clear:
        raise RuntimeError('Encrypted configuration checkpoint failed; stopped before configuration changes.')
    destination.chmod(0o600)
    resumed = False
    if resume_checkpoint:
        previous = Path(resume_checkpoint)
        if previous.resolve() != previous or not str(previous).startswith('/home/shelf/backups/shelf/'):
            raise RuntimeError('Invalid resume checkpoint.')
        encrypted = previous/'private-configuration.tar.gpg'
        r = subprocess.run([*gpg,'--decrypt',str(encrypted)],capture_output=True)
        if r.returncode:
            raise RuntimeError('Resume checkpoint decryption failed.')
        with tarfile.open(fileobj=io.BytesIO(r.stdout),mode='r:') as archive:
            resumed = archive.extractfile('production.env').read() == original
        if not resumed or not AUTH_FILE.exists() or AUTH_FILE.lstat().st_mtime < encrypted.stat().st_mtime:
            raise RuntimeError('Resume provenance/configuration mismatch; no secret replacement.')
    if AUTH_FILE.exists() or AUTH_FILE.is_symlink():
        auth = private(AUTH_FILE).decode().strip()
        if before.get('SHELF_REVENUECAT_IOS_WEBHOOK_AUTH') != auth and not (resumed and not before.get('SHELF_REVENUECAT_IOS_WEBHOOK_AUTH')):
            raise RuntimeError('Existing Apple webhook authorization is not linked; no overwrite.')
    else:
        if before.get('SHELF_REVENUECAT_IOS_WEBHOOK_AUTH'):
            raise RuntimeError('Apple authorization exists in configuration; no replacement.')
        auth = 'Bearer '+secrets.token_hex(32)
        exclusive(AUTH_FILE, (auth+'\n').encode())
    if auth == before.get('SHELF_REVENUECAT_WEBHOOK_AUTH'):
        raise RuntimeError('Apple/Google webhook authorization must be distinct.')
    values = {'SHELF_APPLE_SANDBOX_ENABLED':'false','SHELF_REAL_PURCHASES_ENABLED':'false',
        'SHELF_REVENUECAT_PROJECT_ID':PROJECT,'SHELF_REVENUECAT_IOS_APP_ID':APPLE_APP,
        'SHELF_REVENUECAT_IOS_BOOK3_PRODUCT_ID':PRODUCT,'SHELF_REVENUECAT_IOS_KEY':matches[0],
        'SHELF_REVENUECAT_IOS_WEBHOOK_AUTH':auth,'SHELF_REVENUECAT_V2_SECRET_KEY_PATH':str(V2_KEY)}
    text = original.decode()
    for key, value in values.items():
        if key in before and before[key] not in ('', value):
            raise RuntimeError('Existing Apple configuration differs; no overwrite.')
        line = key+'='+json.dumps(value)
        if len(re.findall('^'+key+'=',text,re.M)) > 1:
            raise RuntimeError('Duplicate configuration field.')
        if re.search('^'+key+'=.*$',text,re.M):
            text = re.sub('^'+key+'=.*$',lambda _:line,text,flags=re.M)
        else:
            text += '\n'+line+'\n'
    target = PRODUCTION/'.env.apple-purchase-new'
    exclusive(target,text.encode(),private_parent=False)
    if private(PRODUCTION/'.env') != original:
        raise RuntimeError('Configuration changed concurrently; stopped.')
    target.replace(PRODUCTION/'.env')
    after = w.settings(PRODUCTION)
    if any(after.get(k)!=v for k,v in values.items()) or {k:v for k,v in before.items() if k not in values}!={k:v for k,v in after.items() if k not in values}:
        raise RuntimeError('Configuration preservation check failed.')
    w.artisan(PRODUCTION,'config:clear')
    w.private_write(checkpoint/'configuration-verification.json',json.dumps({'unrelated_settings_unchanged':True,'gates_off':True,'encrypted_checkpoint_verified':True,'authorization_distinct':True}))
    print('PASS: private Apple configuration saved; both gates OFF; unrelated settings unchanged; encrypted checkpoint verified.',flush=True)
    print('Checkpoint: '+str(checkpoint),flush=True)


def probe_disabled_endpoint():
    request = urllib.request.Request(URL, data=b'{"event":{"store":"APP_STORE"}}',
        headers={'Accept':'application/json','Content-Type':'application/json'},method='POST')
    try:
        with urllib.request.build_opener(NoRedirect).open(request,timeout=20) as response:
            status=response.status
    except urllib.error.HTTPError as response:
        status=response.code
    if status!=503:
        raise RuntimeError('Distinct Apple URL is not guarded by the disabled backend.')
    print('PASS: distinct Apple integration URL reaches the disabled 503 guard; no event accepted.',flush=True)


def webhook():
    w = workflow()
    state = w.current_state()
    commit = w.run(['git','rev-parse','HEAD'],PRODUCTION).strip()
    if state.get('production_commit') != commit or state.get('staging_commit') != commit or (state.get('checks') or {}).get('scope') != 'apple-purchase-backend-disabled':
        raise RuntimeError('Exact disabled purchase backend must be staged/verified/promoted first.')
    w.apple_purchase_runtime_checks(PRODUCTION,production=True)
    probe_disabled_endpoint()
    settings = w.settings(PRODUCTION)
    auth = private(AUTH_FILE).decode().strip()
    if settings.get('SHELF_REVENUECAT_IOS_WEBHOOK_AUTH') != auth:
        raise RuntimeError('Private webhook authorization differs from server configuration.')
    route = '/projects/'+PROJECT+'/integrations/webhooks'
    before = items(route+'?limit=100')
    google = [x for x in before if x['id']==GOOGLE_WEBHOOK]
    if len(google)!=1 or google[0].get('app_id')!='appf83cd58c5c':
        raise RuntimeError('Existing Google webhook identity changed; stopped.')
    desired = {'name':'Shelf iOS sandbox','url':URL,'authorization_header':auth,
               'environment':'sandbox','event_types':EVENTS,'app_id':APPLE_APP}
    apple = [x for x in before if x.get('app_id')==APPLE_APP]
    if apple:
        if not RECORD.is_file() or len(apple)!=1 or json.loads(private(RECORD))['id']!=apple[0]['id']:
            raise RuntimeError('Existing Apple webhook requires private review; no update or duplicate.')
        created = apple[0]
    else:
        if RECORD.exists() or RECORD.is_symlink():
            raise RuntimeError('Webhook record exists without integration; no recreation.')
        created = request(route,desired)  # Exactly one create; no provider test, update or retry.
    verified = request(route+'/'+created['id'])
    for field in ('name','url','environment','app_id'):
        if verified.get(field)!=desired[field]:
            raise RuntimeError('Created webhook configuration mismatch; no automatic corrective write.')
    if set(verified.get('event_types') or [])!=set(EVENTS):
        raise RuntimeError('Created webhook event filter mismatch.')
    after = items(route+'?limit=100')
    if sorted([x for x in after if x['id']!=created['id']],key=lambda x:x['id'])!=sorted(before if not apple else [x for x in before if x['id']!=created['id']],key=lambda x:x['id']):
        raise RuntimeError('Unrelated webhook configuration changed; stop and inspect.')
    safe = {k:verified[k] for k in ('id','name','url','environment','app_id','event_types')}
    safe.update(backend_commit=commit,google_unchanged=True,delivery_verified=False)
    if not RECORD.exists(): exclusive(RECORD,(json.dumps(safe,indent=2)+'\n').encode())
    print('PASS: Apple sandbox webhook '+created['id']+' configured; Google unchanged; delivery unverified; gate remains OFF.',flush=True)


def main():
    os.umask(0o077)
    os.environ.update(TMPDIR='/home/shelf/tmp',TMP='/home/shelf/tmp',TEMP='/home/shelf/tmp',GNUPGHOME='/home/shelf/secrets/backup/gnupg')
    if os.getuid()!=pwd.getpwnam('shelf').pw_uid:
        raise RuntimeError('Run as shelf.')
    parser=argparse.ArgumentParser()
    parser.add_argument('action',choices=['prepare','webhook'])
    parser.add_argument('--resume-checkpoint',help='Verified checkpoint from this interrupted preparation; preserves its new authorization.')
    args=parser.parse_args()
    prepare(args.resume_checkpoint) if args.action=='prepare' else webhook()


if __name__=='__main__':
    try: main()
    except Exception as error:
        print(str(error) if isinstance(error,RuntimeError) else type(error).__name__+'; stopped safely; private details withheld.',file=__import__('sys').stderr)
        raise SystemExit(1)
