"""Focused D14 checks: synthetic journal, boundary expiry, isolated old database."""
from datetime import datetime, timezone, timedelta
import hashlib
import json
import os
from pathlib import Path
import tempfile
import unittest
from unittest.mock import patch
import shelf_deletion_journal as journal
import shelf_offsite_backup as backup
import shelf_retention as retention

class D14BackupTests(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory(prefix='d14-fixture-')
        self.addCleanup(self.temp.cleanup)
        self.root = Path(self.temp.name)
        self.private = self.root / 'private'; self.private.mkdir(mode=0o700)
        self.key = self.private / 'key'; self.key.write_text('a'*64+'\n'); self.key.chmod(0o600)
        home = self.private / 'gnupg'; home.mkdir(mode=0o700)
        self.records = {}
        self.patches = [patch.object(backup,'KEY',self.key), patch.dict(os.environ,{'GNUPGHOME':str(home),'PATH':'/usr/local/bin:/usr/bin:/bin'}),
            patch.object(journal,'ROOT',self.root/'journal'), patch.object(backup,'rclone',side_effect=self.remote)]
        for p in self.patches: p.start(); self.addCleanup(p.stop)
    def remote(self,*args):
        if args[0]=='lsjson':
            if args[1]==backup.REMOTE and '--files-only' not in args:
                return json.dumps([{'Name':'DeletionRecords'}] if journal.REMOTE+'/head.gpg' in self.records else []).encode()
            return json.dumps([{'Name':name.split('/')[-1],'Size':len(value)} for name,value in self.records.items() if name.startswith(backup.REMOTE+'/') and '/' not in name[len(backup.REMOTE)+1:]]).encode()
        if args[0]=='copyto':
            source,target = map(str,args[1:3])
            if source.startswith(backup.REMOTE):
                if source not in self.records: raise RuntimeError('synthetic missing remote')
                Path(target).write_bytes(self.records[source]); Path(target).chmod(0o600)
            else:
                self.records[target]=Path(source).read_bytes()
            return b''
        if args[0]=='deletefile':
            del self.records[str(args[1])]; return b''
        raise AssertionError(args)
    def deletion(self,reader=100):
        return {'reader_id':reader,'created_at':'2026-10-01T00:00:00+00:00','deleted_at':'2026-10-07T00:00:00+00:00','record_id':format(reader,'032x')}
    def test_journal_survives_server_loss_and_missing_or_corrupt_records_block(self):
        journal.initialize(); record=self.deletion(); journal.append(record)
        with patch.object(journal,'ROOT',self.root/'replacement-host'):
            self.assertEqual(journal.current(),[record])
        remote=journal.REMOTE+'/'+record['record_id']+'.gpg'
        self.assertNotIn(b'2026-10-01',self.records[remote])
        self.records[remote]=b'corrupt'
        with self.assertRaisesRegex(RuntimeError,'corrupt'): journal.current()
        del self.records[journal.REMOTE+'/head.gpg']
        with self.assertRaises(RuntimeError): journal.current()
    def test_no_journal_is_not_interpreted_as_no_deletions(self):
        with self.assertRaises(RuntimeError): journal.current()
        with self.assertRaises(RuntimeError): journal.append(self.deletion())
    def test_claims_remain_reserved_across_older_database_or_server_loss(self):
        journal.initialize()
        claim={'reader_id':2,'created_at':'2026-10-01T00:00:00+00:00','claimed_at':'2026-10-07T00:00:00+00:00','record_id':'b'*32,
            'purchase_id':1,'transaction_hash':'c'*64,'owner_id':1,'case_reference':'CASE_123','proof_hash':'d'*64}
        journal.append(claim)
        with self.assertRaisesRegex(RuntimeError,'already durably claimed'): journal.append(dict(claim,record_id='e'*32,reader_id=3))
        journal.append(self.deletion(2))
        journal.append(dict(claim,record_id='e'*32,reader_id=3))
        self.assertEqual(len(journal.current()),3)
    def test_expiry_boundary_is_strict_and_never_classifies_unrelated_files(self):
        now=datetime(2027,1,5,tzinfo=timezone.utc)
        def name(day): return 'shelf-production-'+day.strftime('%Y%m%dT%H%M%SZ')+'-'+'a'*16+'.tar.gpg'
        self.assertFalse(retention.expired(name(now-timedelta(days=90)),now))
        self.assertTrue(retention.expired(name(now-timedelta(days=90,seconds=1)),now))
        self.assertFalse(retention.expired('recovery-key.secret',now))
    def inventory_fixture(self):
        logs=self.root/'logs'; logs.mkdir(); offsite=self.root/'offsite'; offsite.mkdir()
        pinned='shelf-production-20260901T000000Z-'+'a'*16+'.tar.gpg'
        old='shelf-production-20260902T000000Z-'+'b'*16+'.tar.gpg'
        for name in (pinned,old):
            self.records[backup.REMOTE+'/'+name]=b'cipher'
            self.records[backup.REMOTE+'/'+name[:-4]+'.json']=json.dumps({'sha256':hashlib.sha256(b'cipher').hexdigest(),'bytes':6}).encode()
        (offsite/('recovery-'+pinned+'.json')).write_text(json.dumps({'package':pinned,'restored_at':'2026-09-03T00:00:00Z',
            'download_checksum':True,'database_schema_rows_identical':True,'sha256':hashlib.sha256(b'cipher').hexdigest()}))
        self.records[backup.REMOTE+'/unrelated.txt']=b'keep'
        return logs,offsite,pinned,old
    def test_inventory_pins_verified_set_honors_incident_review_and_ignores_unrelated(self):
        logs,offsite,pinned,old=self.inventory_fixture()
        for name in ('laravel-2026-09-01.log','laravel-2026-09-02.log','financial-history.log','credential.secret'):
            (logs/name).write_text('synthetic')
        exceptions=self.root/'holds.json'; exceptions.write_text(json.dumps([{'file':'laravel-2026-09-01.log','case_reference':'INCIDENT_1',
            'owner_id':1,'reviewed_at':'2026-09-02T00:00:00+00:00','review_at':'2026-10-01T00:00:00+00:00'}])); exceptions.chmod(0o600)
        with patch.object(retention,'LOGS',logs),patch.object(backup,'ROOT',offsite),patch.object(retention,'EXCEPTIONS',exceptions):
            result=retention.inventory(datetime(2027,1,1,tzinfo=timezone.utc))
        self.assertEqual(result['pinned'],pinned)
        self.assertEqual(result['remote_delete'],sorted([old,old[:-4]+'.json']))
        self.assertEqual(result['log_delete'],['laravel-2026-09-02.log'])
        self.assertIn({'file':'laravel-2026-09-01.log','reason':'review_overdue'},result['exceptions'])
        self.assertIn({'file':pinned,'reason':'last_verified_recovery_set'},result['exceptions'])
        self.assertEqual(len(self.records),5) # dry-run does not delete.
    def test_cleanup_without_verified_set_or_activation_stops(self):
        logs=self.root/'logs';logs.mkdir();offsite=self.root/'offsite';offsite.mkdir()
        with patch.object(retention,'LOGS',logs),patch.object(backup,'ROOT',offsite):
            with self.assertRaisesRegex(RuntimeError,'No verified'): retention.inventory()
    def test_older_database_replay_removes_profiles_and_sessions_preserves_money_and_blocks_reopening(self):
        target=self.root/'old';target.mkdir();code=target/'code/public';code.mkdir(parents=True);(code/'index.php').write_text('<?php echo "old app";')
        sql=b"""CREATE TABLE readers (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,email VARCHAR(255),created_at TIMESTAMP NULL) ENGINE=InnoDB;
CREATE TABLE purchases (id BIGINT UNSIGNED PRIMARY KEY,reader_id BIGINT UNSIGNED,transaction_id VARCHAR(255)) ENGINE=InnoDB;
CREATE TABLE personal_access_tokens (id BIGINT PRIMARY KEY,tokenable_type VARCHAR(255),tokenable_id BIGINT,token VARCHAR(255));
CREATE TABLE book_entitlements (reader_id BIGINT UNSIGNED,active TINYINT,FOREIGN KEY(reader_id) REFERENCES readers(id) ON DELETE CASCADE);
CREATE TABLE reader_account_actions (reader_id BIGINT UNSIGNED,token VARCHAR(255),FOREIGN KEY(reader_id) REFERENCES readers(id) ON DELETE CASCADE);
INSERT INTO readers VALUES(2,'survivor@example.test','2026-10-01 00:00:00'),(100,'removed@example.test','2026-10-01 00:00:00');
INSERT INTO purchases VALUES(1,100,'GPA.immutable');
INSERT INTO personal_access_tokens VALUES(1,'App\\\\Models\\\\Reader',100,'removed-token');
INSERT INTO reader_account_actions VALUES(100,'removed-link');
INSERT INTO book_entitlements VALUES(100,1),(2,1);
"""
        (target/'database.sql').write_bytes(sql)
        first=self.root/'db1';first.mkdir();dumps=[];actual=backup.run
        def capture(args,**kwargs):
            result=actual(args,**kwargs)
            if args[0]=='mysqldump':dumps.append(result)
            return result
        with patch.object(backup,'run',side_effect=capture),self.assertRaisesRegex(RuntimeError,'Recovered database differs'):
            backup.restore_database(target,first)
        (target/'database.sql').write_bytes(dumps[0]);second=self.root/'db2';second.mkdir()
        backup.restore_database(target,second,deletion_records=[self.deletion()])
        sanitized=(target/'database.sql').read_text()
        self.assertNotIn('removed@example.test',sanitized);self.assertNotIn('removed-token',sanitized);self.assertNotIn('removed-link',sanitized)
        self.assertIn('survivor@example.test',sanitized);self.assertIn('GPA.immutable',sanitized)
        self.assertIn('AUTO_INCREMENT=101',sanitized)
        self.assertTrue((target/'code/.shelf-recovery-blocked').is_file())
        self.assertIn('http_response_code(503)',(code/'index.php').read_text())
        self.assertTrue(json.loads((target/'deletion-replay.json').read_text())['access_disabled'])
