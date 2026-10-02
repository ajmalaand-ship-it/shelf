#!/usr/bin/env bash
# Dedicated read-only Google check; local refunds are append-only.
set -euo pipefail
umask 077
export TMPDIR=/home/shelf/tmp TMP=/home/shelf/tmp TEMP=/home/shelf/tmp
runner_root=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd -P)
if [[ -f "$runner_root/.shelf-staging" ]]; then
    echo "Shelf Test: production refund polling disabled."
    exit 0
fi
[[ "$runner_root" == /home/shelf/apps/shelf ]] || exit 1
cd /home/shelf/apps/shelf
exec 9>storage/framework/play-refunds.lock
flock -n 9 || exit 0
if timeout --signal=TERM --kill-after=10 840 php artisan shelf:check-play-refunds; then
    exit 0
else
    refund_status=$?
    echo "Google refund check stopped safely: exit=$refund_status"
    exit "$refund_status"
fi
