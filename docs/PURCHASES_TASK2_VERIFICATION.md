# Step 4 Task 2 verification — 30 September 2026 UTC

- Governing Master Record v2.3 and owner addendum committed unchanged; file is
  `shelf:shelf`, mode `0644`. AGENTS requires reading it before every task.
- `scripts/run_tests.sh --mobile`: PASS, 158 PHP tests / 1765 assertions and
  134 Flutter tests. Purchase widgets checked at 320 and 430 logical pixels:
  English/LTR purchase form, unchanged RTL title, local store price, agreement
  gate and large Buy target. Real phone layout remains owner acceptance work.
- Verified backup before migration:
  `/home/shelf/backups/shelf/20260930-031043` (SQL completion marker, ZIP test,
  SHA-256 verified). Migration `2026_09_30_010000_create_book_purchases` ran,
  batch 18. Maintenance ended successfully.
- Live HTTP-kernel simulation PASS: authenticated purchase plus mocked REST
  confirmation, duplicate event/transaction, other reader denied, withdrawn
  buyer access, restore, refund, revoke, reader denied admin. All synthetic rows
  rolled back; existing readers, catalogue and financial row counts unchanged.
  This is synthetic testing, not a real externally delivered RevenueCat event.
- Direct HTTPS: `/api/purchases/config` reports disabled, test mode true,
  offline_days 30; unauthenticated `/api/library` returns 401.
- All six existing books remain Draft with unset owner prices and canonical
  product IDs `shelf_book_3` through `shelf_book_8`. No prices or agreements invented.
- New upload key outside git, files 0600; verified backup directory 0700:
  `/home/shelf/backups/keys/shelf-upload-20260930-023650`. Both backup byte hashes
  and restored certificate checked. Final AAB certificate matches this key.
- Release AAB (no owner-preview token), signature verified:
  `/home/shelf/apps/shelf/storage/app/private/owner-aabs/shelf-internal-test-20260930-031242.aab`
  SHA-256 `ea230a31979de78b1ae9d38143c472e4249474ceca7dc6dc70826c5c0d6e5aff`.
- Owner-preview APK, existing Shelf test certificate verified:
  `/home/shelf/apps/shelf/storage/app/private/owner-apks/shelf-owner-preview-20260930-031515.apk`
  SHA-256 `0eca4063da9532d7e0fac17e13a8369be0826d6a0dbe5455e4bad60ff47c5da4`.
  Preview expires `2026-10-07T03:13:06+00:00`.
- Real Play/RevenueCat purchase testing remains pending: new Shelf project,
  keys/app ID, products, entitlement mappings, webhook and license tester setup
  are not supplied. Purchases remain disabled; no money was charged.
  Exact setup instructions: [PURCHASES_TEST_SETUP.md](PURCHASES_TEST_SETUP.md).
