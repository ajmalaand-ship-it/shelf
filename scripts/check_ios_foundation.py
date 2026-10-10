#!/usr/bin/env python3
"""Linux source verification only; never claims Xcode or device acceptance."""
from pathlib import Path
import json
import plistlib
import struct
import subprocess
import xml.etree.ElementTree as ET

root = Path(__file__).resolve().parents[1]
ios = root / 'mobile/ios'
for path in ios.rglob('*'):
    if path.suffix in ('.plist', '.entitlements'):
        plistlib.loads(path.read_bytes())
    elif path.suffix in ('.storyboard', '.xcscheme', '.xcworkspacedata'):
        ET.parse(path)
info = plistlib.loads((ios / 'Runner/Info.plist').read_bytes())
assert info['CFBundleDisplayName'] == 'Shelf'
assert info['CFBundleIdentifier'] == '$(PRODUCT_BUNDLE_IDENTIFIER)'
assert info['UIBackgroundModes'] == ['audio']
assert info['NSPhotoLibraryAddUsageDescription']
assert 'NSAppTransportSecurity' not in info  # No insecure HTTP exception.
entitlements = plistlib.loads((ios / 'Runner/Runner.entitlements').read_bytes())
assert entitlements['com.apple.developer.applesignin'] == ['Default']
assert entitlements['keychain-access-groups'] == ['$(AppIdentifierPrefix)$(CFBundleIdentifier)']
project = (ios / 'Runner.xcodeproj/project.pbxproj').read_text()
assert project.count('PRODUCT_BUNDLE_IDENTIFIER = services.shelf.app;') == 3
assert project.count('DEVELOPMENT_TEAM = YSLWJQDH8B;') == 3
assert project.count('CODE_SIGN_ENTITLEMENTS = Runner/Runner.entitlements;') == 3
assert 'IPHONEOS_DEPLOYMENT_TARGET = 15.0;' in project
assert 'SceneDelegate.swift in Sources' in project
for catalog in ios.glob('Runner/Assets.xcassets/*.appiconset'):
    for image in json.loads((catalog / 'Contents.json').read_text())['images']:
        png = (catalog / image['filename']).read_bytes()
        width, height = struct.unpack('>II', png[16:24])
        size = round(float(image['size'].split('x')[0]) * float(image['scale'][:-1]))
        assert (width, height) == (size, size)
        assert png[25] == 2, 'App icons must be RGB without alpha'
# Check complete accepted shared source/Android/fonts/assets and dependency versions.
changed = subprocess.check_output(['git', 'diff', '5f5c8f6', '--name-only', '--',
    'mobile/lib', 'mobile/android', 'mobile/assets', 'mobile/pubspec.yaml',
    'mobile/pubspec.lock'], cwd=root, text=True)
assert not changed, changed
config = (root / 'codemagic.yaml').read_text()
assert '\n    triggering:' not in config
assert '\n    publishing:' not in config
assert 'flutter build ios --release --no-codesign' in config
assert '--dart-define=SHELF_INTERNAL_TEST_PURCHASES=false' in config
assert '--enforce-lockfile' in config
assert 'SHELF_API_BASE_URL:?' in config
print('PASS: plist/XML, iOS identity/entitlements/icon sizes and opacity, manual unsigned CI, unchanged accepted shared/Android/assets/fonts/dependencies')
print('NOT CHECKED: Swift/Xcode compilation, CocoaPods resolution, signing, Apple/Google login, App Store purchase verification, iPhone behavior')
