#!/usr/bin/env python3
"""Execute the helper's exact receiver with synthetic bytes in a disposable private root."""
import hashlib
import json
import os
from pathlib import Path
import pwd
import re
import stat
import struct
import subprocess
import sys
import tempfile
import unittest

HELPER = Path(__file__).resolve().parents[2] / 'docs/IOS_ASC_KEY_UPLOAD.ps1'
SOURCE = HELPER.read_text()
RECEIVER = SOURCE.split("$receiver = @'\n", 1)[1].split("\n'@", 1)[0]
KEY = b'synthetic-transfer-fixture-only\x00\xff\r\n' * 300
KEY_ID = 'A1B2C3D4E5'
ISSUER = '11111111-2222-3333-4444-555555555555'

class TransferTests(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory(prefix='asc-upload-', dir=os.environ['SHELF_TEST_TMP'])
        self.addCleanup(self.temp.cleanup)
        self.root = Path(self.temp.name)
        self.private = self.root / 'secrets' / 'apple'
        self.private.mkdir(parents=True, mode=0o700)
        self.private.parent.chmod(0o700)
        self.key = self.private / 'shelf-asc-inspection.p8'
        self.meta = self.private / 'shelf-asc-inspection.json'
        self.receiver = RECEIVER.replace("ROOT = '/home/shelf'", 'ROOT = ' + repr(str(self.root)), 1)
        self.receiver = self.receiver.replace("ACCOUNT = 'shelf'", 'ACCOUNT = ' + repr(pwd.getpwuid(os.getuid()).pw_name), 1)

    def payload(self, data=KEY, **overrides):
        header = dict(key_id=KEY_ID, issuer_id=ISSUER, size=len(data), sha256=hashlib.sha256(data).hexdigest())
        header.update(overrides)
        encoded = json.dumps(header).encode()
        return struct.pack('<I', len(encoded)) + encoded + data

    def run_receiver(self, payload):
        result = subprocess.run([sys.executable, '-B', '-c', self.receiver], input=payload, capture_output=True)
        for secret in (KEY, KEY_ID.encode(), ISSUER.encode()):
            self.assertNotIn(secret, result.stdout + result.stderr)
        self.assertNotIn(b'Traceback', result.stderr)
        return result

    def refused(self, payload):
        self.assertNotEqual(self.run_receiver(payload).returncode, 0)
        self.assertFalse(os.path.lexists(self.key))
        self.assertFalse(os.path.lexists(self.meta))

    def test_binary_integrity_metadata_and_permissions(self):
        self.assertEqual(self.run_receiver(self.payload()).returncode, 0)
        self.assertEqual(self.key.read_bytes(), KEY)
        self.assertEqual(json.loads(self.meta.read_bytes()), dict(key_id=KEY_ID, issuer_id=ISSUER))
        for path in (self.key, self.meta):
            s = path.stat()
            self.assertEqual(stat.S_IMODE(s.st_mode), 0o600)
            self.assertEqual((s.st_uid, s.st_gid), (os.getuid(), pwd.getpwuid(os.getuid()).pw_gid))

    def test_original_file_collision_preserved(self):
        self.key.write_bytes(b'original')
        self.assertNotEqual(self.run_receiver(self.payload()).returncode, 0)
        self.assertEqual(self.key.read_bytes(), b'original')
        self.assertFalse(self.meta.exists())

    def test_metadata_collision_leaves_no_new_key(self):
        self.meta.write_bytes(b'original-metadata')
        self.assertNotEqual(self.run_receiver(self.payload()).returncode, 0)
        self.assertEqual(self.meta.read_bytes(), b'original-metadata')
        self.assertFalse(self.key.exists())

    def test_destination_symlink_refused(self):
        outside = self.root / 'untouched'
        outside.write_bytes(b'original')
        self.key.symlink_to(outside)
        self.assertNotEqual(self.run_receiver(self.payload()).returncode, 0)
        self.assertTrue(self.key.is_symlink())
        self.assertEqual(outside.read_bytes(), b'original')
        self.assertFalse(self.meta.exists())

    def test_dangling_metadata_symlink_refused(self):
        self.meta.symlink_to(self.root / 'missing')
        self.assertNotEqual(self.run_receiver(self.payload()).returncode, 0)
        self.assertTrue(self.meta.is_symlink())
        self.assertFalse(self.key.exists())

    def test_parent_symlink_refused(self):
        moved = self.root / 'moved'
        self.private.rename(moved)
        self.private.symlink_to(moved, target_is_directory=True)
        self.assertNotEqual(self.run_receiver(self.payload()).returncode, 0)
        self.assertEqual(list(moved.iterdir()), [])

    def test_unsafe_directory_refused(self):
        self.private.chmod(0o755)
        self.refused(self.payload())

    def test_incomplete_transfer_cleaned(self):
        self.refused(self.payload()[:-1])

    def test_hash_mismatch_cleaned(self):
        self.refused(self.payload(sha256='0' * 64))

    def test_extra_bytes_cleaned(self):
        self.refused(self.payload() + b'extra')

    def test_wrong_identifiers_and_bounds_refused(self):
        for fields in [dict(key_id='2VQ43945YB'), dict(key_id='../escape'), dict(issuer_id='not-uuid'),
                       dict(size=0), dict(size=16385), dict(size=True), dict(sha256='bad'), dict(extra='bad')]:
            with self.subTest(fields=fields):
                self.refused(self.payload(**fields))

    def test_short_or_unbounded_header_refused(self):
        for payload in [b'', b'\x01', struct.pack('<I', 4097), struct.pack('<I', 0), struct.pack('<I', 20) + b'{}']:
            self.refused(payload)

    def test_client_transfer_contract(self):
        compile(RECEIVER, str(HELPER), 'exec')
        self.assertEqual(SOURCE.count("$receiver = @'"), 1)
        for item in ['OpenFileDialog', 'NOT ShelfLogin or ShelfPurchases', '$keyId = $Matches[1]',
                     'ReparsePoint', "'Open', 'Read', 'Read'", 'StrictHostKeyChecking=yes',
                     '$process.StandardInput.BaseStream.Write($header', '$stream.CopyTo($process.StandardInput.BaseStream)',
                     '$process.StandardInput.Close()', "$stream.Dispose()", "$process.Dispose()"]:
            self.assertIn(item, SOURCE)
        pattern = re.search(r"\$name -cnotmatch '([^']+)'", SOURCE).group(1)
        self.assertEqual(re.fullmatch(pattern, 'AuthKey_A1B2C3D4E5.p8').group(1), KEY_ID)
        for invalid in ['AuthKey_A1B2C3D4E5.p8.exe', 'ShelfPurchases.p8', '../AuthKey_A1B2C3D4E5.p8']:
            self.assertIsNone(re.fullmatch(pattern, invalid))
        remote = re.search(r'\$remote = (.+)', SOURCE).group(1)
        for item in ['$metadata', '$issuerId', '$keyId', '$source', '$header']:
            self.assertNotIn(item, remote)
        for forbidden in ['Set-Acl', 'Set-Content', 'WriteAllBytes', 'Invoke-WebRequest', 'Invoke-RestMethod',
                          'Start-Transcript', 'Set-Clipboard']:
            self.assertNotIn(forbidden, SOURCE)
        self.assertEqual(SOURCE.count('InputBox('), 1)

if __name__ == '__main__':
    unittest.main(verbosity=2)
