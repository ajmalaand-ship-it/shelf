"""Offline helper tests: never call main() or connect to a database."""

import json
from pathlib import Path
import subprocess
import tempfile
import unittest
from unittest.mock import patch
import zipfile

import shelf_daily_backup as backup


class BackupTests(unittest.TestCase):
    def setUp(self):
        self.temporary = tempfile.TemporaryDirectory()
        self.addCleanup(self.temporary.cleanup)
        self.root = Path(self.temporary.name).resolve()

    def test_dotenv_uses_file_not_inherited_settings(self):
        app = self.root / 'app'
        app.mkdir()
        (app / 'vendor').symlink_to(backup.APP / 'vendor', target_is_directory=True)
        (app / '.env').write_text(
            'DB_CONNECTION=mysql\nDB_HOST=localhost\nDB_PORT=3306\n'
            'DB_DATABASE=fixture\nDB_USERNAME=fixture\nDB_PASSWORD="fake # password"\n'
            'POETRY_BACKUP_PATH="/tmp/fixture backups"\n'
        )
        with patch.dict(backup.os.environ, {'DB_DATABASE': 'wrong'}):
            settings = backup.read_settings(app)
        self.assertEqual(settings['DB_DATABASE'], 'fixture')
        self.assertEqual(settings['DB_PASSWORD'], 'fake # password')

    def test_retention_only_removes_old_backups_in_selected_root(self):
        root = self.root / 'backups'
        root.mkdir()
        for day in range(1, 17):
            folder = root / f'202609{day:02}-030000'
            folder.mkdir()
            (folder / 'manifest.json').write_text(json.dumps({'system': 'Shelf'}))
        unrelated = root / 'manual-backup'
        unrelated.mkdir()
        outside = self.root / 'outside'
        outside.mkdir()
        (outside / 'keep').write_text('fixture')
        (root / '20260801-030000').symlink_to(outside, target_is_directory=True)
        (root / '20260901-030000' / 'link').symlink_to(outside, target_is_directory=True)
        backup.retain_backups(root)
        self.assertEqual(len(backup.backup_folders(root)), 14)
        self.assertFalse((root / '20260902-030000').exists())
        self.assertTrue(unrelated.is_dir())
        self.assertTrue((outside / 'keep').is_file())

    def test_redirected_or_application_backup_roots_rejected(self):
        app = self.root / 'app'
        app.mkdir()
        link = self.root / 'link'
        link.symlink_to(app, target_is_directory=True)
        for root in (link, app, app / 'storage', self.root, Path('/'), Path('relative')):
            with self.subTest(root=root), self.assertRaises(RuntimeError):
                backup.backup_root({'POETRY_BACKUP_PATH': str(root)}, app)

    def test_full_media_and_integrity_checks(self):
        for name in backup.MEDIA_PATHS:
            source = self.root / 'storage' / 'app' / name
            (source / 'empty').mkdir(parents=True)
            (source / 'fixture.txt').write_text(name)
        (self.root / 'storage' / 'app' / 'excluded.txt').write_text('not in scope')
        backup.write_media(self.root, self.root / 'media.zip')
        with zipfile.ZipFile(self.root / 'media.zip') as archive:
            for name in backup.MEDIA_PATHS:
                self.assertIn(f'app/{name}/fixture.txt', archive.namelist())
                self.assertIn(f'app/{name}/empty/', archive.namelist())
            self.assertNotIn('app/excluded.txt', archive.namelist())
        sql = self.root / 'database.sql'
        sql.write_text('-- Dump completed on fixture\n')
        manifest = {'database_sha256': backup.sha256(sql),
                    'media_sha256': backup.sha256(self.root / 'media.zip')}
        (self.root / 'manifest.json').write_text(json.dumps(manifest))
        backup.verify(self.root)  # Real unzip -t, on synthetic media only.
        sql.write_text('tampered')
        with self.assertRaises(RuntimeError):
            backup.verify(self.root)
        manifest['database_sha256'] = backup.sha256(sql)
        (self.root / 'manifest.json').write_text(json.dumps(manifest))
        with self.assertRaises(RuntimeError):
            backup.verify(self.root)

    def test_media_symlinks_and_missing_directories_rejected(self):
        with self.assertRaises(RuntimeError):
            backup.write_media(self.root, self.root / 'missing.zip')
        for name in backup.MEDIA_PATHS:
            (self.root / 'storage' / 'app' / name).mkdir(parents=True)
        (self.root / 'storage/app/public/link').symlink_to(self.root, target_is_directory=True)
        with self.assertRaises(RuntimeError):
            backup.write_media(self.root, self.root / 'linked.zip')

    def test_dump_uses_private_credentials_and_cleans_up_on_failure(self):
        settings = {'DB_HOST': 'localhost', 'DB_PORT': '3306',
                    'DB_USERNAME': 'fixture', 'DB_PASSWORD': 'fake"\\# password',
                    'DB_DATABASE': 'fixture'}

        def fake_dump(args, **kwargs):
            self.assertEqual(args[0], 'mysqldump')
            self.assertEqual(args[-2:], ['--', 'fixture'])
            self.assertNotIn(settings['DB_PASSWORD'], ' '.join(args))
            self.assertNotIn('MYSQL_PWD', kwargs['env'])
            credentials = self.root / '.mysql.cnf'
            self.assertEqual(credentials.stat().st_mode & 0o777, 0o600)
            self.assertIn('password=' + backup.option_value(settings['DB_PASSWORD']),
                          credentials.read_text())
            raise subprocess.CalledProcessError(1, args)

        with patch.object(backup.subprocess, 'run', side_effect=fake_dump):
            with self.assertRaises(subprocess.CalledProcessError):
                backup.dump_database(self.root, settings)
        self.assertFalse((self.root / '.mysql.cnf').exists())


if __name__ == '__main__':
    unittest.main()
