# Google refund detection

Owner approval: 2 October 2026, Master Record Step 4 / D12 / D13.
The initial read-only Google Voided Purchases API probe returned HTTP 200.

`shelf:check-play-refunds` uses the existing private Shelf Play service account
and only a GET to purchases.voidedpurchases.list for services.shelf.app.
Price sync remains disabled. No new notification configuration is required.
The dedicated production cron runs every 15 minutes; staging rejects real Google
calls and verifies synthetic refunds inside a transaction that is rolled back.

Each run scans Google's last 30 days, paginates and validates each complete page
before applying it. Exact order identity, product and purchase time must match.
After the first order match, only a SHA-256 purchase-token hash is retained for
future token matching. Unknown purchases are retried next run; ambiguous matches
are held and logged. No raw purchase tokens or provider bodies are logged.

Refunds append ledger/event history and revoke the matching ownership. Repeats
and existing RevenueCat refunds cannot duplicate the refund ledger entry. Test
refunds remain Test and excluded from real income. Other valid purchases retain
access. Library returns no refunded book; the existing app removes downloaded
copies at the next successful server check. Existing 30-day offline expiry stays.

Read audit status with `php artisan shelf:check-play-refunds --status`.
Audit rows and storage/logs/play-refunds.log record HTTP status and safe counts.
Errors never guess ownership; verified earlier pages remain valid, and failed
pages are retried on the next run. Locks and a bounded runtime prevent overlap.

Google limits this API to the last 30 days of seen voids; refunds issued without
revoking access do not appear. Long outages beyond that window need an owner
review. Documentation: https://developers.google.com/android-publisher/voided-purchases

Deployment uses scripts/staging/workflow.py deploy/promote at the same commit.
The reversible migration adds audit/hash tables without altering prior rows.
Install only scripts/install_play_refunds_cron.py --after-backup VERIFIED_PATH
with a fresh verified backup; it preserves other jobs and archives the old cron.

Rollback: disable SHELF_PLAY_REFUNDS_ENABLED and clear config, remove only the
refund runner cron entry, then use the staging release rollback process. Retain
all financial history and populated audit tables; migration rollback refuses to
erase these. Never restore a database over later reader or financial activity.
