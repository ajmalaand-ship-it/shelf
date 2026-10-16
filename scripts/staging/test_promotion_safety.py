"""Synthetic promotion tests: no live DB, cron, maintenance or provider calls."""
import copy
import hashlib
import importlib.util
import json
import os
from pathlib import Path
import signal
import subprocess
import sys
import tempfile
import unittest
from unittest.mock import Mock, patch

spec = importlib.util.spec_from_file_location('promotion_test_module', Path(__file__).with_name('promotion_safety.py'))
s = importlib.util.module_from_spec(spec)
spec.loader.exec_module(s)


def fixtures():
    at=s.epoch('2026-10-16 04:30:27')
    old={'id':5,'reader_id':20,'collection_id':3,'active':1, **{k:at for k in s.META}}
    raw=dict(old)
    for k in s.META:raw[k]='2026-10-16 00:30:27'
    history={'book_entitlements':{'count':1,'sha256':hashlib.sha256(json.dumps([raw],separators=(',',':')).encode()).hexdigest()},
        'purchases':{'count':2,'sha256':'unchanged-purchase'},'sales_ledger':{'count':3,'sha256':'unchanged-ledger'},
        'accounting_entries':{'count':0,'sha256':'empty'},'agreement_limit':6}
    run={'id':10,'status':'completed','http_status':200,'pages':1,'seen':1,'refunded':0,'duplicate':1,
        'unmatched':0,'conflict':0,'error_code':None,'started_at':at-5,'finished_at':at-5,'created_at':at-5}
    newrun={**run,'id':11,**{k:at+874 for k in ('started_at','finished_at','created_at')}}
    new={**old,**{k:at+874 for k in s.META}}
    current=copy.deepcopy(history);current['book_entitlements']['sha256']=s.raw_entitlement_hash(new,-14400)
    live={'offset_seconds':-14400,'precision_zero':True,'columns':list(s.ENT_COLUMNS),
        'entitlements':[new],'refund_runs':[copy.deepcopy(run),newrun],
        'link':{'refunded_global':1,'refunded_same_book':1,'google_events':1,'valid_play':1}}
    return {'history':history},[old],[run],current,live


