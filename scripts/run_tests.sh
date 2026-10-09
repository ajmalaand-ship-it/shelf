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
    backup_pattern='test_shelf_*backup.py'
    if [[ "${SHELF_BACKUP_TEST_SCOPE:-}" == 'd14' ]]; then backup_pattern='test_shelf_d14_backup.py';
    elif [[ -n "${SHELF_BACKUP_TEST_SCOPE:-}" ]]; then echo 'Unknown backup scope' >&2; exit 2; fi
    python3 -B -m unittest discover -s scripts -p "$backup_pattern" -v
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
    mobile_args=()
    if [[ "${SHELF_MOBILE_TEST_SCOPE:-}" == "purchases" ]]; then
        mobile_args=(test/book_purchases_test.dart test/library_ux_test.dart test/staging_identity_test.dart)
    elif [[ "${SHELF_MOBILE_TEST_SCOPE:-}" == "privacy-support" ]]; then
        mobile_args=(test/privacy_support_test.dart test/interface_language_test.dart)
    elif [[ "${SHELF_MOBILE_TEST_SCOPE:-}" == "review-2" ]]; then
        mobile_args=(test/bookstore_screens_test.dart test/home_collections_test.dart
            test/interface_language_test.dart test/book_content_language_test.dart
            test/library_ux_test.dart test/reader_widget_test.dart
            test/reader_settings_test.dart test/typography_layout_test.dart
            test/audio_player_widget_test.dart test/share_card_entry_test.dart
            test/privacy_support_test.dart test/review_2_layout_test.dart)
    elif [[ "${SHELF_MOBILE_TEST_SCOPE:-}" == "review-2-corrections" ]]; then
        mobile_args=(test/review_2_layout_test.dart test/reader_widget_test.dart)
    elif [[ "${SHELF_MOBILE_TEST_SCOPE:-}" == "pinned-preview" ]]; then
        mobile_args=(test/reading_preferences_pinned_test.dart test/reader_settings_test.dart
            test/reader_widget_test.dart)
    elif [[ "${SHELF_MOBILE_TEST_SCOPE:-}" == "paginated-reader" ]]; then
        mobile_args=(test/text_pages_test.dart test/paginated_reader_test.dart
            test/reader_settings_test.dart test/reader_widget_test.dart
            test/reading_preferences_pinned_test.dart test/book_content_language_test.dart
            test/audio_player_widget_test.dart test/share_card_entry_test.dart)
    elif [[ -n "${SHELF_MOBILE_TEST_SCOPE:-}" ]]; then
        echo "Unknown mobile test scope" >&2; exit 2
    fi
    if [[ "${SHELF_MOBILE_TEST_SCOPE:-}" == "pinned-preview" ]]; then
        "${FLUTTER_BIN:-/home/shelf/flutter/bin/flutter}" analyze --no-fatal-infos --no-fatal-warnings \
            lib/settings/reading_preferences_sheet.dart test/reading_preferences_pinned_test.dart
    fi
    if [[ "${SHELF_MOBILE_TEST_SCOPE:-}" == "paginated-reader" ]]; then
        "${FLUTTER_BIN:-/home/shelf/flutter/bin/flutter}" analyze --no-fatal-infos --no-fatal-warnings \
            lib/reading lib/screens/poem_reader_screen.dart lib/screens/collection_detail_screen.dart \
            lib/widgets/poetry_text.dart lib/settings/reader_settings.dart lib/settings/reading_preferences_sheet.dart \
            test/text_pages_test.dart test/paginated_reader_test.dart
    fi
    "${FLUTTER_BIN:-/home/shelf/flutter/bin/flutter}" test "${mobile_args[@]}" --reporter expanded --concurrency=2
    "${FLUTTER_BIN:-/home/shelf/flutter/bin/flutter}" test test/staging_identity_test.dart --reporter expanded \
        --dart-define=SHELF_TEST_MODE=true
else
    echo "Flutter/Android tests skipped (no --mobile flag)."
fi
