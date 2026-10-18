#!/usr/bin/env python3
"""Synthetic CLI responses and workflow inspection; no network/build/upload."""
import contextlib
import io
from pathlib import Path
import subprocess
import unittest
from unittest.mock import Mock
import yaml
import check_build_number as guard


class CliCheckTests(unittest.TestCase):
    def run_cli(self, value='4', code=0, chosen='5'):
        runner=Mock(return_value=subprocess.CompletedProcess(guard.COMMAND, code, value, 'synthetic-private-error'))
        with contextlib.redirect_stdout(io.StringIO()) as output:
            guard.check({'SHELF_IOS_BUILD_NUMBER':chosen},runner)
        return runner,output.getvalue()

    def test_explicit_number_no_run_index_or_fallback(self):
        for value in ['', '0', '04', ' 5', '5\n', '5.0', '-1', None]:
            with self.assertRaises(guard.GuardError):guard.number(value)
        runner=Mock()
        with self.assertRaises(guard.GuardError):guard.check({'BUILD_NUMBER':'8','PROJECT_BUILD_NUMBER':'8'},runner)
        runner.assert_not_called()

    def test_documented_cli_scope_and_no_secret_arguments(self):
        runner,output=self.run_cli()
        self.assertEqual(guard.COMMAND,runner.call_args.args[0])
        self.assertIn('--pre-release-version=1.0.0',guard.COMMAND)
        self.assertIn('--platform=IOS',guard.COMMAND)
        self.assertIn('latest 4; chosen build 5',output)
        self.assertNotIn('--private-key',guard.COMMAND)
        self.assertIn('Pending uploads require separate server preflight',output)

    def test_cli_resolves_access_without_wrapper_secret_variable_check(self):
        self.run_cli() # Only chosen number is in env; CLI resolves its own access.

    def test_used_or_lower_number_stops(self):
        for chosen in ['3','4']:
            with self.assertRaisesRegex(guard.GuardError,'must exceed'):self.run_cli(chosen=chosen)
        with self.assertRaises(guard.GuardError):self.run_cli(value='5')

    def test_auth_failure_private_output_withheld(self):
        with self.assertRaises(guard.GuardError) as error:self.run_cli(code=1)
        self.assertNotIn('synthetic-private-error',str(error.exception))
        self.assertIn('Validate existing integration CLI access',str(error.exception))

    def test_malformed_empty_cli_result_stops(self):
        for value in ['', 'null', 'No builds', '4\n5', '{"value":4}']:
            with self.assertRaises(guard.GuardError):self.run_cli(value=value)

    def test_missing_tool_or_timeout_stops(self):
        for failure in [FileNotFoundError(),subprocess.TimeoutExpired(guard.COMMAND,120)]:
            with self.assertRaises(guard.GuardError):
                guard.check({'SHELF_IOS_BUILD_NUMBER':'5'},Mock(side_effect=failure))

    def test_workflow_uses_same_nonpublishing_credential_path(self):
        root=Path(__file__).resolve().parents[2]
        workflows=yaml.safe_load((root/'codemagic.yaml').read_text())['workflows']
        signed=workflows['ios-app-store-release'];probe=workflows['ios-asc-access-check']
        export = next(x['script'] for x in signed['scripts'] if 'xcode-project use-profiles' in x['script'])
        self.assertIn('"testFlightInternalTestingOnly":false', export)
        self.assertIn('p["testFlightInternalTestingOnly"] is False', export)
        self.assertFalse(signed['publishing']['app_store_connect']['submit_to_app_store'])
        self.assertEqual(signed['integrations'],probe['integrations'])
        self.assertEqual(signed['environment']['groups'],probe['environment']['groups'])
        self.assertNotIn('publishing',probe);self.assertNotIn('artifacts',probe)
        self.assertNotIn('ios_signing',probe['environment']);self.assertNotIn('triggering',probe)
        self.assertEqual([{'name':'Validate existing integration CLI access and explicit build candidate',
                          'script':'python3 ../scripts/ios/check_build_number.py'}],probe['scripts'])
        self.assertEqual(1,sum('check_build_number.py' in x['script'] for x in signed['scripts']))
        index=next(i for i,s in enumerate(signed['scripts']) if 'check_build_number.py' in s['script'])
        self.assertLess(index,next(i for i,s in enumerate(signed['scripts']) if 'flutter ' in s['script']))
        self.assertEqual('shelf_app_store_connect',signed['integrations']['app_store_connect'])
        for workflow in [signed,probe]:
            for step in workflow['scripts']:
                result=subprocess.run(['bash','-n'],input=step['script'],text=True,capture_output=True)
                self.assertEqual(0,result.returncode,step['name'])


if __name__=='__main__':unittest.main(verbosity=2)
