#!/usr/bin/env python3
"""Validate an explicit build number via Codemagic's documented read-only CLI.

Credential resolution belongs to app-store-connect, not this wrapper.
Pending upload evidence requires the separate server-side Apple preflight.
"""
import os
import re
import subprocess

COMMAND = ['app-store-connect', 'get-latest-testflight-build-number', '6821471140',
           '--platform=IOS', '--pre-release-version=1.0.0', '--disable-jwt-cache', '--silent']


class GuardError(Exception):
    pass


def number(value):
    if not isinstance(value, str) or not re.fullmatch(r'[1-9][0-9]{0,17}', value):
        raise GuardError('Set explicit positive SHELF_IOS_BUILD_NUMBER; no fallback.')
    return int(value)


def check(env, run=None):
    chosen = number(env.get('SHELF_IOS_BUILD_NUMBER', ''))
    run = run or subprocess.run
    try:
        result = run(COMMAND, capture_output=True, text=True, timeout=120)
    except (OSError, subprocess.TimeoutExpired):
        raise GuardError('Codemagic App Store Connect CLI unavailable or timed out; stop, no fallback.') from None
    if result.returncode:
        raise GuardError('App Store Connect CLI authentication/read failed. Validate existing integration CLI access before a signed build; private output withheld.')
    value = result.stdout.strip()
    latest = number(value)
    if chosen <= latest:
        raise GuardError(f'Chosen build {chosen} must exceed TestFlight build {latest}; stop.')
    print(f'PASS: Codemagic CLI read TestFlight IOS/1.0.0 latest {latest}; chosen build {chosen}. Pending uploads require separate server preflight; no number reserved.')


if __name__ == '__main__':
    try:
        check(os.environ)
    except GuardError as error:
        print(str(error))
        raise SystemExit(1)
