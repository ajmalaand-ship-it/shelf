#!/usr/bin/env python3
"""Manual GitHub signed internal build. No credential creation or review submission."""
from pathlib import Path
import base64
import datetime as dt
import hashlib
import json
import os
import plistlib
import re
import secrets
import shlex
import shutil
import signal
import subprocess
import sys
import time
import urllib.error
import urllib.parse
import urllib.request
import zipfile

TEAM = 'YSLWJQDH8B'
BUNDLE = 'services.shelf.app'
SECRET_NAMES = (
    'SHELF_IOS_DISTRIBUTION_P12_BASE64', 'SHELF_IOS_DISTRIBUTION_P12_PASSWORD',
    'SHELF_IOS_APP_STORE_PROFILE_BASE64', 'SHELF_APP_STORE_CONNECT_KEY_ID',
    'SHELF_APP_STORE_CONNECT_ISSUER_ID', 'SHELF_APP_STORE_CONNECT_PRIVATE_KEY',
)

class BuildError(Exception):
    pass


def require(condition, message):
    if not condition:
        raise BuildError(message)


def run(args, *, data=None, visible=False, extra_env=None):
    # Private material is never inherited by Flutter, CocoaPods or compiler children.
    env = {k: v for k, v in os.environ.items() if k not in SECRET_NAMES}
    env.update(extra_env or {})
    result = subprocess.run(args, input=data, env=env,
                            stdout=None if visible else subprocess.PIPE,
                            stderr=None if visible else subprocess.PIPE)
    require(result.returncode == 0, f'{Path(args[0]).name} failed (exit {result.returncode}); private command output withheld')
    return result.stdout or b''


def context():
    run_id, attempt = os.environ.get('GITHUB_RUN_ID', ''), os.environ.get('GITHUB_RUN_ATTEMPT', '')
    require(run_id.isdigit() and attempt.isdigit(), 'Requires a GitHub Actions run context')
    return Path.home() / 'shelf/tmp' / f'ios-signing-{run_id}-{attempt}'


def save_state(folder, state):
    (folder / 'state.json').write_text(json.dumps(state))


def cleanup():
    folder = context()
    if not folder.exists():
        return
    require(not folder.is_symlink(), 'Refusing symlinked signing directory')
    state = json.loads((folder / 'state.json').read_text()) if (folder / 'state.json').exists() else {}
    errors = []
    if 'keychains' in state:
        try:
            run(['security', 'list-keychains', '-d', 'user', '-s', *state['keychains']])
        except BuildError:
            errors.append('keychain search-list restoration')
    keychain = folder / 'signing.keychain-db'
    if keychain.exists():
        try:
            run(['security', 'delete-keychain', str(keychain)])
        except BuildError:
            errors.append('temporary keychain deletion')
    for name in state.get('profiles', []):
        path = Path(name)
        allowed = [Path.home() / 'Library/MobileDevice/Provisioning Profiles',
                   Path.home() / 'Library/Developer/Xcode/UserData/Provisioning Profiles']
        require(path.parent in allowed and re.fullmatch(r'[0-9A-Fa-f-]{36}\.mobileprovision', path.name), 'Unexpected cleanup profile path')
        try:
            path.unlink(missing_ok=True)
        except OSError:
            errors.append('temporary provisioning profile deletion')
    # Keep state for the always() retry if any signing cleanup failed.
    for item in folder.iterdir():
        if item.name not in ('state.json', 'signing.keychain-db'):
            if item.is_dir():
                shutil.rmtree(item)
            else:
                item.unlink()
    if not errors:
        shutil.rmtree(folder)
    require(not errors, 'Cleanup incomplete: ' + ', '.join(errors))
    print('Temporary signing credentials removed; original keychain search list restored')


