import importlib.util
import os
from pathlib import Path
import plistlib
import tempfile
import unittest

spec = importlib.util.spec_from_file_location('configure_google', Path(__file__).with_name('configure_google.py'))
module = importlib.util.module_from_spec(spec)
spec.loader.exec_module(module)

class GoogleIosConfigurationTest(unittest.TestCase):
    def test_client_callback_and_removal_preserve_other_plist_fields(self):
        with tempfile.TemporaryDirectory(dir=os.environ['SHELF_TEST_TMP']) as folder:
            path = Path(folder)/'Info.plist'
            original = {'NSPhotoLibraryUsageDescription':'Photos', 'CFBundleURLTypes':[
                {'CFBundleURLName':'other','CFBundleURLSchemes':['existing']}]}
            path.write_bytes(plistlib.dumps(original))
            client='synthetic-client.apps.googleusercontent.com'
            module.configure(path,client)
            p=plistlib.loads(path.read_bytes())
            self.assertEqual(p['GIDClientID'],client)
            self.assertEqual(p['CFBundleURLTypes'][1]['CFBundleURLSchemes'],['com.googleusercontent.apps.synthetic-client'])
            module.configure(path,client)
            self.assertEqual(len(plistlib.loads(path.read_bytes())['CFBundleURLTypes']),2)
            module.configure(path,'')
            self.assertEqual(plistlib.loads(path.read_bytes()),original)
            before=path.read_bytes()
            with self.assertRaises(SystemExit): module.configure(path,'invalid-client')
            self.assertEqual(path.read_bytes(),before)

if __name__ == '__main__': unittest.main()
