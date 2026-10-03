#!/usr/bin/env bash
set -euo pipefail

usage() {
    echo "Usage: scripts/run_tests.sh [--mobile|--backup]"
    echo "Always runs PHP tests. Use --mobile only when mobile/ changed in this task."
}

run_mobile=false
run_backup=false
case "${1:-}" in
    '') ;;
    --mobile) run_mobile=true; shift ;;
    --backup) run_backup=true; shift ;;
    -h|--help) usage; exit 0 ;;
    *) usage >&2; exit 2 ;;
esac
if (( $# )); then
    usage >&2
    exit 2
fi

repo_root=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)
export TMPDIR=/home/shelf/tmp
export TMP="$TMPDIR" TEMP="$TMPDIR"
mkdir -p -- "$TMPDIR"
umask 077
run_dir=$(mktemp -d "$TMPDIR/shelf-tests.XXXXXXXX")
cleanup() {
    local status=$?
    trap - EXIT
    rm -rf -- "$run_dir"
    exit "$status"
}
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

# Every child process gets a private temporary directory beneath /home/shelf/tmp.
export TMPDIR="$run_dir" TMP="$run_dir" TEMP="$run_dir"
export SHELF_TEST_TMP="$run_dir"
export XDG_CACHE_HOME="$run_dir/cache" XDG_CONFIG_HOME="$run_dir/config"
export COMPOSER_CACHE_DIR="$run_dir/cache/composer"
export PUB_CACHE="$run_dir/cache/pub" GRADLE_USER_HOME="$run_dir/cache/gradle"
export ANDROID_USER_HOME="$run_dir/android"
export JAVA_TOOL_OPTIONS="${JAVA_TOOL_OPTIONS:+$JAVA_TOOL_OPTIONS }-Djava.io.tmpdir=$run_dir"
mkdir -p -- "$XDG_CACHE_HOME" "$XDG_CONFIG_HOME"

cd -- "$repo_root"
if "$run_backup"; then
    echo "Running focused backup/recovery checks (no PHP or phone suite)"
    python3 -B -m unittest discover -s scripts -p 'test_shelf_*backup.py' -v
    exit 0
fi
echo "Running PHP tests (temporary files: $run_dir)"
php_args=(--do-not-cache-result)
if [[ -n "${SHELF_PHP_TEST_FILTER:-}" ]]; then
    php_args+=(--filter "$SHELF_PHP_TEST_FILTER")
fi
php -d display_errors=1 -d sys_temp_dir="$run_dir" -d upload_tmp_dir="$run_dir" \
    -d session.save_path="$run_dir" vendor/bin/phpunit "${php_args[@]}"

if "$run_mobile"; then
    # Flutter generates build and .dart_tool files in the project directory.
    # Test a disposable copy so those files are also contained and cleaned up.
    mkdir -- "$run_dir/mobile"
    tar -C "$repo_root/mobile" --exclude='./build' --exclude='./.dart_tool' \
        --exclude='./.gradle' --exclude='./android/.gradle' \
        --exclude='./.flutter-plugins' --exclude='./.flutter-plugins-dependencies' \
        -cf - . | tar -C "$run_dir/mobile" -xf -
    cd -- "$run_dir/mobile"
    echo "Running Flutter tests (--mobile requested)"
    "${FLUTTER_BIN:-/home/shelf/flutter/bin/flutter}" test --reporter expanded --concurrency=2
    "${FLUTTER_BIN:-/home/shelf/flutter/bin/flutter}" test test/staging_identity_test.dart --reporter expanded \
        --dart-define=SHELF_TEST_MODE=true
else
    echo "Flutter/Android tests skipped (no --mobile flag)."
fi
