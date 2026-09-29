#!/usr/bin/env python3
"""Owner-only HTTPS account lifecycle check; email-link tokens arrive on stdin.

Run only after an approved backup/rollout. Never reads or modifies the database.
On any unexpected response, stop and leave the test account for owner review.
"""
import argparse
import http.cookiejar
import json
import re
import secrets
import sys
import urllib.error
import urllib.parse
import urllib.request


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--base-url', required=True)
    parser.add_argument('--owner-email', required=True)
    parser.add_argument('--alias', required=True)
    parser.add_argument('--recover', action='store_true', help='Finish an interrupted owner test using its existing verification email.')
    args = parser.parse_args()
    if not sys.stdin.isatty():
        raise RuntimeError('Run in an interactive terminal with input echo disabled before creating a test account.')
    local, domain = args.owner_email.split('@')
    if domain.lower() != 'gmail.com' or not re.fullmatch(r'shelf-test-[a-z0-9-]+', args.alias):
        raise RuntimeError('Use only an owner Gmail plus-alias.')
    email = local + '+' + args.alias + '@' + domain
    base = args.base_url.rstrip('/')
    if not base.startswith('https://'):
        raise RuntimeError('HTTPS is required.')
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))

    def request(path, data=None, token=None, expected=200):
        headers = {'Accept': 'application/json', 'User-Agent': 'Shelf-owner-account-check'}
        if token:
            headers['Authorization'] = 'Bearer ' + token
        body = None
        if data is not None:
            body = json.dumps(data).encode()
            headers['Content-Type'] = 'application/json'
        req = urllib.request.Request(base + path, data=body, headers=headers)
        try:
            response = opener.open(req, timeout=30)
        except urllib.error.HTTPError as error:
            response = error
        with response:
            status = response.code
            content = response.read()
        if status != expected:
            raise RuntimeError(f'{path}: expected {expected}, received {status}')
        print(f'PASS {path}: {status}', flush=True)
        return json.loads(content) if content else {}

    def consume(purpose):
        print('WAITING_FOR_EMAIL ' + purpose, flush=True)
        value = json.loads(sys.stdin.readline())
        url = value['url']
        parsed = urllib.parse.urlparse(url)
        expected_path = '/account/delete/confirm' if purpose == 'delete' else '/account/' + purpose
        if urllib.parse.urlunsplit((parsed.scheme, parsed.netloc, '', '', '')) != base or parsed.path != expected_path or not re.fullmatch(r'[A-Za-z0-9]{64}', parsed.fragment):
            raise RuntimeError('Unexpected email link; stopped.')
        with opener.open(base + parsed.path, timeout=30) as response:
            html = response.read().decode()
        csrf = re.search(r'name="_token"\s+value="([^"]+)"', html)
        if not csrf:
            raise RuntimeError('Missing confirmation form.')
        data = {'_token': csrf.group(1), 'token': parsed.fragment}
        if purpose == 'reset':
            data.update(password=passwords[1], password_confirmation=passwords[1])
        request('/account/' + purpose, data)
        request('/account/' + purpose, data, expected=422)

    passwords = ['Shelf!Aa1' + secrets.token_urlsafe(18) for _ in range(3)]
    configuration = request('/api/auth/config')
    assert configuration['enabled'] and configuration['owner_testing_only'] is False
    first = None
    if not args.recover:
        request('/api/auth/register', dict(email=email, password=passwords[0], password_confirmation=passwords[0]), expected=202)
        first = request('/api/auth/login', dict(email=email, password=passwords[0]))
        assert not first['user']['email_verified']
    consume('verify')
    if first:
        assert request('/api/auth/me', token=first['token'])['user']['email_verified']
        # Reader tokens cannot unlock draft content or owner-preview routes.
        request('/api/poems/301', token=first['token'], expected=404)
        request('/api/owner-preview/collections', token=first['token'], expected=401)
    request('/api/auth/forgot-password', dict(email=email), expected=202)
    consume('reset')
    if first:
        request('/api/auth/me', token=first['token'], expected=401)
    second = request('/api/auth/login', dict(email=email, password=passwords[1]))
    request('/api/auth/password', dict(current_password=passwords[1], password=passwords[2], password_confirmation=passwords[2]), token=second['token'], expected=204)
    request('/api/auth/me', token=second['token'], expected=401)
    third = request('/api/auth/login', dict(email=email, password=passwords[2]))
    fourth = request('/api/auth/login', dict(email=email, password=passwords[2]))
    request('/api/auth/logout', {}, token=third['token'], expected=204)
    request('/api/auth/me', token=fourth['token'], expected=401)
    # Five logins per email/minute: allow the final login in a later window.
    print('WAITING_FOR_NEXT_LOGIN_WINDOW', flush=True)
    if sys.stdin.readline().strip() != 'continue':
        raise RuntimeError('Expected continuation after rate-limit window.')
    final = request('/api/auth/login', dict(email=email, password=passwords[2]))
    request('/api/auth/delete-request', {}, token=final['token'], expected=202)
    consume('delete')
    request('/api/auth/me', token=final['token'], expected=401)
    request('/api/auth/login', dict(email=email, password=passwords[2]), expected=422)
    print('PASS complete lifecycle; test account deleted through email confirmation.', flush=True)


if __name__ == '__main__':
    try:
        main()
    except Exception as error:
        # Do not print email links, response bodies, tokens, or passwords.
        print('STOP: ' + (str(error) if isinstance(error, RuntimeError) else type(error).__name__), file=sys.stderr)
        sys.exit(1)
