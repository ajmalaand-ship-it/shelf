# D14 — approved 7 October 2026

Financial expiry remains unresolved. No financial rows, agreement snapshots,
Google records, source originals, credentials or keys are expired by these jobs.
Real checkout remains OFF; manual Play products/prices remain approved for the
first 100 books and automatic sync remains DEFERRED.

## Deletion and recovery

Email-confirmed deletion first writes and reads back an encrypted receipt in
`Shelf-Backups/DeletionRecords` on the existing OneDrive. A restricted local copy
is under `/home/shelf/backups/shelf/deletion-journal`. Receipts contain numeric
reader identity, original creation time, suppression/deletion time and random
receipt ID; no profile, password, email or sign-in token. They remain while older
backups may restore that identity; no journal expiry is currently enabled.
Independent journal encryption uses the existing separately retained recovery
key. No credentials/key are changed. Remote failure blocks deletion safely.
A committed intent is retried even if its database transaction failed.

Initial activation inventories **all retained local and remote Shelf sets** in
socket-only disposable databases. Identities absent from the current live reader
list get baseline suppression receipts; their recorded time is the baseline
recording time, not a claim about the unknown original deletion time. No live
reader is deleted to establish this baseline.

Owner recovery: verify the claimant's original Google receipt/store account in a
private support case, then Admin → Readers → target verified new reader → Recover
a purchase. Enter original purchase ID, a case reference without personal data,
and confirm claimant verification. Leave token blank for server Google order
lookup; a privately obtained purchase token is an optional alternative. Server
checks current Google state/acknowledgement, exact order/product/time and provider
Test/real evidence, plus the original RevenueCat entitlement. Neither email nor
an order screenshot alone automatically grants anything. Unknown/refunded/revoked
transactions remain locked. Owner-only action, immutable audit and unique claim
prevent duplicates. The encrypted independent journal reserves claims across
server loss and older restores. Original reader/book sale and financial snapshots
remain unchanged. Sales ledger → Recovery history shows the audit.

RevenueCat metadata deletion retries daily. Pure V2 paginated attribute reads use
the existing private V2 key; 404 confirms absence without V1 Get-or-Create. Attribute removal uses documented
null values with newer timestamps, followed by a server read to verify removal.
The whole customer is never deleted: RevenueCat documents that this would remove
sandbox **and production purchase history**, conflicting with approved recovery
and later refunds. Immutable attribution metadata is recorded as `review_required`
rather than destructively deleting that evidence. Review genuine occurrences with
the owner/provider. No promise is made to delete Google's store orders.

## Deployment/activation evidence

Core implementation promoted at 34a01db. Final implementation e387b36 promoted
from that release after identical-commit staging (89 PHP / 656 assertions,
25 backup checks) and verified backup `/home/shelf/backups/shelf/20261007-231416/`.
Passing evidence reused on resume; historical records/handler and checkout OFF
verified again. Records-only follow-up does not change the application release.
Activation completed 7 October 22:54:41 UTC after verified local backup
`/home/shelf/backups/shelf/20261007-224743/`. All 14 local and 7 production OneDrive
sets were inventoried; zero already-absent identities were found. Encrypted remote
journal initialization/read-back passed. Dry-run expiry candidates/exceptions were
zero; maintenance runs at 03:45. No reader was deleted, RevenueCat written, key
changed or unrelated job removed during activation. Final release/evidence lives
in `release-state.json`; later focused corrections preserve this activation.

## Provider metadata permission verified — 8 October UTC / 7 October local

Owner saved Customer information → Read only on the existing V2 key. One repeat
GET `https://api.revenuecat.com/v2/projects/projbbce26da/customers/{numeric_reader_id}/attributes`
at 00:13:34 UTC returned HTTP 200 with expected list shape. The former read-scope
403 blocker is resolved; no secrets/attributes displayed, write probe, credential
change or customer deletion. Zero unfinished/eligible deletion jobs exist, so
there is no approved pending cleanup to drain. Existing 03:45 worker remains.

The approved implementation reads attributes with V2
`customer_information:customers:read`, removes mutable attributes using existing
V1 POST `/v1/subscribers/{reader_id}/attributes` null values, and verifies by V2
read-back. No additional scope is established as necessary; V2 read_write is not
used. Actual provider erasure remains unverified until genuine approved deleted-
reader work exists. Immutable metadata is held for reviewed resolution; never
remove whole-customer purchase/refund history. Financial expiry remains unresolved.
Prior focused tests reused, real checkout OFF, automatic Play sync DEFERRED.