class HistoricalRefresh(unittest.TestCase):
    def test_exact_reviewed_duplicate_refresh_is_accepted(self):
        result=s.compare_history(*fixtures())
        self.assertEqual(result['run_id'],11);self.assertEqual(result['after_utc_epoch']-result['before_utc_epoch'],874)

    def test_no_change_needs_no_new_run_or_allowance(self):
        r,old,runs,cur,live=fixtures();cur=copy.deepcopy(r['history']);live['entitlements']=copy.deepcopy(old);live['refund_runs']=runs
        self.assertEqual(s.compare_history(r,old,runs,cur,live)['changed_columns'],[])

    def test_every_non_entitlement_fingerprint_is_strict(self):
        for table in ('purchases','sales_ledger','accounting_entries','agreement_limit'):
            args=list(fixtures());args[3][table]='changed'
            with self.subTest(table=table),self.assertRaises(RuntimeError):s.compare_history(*args)

    def test_added_missing_table_or_entitlement_rows_refused(self):
        for mutate in [lambda a:a[3].pop('purchases'),lambda a:a[3].update(other={}),
                       lambda a:a[4]['entitlements'].append(copy.deepcopy(a[4]['entitlements'][0])),
                       lambda a:a[3]['book_entitlements'].update(count=2)]:
            args=list(fixtures());mutate(args)
            with self.assertRaises(RuntimeError):s.compare_history(*args)

    def test_identity_book_active_and_extra_fields_never_waived(self):
        for field,value in [('id',6),('reader_id',99),('collection_id',4),('active',0),('extra',1)]:
            args=list(fixtures());args[4]['entitlements'][0][field]=value
            with self.subTest(field=field),self.assertRaises(RuntimeError):s.compare_history(*args)

    def test_null_backward_partial_unequal_and_unexplained_times_refused(self):
        for change in [{'checked_at':None},{'checked_at':1},{'created_at':123},
                       {k:0 for k in s.META},{k:9999999999 for k in s.META}]:
            args=list(fixtures());args[4]['entitlements'][0].update(change)
            args[3]['book_entitlements']['sha256']=s.raw_entitlement_hash(args[4]['entitlements'][0],-14400)
            with self.assertRaises(RuntimeError):s.compare_history(*args)

    def test_failed_refund_real_refund_unmatched_conflict_http_error_and_no_audit_fail(self):
        for field,value in [('status','failed'),('http_status',503),('refunded',1),('unmatched',1),
                            ('conflict',1),('error_code','bad'),('duplicate',0),('seen',2),('pages',2)]:
            args=list(fixtures());args[4]['refund_runs'][-1][field]=value
            with self.subTest(field=field),self.assertRaises(RuntimeError):s.compare_history(*args)
        args=list(fixtures());args[4]['refund_runs']=args[2]
        with self.assertRaises(RuntimeError):s.compare_history(*args)

    def test_ambiguous_duplicate_or_rewritten_audit_fails(self):
        for case in ['duplicate','overlap','prior_change']:
            args=list(fixtures())
            if case=='duplicate':args[4]['refund_runs'].append(copy.deepcopy(args[4]['refund_runs'][-1]))
            elif case=='overlap':args[4]['refund_runs'].append({**args[4]['refund_runs'][-1],'id':12})
            else:args[4]['refund_runs'][0]['duplicate']=0
            with self.subTest(case=case),self.assertRaises(RuntimeError):s.compare_history(*args)

    def test_missing_or_ambiguous_existing_refund_link_refused(self):
        for key in fixtures()[-1]['link']:
            args=list(fixtures());args[4]['link'][key]=0
            with self.subTest(key=key),self.assertRaises(RuntimeError):s.compare_history(*args)

    def test_schema_timezone_precision_and_checkpoint_hash_are_not_guessed(self):
        for key,value in [('offset_seconds',0),('precision_zero',False),('columns',['id'])]:
            args=list(fixtures());args[4][key]=value
            with self.assertRaises(RuntimeError):s.compare_history(*args)
        args=list(fixtures());args[0]['history']['book_entitlements']['sha256']='different'
        with self.assertRaises(RuntimeError):s.compare_history(*args)

    def test_utc_dump_parser_types_nulls_and_second_precision(self):
        sql="SET TIME_ZONE='+00:00';\nINSERT INTO `book_entitlements` VALUES\n(5,20,3,1,'2026-10-16 04:30:27',NULL,'2026-10-16 04:30:27');"
        row=s.dump_rows(sql,'book_entitlements',s.ENT_COLUMNS)[0]
        self.assertEqual(row['checked_at'],s.epoch('2026-10-16 04:30:27'));self.assertIsNone(row['created_at'])
        for bad in [sql.replace("'+00:00'","'-04:00'"),sql.replace('04:30:27','04:30:27.123456')]:
            with self.assertRaises(RuntimeError):s.dump_rows(bad,'book_entitlements',s.ENT_COLUMNS)

    def test_checkpoint_requires_exact_source_path_and_backup_checksum(self):
        with self.assertRaises(RuntimeError):s.load_checkpoint(Path('/wrong'),s.SOURCE,s.PREVIOUS)
        with self.assertRaises(RuntimeError):s.load_checkpoint(s.CHECKPOINT,'other',s.PREVIOUS)
        with patch.object(s,'private_bytes',return_value=json.dumps({'source':s.SOURCE,'previous':s.PREVIOUS,
                'configuration_checkpoint_verified':True,'backup':str(s.CHECKPOINT.parent)}).encode()):
            with self.assertRaises((RuntimeError,KeyError)):s.load_checkpoint(s.CHECKPOINT,s.SOURCE,s.PREVIOUS)

    def test_valid_checkpoint_loader_and_tamper_fail_closed(self):
        with tempfile.TemporaryDirectory(dir=os.environ['SHELF_TEST_TMP']) as directory:
            root=Path(directory);checkpoint=root/'checkpoint';checkpoint.mkdir()
            data="SET TIME_ZONE='+00:00';\nINSERT INTO `book_entitlements` VALUES (5,20,3,1,'2026-10-16 04:30:27','2026-10-16 04:30:27','2026-10-16 04:30:27');"
            record={'source':s.SOURCE,'previous':s.PREVIOUS,'configuration_checkpoint_verified':True,'backup':str(root),'files':{}}
            for path,value in [(checkpoint/'verification.json',json.dumps(record)),(root/'database.sql',data),
                (root/'manifest.json',json.dumps({'system':'Shelf','database_sha256':hashlib.sha256(data.encode()).hexdigest()}))]:
                path.write_text(value);path.chmod(0o600)
            with patch.object(s,'CHECKPOINT',checkpoint), patch.object(s,'CHECKPOINT_SHA256',hashlib.sha256((checkpoint/'verification.json').read_bytes()).hexdigest()):
                self.assertEqual(s.load_checkpoint(checkpoint,s.SOURCE,s.PREVIOUS)[1][0]['id'],5)
                (root/'database.sql').write_text(data+'modified')
                with self.assertRaises(RuntimeError):s.load_checkpoint(checkpoint,s.SOURCE,s.PREVIOUS)
                (root/'database.sql').write_text(data)
                (checkpoint/'verification.json').write_text(json.dumps({**record,'files':{'changed':'value'}}))
                with self.assertRaisesRegex(RuntimeError,'no rebaselining'):s.load_checkpoint(checkpoint,s.SOURCE,s.PREVIOUS)

    def test_extended_insert_and_quoted_null_are_parsed_without_execution(self):
        self.assertEqual(s.sql_values("('a; b','NULL',NULL),(2,'c\\\'d',3);",0),[['a; b','NULL',None],['2',"c'd",'3']])
        sql="SET TIME_ZONE='+00:00';\nINSERT INTO `book_entitlements` VALUES (5,20,3,1,NULL,NULL,NULL),(6,20,4,1,NULL,NULL,NULL);"
        self.assertEqual(len(s.dump_rows(sql,'book_entitlements',s.ENT_COLUMNS)),2)