def der_signature_to_raw(data):
    def read(offset, tag):
        require(data[offset] == tag, 'Invalid ES256 signature encoding')
        size = data[offset + 1]
        start = offset + 2
        if size & 128:
            count = size & 127
            size = int.from_bytes(data[start:start + count], 'big')
            start += count
        return data[start:start + size], start + size, start
    _, end, start = read(0, 0x30)
    r, following, _ = read(start, 0x02)
    s, final, _ = read(following, 0x02)
    require(end == final == len(data), 'Invalid ES256 signature length')
    return int.from_bytes(r, 'big').to_bytes(32, 'big') + int.from_bytes(s, 'big').to_bytes(32, 'big')


def apple_number_check(key_file, key_id, issuer, number):
    def encode(value):
        return base64.urlsafe_b64encode(value).rstrip(b'=')
    now = int(time.time())
    header = {'alg': 'ES256', 'kid': key_id, 'typ': 'JWT'}
    claims = {'iss': issuer, 'iat': now, 'exp': now + 600, 'aud': 'appstoreconnect-v1'}
    body = b'.'.join(encode(json.dumps(v, separators=(',', ':')).encode()) for v in (header, claims))
    signature = run(['openssl', 'dgst', '-sha256', '-sign', str(key_file)], data=body)
    token = (body + b'.' + encode(der_signature_to_raw(signature))).decode()

    def get(url):
        parsed = urllib.parse.urlparse(url)
        require(parsed.scheme == 'https' and parsed.netloc == 'api.appstoreconnect.apple.com', 'Unexpected Apple API pagination origin')
        request = urllib.request.Request(url, headers={'Authorization': 'Bearer ' + token, 'Accept': 'application/json'})
        try:
            with urllib.request.urlopen(request, timeout=30) as response:
                return json.load(response)
        except urllib.error.HTTPError as error:
            raise BuildError(f'Apple build-number check failed (HTTP {error.code}); response withheld') from None
        except (urllib.error.URLError, ValueError):
            raise BuildError('Apple build-number check unavailable; no upload permitted') from None

    query = urllib.parse.urlencode({'filter[bundleId]': BUNDLE, 'limit': '2'})
    apps = get('https://api.appstoreconnect.apple.com/v1/apps?' + query)
    require(len(apps.get('data', [])) == 1 and not apps.get('links', {}).get('next'), 'Expected exactly one existing Shelf app in Apple account')
    app = apps['data'][0]
    require(app['attributes']['bundleId'] == BUNDLE and str(app['id']).isdigit(), 'Unexpected Apple app identity')
    url = f"https://api.appstoreconnect.apple.com/v1/apps/{app['id']}/builds?limit=200&fields%5Bbuilds%5D=version"
    seen = set()
    while url:
        require(url not in seen and len(seen) < 1000, 'Unexpected Apple build pagination')
        seen.add(url)
        page = get(url)
        require(isinstance(page.get('data'), list), 'Invalid Apple build list')
        require(all(str(b['attributes']['version']) != number for b in page['data']), 'Apple already received this build number; choose a new number')
        url = page.get('links', {}).get('next')
    print('Apple API check: selected build number not present in received Shelf builds')


def validate_profile(profile, now=None):
    now = now or dt.datetime.now(dt.timezone.utc)
    expires = profile['ExpirationDate']
    if expires.tzinfo is None:
        expires = expires.replace(tzinfo=dt.timezone.utc)
    require(expires > now, 'Provisioning profile expired')
    require(profile['TeamIdentifier'] == [TEAM], 'Wrong profile team')
    require(re.fullmatch(r'[0-9A-Fa-f-]{36}', profile['UUID']), 'Invalid profile UUID')
    ent = profile['Entitlements']
    require(ent['application-identifier'] == f'{TEAM}.{BUNDLE}', 'Wrong explicit profile app ID')
    require(ent.get('com.apple.developer.team-identifier') == TEAM, 'Wrong profile entitlement team')
    require(ent.get('get-task-allow') is False, 'Development profile is not allowed')
    require(not profile.get('ProvisionedDevices') and not profile.get('ProvisionsAllDevices'), 'Requires App Store distribution profile')
    require(ent.get('com.apple.developer.applesignin') == ['Default'], 'Profile lacks existing Sign In with Apple entitlement')
    require(profile.get('DeveloperCertificates'), 'Profile has no distribution certificate')


