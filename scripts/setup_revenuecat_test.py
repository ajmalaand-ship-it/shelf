#!/usr/bin/env python3
"""Configure Shelf's per-book RevenueCat catalogue and sandbox-only webhook.

Run as Shelf after a backup. Credentials and webhook authorization are never printed.
This script changes RevenueCat configuration and .env, not the database.
"""
import json
import os
from pathlib import Path
import re
import subprocess
import sys
import urllib.error
import urllib.request

APP = Path(__file__).resolve().parent.parent
BASE = 'https://api.revenuecat.com/v2'


def php_json(code):
    result = subprocess.run(['php', '-r', code, str(APP)], capture_output=True, text=True)
    if result.returncode:
        raise RuntimeError('Private Laravel read failed; stopped.')
    return json.loads(result.stdout)


def update_env(values):
    path = APP / '.env'
    content = path.read_text()
    for key, value in values.items():
        line = key + '=' + value
        if re.search('^' + key + '=.*$', content, re.M):
            content = re.sub('^' + key + '=.*$', lambda _: line, content, flags=re.M)
        else:
            content += '\n' + line + '\n'
    path.write_text(content)
    path.chmod(0o600)


def main():
    if (APP / '.shelf-staging').is_file():
        raise RuntimeError('Production RevenueCat setup is forbidden on staging.')
    os.umask(0o077)
    settings = php_json(r'''require $argv[1].'/vendor/autoload.php';
echo json_encode(Dotenv\Dotenv::parse(file_get_contents($argv[1].'/.env')));''')
    key_path = settings.get('SHELF_REVENUECAT_V2_SECRET_KEY_PATH',
                            '/home/shelf/secrets/revenuecat-v2-secret-key.txt')
    path = Path(key_path)
    if path.resolve() != path or APP in path.parents or path.stat().st_mode & 0o077:
        raise RuntimeError('V2 credential must be a private file outside git.')
    key = path.read_text().strip()

    def request(route, body=None):
        req = urllib.request.Request(BASE + route,
                                     data=json.dumps(body).encode() if body is not None else None,
                                     headers={'Authorization': 'Bearer ' + key,
                                              'Content-Type': 'application/json', 'Accept': 'application/json'})
        try:
            with urllib.request.urlopen(req, timeout=20) as response:
                return json.load(response)
        except urllib.error.HTTPError as error:
            raise RuntimeError('RevenueCat HTTP ' + str(error.code) + ' at ' + route) from None

    def items(route):
        values = []
        while route:
            data = request(route)
            values.extend(data['items'])
            next_page = data.get('next_page')
            if next_page and not next_page.startswith('/v2/'):
                raise RuntimeError('Unexpected pagination URL; stopped.')
            route = next_page[3:] if next_page else None
        return values

    projects = [p for p in items('/projects?limit=100') if p['name'] == 'Shelf']
    if len(projects) != 1:
        raise RuntimeError('Expected exactly one new Shelf project.')
    prefix = '/projects/' + projects[0]['id']
    apps = [a for a in items(prefix + '/apps?limit=100') if a['type'] == 'play_store'
            and a.get('play_store', {}).get('package_name') == 'services.shelf.app']
    if len(apps) != 1:
        raise RuntimeError('Expected exactly one Shelf Google Play app.')
    app_id = apps[0]['id']
    books = php_json(r'''require $argv[1].'/vendor/autoload.php';
$app=require $argv[1].'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo App\Models\Collection::where('status','published')->where('price_usd','>',0)
->orderBy('id')->get(['id','title','product_id','price_usd'])->toJson();''')
    products = items(prefix + '/products?limit=100')
    entitlements = items(prefix + '/entitlements?limit=100')
    for book in books:
        identifier = 'shelf_book_' + str(book['id'])
        if book['product_id'] != identifier:
            raise RuntimeError('Unexpected book product ID; stopped.')
        matches = [p for p in products if p['app_id'] == app_id and p['store_identifier'] == identifier]
        if len(matches) > 1:
            raise RuntimeError('Duplicate product; stopped.')
        product = matches[0] if matches else request(prefix + '/products', {
            'app_id': app_id, 'store_identifier': identifier, 'type': 'non_consumable',
            'display_name': book['title']})
        if product['type'] not in ('non_consumable', 'one_time'):
            raise RuntimeError('Unexpected product type; stopped.')
        if product.get('one_time', {}).get('is_consumable') is True:
            raise RuntimeError('Consumable book product; stopped.')
        matches = [e for e in entitlements if e['lookup_key'] == identifier]
        if len(matches) > 1:
            raise RuntimeError('Duplicate entitlement; stopped.')
        entitlement = matches[0] if matches else request(prefix + '/entitlements', {
            'lookup_key': identifier, 'display_name': book['title']})
        route = prefix + '/entitlements/' + entitlement['id']
        attached = items(route + '/products?limit=100')
        if any(p['id'] != product['id'] for p in attached):
            raise RuntimeError('Entitlement contains another product; stopped.')
        if not attached:
            request(route + '/actions/attach_products', {'product_ids': [product['id']]})
        verified = items(route + '/products?limit=100')
        if [p['id'] for p in verified] != [product['id']]:
            raise RuntimeError('Entitlement attachment not confirmed.')
        print(identifier + ': product and separate entitlement verified', flush=True)
    webhook_auth = settings['SHELF_REVENUECAT_WEBHOOK_AUTH']
    values = {'name': 'Shelf sandbox book purchases',
              'url': 'https://shelf.services/api/purchases/webhook',
              'authorization_header': webhook_auth, 'environment': 'sandbox',
              'event_types': ['non_renewing_purchase', 'cancellation', 'expiration'], 'app_id': app_id}
    auth_file = Path('/home/shelf/secrets/revenuecat-webhook-auth.txt')
    auth_file.write_text(webhook_auth + '\n')
    auth_file.chmod(0o600)
    try:
        route = prefix + '/integrations/webhooks'
        existing = [w for w in items(route + '?limit=100') if w['url'] == values['url']]
        if len(existing) > 1:
            raise RuntimeError('Multiple Shelf webhooks; stopped.')
        target = route + '/' + existing[0]['id'] if existing else route
        webhook = request(target, values)
        for field in ('environment', 'app_id', 'url'):
            if webhook.get(field) != values[field]:
                raise RuntimeError('Webhook scope not confirmed; stopped.')
        if set(webhook.get('event_types', [])) != set(values['event_types']):
            raise RuntimeError('Webhook events not confirmed; stopped.')
        print('Sandbox webhook created/updated and scope verified.', flush=True)
    except RuntimeError:
        print('Webhook not confirmed. Use the private auth file and dashboard instructions.', flush=True)
        raise
    update_env({'SHELF_REVENUECAT_APP_ID': app_id,
                'SHELF_REVENUECAT_V2_SECRET_KEY_PATH': str(path),
                'SHELF_REVENUECAT_SECRET_KEY_PATH': '/home/shelf/secrets/revenuecat-v1-secret-key.txt'})
    print('Shelf app ID and separate V1/V2 credential paths saved. Purchase enablement unchanged.')


if __name__ == '__main__':
    try:
        main()
    except Exception as error:
        # Never expose provider responses, request headers, or secret-bearing tracebacks.
        print(str(error) if isinstance(error, RuntimeError) else type(error).__name__, file=sys.stderr)
        sys.exit(1)
