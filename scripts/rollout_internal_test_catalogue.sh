#!/usr/bin/env bash
# CHANGES: approved 30 September 2026. Backup first; stop at first failure.
set -euo pipefail
umask 077
export TMPDIR=/home/shelf/tmp TMP=/home/shelf/tmp TEMP=/home/shelf/tmp
repo_root=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)
cd "$repo_root"
migration=database/migrations/2026_09_30_070000_publish_owner_internal_test_catalogue.php
test "$(git branch --show-current)" = main
git diff --quiet
git diff --cached --quiet
git ls-files --error-unmatch "$migration" >/dev/null
php scripts/check_internal_test_catalogue.php --preflight
backup_report=$(python3 scripts/shelf_daily_backup.py)
echo "$backup_report"
backup_path=$(printf '%s\n' "$backup_report" | sed -n 's/^Verified SQL, ZIP (unzip -t), and SHA-256: //p')
test -d "$backup_path"
case "$backup_path" in
    /home/shelf/backups/shelf/[0-9]*-[0-9]*) ;;
    *) echo 'Unexpected backup path; stopping.' >&2; exit 1 ;;
esac
{
    echo 'Shelf internal-test catalogue publication: owner approval 30 September 2026.'
    echo "Verified backup: $backup_path"
    echo "Code commit: $(git rev-parse HEAD)"
    echo "Migration: $migration"
    echo 'Rollback requires a fresh backup and disabled purchases/Play sync; refuses newer owner edits.'
    echo "Rollback command: php artisan migrate:rollback --path=$migration --step=1 --force"
} > "$backup_path/internal-test-publication-notes.txt"
maintenance_started=false
cleanup() {
    result=$?
    trap - EXIT
    if "$maintenance_started"; then
        if ! php artisan up; then
            echo 'Maintenance recovery failed; stop and report immediately.' >&2
            exit 1
        fi
    fi
    exit "$result"
}
trap cleanup EXIT
maintenance_started=true
php artisan down
php artisan migrate --path="$migration" --force
php artisan up
maintenance_started=false
php scripts/check_internal_test_catalogue.php
echo 'Live publication and access verification passed.' >> "$backup_path/internal-test-publication-notes.txt"
