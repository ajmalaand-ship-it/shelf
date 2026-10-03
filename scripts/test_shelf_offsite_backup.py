"""Focused backup safety tests use synthetic files, encryption and a disposable DB."""
import io
import json
import os
from pathlib import Path
import subprocess
import tarfile
import tempfile
import unittest
from unittest.mock import patch
import zipfile

import shelf_offsite_backup as backup


class OffsiteBackupTests(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory(prefix='offsite-test-')
        self.addCleanup(self.temp.cleanup)
        self.root = Path(self.temp.name)
        self.key = self.root / 'key'
        self.key.write_text('d' * 64 + '\n')
        self.key.chmod(0o600)
        home = self.root / 'gnupg'
        home.mkdir(mode=0o700)
        self.patches = [patch.object(backup, 'KEY', self.key),
                        patch.dict(os.environ, {'GNUPGHOME': str(home)})]
        for item in self.patches:
            item.start()
            self.addCleanup(item.stop)

    def test_saved_oauth_resumes_and_only_verified_default_drive_is_saved(self):
        config = self.root / 'rclone.conf'
        config.write_text('[shelf_onedrive]\ntype = onedrive\ntoken = {"access_token":"fixture","refresh_token":"retain"}\n')
        config.chmod(0o600)
        responses = [{'id': 'fixture-drive', 'driveType': 'personal', 'quota': {'remaining': 100}},
                     {'id': 'root', 'folder': {}, 'parentReference': {'driveId': 'fixture-drive', 'driveType': 'personal'}}]
        with patch.object(backup, 'CONFIG', config), patch.object(backup, 'prepare'), \
             patch.object(backup, 'graph_get', side_effect=responses) as get, \
             patch.object(backup.subprocess, 'Popen') as browser:
            backup.authorize()
        browser.assert_not_called()
        self.assertEqual(get.call_count, 2)
        parser = backup.configparser.ConfigParser(); parser.read(config)
        self.assertEqual(parser['shelf_onedrive']['drive_id'], 'fixture-drive')
        self.assertEqual(json.loads(parser['shelf_onedrive']['token'])['refresh_token'], 'retain')

    def test_failed_root_does_not_save_selection_or_retry(self):
        config = self.root / 'rclone.conf'
        original = '[shelf_onedrive]\ntoken = {"access_token":"fixture"}\n'
        config.write_text(original); config.chmod(0o600)
        with patch.object(backup, 'CONFIG', config), \
             patch.object(backup, 'graph_get', side_effect=[{'id': 'drive', 'driveType': 'personal'},
                backup.OneDriveSetupError('Microsoft drive verification failed (HTTP 400)')]) as get:
            with self.assertRaises(backup.OneDriveSetupError):
                backup.finish_authorization()
        self.assertEqual(get.call_count, 2)
        self.assertEqual(config.read_text(), original)

    def test_encryption_roundtrip_wrong_key_and_corruption(self):
        plain, encrypted, restored = [self.root / n for n in ('plain', 'encrypted', 'restored')]
        content = 'Synthetic secret and source: پښتو فارسی\n'.encode()
        plain.write_bytes(content)
        backup.crypt(plain, encrypted)
        self.assertNotIn(content, encrypted.read_bytes())
        backup.crypt(encrypted, restored, decrypt=True)
        self.assertEqual(restored.read_bytes(), content)
        self.key.write_text('e' * 64 + '\n')
        with self.assertRaises(RuntimeError):
            backup.crypt(encrypted, self.root / 'wrong-key', decrypt=True)
        self.key.write_text('d' * 64 + '\n')
        encrypted.write_bytes(encrypted.read_bytes()[:-20])
        with self.assertRaises(RuntimeError):
            backup.crypt(encrypted, self.root / 'damaged', decrypt=True)

    def test_unsafe_tar_symlinks_traversal_and_duplicates_refused(self):
        for name, kind, duplicate in [('escape', tarfile.SYMTYPE, False),
                                      ('../escape', tarfile.REGTYPE, False),
                                      ('/escape', tarfile.REGTYPE, False),
                                      ('same', tarfile.REGTYPE, True)]:
            with self.subTest(name=name):
                memory = io.BytesIO()
                with tarfile.open(fileobj=memory, mode='w') as archive:
                    member = tarfile.TarInfo(name)
                    member.type = kind
                    member.linkname = '/etc/passwd' if kind == tarfile.SYMTYPE else ''
                    archive.addfile(member)
                    if duplicate:
                        archive.addfile(member)
                memory.seek(0)
                with tarfile.open(fileobj=memory) as archive, self.assertRaises(RuntimeError):
                    backup.safe_extract(archive, self.root / 'restore')

    def test_transfer_uses_only_copyto_inside_shelf_folder_and_checks_space(self):
        file = self.root / 'fixture'
        calls = []
        with patch.object(backup, 'valid_package', return_value={'bytes': 1024, 'sha256': 'fixture'}), \
             patch.object(backup, 'quota', return_value={'free': 1024**3}), \
             patch.object(backup, 'rclone', side_effect=lambda *args: calls.append(args)), \
             patch.object(backup, 'ROOT', self.root):
            backup.upload(file)
        self.assertEqual(calls, [('copyto', file, 'shelf_onedrive:Shelf-Backups/fixture', '--immutable'),
                                ('copyto', file.with_suffix('.json'), 'shelf_onedrive:Shelf-Backups/fixture.json', '--immutable')])
        with patch.object(backup, 'valid_package', return_value={'bytes': 1024}), \
             patch.object(backup, 'quota', return_value={'free': 0}), \
             patch.object(backup, 'rclone') as transfer, self.assertRaises(RuntimeError):
            backup.upload(file)
        transfer.assert_not_called()

    def test_secrets_key_directory_excluded_and_symlinks_fail_closed(self):
        secrets = self.root / 'secrets'
        (secrets / 'backup').mkdir(parents=True)
        (secrets / 'backup' / 'key').write_text('exclude')
        (secrets / 'provider').write_text('fixture')
        self.assertEqual(list(backup.tree_files(secrets, ('backup',))), [secrets / 'provider'])
        (secrets / 'link').symlink_to(self.key)
        with self.assertRaises(RuntimeError):
            list(backup.tree_files(secrets, ('backup',)))

    def test_isolated_sql_restore_compares_schema_rows_and_rejects_missing_data(self):
        # The same drill that will consume the OneDrive download. No TCP socket,
        # no real database names/users/passwords. SQL contains exact Unicode.
        workspace = self.root / 'db'
        workspace.mkdir()
        fixture = self.root / 'fixture'
        fixture.mkdir()
        sql = b"CREATE TABLE works (id int PRIMARY KEY, body text) CHARACTER SET utf8mb4;\nINSERT INTO works VALUES (1, '" + 'پښتو فارسی'.encode() + b"');\n"
        (fixture / 'database.sql').write_bytes(sql)
        dumps = []
        actual_run = backup.run
        def capture_dump(args, **kwargs):
            result = actual_run(args, **kwargs)
            if args[0] == 'mysqldump':
                dumps.append(result)
            return result
        with patch.object(backup, 'run', side_effect=capture_dump), self.assertRaisesRegex(RuntimeError, 'Recovered database differs'):
            # A hand-written incomplete dump cannot match a canonical restore.
            backup.restore_database(fixture, workspace)
        self.assertFalse((workspace / 'mysql.pid').exists())
        self.assertEqual(len(dumps), 1)
        (fixture / 'database.sql').write_bytes(dumps[0])
        second = self.root / 'second-db'
        second.mkdir()
        self.assertEqual(backup.restore_database(fixture, second), 1)
        self.assertFalse((second / 'mysql.pid').exists())

    def test_failure_alert_has_no_secrets_and_targets_only_owner(self):
        with patch.object(backup, 'ROOT', self.root), \
             patch.object(backup, 'run', side_effect=[b'owner@example.test', b'']) as process:
            backup.report_failure()
        alert = process.call_args.kwargs['input'].decode()
        self.assertIn('To: owner@example.test', alert)
        self.assertNotIn(self.key.read_text().strip(), alert)
        self.assertNotIn('password', alert)
        self.assertTrue((self.root / 'failure.json').is_file())


if __name__ == '__main__':
    unittest.main()
