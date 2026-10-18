#!/usr/bin/env python3
"""One bounded, anonymous real-Store capture via simctl; no tests or release build."""
import hashlib
import json
import os
from pathlib import Path
import plistlib
import subprocess
import sys
import time

ROOT = Path(__file__).resolve().parents[2]
MOBILE = ROOT / 'mobile'
OUT = ROOT / 'direct-ipad-store-capture'
DEADLINE = time.monotonic() + 1080


def run(args, timeout=120, capture=False, cwd=ROOT):
    remaining = int(DEADLINE - time.monotonic())
    if remaining <= 0:
        raise RuntimeError('Single capture run exceeded its 18-minute bound')
    result = subprocess.run(args, cwd=cwd, check=True, timeout=min(timeout, remaining),
                            text=True, capture_output=capture)
    return result.stdout if capture else None


def read(args):
    return json.loads(run(args, capture=True))


def main():
    os.umask(0o077)
    if sys.platform != 'darwin':
        raise RuntimeError('Direct capture requires the existing Codemagic macOS worker')
    api = os.environ.get('SHELF_API_BASE_URL', '')
    if not api.startswith('https://'):
        raise RuntimeError('Existing approved HTTPS API configuration is required')
    source = run(['git', 'rev-parse', 'HEAD:mobile'], capture=True).strip()
    if source != '1aecaddba930910a4110a660612365bf4abf8d84':
        raise RuntimeError('Mobile source must match existing App Store Build 8')
    OUT.mkdir(mode=0o700, exist_ok=False)
    temporary = ROOT / '.direct-ipad-capture-tmp'
    temporary.mkdir(mode=0o700, exist_ok=False)
    os.environ.update(TMPDIR=str(temporary), TMP=str(temporary), TEMP=str(temporary))
    run(['flutter', 'pub', 'get', '--enforce-lockfile'], timeout=240, cwd=MOBILE)
    # Existing source selector: requested owner preview with NO token fails closed
    # to the live public catalogue, rather than debug QA fixtures. No main/UI edits.
    run(['flutter', 'build', 'ios', '--simulator', '--debug', '--no-codesign', '--no-pub',
         '--dart-define=SHELF_API_BASE_URL=' + api,
         '--dart-define=OWNER_PREVIEW=true', '--dart-define=OWNER_PREVIEW_TOKEN=',
         '--dart-define=SHELF_TEST_MODE=false',
         '--dart-define=SHELF_INTERNAL_TEST_PURCHASES=false'], timeout=720, cwd=MOBILE)
    app = MOBILE / 'build/ios/iphonesimulator/Runner.app'
    info = plistlib.loads((app / 'Info.plist').read_bytes())
    if info['CFBundleIdentifier'] != 'services.shelf.app':
        raise RuntimeError('Unexpected simulator app identity')
    devices = read(['xcrun', 'simctl', 'list', 'devices', 'available', '--json'])['devices']
    choices = [d for runtime, rows in devices.items() if 'iOS' in runtime for d in rows
               if d.get('isAvailable') and 'iPad Pro 13-inch' in d['name']]
    if not choices:
        types = read(['xcrun', 'simctl', 'list', 'devicetypes', '--json'])['devicetypes']
        types = [d for d in types if 'iPad Pro 13-inch' in d['name']]
        runtimes = [r for r in read(['xcrun', 'simctl', 'list', 'runtimes', '--json'])['runtimes']
                    if r.get('isAvailable') and r['name'].startswith('iOS ')]
        if not types or not runtimes:
            raise RuntimeError('No installed 13-inch iPad simulator type/runtime; no retry')
        runtime = max(runtimes, key=lambda r: tuple(map(int, r['version'].split('.'))))
        device_type = sorted(types, key=lambda d: d['name'])[-1]
        udid = run(['xcrun', 'simctl', 'create', 'Shelf one-shot iPad Store',
                    device_type['identifier'], runtime['identifier']], capture=True).strip()
        selected = {'name': device_type['name'], 'udid': udid, 'state': 'Shutdown'}
    else:
        selected = sorted(choices, key=lambda d: d['name'])[-1]
    udid = selected['udid']
    try:
        if selected.get('state') != 'Booted':
            run(['xcrun', 'simctl', 'boot', udid])
        run(['xcrun', 'simctl', 'bootstatus', udid, '-b'], timeout=120)
        run(['xcrun', 'simctl', 'install', udid, str(app)])
        container = Path(run(['xcrun', 'simctl', 'get_app_container', udid,
                              'services.shelf.app', 'data'], capture=True).strip())
        prefs = container / 'Library/Preferences/services.shelf.app.plist'
        prefs.parent.mkdir(parents=True, exist_ok=True)
        # An ordinary language preference; anonymous app, no credentials/ownership.
        saved = plistlib.loads(prefs.read_bytes()) if prefs.exists() else {}
        saved['flutter.shelf.interface_language'] = 'en'
        prefs.write_bytes(plistlib.dumps(saved))
        run(['xcrun', 'simctl', 'launch', udid, 'services.shelf.app'])
        time.sleep(45)  # One fixed render window; no navigation or capture retries.
        screenshot = OUT / 'ipad-13-store.png'
        run(['xcrun', 'simctl', 'io', udid, 'screenshot', '--type=png', str(screenshot)])
        validator = temporary / 'validate.swift'
        validator.write_text('''import AppKit
import Vision
let url = URL(fileURLWithPath: CommandLine.arguments[1])
let image = NSImage(contentsOf: url)!
var rect = CGRect(origin: .zero, size: image.size)
let cg = image.cgImage(forProposedRect: &rect, context: nil, hints: nil)!
precondition((cg.width == 2064 && cg.height == 2752) || (cg.width == 2048 && cg.height == 2732), "Wrong native 13-inch iPad dimensions")
let request = VNRecognizeTextRequest()
request.recognitionLevel = .accurate
request.recognitionLanguages = ["en-US"]
try VNImageRequestHandler(cgImage: cg).perform([request])
let texts = (request.results ?? []).compactMap { $0.topCandidates(1).first?.string }
let all = texts.joined(separator: "\\n").lowercased()
let navigation = ["store", "search", "my library", "settings"].filter { all.contains($0) }
precondition(navigation.count >= 3 && all.contains("new books") && all.contains("poetry"), "Not a rendered live Shelf Store; no retry")
precondition(!all.contains("could not load") && !all.contains("try again"), "Store shows a load failure")
print("PASS: native iPad \\(cg.width)x\\(cg.height), Shelf Store navigation, New books and Poetry rendered")
''')
        run(['swift', str(validator), str(screenshot)], timeout=90)
        evidence = {'mobile_tree': source, 'capture': 'xcrun simctl io screenshot',
                    'device': selected['name'], 'source_mode': 'live public catalogue',
                    'authenticated': False, 'integration_tests': False, 'capture_attempts': 1,
                    'release_build_created': False, 'release_upload': False,
                    'sha256': hashlib.sha256(screenshot.read_bytes()).hexdigest()}
        (OUT / 'evidence.json').write_text(json.dumps(evidence, indent=2) + '\n')
        print('PASS: one direct iPad Store capture completed; no release build/upload')
    finally:
        run(['xcrun', 'simctl', 'shutdown', udid], timeout=30)


if __name__ == '__main__':
    try:
        main()
    except Exception as error:
        print('Single iPad capture stopped: ' + type(error).__name__ + ': ' + str(error), file=sys.stderr)
        raise SystemExit(1)
