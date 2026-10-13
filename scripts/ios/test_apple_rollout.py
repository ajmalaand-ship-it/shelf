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


if __name__=='__main__':unittest.main()