def prepare_project(project, uuid, identity, keychain):
    text = project.read_text()
    matches = 0
    pattern = r'(buildSettings = \{\n)(.*?)(\n\t{3}\};)'
    def replace(match):
        nonlocal matches
        settings = match[2]
        if f'PRODUCT_BUNDLE_IDENTIFIER = {BUNDLE};' not in settings:
            return match[0]
        require(f'DEVELOPMENT_TEAM = {TEAM};' in settings, 'Runner project team changed')
        require('CODE_SIGN_STYLE = Automatic;' in settings, 'Unexpected Runner signing settings')
        matches += 1
        settings = settings.replace('CODE_SIGN_STYLE = Automatic;', 'CODE_SIGN_STYLE = Manual;')
        settings += f'\n\t\t\t\tPROVISIONING_PROFILE_SPECIFIER = "{uuid}";'
        settings += f'\n\t\t\t\t"CODE_SIGN_IDENTITY[sdk=iphoneos*]" = "{identity}";'
        settings += f'\n\t\t\t\tOTHER_CODE_SIGN_FLAGS = "--keychain {keychain}";'
        return match[1] + settings + match[3]
    updated = re.sub(pattern, replace, text, flags=re.S)
    require(matches == 3, 'Expected exactly three Runner configurations; refusing guessed project edits')
    project.write_text(updated)  # Only the ephemeral runner checkout is changed.


def verify_ipa(ipa, folder, number, identity, profile_uuid):
    with zipfile.ZipFile(ipa) as archive:
        require(archive.testzip() is None, 'IPA ZIP checksum failure')
        for name in archive.namelist():
            require(not name.startswith('/') and '..' not in Path(name).parts, 'Unsafe IPA archive path')
    # ditto preserves executable modes and framework symlinks from Apple's IPA.
    run(['ditto', '-x', '-k', str(ipa), str(folder / 'exported')])
    apps = list((folder / 'exported/Payload').glob('*.app'))
    require(len(apps) == 1, 'Expected one app in IPA')
    app = apps[0]
    info = plistlib.loads((app / 'Info.plist').read_bytes())
    require(info['CFBundleIdentifier'] == BUNDLE and info['CFBundleVersion'] == number and info['CFBundleShortVersionString'] == '1.0.0', 'Exported app identity/version mismatch')
    run(['codesign', '--verify', '--deep', '--strict', str(app)])
    output = run(['codesign', '-d', '--entitlements', ':-', str(app)])
    ent = plistlib.loads(output)
    require(ent.get('application-identifier') == f'{TEAM}.{BUNDLE}', 'Signed app ID mismatch')
    require(ent.get('com.apple.developer.team-identifier') == TEAM and ent.get('get-task-allow') is False, 'Signed team/debug entitlement mismatch')
    require(ent.get('com.apple.developer.applesignin') == ['Default'], 'Signed Apple entitlement missing')
    embedded = plistlib.loads(run(['security', 'cms', '-D', '-i', str(app / 'embedded.mobileprovision')]))
    validate_profile(embedded)
    require(embedded['UUID'] == profile_uuid, 'Unexpected exported provisioning profile')
    cert_prefix = folder / 'signed-cert'
    run(['codesign', '-d', '--extract-certificates', str(cert_prefix), str(app)])
    leaf = Path(str(cert_prefix) + '0').read_bytes()
    require(hashlib.sha1(leaf).hexdigest().upper() == identity, 'Exported signer does not match supplied profile certificate')
    print('PASS: signed IPA CRC, package/version, strict signatures, team, entitlements, profile and signing certificate')


