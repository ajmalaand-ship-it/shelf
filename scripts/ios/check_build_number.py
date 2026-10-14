#!/usr/bin/env python3
"""Read-only Apple collision guard. Explicit number only; no fallback or reservation."""
import base64
import json
import os
from pathlib import Path
import re
import subprocess
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request
from github_testflight import der_signature_to_raw

APP = '6821471140'
BUNDLE = 'services.shelf.app'
BASE = 'https://api.appstoreconnect.apple.com'


class GuardError(Exception):
    pass


def require(ok, message):
    if not ok:
        raise GuardError(message)


def number(value):
    require(isinstance(value, str) and re.fullmatch(r'[1-9][0-9]{0,17}', value),
            'Set an explicit positive SHELF_IOS_BUILD_NUMBER; no fallback is allowed.')
    return int(value)


def validate(chosen, builds, uploads):
    chosen = number(chosen)
    used = []
    for item in builds:
        a = item.get('attributes', {})
        require(a.get('processingState') in ('VALID', 'INVALID', 'FAILED'),
                'An Apple build is processing or its state is unknown; stop.')
        used.append(number(a.get('version')))
    for item in uploads:
        a = item.get('attributes', {})
        require(a.get('platform') == 'IOS', 'Unexpected Apple upload platform; stop.')
        state = a.get('state', {})
        require(isinstance(state, dict) and state.get('state') in ('COMPLETE', 'FAILED'),
                'An Apple upload is pending or its state is unknown; stop.')
        used.append(number(a.get('cfBundleVersion')))
    highest = max(used, default=0)
    require(chosen > highest,
            f'Chosen iOS build {chosen} must exceed Apple used/uploaded build {highest}; update SHELF_IOS_BUILD_NUMBER.')
    return highest


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *args, **kwargs):
        return None


class Apple:
    def __init__(self, env):
        key_id = env.get('APP_STORE_CONNECT_KEY_IDENTIFIER', '')
        issuer = env.get('APP_STORE_CONNECT_ISSUER_ID', '')
        key = env.get('APP_STORE_CONNECT_PRIVATE_KEY', '')
        require(re.fullmatch(r'[A-Z0-9]{10}', key_id) and
                re.fullmatch(r'[0-9a-fA-F-]{36}', issuer) and
                '-----BEGIN PRIVATE KEY-----' in key,
                'Existing App Store Connect integration credentials unavailable to guard; stop. No build-number fallback.')
        enc = lambda data: base64.urlsafe_b64encode(data).rstrip(b'=')
        now = int(time.time())
        body = b'.'.join(enc(json.dumps(x, separators=(',', ':')).encode()) for x in
                         [{'alg': 'ES256', 'kid': key_id, 'typ': 'JWT'},
                          {'iss': issuer, 'iat': now, 'exp': now + 300, 'aud': 'appstoreconnect-v1'}])
        with tempfile.TemporaryDirectory(prefix='shelf-asc-guard-') as folder:
            path = Path(folder) / 'key.p8'
            with path.open('x') as stream:
                os.chmod(path, 0o600)
                stream.write(key)
            result = subprocess.run(['openssl', 'dgst', '-sha256', '-sign', str(path)],
                                    input=body, capture_output=True, timeout=15)
            require(result.returncode == 0, 'Apple guard credential signing failed; details withheld.')
        self.token = (body + b'.' + enc(der_signature_to_raw(result.stdout))).decode()

    def get(self, path):
        url = BASE + path
        request = urllib.request.Request(url, headers={
            'Authorization': 'Bearer ' + self.token, 'Accept': 'application/json'})
        try:
            with urllib.request.build_opener(NoRedirect).open(request, timeout=25) as response:
                require(response.status == 200, 'Apple guard GET failed; stop.')
                data = json.load(response)
                require(isinstance(data, dict), 'Apple guard response is malformed; stop.')
                return data
        except urllib.error.HTTPError as error:
            raise GuardError(f'Apple guard GET failed (HTTP {error.code}); stop, no fallback.') from None
        except (urllib.error.URLError, ValueError, TimeoutError):
            raise GuardError('Apple guard API unavailable or malformed; stop, no fallback.') from None

    def listed(self, path):
        next_path = path + '?limit=200'
        seen, items, total = set(), [], None
        while next_path:
            require(next_path not in seen and len(seen) < 10, 'Apple guard pagination incomplete; stop.')
            seen.add(next_path)
            page = self.get(next_path)
            require(isinstance(page.get('data'), list) and isinstance(page.get('links'), dict),
                    'Apple guard list evidence incomplete; stop.')
            count = page.get('meta', {}).get('paging', {}).get('total')
            require(type(count) is int and count >= 0 and (total is None or total == count),
                    'Apple guard pagination total missing or changed; stop.')
            total = count
            require(all(isinstance(x, dict) for x in page['data']), 'Malformed Apple guard item; stop.')
            items.extend(page['data'])
            next_url = page['links'].get('next')
            if next_url is None:
                require(len(items) == total, 'Apple guard list is truncated; stop.')
                break
            require(isinstance(next_url, str), 'Malformed Apple guard pagination; stop.')
            parsed = urllib.parse.urlsplit(next_url)
            require(parsed.scheme == 'https' and parsed.netloc == 'api.appstoreconnect.apple.com'
                    and parsed.path == path and not parsed.fragment, 'Untrusted Apple guard pagination; stop.')
            next_path = parsed.path + '?' + parsed.query
        return items


def check(env, api=None):
    chosen = env.get('SHELF_IOS_BUILD_NUMBER', '')
    number(chosen)
    api = api or Apple(env)
    app = api.get('/v1/apps/' + APP).get('data', {})
    require(app.get('id') == APP and app.get('attributes', {}).get('bundleId') == BUNDLE,
            'Apple guard app identity mismatch; stop.')
    highest = validate(chosen, api.listed('/v1/apps/' + APP + '/builds'),
                       api.listed('/v1/apps/' + APP + '/buildUploads'))
    print(f'PASS: chosen iOS build {chosen}; Apple highest used/uploaded {highest}; no processing build or pending upload. No number reserved.')


if __name__ == '__main__':
    try:
        check(os.environ)
    except Exception as error:
        print(str(error) if isinstance(error, GuardError) else
              'Apple build-number guard failed; private details withheld. Stop, no fallback.')
        raise SystemExit(1)
