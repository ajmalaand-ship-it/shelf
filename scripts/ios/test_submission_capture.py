"""Synthetic upload orchestration checks: never contact Apple or render fake assets."""
import importlib.util
import json
import os
from pathlib import Path
import tempfile
import unittest
from unittest.mock import patch

spec = importlib.util.spec_from_file_location('upload', Path(__file__).with_name('upload_submission_screenshots.py'))
upload = importlib.util.module_from_spec(spec)
spec.loader.exec_module(upload)

class SubmissionCaptureTests(unittest.TestCase):
    def setUp(self):
        self.directory = tempfile.TemporaryDirectory(dir=os.environ['SHELF_TEST_TMP'])
        self.addCleanup(self.directory.cleanup)
        self.output = Path(self.directory.name)
        patcher = patch.object(upload, 'OUT', self.output)
        patcher.start()
        self.addCleanup(patcher.stop)
        self.names = ['listing-store.png', 'listing-sample.png'] + [f'iap-book-{n}.png' for n in range(3, 9)]
        evidence = {'app_source': '47f0e8e1ecf4ec5825f8c05d20231e229f7e04cf', 'live_api': True,
                    'store_upload': False, 'simulators': []}
        import hashlib
        for family in ['iphone', 'ipad']:
            (self.output / family).mkdir()
            files = {}
            for name in self.names:
                (self.output / family / name).write_bytes(b'synthetic orchestration fixture, never an image')
                files[name] = hashlib.sha256(b'synthetic orchestration fixture, never an image').hexdigest()
            evidence['simulators'].append({'family': family, 'files': files})
        (self.output / 'capture-evidence.json').write_text(json.dumps(evidence))

    def api(self, method, path, data=None, **kwargs):
        if path.endswith('/relationships/build'):
            return {'data': {'id': upload.BUILD}}
        if path == '/v1/appStoreVersions/' + upload.VERSION:
            return {'data': {'attributes': {'appVersionState': 'PREPARE_FOR_SUBMISSION', 'releaseType': 'MANUAL'}}}
        if path == '/v1/appStoreVersionLocalizations/' + upload.LOCALIZATION:
            return {'data': {'attributes': {'locale': 'en-US'}}}
        if path.endswith('/appScreenshotSets') and method == 'GET':
            return {'data': []}
        if path == '/v1/appScreenshotSets' and method == 'POST':
            family = 'iphone' if data['data']['attributes']['screenshotDisplayType'] == 'APP_IPHONE_67' else 'ipad'
            return {'data': {'id': family}}
        if path.endswith('/appScreenshots'):
            return {'data': []}
        if path.endswith('/appStoreReviewScreenshot'):
            return {'data': None}
        self.fail('Unexpected Apple request: ' + method + ' ' + path)

    def test_all_native_capture_files_and_six_iaps_are_uploaded(self):
        calls = []
        def saved(kind, rel, target, path, family):
            calls.append((kind, target['id'], path.name, family))
            return {'id': str(len(calls)), 'processing': 'COMPLETE'}
        with patch.object(upload, 'api', side_effect=self.api), patch.object(upload, 'png', return_value=b'synthetic orchestration fixture, never an image'), patch.object(upload, 'upload', side_effect=saved):
            upload.main()
        self.assertEqual(16, sum(x[0] == 'appScreenshots' for x in calls))
        self.assertEqual(set(upload.PRODUCTS.values()), {x[1] for x in calls if x[0] == 'inAppPurchaseAppStoreReviewScreenshots'})
        self.assertEqual(22, len(json.loads((self.output / 'apple-upload-results.json').read_text())))

    def test_existing_completed_assets_are_preserved(self):
        def existing(method, path, data=None, **kwargs):
            if path.endswith('/appScreenshotSets'):
                return {'data': [{'id': f, 'attributes': {'screenshotDisplayType': d}} for f, d in [('iphone', 'APP_IPHONE_67'), ('ipad', 'APP_IPAD_PRO_3GEN_129')]]}
            if path.endswith('/appScreenshots'):
                family = path.split('/')[3]
                return {'data': [{'id': family + name, 'attributes': {'fileName': family + '-' + name}} for name in self.names]}
            if path.endswith('/appStoreReviewScreenshot'):
                return {'data': {'id': 'existing-review'}}
            return self.api(method, path, data, **kwargs)
        with patch.object(upload, 'api', side_effect=existing), patch.object(upload, 'png', return_value=b'synthetic orchestration fixture, never an image'), patch.object(upload, 'upload') as writer, patch.object(upload, 'complete') as verified:
            upload.main()
        writer.assert_not_called()
        self.assertEqual(22, verified.call_count)

    def test_proven_cli_auth_is_reused_without_private_key_environment(self):
        import base64, time
        payload = base64.urlsafe_b64encode(json.dumps({'aud': 'appstoreconnect-v1', 'exp': int(time.time()) + 600}).encode()).decode().rstrip('=')
        token = 'synthetic-header.' + payload + '.synthetic-signature'
        def cli(command, env, **kwargs):
            from types import SimpleNamespace
            self.assertEqual('app-store-connect', command[0])
            self.assertNotIn('--disable-jwt-cache', command)
            cache = Path(env['TMPDIR']) / '.codemagic-cli-tools/cache/app_store_connect_jwt'
            cache.mkdir(parents=True, exist_ok=True)
            (cache / 'synthetic-key-id').write_text(token)
            return SimpleNamespace(returncode=0)
        with patch.object(upload, '_token', None), patch.object(upload, '_token_expiry', 0), patch.object(upload.subprocess, 'run', side_effect=cli) as command:
            self.assertEqual(token, upload.jwt())
            self.assertEqual(token, upload.jwt())
            self.assertEqual(1, command.call_count)
            self.assertFalse(list((self.output.parent / '.submission-capture-tmp/apple-assets/.codemagic-cli-tools/cache/app_store_connect_jwt').glob('*')))

    def test_failed_processing_is_not_reported_complete(self):
        with patch.object(upload, 'api', return_value={'data': {'attributes': {'assetDeliveryState': {'state': 'FAILED'}}}}):
            with self.assertRaisesRegex(RuntimeError, 'processing FAILED'):
                upload.complete('appScreenshots', 'failed-fixture')

if __name__ == '__main__':
    unittest.main(verbosity=2)
