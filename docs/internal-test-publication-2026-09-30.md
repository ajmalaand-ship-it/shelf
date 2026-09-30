# Internal-test catalogue publication — 30 September 2026

Ajmal approved publishing books 3–8 and accepted public catalogue visibility.
He confirmed that item hiding was inherited from the old private-draft period,
not an editorial choice, and approved making all non-deleted items visible.
The temporary full-item samples are the first two items in current book order:

| Book ID | Items | Temporary sample item IDs |
|---|---:|---|
| 3 | 81 | 4, 5 |
| 4 | 67 | 85, 86 |
| 5 | 57 | 152, 153 |
| 6 | 74 | 209, 210 |
| 7 | 22 | 283, 284 |
| 8 | 41 | 305, 306 |

Read-only preflight confirms author credits, Pashto language, existing private
covers, USD 2.99 prices and canonical `shelf_book_<id>` product identifiers.
All 342 items have no existing sample choices; one item was already visible.
Purchases and Google Play sync are disabled. No source text or access rule changes.

Migration `2026_09_30_070000_publish_owner_internal_test_catalogue` makes the
items visible, sets the temporary samples and invokes the same
`Collection::changeStatus('published')` method as the admin Publish action.
The six-book operation is one transaction, with the owner set as the actor.
The private setting `shelf_internal_test_catalogue_20260930` records owner,
time, previous item flags/sample choices, sample IDs and source checksums.
Normal status history also records publication. Rollback restores previous
visibility/samples and Draft states, retaining history with new reverse entries;
it refuses to overwrite any subsequent edits to these books/items.
Deleted items and unrelated books are excluded. No app rebuild is involved.

Run `scripts/rollout_internal_test_catalogue.sh` with live-server approval.
It requires committed code, runs read-only preflight, makes a verified SQL/media
backup, records its path and rollback command inside the backup directory,
enters maintenance, applies only this migration, restores the site and checks
the live HTTPS catalogue, samples, locked items/media, paginated contents,
covers, disabled purchases and protected owner preview. A failure stops the
run; maintenance is lifted before stopping. No automatic rollback is attempted.

Automated PHP checks passed: 171 tests, 2,083 assertions, including publication,
exact source preservation, samples/locked remainder, unrelated drafts, owner
preview, all-or-nothing failure, successful reversal and rollback refusal after
later owner edits. Flutter tests are skipped because no mobile files changed.

Live rollout results will be appended after verification.
