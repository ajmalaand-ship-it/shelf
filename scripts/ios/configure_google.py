#!/usr/bin/env python3
"""Configure public Google iOS client/callback before building; no secrets."""
import os
import plistlib
import re
from pathlib import Path


def configure(path, client):
    plist = plistlib.loads(path.read_bytes())
    # Own only this provider's callback entry; preserve other URL handlers.
    urls = [u for u in plist.get('CFBundleURLTypes', []) if u.get('CFBundleURLName') != 'shelf.google']
    if client:
        if not re.fullmatch(r'[A-Za-z0-9-]+\.apps\.googleusercontent\.com', client):
            raise SystemExit('Invalid SHELF_GOOGLE_IOS_CLIENT_ID')
        plist['GIDClientID'] = client
        urls.append({'CFBundleURLName': 'shelf.google', 'CFBundleTypeRole': 'Editor',
                     'CFBundleURLSchemes': ['.'.join(reversed(client.split('.')))]})
    else:
        plist.pop('GIDClientID', None)
    if urls:
        plist['CFBundleURLTypes'] = urls
    else:
        plist.pop('CFBundleURLTypes', None)
    path.write_bytes(plistlib.dumps(plist, sort_keys=False))
    print('Google iOS client/callback configured' if client else 'Google iOS disabled: client ID not supplied')


if __name__ == '__main__':
    configure(Path('ios/Runner/Info.plist'), os.environ.get('SHELF_GOOGLE_IOS_CLIENT_ID', '').strip())