def build():
    require(os.environ.get('GITHUB_EVENT_NAME') == 'workflow_dispatch' and os.environ.get('GITHUB_REF') == 'refs/heads/ios/foundation', 'Only manual dispatch from ios/foundation is allowed')
    number = os.environ.get('SHELF_IOS_BUILD_NUMBER', '')
    require(re.fullmatch(r'[1-9][0-9]*', number), 'Specify an explicit positive unused iOS build number')
    require(os.environ.get('APPLE_NUMBER_UNUSED') == 'true', 'Owner must confirm selected number was not received by Apple')
    if number == '1':
        require(os.environ.get('CODEMAGIC_CANCELLED_BEFORE_UPLOAD') == 'true', 'Build 1 requires confirmed Codemagic cancellation before upload')
    require(os.environ.get('SHELF_API_BASE_URL') == 'https://shelf.services/api/', 'Approved production API required')
    source = os.environ.get('SHELF_SOURCE_SHA', '')
    require(re.fullmatch(r'[0-9a-f]{40}', source), 'Select a full lowercase commit SHA')
    require(run(['git', 'rev-parse', 'HEAD']).decode().strip() == source, 'Checkout does not match selected source')
    run(['git', 'merge-base', '--is-ancestor', source, 'refs/remotes/origin/ios/foundation'])
    material = {name: os.environ.pop(name, '') for name in SECRET_NAMES}
    require(all(material.values()), 'One or more required GitHub signing secrets are missing')
    key_id, issuer = material[SECRET_NAMES[3]], material[SECRET_NAMES[4]]
    require(re.fullmatch(r'[A-Z0-9]{10}', key_id) and re.fullmatch(r'[0-9A-Fa-f-]{36}', issuer), 'Invalid App Store Connect key metadata')
    os.umask(0o077)
    folder = context()
    require(not folder.exists(), 'Signing workspace already exists; cleanup before retry')
    folder.mkdir(parents=True, mode=0o700)
    state = {'profiles': []}
    save_state(folder, state)
    def cancelled(signum, frame):
        raise BuildError('Run interrupted; removing temporary signing credentials')
    signal.signal(signal.SIGTERM, cancelled)
    signal.signal(signal.SIGINT, cancelled)
    try:
        p12, profile_file = folder / 'distribution.p12', folder / 'profile.mobileprovision'
        for path, value in ((p12, material[SECRET_NAMES[0]]), (profile_file, material[SECRET_NAMES[2]])):
            path.write_bytes(base64.b64decode(''.join(value.split()), validate=True))
        private_keys = folder / 'private_keys'
        private_keys.mkdir()
        key_file = private_keys / f'AuthKey_{key_id}.p8'
        key_file.write_text(material[SECRET_NAMES[5]])
        run(['openssl', 'pkey', '-in', str(key_file), '-check', '-noout'])
        apple_number_check(key_file, key_id, issuer, number)
        profile = plistlib.loads(run(['security', 'cms', '-D', '-i', str(profile_file)]))
        validate_profile(profile)
        keychain = folder / 'signing.keychain-db'
        state['keychains'] = shlex.split(run(['security', 'list-keychains', '-d', 'user']).decode())
        save_state(folder, state)
        password = secrets.token_urlsafe(36)
        run(['security', 'create-keychain', '-p', password, str(keychain)])
        run(['security', 'set-keychain-settings', '-lut', '21600', str(keychain)])
        run(['security', 'unlock-keychain', '-p', password, str(keychain)])
        run(['security', 'import', str(p12), '-P', material[SECRET_NAMES[1]], '-k', str(keychain), '-t', 'cert', '-f', 'pkcs12', '-T', '/usr/bin/codesign', '-T', '/usr/bin/security'])
        run(['security', 'set-key-partition-list', '-S', 'apple-tool:,apple:,codesign:', '-s', '-k', password, str(keychain)])
        run(['security', 'list-keychains', '-d', 'user', '-s', str(keychain), *state['keychains']])
        identities = run(['security', 'find-identity', '-v', '-p', 'codesigning', str(keychain)]).decode()
        supplied = re.findall(r'\b([0-9A-F]{40})\s+"Apple Distribution[^\"]*"', identities)
        profile_ids = {hashlib.sha1(cert).hexdigest().upper() for cert in profile['DeveloperCertificates']}
        matching = [identity for identity in supplied if identity in profile_ids]
        require(len(matching) == 1, 'Requires one valid matching Apple Distribution private signing identity')
        identity = matching[0]
        for parent in (Path.home() / 'Library/MobileDevice/Provisioning Profiles', Path.home() / 'Library/Developer/Xcode/UserData/Provisioning Profiles'):
            parent.mkdir(parents=True, exist_ok=True)
            path = parent / (profile['UUID'] + '.mobileprovision')
            require(not path.exists(), 'Refusing to overwrite a preexisting profile')
            state['profiles'].append(str(path))
            save_state(folder, state)
            path.write_bytes(profile_file.read_bytes())
        export_options = {'method': 'app-store-connect', 'destination': 'export', 'signingStyle': 'manual',
                          'teamID': TEAM, 'signingCertificate': identity,
                          'provisioningProfiles': {BUNDLE: profile['UUID']},
                          'testFlightInternalTestingOnly': True, 'manageAppVersionAndBuildNumber': False}
        export_file = folder / 'ExportOptions.plist'
        export_file.write_bytes(plistlib.dumps(export_options))
        root = Path.cwd()
        prepare_project(root / 'mobile/ios/Runner.xcodeproj/project.pbxproj', profile['UUID'], identity, keychain)
        os.chdir(root / 'mobile')
        run(['flutter', 'build', 'ipa', '--release', '--no-pub', '--build-name=1.0.0', '--build-number=' + number,
             '--export-options-plist=' + str(export_file), '--dart-define=SHELF_API_BASE_URL=https://shelf.services/api/',
             '--dart-define=SHELF_TEST_MODE=false', '--dart-define=SHELF_INTERNAL_TEST_PURCHASES=false'], visible=True)
        ipas = list(Path('build/ios/ipa').glob('*.ipa'))
        require(len(ipas) == 1, 'Expected exactly one signed IPA')
        verify_ipa(ipas[0], folder, number, identity, profile['UUID'])
        evidence = Path('build/ios-github-evidence')
        evidence.mkdir()
        shutil.copy('ios/Podfile.lock', evidence / 'Podfile.lock')
        shutil.copy(export_file, evidence / 'ExportOptions.plist')
        (evidence / 'verification.json').write_text(json.dumps({'source_sha': source, 'team': TEAM, 'bundle': BUNDLE,
            'build_number': number, 'internal_only': True, 'checkout': False,
            'ipa_sha256': hashlib.sha256(ipas[0].read_bytes()).hexdigest(), 'signature_verified': True}, indent=2))
        print('Validating signed IPA with Apple before upload')
        auth_args = ['--apiKey', key_id, '--apiIssuer', issuer]
        upload_env = {'API_PRIVATE_KEYS_DIR': str(private_keys)}
        run(['xcrun', 'altool', '--validate-app', '--type', 'ios', '-f', str(ipas[0]), *auth_args], extra_env=upload_env)
        apple_number_check(key_file, key_id, issuer, number)
        # Upload only. No review, release, tester-assignment or credential-creation API.
        run(['xcrun', 'altool', '--upload-app', '--type', 'ios', '-f', str(ipas[0]), *auth_args], extra_env=upload_env)
        print('Apple upload command accepted; processing and iPhone installation remain separate checks')
    finally:
        # Keep the primary error visible even if cleanup also fails.
        had_error = sys.exc_info()[0] is not None
        try:
            cleanup()
        except Exception:
            if not had_error:
                raise
            print('Cleanup needs always() retry; original build/upload failure preserved', file=sys.stderr)


if __name__ == '__main__':
    try:
        if sys.argv[1:] == ['cleanup']:
            cleanup()
        elif not sys.argv[1:]:
            build()
        else:
            raise BuildError('Unknown command')
    except BuildError as error:
        print(str(error), file=sys.stderr)
        sys.exit(1)
    except Exception:
        # Never expose subprocess arguments, decoded files, tokens or secret values.
        print('Signed workflow failed; private details withheld. No success claimed.', file=sys.stderr)
        sys.exit(1)
