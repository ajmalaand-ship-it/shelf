#!/usr/bin/env python3
"""One simulator-only Codemagic capture run; no archive/export/Apple upload."""
import hashlib, json, os, pathlib, shutil, subprocess, sys
ROOT = pathlib.Path(__file__).resolve().parents[2]
MOBILE = ROOT / 'mobile'
OUT = ROOT / 'submission-captures'

def run(args, **kw):
    return subprocess.run(args, check=True, **kw)

def read(args):
    return json.loads(subprocess.check_output(args))

def prepare():
    if sys.platform != 'darwin':
        raise SystemExit('Capture requires the existing Codemagic Mac runner')
    if os.environ.get('SHELF_API_BASE_URL') != 'https://shelf.services/api/':
        raise SystemExit('Retain approved production API')
    if subprocess.check_output(['git', 'rev-parse', 'HEAD:mobile'], cwd=ROOT).strip() != b'1aecaddba930910a4110a660612365bf4abf8d84':
        raise SystemExit('Capture app must match Build 8 mobile source')
    OUT.mkdir(exist_ok=False)
    temp = ROOT / '.submission-capture-tmp'
    temp.mkdir(exist_ok=False)
    os.environ.update(TMPDIR=str(temp), TMP=str(temp), TEMP=str(temp))
    run(['flutter', 'pub', 'get', '--enforce-lockfile'], cwd=MOBILE)
    # Runner-only SDK instrumentation, never committed to app dependencies.
    p = MOBILE / 'pubspec.yaml'
    text = p.read_text()
    assert text.count('dev_dependencies:') == 1
    p.write_text(text.replace('dev_dependencies:', 'dev_dependencies:\n  integration_test:\n    sdk: flutter\n  flutter_driver:\n    sdk: flutter', 1))
    run(['flutter', 'pub', 'get'], cwd=MOBILE)
    main = (MOBILE / 'lib/main.dart').read_text()
    assert main.count('requestedMode: currentBuildMode,') == 1
    main = main.replace('requestedMode: currentBuildMode,', 'requestedMode: AppBuildMode.release,')
    needle = 'final repository = createPoetryDataSource(\n'
    assert main.count(needle) == 1
    main = main.replace(needle, needle + '    buildMode: AppBuildMode.release,\n', 1)
    # Debug simulator instrumentation explicitly selects the production source
    # mode; original main.dart, all UI, access and purchase gates stay unchanged.
    (MOBILE / 'lib/shelf_capture_main.dart').write_text(main)
    (MOBILE / 'integration_test').mkdir(exist_ok=True)
    (MOBILE / 'test_driver').mkdir(exist_ok=True)
    shutil.copyfile(ROOT / 'scripts/ios/submission_capture.dart', MOBILE / 'integration_test/submission_capture_test.dart')
    shutil.copyfile(ROOT / 'scripts/ios/submission_capture_driver.dart', MOBILE / 'test_driver/submission_capture_driver.dart')
    run(['flutter', 'analyze', '--no-pub', 'integration_test/submission_capture_test.dart', 'test_driver/submission_capture_driver.dart', 'lib/shelf_capture_main.dart'], cwd=MOBILE)


def main():
    prepare()
    devices = read(['xcrun', 'simctl', 'list', 'devices', 'available', '--json'])['devices']
    choices = [('iphone', ['iPhone 15 Pro Max', 'iPhone 14 Pro Max']),
               ('ipad', ['iPad Pro 13-inch (M5)', 'iPad Pro 13-inch (M4)', 'iPad Pro (12.9-inch) (6th generation)'])]
    evidence = {'source': subprocess.check_output(['git', 'rev-parse', 'HEAD'], cwd=ROOT, text=True).strip(),
                'app_source': '47f0e8e1ecf4ec5825f8c05d20231e229f7e04cf', 'live_api': True,
                'store_upload': False, 'simulators': []}
    for family, names in choices:
        selected = next((d for name in names for group in devices.values() for d in group if d['name'] == name and d.get('isAvailable')), None)
        if selected is None:
            types = read(['xcrun', 'simctl', 'list', 'devicetypes', '--json'])['devicetypes']
            device_type = next((t for name in names for t in types if t['name'] == name), None)
            runtimes = [r for r in read(['xcrun', 'simctl', 'list', 'runtimes', '--json'])['runtimes']
                        if r.get('isAvailable') and 'iOS' in r['name']]
            if not device_type or not runtimes:
                raise SystemExit('Supported ' + family + ' runtime/type unavailable; never resize images')
            runtime = max(runtimes, key=lambda r: tuple(int(x) for x in r['version'].split('.')))
            created = subprocess.check_output(['xcrun', 'simctl', 'create', 'Shelf submission ' + family,
                device_type['identifier'], runtime['identifier']], text=True).strip()
            selected = {'name': device_type['name'], 'udid': created}

        udid = selected['udid']
        run(['xcrun', 'simctl', 'shutdown', 'all'])
        run(['xcrun', 'simctl', 'boot', udid])
        run(['xcrun', 'simctl', 'bootstatus', udid, '-b'])
        os.environ.update(SHELF_CAPTURE_DEVICE=family, SHELF_CAPTURE_OUTPUT=str(OUT / family))
        command = ['flutter', 'drive', '--driver=test_driver/submission_capture_driver.dart',
                   '--target=integration_test/submission_capture_test.dart', '-d', udid, '--no-pub',
                   '--dart-define=SHELF_API_BASE_URL=https://shelf.services/api/',
                   '--dart-define=SHELF_TEST_MODE=false', '--dart-define=SHELF_INTERNAL_TEST_PURCHASES=false']
        # Credentials are never printed, committed, or sent to a different host.
        if os.environ.get('SHELF_REVIEW_EMAIL') and os.environ.get('SHELF_REVIEW_PASSWORD'):
            secret_file = pathlib.Path(os.environ['TMPDIR']) / 'reviewer.json'
            secret_file.write_text(json.dumps({'SHELF_CAPTURE_REVIEW_EMAIL': os.environ['SHELF_REVIEW_EMAIL'], 'SHELF_CAPTURE_REVIEW_PASSWORD': os.environ['SHELF_REVIEW_PASSWORD']}))
            secret_file.chmod(0o600)
            command += ['--dart-define-from-file=' + str(secret_file)]
        try:
            run(command, cwd=MOBILE)
        finally:
            secret = pathlib.Path(os.environ['TMPDIR']) / 'reviewer.json'
            if secret.exists(): secret.unlink()
        files = list((OUT / family).glob('*.png'))
        required = {'listing-store', 'listing-sample', *('iap-book-' + str(n) for n in range(3, 9))}
        assert required <= {p.stem for p in files}, 'Missing genuine captures'
        evidence['simulators'].append({'family': family, 'name': selected['name'], 'udid': udid,
            'files': {p.name: hashlib.sha256(p.read_bytes()).hexdigest() for p in files}})
        (OUT / 'capture-evidence.json').write_text(json.dumps(evidence, indent=2))
        run(['xcrun', 'simctl', 'shutdown', udid])

if __name__ == '__main__': main()
