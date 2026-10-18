#!/usr/bin/env python3
"""Upload only genuine, completed simulator captures; never upload a build/submit."""
import base64
import hashlib
import json
import os
from pathlib import Path
import struct
import subprocess
import time
import urllib.error
import urllib.parse
import urllib.request

BASE = 'https://api.appstoreconnect.apple.com'
APP = '6821471140'
VERSION = '47de1d04-6b51-4338-be3a-1e26343a057a'
LOCALIZATION = '1f251642-fb78-4c9d-a0cb-d83c8aa2afa3'
BUILD = '5e4b0e7d-2e6f-44cf-94fa-c9d9ee40d000'
PRODUCTS = {3: '6822080809', 4: '6824083153', 5: '6824083087',
            6: '6824083088', 7: '6824083193', 8: '6824083291'}
OUT = Path(__file__).resolve().parents[2] / 'submission-captures'
ASSETS = {'appScreenshotSets', 'appScreenshots', 'inAppPurchaseAppStoreReviewScreenshots'}

class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *args, **kwargs):
        return None


_token = None
_token_expiry = 0


def jwt():
    # Reuse the same proven CLI credential resolution as Build 8. Do not assume
    # the integration exposes its private-key environment to Python scripts.
    global _token, _token_expiry
    if _token and _token_expiry > time.time() + 90:
        return _token
    private_temp = OUT.parent / '.submission-capture-tmp' / 'apple-assets'
    private_temp.mkdir(parents=True, exist_ok=True, mode=0o700)
    os.chmod(private_temp, 0o700)
    env = os.environ.copy()
    env.update(TMPDIR=str(private_temp), TMP=str(private_temp), TEMP=str(private_temp))
    env.pop('APP_STORE_CONNECT_DISABLE_JWT_CACHE', None)
    command = ['app-store-connect', 'get-latest-testflight-build-number', APP,
               '--platform=IOS', '--pre-release-version=1.0.0', '--silent']
    # Official CLI JWT cache: tempfile.gettempdir()/.codemagic-cli-tools/cache/
    # app_store_connect_jwt/<key-id>. Private directory, never an artifact.
    result = subprocess.run(command, env=env, capture_output=True, timeout=120)
    if result.returncode:
        raise RuntimeError('Existing Codemagic CLI Apple access failed; private output withheld')
    cache = private_temp / '.codemagic-cli-tools/cache/app_store_connect_jwt'
    files = list(cache.glob('*'))
    if len(files) != 1 or not files[0].is_file():
        raise RuntimeError('Existing CLI did not provide one cached Apple token; captures retained')
    token = files[0].read_text().strip()
    files[0].unlink()
    try:
        payload = token.split('.')[1]
        claims = json.loads(base64.urlsafe_b64decode(payload + '=' * (-len(payload) % 4)))
        assert claims['aud'] == 'appstoreconnect-v1' and claims['exp'] > time.time() + 90
    except (AssertionError, IndexError, KeyError, ValueError):
        raise RuntimeError('CLI token audience/expiry could not be verified; details withheld') from None
    _token, _token_expiry = token, claims['exp']
    return _token


def api(method, path, data=None, absent=False):
    # No submission, builds, pricing, legal, privacy or availability mutation path.
    if method != 'GET':
        assert method in {'POST', 'PATCH'}
        assert path.split('/')[2] in ASSETS
    assert path.startswith(('/v1/', '/v2/')) and '?' not in path
    req = urllib.request.Request(BASE + path, method=method,
        headers={'Authorization': 'Bearer ' + jwt(), 'Content-Type': 'application/json'},
        data=json.dumps(data).encode() if data is not None else None)
    try:
        with urllib.request.build_opener(NoRedirect).open(req, timeout=40) as response:
            raw = response.read()
            return json.loads(raw) if raw else None
    except urllib.error.HTTPError as error:
        if absent and error.code == 404:
            return None
        try:
            codes = [e.get('code') for e in json.load(error).get('errors', [])]
        except (ValueError, OSError):
            codes = []
        raise RuntimeError(f'Apple {method} {path}: HTTP {error.code}, codes={codes}') from None


def complete(kind, identity):
    for _ in range(40):
        data = api('GET', '/v1/' + kind + '/' + identity)['data']
        state = data['attributes'].get('assetDeliveryState', {}).get('state')
        if state == 'COMPLETE':
            return data
        if state == 'FAILED':
            raise RuntimeError(f'Apple image processing FAILED: {kind}/{identity}')
        time.sleep(3)
    raise RuntimeError(f'Apple image processing not COMPLETE yet: {kind}/{identity}')


def png(path, family):
    raw = path.read_bytes()
    assert raw[:8] == b'\x89PNG\r\n\x1a\n'
    width, height = struct.unpack('>II', raw[16:24])
    sizes = {(1320, 2868), (1290, 2796)} if family == 'iphone' else {(2064, 2752), (2048, 2732)}
    assert (width, height) in sizes, 'Never resize or upload wrong-device captures'
    return raw