## Retention and operation

Local daily SQL/media copies retain 14 manifest-labelled Shelf sets; an inventory
is saved before expiry. Local encrypted offsite spool retains 14 packages, with a
reported extra protected recovery set when necessary. OneDrive expiry is strictly
older than 90 days; ciphertext/sidecar pairs only inside `Shelf-Backups`. The last
verified remote recovery set remains pinned even past 90 days. DeletionRecords,
keys, unknown/incomplete files and unrelated files are excluded. A missing verified
set blocks cleanup. Dry-run inventory is mandatory before activation; inventories
and exception reports stay in the private offsite backup directory.

Routine dated application/job logs expire after 90 days (retain the full boundary
day). Pre-policy single logs are archived on activation/day of rotation and aged
conservatively from that date. Financial evidence is in the append-only database,
not these routine logs. Monolog's automatic deletion is disabled so incident holds
are honored. Incident holds in the restricted
`/home/shelf/backups/shelf/security-retention-exceptions.json` are owner-reviewed
objects containing only `file`, `case_reference`, `owner_id`, `reviewed_at` and
`review_at` (ISO dates with timezone). Only allowed dated routine log names are
accepted. Overdue reviews are reported and continue to hold the log; expiry never
silently overrides an incident hold. The owner must authorize each hold/review;
use a case reference without personal data. This file is not a financial policy.

Activation uses a fresh verified backup, initializes/verifies the independent
journal, inventories retained backup identities, saves the cleanup dry-run and
adds only Shelf's 03:45 daily maintenance job. Existing jobs/credentials stay.
`python3 -B scripts/shelf_retention.py inventory` is the dry-run command.
`scripts/shelf_d14_maintenance.py run` retries deletion/provider jobs and performs
scoped expiry. Fixed provider status/counts are logged, never tokens or attributes.

## Older-backup/replacement-host recovery

Use current committed recovery tools and existing independently retained key and
OneDrive connection. `shelf_offsite_backup.py recover` must obtain **current remote**
journal records, not the journal state captured by an old database. Missing,
unreadable or corrupt records stop recovery before exporting usable restored data.
After original SQL/media/code hash verification, the committed reversible D14
migration runs only against the disposable socket database. Deletions/session
removal are replayed; identity allocation is reserved above journal identities;
minimal provider jobs and recovery claims are restored without altering sales.
Paid access is set inactive until a fresh provider check. Sanitized SQL gets a
separate hash/replay receipt; the original manifest describes the original backup.
Both an HTTP entry guard and `.shelf-recovery-blocked` prevent reopening even an
old archived release. Recovery never starts the restored app or contacts live DB.

Before cutover, keep maintenance on, re-fetch/reapply the latest journal (deletions
may have arrived since extraction), import sanitized SQL, promote current code,
verify no suppressed reader/session remains and reconcile ownership/refunds. Only
then may the owner approve removing the recovery block and reopening. This task
proves isolated recovery; full replacement-host cutover and alert delivery remain
pre-release checks, not claimed as passed. A claim reserved remotely but absent
from local audit after a transaction failure requires reviewed repair; never
ignore the reservation or transfer it to another account.

## Provider documentation checked

- [RevenueCat V2 customer attributes](https://www.revenuecat.com/docs/api-v2/customer):
  read-only paginated metadata/404; V1 Get-or-Create is avoided for deletion.
- [RevenueCat customer API](https://www.revenuecat.com/docs/api-v1/customers):
  subscriber attributes, null/empty removal, newer update timestamps.
- [RevenueCat customer profile](https://www.revenuecat.com/docs/dashboard-and-metrics/customer-profile):
  customer deletion removes purchase history.
- [RevenueCat customer attributes](https://www.revenuecat.com/docs/customers/customer-attributes):
  immutable device/attribution attributes.
- [Google order resource](https://developers.google.com/android-publisher/api-ref/rest/v3/orders):
  server order/token and refund states; response metadata is not retained.
- [Google product purchase V2](https://developers.google.com/android-publisher/api-ref/rest/v3/purchases.productsv2):
  exact product, completion, acknowledgement, refund quantities and Test evidence.
