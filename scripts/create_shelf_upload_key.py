#!/usr/bin/env python3
"""Owner-approved NEW Shelf upload key; private verified backup, no secrets in output."""
import hashlib
import os
from pathlib import Path
import secrets
import shutil
import subprocess
from datetime import datetime, timezone

KEYTOOL = '/home/shelf/android/jdk-17/bin/keytool'
ROOT = Path('/home/shelf/android/keys')
BACKUPS = Path('/home/shelf/backups/keys')

def main():
    os.umask(0o077)
    ROOT.mkdir(parents=True, exist_ok=True)
    BACKUPS.mkdir(parents=True, exist_ok=True)
    key = ROOT / 'shelf-upload.jks'
    password_file = ROOT / 'shelf-upload.secret'
    properties = ROOT / 'shelf-upload.properties'
    if any(p.exists() for p in [key, password_file, properties]):
        raise RuntimeError('An upload-key artifact already exists; never overwrite it.')
    password_file.write_text(secrets.token_urlsafe(48) + '\n')
    subprocess.run([KEYTOOL, '-genkeypair', '-keystore', str(key), '-storetype', 'PKCS12',
                    '-alias', 'shelf-upload', '-keyalg', 'RSA', '-keysize', '4096', '-validity', '10000',
                    '-dname', 'CN=Shelf Upload, O=Shelf, C=US', '-storepass:file', str(password_file)],
                   check=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    password = password_file.read_text().strip()
    properties.write_text(f'storeFile={key}\nstorePassword={password}\nkeyAlias=shelf-upload\nkeyPassword={password}\n')
    cert = subprocess.run([KEYTOOL, '-exportcert', '-keystore', str(key), '-alias', 'shelf-upload',
                           '-storepass:file', str(password_file)], check=True, stdout=subprocess.PIPE, stderr=subprocess.DEVNULL).stdout
    certificate = ROOT / 'shelf-upload-cert.der'
    certificate.write_bytes(cert)
    folder = BACKUPS / ('shelf-upload-' + datetime.now(timezone.utc).strftime('%Y%m%d-%H%M%S'))
    folder.mkdir(mode=0o700)
    for source in [key, password_file, properties, certificate]:
        os.chmod(source, 0o600)
        target = folder / source.name
        shutil.copyfile(source, target)
        os.chmod(target, 0o600)
        assert hashlib.sha256(target.read_bytes()).digest() == hashlib.sha256(source.read_bytes()).digest()
    restored = subprocess.run([KEYTOOL, '-exportcert', '-keystore', str(folder / key.name), '-alias', 'shelf-upload',
                               '-storepass:file', str(folder / password_file.name)], check=True, stdout=subprocess.PIPE, stderr=subprocess.DEVNULL).stdout
    assert restored == cert
    fingerprint = lambda name: ':'.join(getattr(hashlib, name)(cert).hexdigest().upper()[i:i+2] for i in range(0, getattr(hashlib, name)(cert).digest_size * 2, 2))
    print('New Shelf upload key created; private key/password not displayed.')
    print('Verified backup: ' + str(folder))
    print('Upload certificate SHA-1: ' + fingerprint('sha1'))
    print('Upload certificate SHA-256: ' + fingerprint('sha256'))

if __name__ == '__main__':
    try:
        main()
    except Exception as error:
        print(f'Upload key creation/backup failed ({type(error).__name__}); stop and inspect privately.')
        raise SystemExit(1)
