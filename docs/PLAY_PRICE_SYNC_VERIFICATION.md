# Step 4 Task 2b verification — 30 September 2026 UTC

- Owner decisions of 29 September recorded in AGENTS section 8 and the Master
  Record addendum: all prices are edited only in admin and automatically sent to
  Play; one service account is shared with RevenueCat. No real money.
- `scripts/run_tests.sh`: PASS, 167 PHP tests, 1831 assertions. No mobile files
  changed, so Flutter tests/builds were correctly skipped.
- Mocked Google API covers signed OAuth credentials, USD/local conversion,
  missing-product creation, price updates, activation, withdrawal/bin/restore,
  duplicate jobs, newest admin price, redacted errors/backoff/recovery,
  unexpected option protection, transactional rollback, private credentials,
  admin status and reversible price migration.
- Backup verified before rollout:
  `/home/shelf/backups/shelf/20260930-042654` (SQL, ZIP and SHA-256 verified).
- Migrations `2026_09_30_020000_create_play_price_sync` and
  `2026_09_30_020100_set_owner_approved_book_test_prices` applied successfully.
  Maintenance ended successfully.
- Existing books 3, 4, 5, 6, 7 and 8 now have USD 2.99 test prices. Each change
  records old/new price, owner ID, Ajmal's approval and execution timestamp.
  Their identities, content, publication states and purchase records are retained.
- Dedicated Shelf `play-prices` queue runner installed once per minute, tested
  directly; nightly backup cron preserved. Previous crontab backed up at
  `/home/shelf/backups/shelf/20260930-042654/shelf-crontab-before-play-sync.txt`.
- Live sync command reports disabled and makes no Google API calls. All six
  statuses: Pending — Waiting for Google Play setup. Public purchase config remains
  disabled, test_mode true and offline_days 30. RevenueCat purchase flow unchanged.
- Real Google/RevenueCat integration remains pending owner credentials/setup.
  [Owner setup instructions](PURCHASES_TEST_SETUP.md) require no hand-created
  Play products or Play price edits.

Undo before credentials exist: restore the previous crontab with `crontab` and
the backed-up file above if removing the runner, then roll back the price migration
through Laravel. It restores the previous prices with reverse audit entries and
refuses to overwrite later owner price edits. Once Google settings have synced,
rollback also requires a corrective sync of the restored admin settings; reverting
the database alone cannot undo Google's accepted prices.
