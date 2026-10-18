#!/usr/bin/env python3
"""Run only capture navigation regression in the wrapper's disposable checkout."""
import os
from pathlib import Path
import shutil
import subprocess
root = Path(__file__).resolve().parents[2]
target = Path(os.environ['SHELF_TEST_TMP']) / 'capture-mobile'
shutil.copytree(root / 'mobile', target, ignore=shutil.ignore_patterns('.dart_tool', 'build', '.gradle', 'Pods'))
shutil.copyfile(root / 'scripts/ios/test_capture_navigation.dart', target / 'test/submission_capture_navigation_test.dart')
shutil.copyfile(root / 'scripts/ios/capture_navigation.dart', target / 'test/capture_navigation.dart')
flutter = os.environ.get('FLUTTER_BIN', '/home/shelf/flutter/bin/flutter')
resolved = subprocess.run([flutter, 'pub', 'get', '--enforce-lockfile'], cwd=target, capture_output=True, text=True)
if resolved.returncode:
    print(resolved.stdout)
    print(resolved.stderr)
    raise SystemExit(resolved.returncode)
subprocess.run([flutter, 'analyze', '--no-pub', 'test/submission_capture_navigation_test.dart', 'test/capture_navigation.dart'], cwd=target, check=True)
subprocess.run([flutter, 'test', '--no-pub', 'test/submission_capture_navigation_test.dart'], cwd=target, check=True)
