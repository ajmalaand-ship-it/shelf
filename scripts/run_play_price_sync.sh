#!/usr/bin/env bash
# Dedicated price queue only; never drain unrelated queues.
set -euo pipefail
umask 077
export TMPDIR=/home/shelf/tmp TMP=/home/shelf/tmp TEMP=/home/shelf/tmp
runner_root=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd -P)
if [[ -f "$runner_root/.shelf-staging" ]]; then
    echo "Shelf Test: Play sync and production queue runner disabled."
    exit 0
fi
[[ "$runner_root" == /home/shelf/apps/shelf ]] || exit 1
cd /home/shelf/apps/shelf
exec 9>storage/framework/play-price-sync.lock
flock -n 9 || exit 0
php artisan shelf:sync-play-prices --pending --quiet
php artisan queue:work database --queue=play-prices --stop-when-empty --max-time=40 --timeout=60 --tries=1 --quiet