class OperatorWindow(unittest.TestCase):
    def setUp(self):
        self.temp=tempfile.TemporaryDirectory(dir=os.environ['SHELF_TEST_TMP']);self.addCleanup(self.temp.cleanup)
        self.root=Path(self.temp.name);self.lock=self.root/'refund.lock';self.lock.touch(mode=0o600)
        self.cleanup=Mock()

    def unlocked(self):
        import fcntl
        with self.lock.open('r+') as f:fcntl.flock(f,fcntl.LOCK_EX|fcntl.LOCK_NB)

    def test_success_and_error_restore_maintenance_and_release_same_inode(self):
        inode=self.lock.stat().st_ino
        for error in [None,ValueError('synthetic failure')]:
            try:
                with s.PromotionWindow(self.lock,self.cleanup) as window:
                    window.arm_maintenance()
                    if error:raise error
            except ValueError:pass
            self.unlocked();self.assertIsNone(s.ACTIVE)
        self.assertEqual(self.cleanup.call_count,2);self.assertEqual(self.lock.stat().st_ino,inode)

    def test_busy_lock_refuses_before_maintenance_or_work(self):
        import fcntl
        with self.lock.open('r+') as holder:
            fcntl.flock(holder,fcntl.LOCK_EX|fcntl.LOCK_NB)
            with self.assertRaises(BlockingIOError):
                with s.PromotionWindow(self.lock,self.cleanup):self.fail('must not enter')
        self.cleanup.assert_not_called();self.unlocked()

    def test_symlink_and_public_lock_refused(self):
        link=self.root/'link';link.symlink_to(self.lock)
        with self.assertRaises(OSError):
            with s.PromotionWindow(link,self.cleanup):pass
        self.lock.chmod(0o644)
        with self.assertRaises(RuntimeError):
            with s.PromotionWindow(self.lock,self.cleanup):pass

    def test_signals_restore_and_previous_handlers_survive(self):
        for sig in [signal.SIGINT,signal.SIGTERM,signal.SIGHUP,signal.SIGALRM]:
            previous=signal.getsignal(sig)
            with self.assertRaises(s.PromotionInterrupted):
                with s.PromotionWindow(self.lock,self.cleanup) as window:
                    window.arm_maintenance();os.kill(os.getpid(),sig)
            self.assertEqual(signal.getsignal(sig),previous);self.unlocked()
        self.assertEqual(self.cleanup.call_count,4)

    def test_cleanup_error_preserves_original_type_and_unlocks(self):
        self.cleanup.side_effect=RuntimeError('synthetic cleanup failure')
        with self.assertRaisesRegex(RuntimeError,'original=ValueError'):
            with s.PromotionWindow(self.lock,self.cleanup) as window:
                window.arm_maintenance();raise ValueError('original')
        self.cleanup.assert_called_once();self.unlocked()

    def test_second_signal_during_cleanup_does_not_skip_restore(self):
        def restore():os.kill(os.getpid(),signal.SIGTERM)
        with s.PromotionWindow(self.lock,restore) as window:window.arm_maintenance()
        self.unlocked()

    def test_deadline_kills_child_and_releases_scheduler(self):
        started=__import__('time').monotonic()
        with self.assertRaises(s.PromotionInterrupted):
            with s.PromotionWindow(self.lock,self.cleanup,seconds=0.15) as window:
                window.arm_maintenance();window.process([sys.executable,'-c','import time;time.sleep(30)'])
        self.assertLess(__import__('time').monotonic()-started,6);self.unlocked();self.cleanup.assert_called_once()

    def test_independent_child_timeout_inherits_lock_and_bounds_orphan(self):
        with s.PromotionWindow(self.lock,self.cleanup) as window:
            window.deadline=__import__('time').monotonic()+0.1
            result=window.process([sys.executable,'-c','import time;time.sleep(30)'])
            self.assertNotEqual(result.returncode,0)
        self.unlocked()

    def test_uncatchable_kill_releases_lock_without_changing_schedule(self):
        code="""import importlib.util,sys,time
spec=importlib.util.spec_from_file_location('s',sys.argv[1]);s=importlib.util.module_from_spec(spec);spec.loader.exec_module(s)
with s.PromotionWindow(sys.argv[2],lambda:None):
 print('locked',flush=True)
 time.sleep(30)
"""
        child=subprocess.Popen([sys.executable,'-B','-c',code,str(Path(s.__file__)),str(self.lock)],stdout=subprocess.PIPE,text=True)
        try:
            self.assertEqual(child.stdout.readline().strip(),'locked');child.kill();child.wait(timeout=3)
        finally:
            if child.poll() is None:child.kill();child.wait(timeout=3)
            child.stdout.close()
        self.unlocked()

    def test_killed_operator_orphan_keeps_lock_until_independent_timeout(self):
        import fcntl,time
        marker=self.root/'child-started'
        code="""import importlib.util,sys,time
spec=importlib.util.spec_from_file_location('s',sys.argv[1]);s=importlib.util.module_from_spec(spec);spec.loader.exec_module(s)
with s.PromotionWindow(sys.argv[2],lambda:None) as window:
 window.deadline=time.monotonic()+1
 window.process([sys.executable,'-c',"from pathlib import Path;import sys,time;Path(sys.argv[1]).write_text('started');time.sleep(30)",sys.argv[3]])
"""
        child=subprocess.Popen([sys.executable,'-B','-c',code,str(Path(s.__file__)),str(self.lock),str(marker)],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
        try:
            deadline=time.monotonic()+3
            while not marker.exists() and time.monotonic()<deadline:time.sleep(0.01)
            self.assertTrue(marker.exists());child.kill();child.wait(timeout=2)
            with self.lock.open('r+') as f:
                with self.assertRaises(BlockingIOError):fcntl.flock(f,fcntl.LOCK_EX|fcntl.LOCK_NB)
            deadline=time.monotonic()+3
            while True:
                try:self.unlocked();break
                except BlockingIOError:
                    self.assertLess(time.monotonic(),deadline);time.sleep(0.02)
        finally:
            if child.poll() is None:child.kill();child.wait(timeout=2)


class PromotionIntegration(unittest.TestCase):
    def setUp(self):
        spec=importlib.util.spec_from_file_location('promotion_integration',Path(__file__).with_name('workflow.py'))
        self.w=importlib.util.module_from_spec(spec);spec.loader.exec_module(self.w)
        self.temp=tempfile.TemporaryDirectory(dir=os.environ['SHELF_TEST_TMP']);self.addCleanup(self.temp.cleanup)
        root=Path(self.temp.name);self.prod=root/'production';self.stage=root/'stage';self.runtime=root/'runtime'
        for p in [self.prod/'public/apps/shelf-staging',self.prod/'storage/framework',self.stage,self.runtime]:p.mkdir(parents=True,exist_ok=True)
        (self.prod/'public/apps/shelf-staging/public').symlink_to(self.stage/'public')
        (self.prod/'public/.htaccess').write_text('synthetic handler')
        self.lock=self.prod/'storage/framework/play-refunds.lock';self.lock.touch(mode=0o600)
        for p in [self.prod,self.stage]:(p/'composer.lock').write_text('same lock')
        (self.stage/'REVISION').write_text(s.SOURCE)
        log=self.runtime/('checks-'+s.SOURCE+'.log');log.write_text('synthetic evidence')
        self.state={'staging_commit':s.SOURCE,'production_commit':s.PREVIOUS,'checks':{'commit':s.SOURCE,'passed':True,
            'scope':'apple-session-safety-disabled','log_sha256':hashlib.sha256(log.read_bytes()).hexdigest()}}
        self.baseline={'agreement_limit':6,'book_entitlements':{'count':1,'sha256':'strict all timestamps'}}
        self.saved=root/'backup';self.saved.mkdir()
        values={'PRODUCTION':self.prod,'STAGING':self.stage,'RUNTIME':self.runtime,
            'current_state':Mock(return_value=self.state),'backup':Mock(return_value=self.saved),
            'artisan':Mock(),'closed_production_preflight':Mock(),'preservation_gate':Mock(return_value={'files':{}}),
            'production_history_snapshot':Mock(side_effect=[self.baseline,copy.deepcopy(self.baseline)]),
            'apple_purchase_runtime_checks':Mock(),'https_checks':Mock(),'save_state':Mock()}
        for name,value in values.items():
            p=patch.object(self.w,name,value);p.start();self.addCleanup(p.stop)
        self.run=Mock(side_effect=lambda args,*a,**kw:s.PREVIOUS if args[:3]==['git','rev-parse','HEAD'] else '')
        p=patch.object(self.w,'run',self.run);p.start();self.addCleanup(p.stop)
        p=patch.object(self.w.shutil,'copytree');p.start();self.addCleanup(p.stop)

    def test_promotion_lock_covers_backup_and_exact_final_comparison(self):
        import fcntl
        def backup(app):
            with self.lock.open('r+') as f:
                with self.assertRaises(BlockingIOError):fcntl.flock(f,fcntl.LOCK_EX|fcntl.LOCK_NB)
            return self.saved
        self.w.backup.side_effect=backup
        self.w.promote(s.SOURCE,s.CHECKPOINT)
        self.w.preservation_gate.assert_called_once_with(s.CHECKPOINT,s.SOURCE,s.PREVIOUS,self.saved)
        self.w.save_state.assert_called_once()
        self.assertEqual([c.args[1] for c in self.w.artisan.call_args_list].count('up'),1)

    def test_missing_checkpoint_or_busy_lock_never_backs_up_or_migrates(self):
        import fcntl
        with self.assertRaises(RuntimeError):self.w.promote(s.SOURCE)
        with self.lock.open('r+') as f:
            fcntl.flock(f,fcntl.LOCK_EX|fcntl.LOCK_NB)
            with self.assertRaises(BlockingIOError):self.w.promote(s.SOURCE,s.CHECKPOINT)
        self.w.backup.assert_not_called();self.w.artisan.assert_not_called()

    def test_existing_maintenance_or_open_gate_is_never_overwritten(self):
        self.w.closed_production_preflight.side_effect=RuntimeError('pre-existing maintenance or gate changed')
        with self.assertRaises(RuntimeError):self.w.promote(s.SOURCE,s.CHECKPOINT)
        self.w.backup.assert_not_called();self.w.artisan.assert_not_called();self.w.save_state.assert_not_called()

    def test_staging_evidence_tampering_never_enters_window(self):
        self.state['checks']['log_sha256']='changed'
        with self.assertRaises(RuntimeError):self.w.promote(s.SOURCE,s.CHECKPOINT)
        self.w.backup.assert_not_called();self.w.artisan.assert_not_called()

    def test_backup_failure_has_no_maintenance_and_does_not_strand_lock(self):
        import fcntl
        self.w.backup.side_effect=RuntimeError('backup failed')
        with self.assertRaises(RuntimeError):self.w.promote(s.SOURCE,s.CHECKPOINT)
        self.w.artisan.assert_not_called();self.w.save_state.assert_not_called()
        with self.lock.open('r+') as f:fcntl.flock(f,fcntl.LOCK_EX|fcntl.LOCK_NB)

    def test_historical_failure_never_merges_or_migrates_and_restores_maintenance(self):
        self.w.preservation_gate.side_effect=RuntimeError('ownership changed')
        with self.assertRaises(RuntimeError):self.w.promote(s.SOURCE,s.CHECKPOINT)
        self.assertFalse(any(c.args[0][:2]==['git','merge'] for c in self.run.call_args_list))
        self.assertEqual([c.args[1] for c in self.w.artisan.call_args_list],['down','up'])
        self.w.production_history_snapshot.assert_not_called();self.w.save_state.assert_not_called()

    def test_actual_window_stays_strict_even_for_verified_timestamp_columns(self):
        changed=copy.deepcopy(self.baseline);changed['book_entitlements']['sha256']='timestamp drift'
        self.w.production_history_snapshot.side_effect=[self.baseline,changed]
        with self.assertRaises(RuntimeError):self.w.promote(s.SOURCE,s.CHECKPOINT)
        self.w.save_state.assert_not_called()
        self.assertEqual([c.args[1] for c in self.w.artisan.call_args_list].count('up'),1)

    def test_partial_down_signal_and_migration_failure_each_restore_maintenance(self):
        for phase in ['down','migrate']:
            self.w.artisan.reset_mock();self.w.production_history_snapshot.side_effect=[self.baseline,copy.deepcopy(self.baseline)]
            def artisan(app,action,*args):
                if action==phase:raise self.w.safety.PromotionInterrupted('synthetic interruption')
            self.w.artisan.side_effect=artisan
            with self.assertRaises(self.w.safety.PromotionInterrupted):self.w.promote(s.SOURCE,s.CHECKPOINT)
            self.assertEqual([c.args[1] for c in self.w.artisan.call_args_list].count('up'),1)
        self.w.save_state.assert_not_called()

    def test_coherent_read_only_evidence_php_syntax_and_shared_strict_history(self):
        with patch.object(self.w,'php',return_value='{}') as php:
            self.w.entitlement_refresh_evidence()
        code=php.call_args.args[1]
        self.assertIn(self.w.HISTORY_SNAPSHOT_PHP,code)
        self.assertIn("$result['history']=$out",code)
        self.assertIn('$db->rollBack()',code)
        result=subprocess.run(['php','-l'],input='<?php\n'+code,capture_output=True,text=True)
        self.assertEqual(result.returncode,0,'Embedded PHP evidence syntax failed')
