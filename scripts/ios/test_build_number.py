#!/usr/bin/env python3
"""Synthetic only: no Apple API, build, signing identity or upload."""
import contextlib
import io
import json
from pathlib import Path
import subprocess
import unittest
from unittest.mock import patch
import urllib.error
import yaml
import check_build_number as guard


def build(n='4', state='VALID'):
    return {'attributes': {'version': n, 'processingState': state}}


def upload(n='4', state='COMPLETE', platform='IOS'):
    return {'attributes': {'cfBundleVersion': n, 'state': {'state': state}, 'platform': platform}}


class NumberGuardTests(unittest.TestCase):
    def test_explicit_number_rejects_missing_fallback_whitespace_and_invalid_values(self):
        for value in [None, '', '0', '04', '-1', '5.0', ' 5', '5\n', 'fallback', '9'*19]:
            with self.subTest(value=value), self.assertRaises(guard.GuardError):
                guard.number(value)
        self.assertEqual(5, guard.number('5'))

    def test_used_number_and_lower_holes_are_rejected(self):
        for value in ['1', '3', '4']:
            with self.assertRaisesRegex(guard.GuardError, 'must exceed'):
                guard.validate(value, [build()], [upload('1', 'FAILED')])

    def test_next_candidate_accepts_complete_and_failed_history(self):
        self.assertEqual(4, guard.validate('5', [build('4'), build('2')],
                                         [upload('4'), upload('1', 'FAILED')]))

    def test_upload_only_higher_number_counts_even_if_failed(self):
        with self.assertRaises(guard.GuardError):
            guard.validate('5', [build()], [upload('5', 'FAILED')])
        self.assertEqual(5, guard.validate('6', [build()], [upload('5', 'FAILED')]))

    def test_processing_or_unknown_build_state_stops(self):
        for state in ['PROCESSING', None, 'NEW_STATE']:
            with self.assertRaises(guard.GuardError):
                guard.validate('5', [build(state=state)], [])

    def test_pending_unknown_and_malformed_upload_states_stop(self):
        for state in ['UPLOADING', 'PROCESSING', None, 'NEW_STATE']:
            with self.assertRaises(guard.GuardError):
                guard.validate('5', [], [upload(state=state)])
        with self.assertRaises(guard.GuardError):
            guard.validate('5', [], [{'attributes': {'platform': 'IOS', 'state': 'COMPLETE'}}])
        with self.assertRaises(guard.GuardError):
            guard.validate('5', [], [upload(platform='MAC_OS')])

    def test_missing_credentials_stop_without_subprocess_or_network(self):
        with patch.object(guard.subprocess, 'run') as process:
            with self.assertRaisesRegex(guard.GuardError, 'credentials unavailable'):
                guard.check({'SHELF_IOS_BUILD_NUMBER': '5'})
            process.assert_not_called()

    def test_read_only_app_scope_and_safe_output(self):
        class Api:
            def __init__(self): self.paths=[]
            def get(self, path):
                self.paths.append(path)
                return {'data': {'id': guard.APP, 'attributes': {'bundleId': guard.BUNDLE}}}
            def listed(self, path):
                self.paths.append(path)
                return [upload()] if path.endswith('buildUploads') else [build()]
        api=Api()
        with contextlib.redirect_stdout(io.StringIO()) as output:
            guard.check({'SHELF_IOS_BUILD_NUMBER': '5'}, api)
        self.assertEqual(['/v1/apps/'+guard.APP, '/v1/apps/'+guard.APP+'/builds',
                          '/v1/apps/'+guard.APP+'/buildUploads'], api.paths)
        self.assertIn('chosen iOS build 5', output.getvalue())

    def test_wrong_app_identity_stops(self):
        from unittest.mock import Mock
        api=Mock(); api.get.return_value={'data': {'id': 'other', 'attributes': {'bundleId': guard.BUNDLE}}}
        with self.assertRaises(guard.GuardError): guard.check({'SHELF_IOS_BUILD_NUMBER':'5'}, api)
        api.listed.assert_not_called()

    def test_bounded_pagination_and_no_foreign_key_forwarding(self):
        api=guard.Apple.__new__(guard.Apple)
        path='/v1/apps/'+guard.APP+'/buildUploads'
        pages=[{'data':[upload('3')],'links':{'next':guard.BASE+path+'?cursor=2'},'meta':{'paging':{'total':2}}},
               {'data':[upload('4')],'links':{'next':None},'meta':{'paging':{'total':2}}}]
        with patch.object(api,'get',side_effect=pages): self.assertEqual(2,len(api.listed(path)))
        for next_url in ['https://attacker.test/x',guard.BASE+'/v1/apps/other/buildUploads?x=1',
                         guard.BASE+path+'?limit=200', 7]:
            with patch.object(api,'get',return_value={'data':[],'links':{'next':next_url},'meta':{'paging':{'total':2}}}):
                with self.assertRaises(guard.GuardError): api.listed(path)
        with patch.object(api,'get',return_value={'data':[],'links':{}}):
            with self.assertRaises(guard.GuardError): api.listed(path)

    def test_actual_terminal_links_shape_requires_matching_total(self):
        api=guard.Apple.__new__(guard.Apple)
        page={'data':[upload()],'links':{'self':'withheld'},'meta':{'paging':{'total':1}}}
        with patch.object(api,'get',return_value=page):
            self.assertEqual(1,len(api.listed('/v1/apps/'+guard.APP+'/buildUploads')))
        page['meta']['paging']['total']=2
        with patch.object(api,'get',return_value=page):
            with self.assertRaisesRegex(guard.GuardError,'truncated'): api.listed('/v1/apps/'+guard.APP+'/buildUploads')

    def test_http_auth_failure_stops_without_response_or_token_output(self):
        api=guard.Apple.__new__(guard.Apple);api.token='synthetic-private-token'
        error=urllib.error.HTTPError(guard.BASE,403,'private-provider-message',{},None)
        with patch.object(guard.urllib.request,'build_opener') as opener:
            opener.return_value.open.side_effect=error
            with self.assertRaisesRegex(guard.GuardError,'HTTP 403') as caught: api.get('/v1/apps/'+guard.APP)
        self.assertNotIn('synthetic-private-token',str(caught.exception))
        self.assertNotIn('private-provider-message',str(caught.exception))

    def test_workflow_places_guard_before_preparation_and_signed_compile(self):
        root=Path(__file__).resolve().parents[2]
        workflow=yaml.safe_load((root/'codemagic.yaml').read_text())['workflows']['ios-testflight-internal']
        scripts=workflow['scripts'];guard_index=next(i for i,s in enumerate(scripts) if s['script']=='python3 ../scripts/ios/check_build_number.py')
        self.assertLess(guard_index,next(i for i,s in enumerate(scripts) if 'flutter ' in s['script']))
        compile_script=next(s['script'] for s in scripts if 'flutter build ipa' in s['script'])
        self.assertLess(compile_script.index('check_build_number.py'),compile_script.index('flutter build ipa'))
        self.assertIn('--build-number="$SHELF_IOS_BUILD_NUMBER"',compile_script)
        self.assertNotIn('${SHELF_IOS_BUILD_NUMBER:-',compile_script)
        self.assertEqual('shelf_app_store_connect',workflow['integrations']['app_store_connect'])
        self.assertFalse(workflow['publishing']['app_store_connect']['submit_to_testflight'])
        for step in scripts:
            result=subprocess.run(['bash','-n'],input=step['script'],text=True,capture_output=True)
            self.assertEqual(0,result.returncode,step['name'])


if __name__=='__main__': unittest.main(verbosity=2)
