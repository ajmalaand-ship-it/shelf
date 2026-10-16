"""Synthetic safeguards for disabled rollout automation; no real credentials/providers."""
import importlib.util
import json
import os
from pathlib import Path
import tempfile
import unittest
from unittest.mock import Mock, patch

spec = importlib.util.spec_from_file_location('apple_rollout', Path(__file__).with_name('configure_apple_sandbox.py'))
m = importlib.util.module_from_spec(spec)
spec.loader.exec_module(m)


class WebhookSafety(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory(dir=os.environ['SHELF_TEST_TMP'])
        self.addCleanup(self.temp.cleanup)
        self.record = Path(self.temp.name)/'webhook.json'
        self.w = Mock()
        self.w.run.return_value = 'verified-commit\n'
        self.w.current_state.return_value = {'production_commit':'verified-commit', 'staging_commit':'verified-commit',
            'checks':{'scope':'apple-purchase-backend-disabled'}}
        self.w.settings.return_value = {'SHELF_REVENUECAT_IOS_WEBHOOK_AUTH':'synthetic-distinct-auth'}
        self.google = {'id':m.GOOGLE_WEBHOOK,'app_id':'appf83cd58c5c','environment':None,'event_types':['cancellation']}
        self.apple = {'id':'synthetic-apple','name':'Shelf iOS sandbox','url':m.URL,'environment':'sandbox',
            'app_id':m.APPLE_APP,'event_types':m.EVENTS}
        for name, value in [('workflow', Mock(return_value=self.w)),('RECORD',self.record),
            ('private',Mock(return_value=b'synthetic-distinct-auth')),('exclusive',Mock()),('probe_disabled_endpoint',Mock())]:
            p = patch.object(m,name,value);p.start();self.addCleanup(p.stop)

    def test_unpromoted_backend_cannot_call_provider(self):
        self.w.current_state.return_value['production_commit']='other'
        with patch.object(m,'request') as request,patch.object(m,'items') as items:
            with self.assertRaises(RuntimeError):m.webhook()
            request.assert_not_called();items.assert_not_called()

    def test_closed_runtime_guard_prevents_provider_creation(self):
        self.w.apple_purchase_runtime_checks.side_effect=RuntimeError('Synthetic guard failure')
        with patch.object(m,'request') as request:
            with self.assertRaises(RuntimeError):m.webhook()
            request.assert_not_called()

    def test_only_distinct_apple_sandbox_integration_is_created(self):
        with patch.object(m,'items',side_effect=[[self.google],[self.google,self.apple]]),patch.object(m,'request',side_effect=[self.apple,self.apple]) as request:
            m.webhook()
            route,body=request.call_args_list[0].args
            self.assertEqual(route,'/projects/'+m.PROJECT+'/integrations/webhooks')
            self.assertEqual(body['app_id'],m.APPLE_APP);self.assertEqual(body['environment'],'sandbox')
            self.assertEqual(set(body['event_types']),set(m.EVENTS))
            self.assertNotIn(m.GOOGLE_WEBHOOK,route)
            self.assertEqual(len(request.call_args_list),2)
            self.assertEqual(request.call_args_list[1].args,(route+'/synthetic-apple',))
            m.exclusive.assert_called_once()

    def test_existing_unrecognized_apple_integration_is_preserved(self):
        with patch.object(m,'items',return_value=[self.google,self.apple]),patch.object(m,'request') as request:
            with self.assertRaises(RuntimeError):m.webhook()
            request.assert_not_called()

    def test_event_filter_mismatch_is_not_corrected_by_another_write(self):
        bad={**self.apple,'event_types':['cancellation']}
        with patch.object(m,'items',return_value=[self.google]),patch.object(m,'request',side_effect=[self.apple,bad]) as request:
            with self.assertRaises(RuntimeError):m.webhook()
            self.assertEqual(len(request.call_args_list),2);m.exclusive.assert_not_called()

    def test_google_change_stops_without_corrective_provider_write(self):
        changed={**self.google,'environment':'sandbox'}
        with patch.object(m,'items',side_effect=[[self.google],[changed,self.apple]]),patch.object(m,'request',side_effect=[self.apple,self.apple]) as request:
            with self.assertRaises(RuntimeError):m.webhook()
            self.assertEqual(len(request.call_args_list),2);m.exclusive.assert_not_called()


class FileSafety(unittest.TestCase):
    def test_private_file_in_owned_application_directory_and_collision_refusal(self):
        with tempfile.TemporaryDirectory(dir=os.environ['SHELF_TEST_TMP']) as folder:
            parent=Path(folder)/'application';parent.mkdir(mode=0o755);parent.chmod(0o755)
            path=parent/'synthetic.env'
            m.exclusive(path,b'synthetic-only',private_parent=False)
            self.assertEqual(path.stat().st_mode & 0o777,0o600)
            self.assertEqual(path.read_bytes(),b'synthetic-only')
            with self.assertRaises(FileExistsError):m.exclusive(path,b'replacement',private_parent=False)
            self.assertEqual(path.read_bytes(),b'synthetic-only')

    def test_secret_requires_private_parent_and_rejects_symlink(self):
        with tempfile.TemporaryDirectory(dir=os.environ['SHELF_TEST_TMP']) as folder:
            parent=Path(folder)/'unsafe';parent.mkdir(mode=0o755);parent.chmod(0o755)
            with self.assertRaises(RuntimeError):m.exclusive(parent/'synthetic.secret',b'synthetic-only')
            safe=Path(folder)/'safe';safe.mkdir(mode=0o700)
            link=Path(folder)/'link';link.symlink_to(safe,target_is_directory=True)
            with self.assertRaises(RuntimeError):m.exclusive(link/'synthetic.secret',b'synthetic-only')


class SessionRolloutSafety(unittest.TestCase):
    def setUp(self):
        spec = importlib.util.spec_from_file_location('staging_safety', Path(__file__).parents[1]/'staging/workflow.py')
        self.w = importlib.util.module_from_spec(spec)
        spec.loader.exec_module(self.w)
        self.temp = tempfile.TemporaryDirectory(dir=os.environ['SHELF_TEST_TMP'])
        self.addCleanup(self.temp.cleanup)
        self.app = Path(self.temp.name)
        self.w.RUNTIME = self.app
        self.session = 'app/Services/Purchases/ApplePurchaseSession.php'

    def test_older_rollout_is_not_silently_reclassified(self):
        self.assertFalse(self.w.apple_safety_checked(self.app, 'synthetic', ['docs/IOS_PURCHASES.md']))

    def test_unrelated_backend_change_refuses_focused_gate(self):
        with patch.object(self.w.subprocess, 'run') as process:
            with self.assertRaises(RuntimeError):
                self.w.apple_safety_checked(self.app, 'synthetic', [self.session, 'app/Unrelated.php'])
            process.assert_not_called()

    def test_staged_byte_mismatch_cannot_reuse_test_record(self):
        with patch.object(self.w.subprocess, 'run', return_value=Mock(stdout=b'expected')), patch.object(Path, 'read_bytes', return_value=b'changed'):
            with self.assertRaises(RuntimeError):
                self.w.apple_safety_checked(self.app, 'synthetic', [self.session])

    def test_focused_tests_and_disabled_runtime_required_before_pass_record(self):
        with patch.object(self.w.subprocess, 'run', return_value=Mock(stdout=b'synthetic', returncode=0)) as process, \
            patch.object(Path, 'read_bytes', return_value=b'synthetic'), \
            patch.object(self.w, 'artisan') as artisan, patch.object(self.w, 'apple_purchase_runtime_checks') as runtime, \
            patch.object(self.w, 'https_checks'), patch.object(self.w, 'current_state', return_value={}), \
            patch.object(self.w, 'save_state') as save:
            self.assertTrue(self.w.apple_safety_checked(self.app, 'synthetic', [self.session]))
            test_call = process.call_args_list[-1]
            self.assertEqual(test_call.args[0], ['bash', 'scripts/run_tests.sh'])
            self.assertEqual(test_call.kwargs['env']['SHELF_PHP_TEST_FILTER'], 'AppleSandboxPurchasesTest|BookPurchasesTest|AccountingTest')
            self.assertNotIn('SHELF_TOOL_TEST_SCOPE', test_call.kwargs['env'])
            artisan.assert_called_once_with(self.app, 'shelf:check-staging')
            runtime.assert_called_once_with(self.app)
            self.assertEqual(save.call_args.args[0]['checks']['scope'], 'apple-session-safety-disabled')
            save.reset_mock()
            runtime.side_effect = RuntimeError('synthetic closed-guard mismatch')
            with self.assertRaises(RuntimeError): self.w.apple_safety_checked(self.app, 'synthetic', [self.session])
            save.assert_not_called()
            runtime.side_effect = None
            process.return_value.returncode = 1
            with self.assertRaises(RuntimeError): self.w.apple_safety_checked(self.app, 'synthetic', [self.session])
            save.assert_not_called()


if __name__=='__main__':unittest.main()