def upload(kind, relationship, target, path, family):
    raw = png(path, family)
    name = family + '-' + path.name
    created = api('POST', '/v1/' + kind, {'data': {'type': kind,
        'attributes': {'fileName': name, 'fileSize': len(raw)},
        'relationships': {relationship: {'data': target}}}})['data']
    identity = created['id']
    operations = created['attributes']['uploadOperations']
    assert operations, 'Apple returned no asset upload operations'
    total = 0
    for operation in operations:
        offset, length = operation['offset'], operation['length']
        assert offset == total and length > 0 and offset + length <= len(raw)
        url = urllib.parse.urlsplit(operation['url'])
        assert url.scheme == 'https' and not url.username and operation['method'] == 'PUT'
        request = urllib.request.Request(operation['url'], method='PUT', data=raw[offset:offset + length],
            headers={h['name']: h['value'] for h in operation.get('requestHeaders', [])})
        # Apple bearer credentials are never forwarded to its asset-storage host.
        try:
            with urllib.request.build_opener(NoRedirect).open(request, timeout=60) as response:
                assert 200 <= response.status < 300
        except urllib.error.HTTPError as error:
            raise RuntimeError(f'Apple asset storage returned HTTP {error.code}; URL withheld') from None
        total += length
    assert total == len(raw)
    api('PATCH', '/v1/' + kind + '/' + identity, {'data': {'type': kind, 'id': identity,
        'attributes': {'uploaded': True, 'sourceFileChecksum': hashlib.md5(raw).hexdigest()}}})
    saved = complete(kind, identity)
    print('VERIFIED_SCREENSHOT', kind, identity, family, path.name, 'COMPLETE', flush=True)
    return {'type': kind, 'id': identity, 'file': name,
            'sha256': hashlib.sha256(raw).hexdigest(), 'processing': 'COMPLETE'}


def main():
    os.umask(0o077)
    evidence = json.loads((OUT / 'capture-evidence.json').read_text())
    assert evidence['app_source'] == '47f0e8e1ecf4ec5825f8c05d20231e229f7e04cf'
    assert evidence['live_api'] is True and evidence['store_upload'] is False
    for simulator in evidence['simulators']:
        family = simulator['family']
        for name, digest in simulator['files'].items():
            assert Path(name).name == name
            assert hashlib.sha256(png(OUT / family / name, family)).hexdigest() == digest
    assert {x['family'] for x in evidence['simulators']} == {'iphone', 'ipad'}
    version = api('GET', '/v1/appStoreVersions/' + VERSION)['data']['attributes']
    assert version['appVersionState'] == 'PREPARE_FOR_SUBMISSION' and version['releaseType'] == 'MANUAL'
    assert api('GET', '/v1/appStoreVersions/' + VERSION + '/relationships/build')['data']['id'] == BUILD
    locale = api('GET', '/v1/appStoreVersionLocalizations/' + LOCALIZATION)['data']['attributes']
    assert locale['locale'] == 'en-US'
    sets = api('GET', '/v1/appStoreVersionLocalizations/' + LOCALIZATION + '/appScreenshotSets')['data']
    results = []
    for family, display in [('iphone', 'APP_IPHONE_67'), ('ipad', 'APP_IPAD_PRO_3GEN_129')]:
        found = [x for x in sets if x['attributes']['screenshotDisplayType'] == display]
        assert len(found) <= 1
        target = found[0] if found else api('POST', '/v1/appScreenshotSets', {'data': {
            'type': 'appScreenshotSets', 'attributes': {'screenshotDisplayType': display},
            'relationships': {'appStoreVersionLocalization': {'data': {
                'type': 'appStoreVersionLocalizations', 'id': LOCALIZATION}}}}})['data']
        existing = api('GET', '/v1/appScreenshotSets/' + target['id'] + '/appScreenshots')['data']
        # Store first, then sample, then product details; up to ten native images.
        files = sorted((OUT / family).glob('*.png'), key=lambda p: (0 if p.stem == 'listing-store' else 1 if p.stem == 'listing-sample' else 2, p.name))
        for path in files:
            prior = [x for x in existing if x['attributes']['fileName'] == family + '-' + path.name]
            if prior:
                complete('appScreenshots', prior[0]['id'])
                continue
            assert len(existing) < 10, 'Preserve existing images; screenshot-set limit reached'
            result = upload('appScreenshots', 'appScreenshotSet',
                            {'type': 'appScreenshotSets', 'id': target['id']}, path, family)
            results.append(result)
            existing.append({'id': result['id'], 'attributes': {'fileName': family + '-' + path.name}})
            (OUT / 'apple-upload-results.json').write_text(json.dumps(results, indent=2))
    for book, product in PRODUCTS.items():
        path = OUT / 'iphone' / f'iap-book-{book}.png'
        existing = api('GET', '/v2/inAppPurchases/' + product + '/appStoreReviewScreenshot', absent=True)
        if existing and existing.get('data'):
            complete('inAppPurchaseAppStoreReviewScreenshots', existing['data']['id'])
            continue
        results.append(upload('inAppPurchaseAppStoreReviewScreenshots', 'inAppPurchaseV2',
            {'type': 'inAppPurchases', 'id': product}, path, 'iphone'))
        (OUT / 'apple-upload-results.json').write_text(json.dumps(results, indent=2))
    print('All new screenshot uploads independently verified COMPLETE; no build or review submission', flush=True)

if __name__ == '__main__':
    main()
