# Shelf — one-book Apple sandbox setup

## Apple purchase-session safety — production promotion completed, 15 October local / 16 October 2026 UTC

Owner authorized resuming the already-staged application rollout using the isolated
promotion operator at **71c56cf7ff1dc479ebdb4cdd5433aa4d36f92534**. Started from that
exact clean `ios/foundation` checkout. **Production and staging now both run application
source 1d3dfa5abc1bb35097ada6f2a69fb003c2e57b19**. This entry supersedes the earlier
stopped-promotion and separate-promotion-authorization status; historical evidence below
is retained. The operator correction remains in the trusted isolated checkout; production
was advanced only to the intended application source, not to the later operator/docs commits.

### Preflight, backup and bounded safeguards

Verified production initially at **c8c266221d972d409ffa947ca87397293c69c004**, healthy and
out of maintenance; the only local change was the recognized cPanel PHP handler.
Staging remained at exact **1d3dfa5**, migration **batch 12**, zero pending migrations.
Reused the passing staged log SHA-256
**ec4faca3ecd88b1c0a498fc8b5732a8341a045ba180bc8bc86088cdfa0082723**.
Finished the previously unreached staging purchase/accounting/owner-share health commands;
Apple schema/closed-gate and real HTTPS/private-boundary checks all passed. No broad
unit or mobile suite rerun: prior **64 PHP / 604 assertions, 16 Flutter + 1 staging
identity** and **45 focused operator tests** remain the source-validation evidence.
Backup storage readiness and the pinned historical checkpoint/configuration passed.
Both existing provider webhook configurations matched their original verified fingerprint
by read-only RevenueCat GET (HTTP 200), before and after promotion.

Fresh production backup **/home/shelf/backups/shelf/20261016-021356/** passed SQL,
ZIP `unzip -t` and SHA-256 verification before maintenance/source/migration changes.
It contains `promotion-notes.txt`, the rollback source and
`entitlement-refresh-verification.json`. The original encrypted private-configuration
checkpoint remains **/home/shelf/backups/shelf/20261016-004143/apple-session-rollout-checkpoint/**;
its pinned provenance/hash and all protected private-file fingerprints passed again.
The new backup is the current release-state backup; prior rollback source is **c8c2662**.

The operator acquired the existing refund lock non-blockingly before backup and held it
through the promotion checks, under the documented 10-minute window and bounded child
commands/maintenance cleanup. Cron was never edited or disabled. Historical acceptance
was limited to entitlement **id 5**'s `checked_at`, `created_at`, `updated_at`, independently
linked to completed duplicate-only refund audit **1342**. UTC epoch normalization verified
the backup/live SYSTEM/AST offset without changing stored timestamps. All identity,
active-status, ownership, reader, purchase/refund, ledger, consent, agreement and accounting
comparisons remained strict. The fresh actual-promotion before/after comparison included
all three timestamps and passed exactly; no table-wide exemption or rebaseline was used.

### Production results and resumed scheduling

Fast-forward promotion succeeded. Apple purchase-session migration
**2026_10_15_010000_create_apple_purchase_sessions** applied in production **batch 29**;
zero pending migrations, zero sessions, no Apple purchase history. Production purchase,
accounting and all six owner-share checks passed. Maintenance was lifted and HTTPS
health, locked paid content/Library for unauthenticated requests, webhook guards and
staging isolation passed. Release state records source **1d3dfa5** on both environments.

The existing verified owner's Book 3 Play ownership and paid access passed. The actual
Library controller reconciliation performed independent read-only provider proof and
returned Book 3, its lease and all Apple denials correctly. This verification ran inside
a database transaction that was rolled back, under the same refund lock. Strict protected
history snapshots before/after matched exactly: no persistent ownership, timestamp,
purchase, refund, consent, ledger or accounting change was made by the probe. No duplicate
sale or Test income in ordinary real-income queries was found.

The lock was released and the original crontab fingerprint was unchanged. Natural
scheduled refund audit **1343** completed at **2026-10-16 02:15:02 UTC**, HTTP **200**,
**pages=1, seen=duplicate=1, refunded=unmatched=conflict=0**. No manual refund run was
started. A subsequent coherent comparison against the original backup again verified
strict ownership/financial history and accepted only the same three metadata columns,
now independently linked to audit 1343. Private final evidence is in the fresh backup's
`post-promotion-verification.json`; no reader identity or provider secret is published.

Production `.env`, staging private `.env`, Apple webhook authorization/metadata, provider
key and cPanel handler fingerprints are unchanged. Existing Apple and Google RevenueCat
webhooks are unchanged. Production's sole local change remains its preserved cPanel
handler. **Both Apple sandbox gates OFF; real checkout OFF; Apple sign-in/relay gates
OFF; pricing sync OFF. Production healthy and out of maintenance.** No rollback was
needed. If a later rollback is authorized, follow `docs/STAGING.md` and this backup's
notes: inspect migration batch/evidence first; never restore over subsequent reader or
money activity. No database restore or migration rollback was performed.

No TestFlight build/upload, purchase, provider-setting edit, gate activation or public
release occurred. France exclusion/ANSSI cancellation, authentication/relay deferrals,
Android/UI pause, pricing-sync and second-phone-restore deferrals, and the joint
Android/iOS public-launch hold remain unchanged. A **new internal TestFlight build is
required** because installed build 5 lacks the purchase-session identity protocol;
backend promotion does not update that app. Apple activation and automatic test closure
remain separately gated; this rollout authorizes neither.

**Exactly one next action:** authorize a new internal TestFlight build containing the
validated purchase-session protocol, keeping all purchasing gates OFF.

## Promotion validator and operator safety — implemented in isolation, 15 October local / 16 October 2026 UTC

Owner authorized implementation/testing of the scoped correction documented at
**e523c7b047558dbe6ee2b4f99d687fec29894d62**, with no deployment or production changes.
Started from that exact clean checkpoint. **Implemented and synthetically tested only.**
This supersedes the investigation's request to authorize validator implementation;
production promotion still requires a separate task. No application, migration, mobile,
purchase/provider configuration or scheduled-job source changed.

### Narrow historical acceptance; strict actual promotion

New isolated operator module **scripts/staging/promotion_safety.py** is called only by
the protected Apple-safety promotion path. It requires explicit
**--preservation-checkpoint**, exactly the reviewed checkpoint at
**/home/shelf/backups/shelf/20261016-004143/apple-session-rollout-checkpoint/**,
selected application source **1d3dfa5abc1bb35097ada6f2a69fb003c2e57b19** and prior
production source **c8c266221d972d409ffa947ca87397293c69c004**. There is no general
entitlement-table exemption or automatic acceptance of another rollout.

The original verification.json is pinned to SHA-256
**a9ff66431e3a031a1c7e960594a46e8b2358855b90b757328c044a5dc0ae425d**,
preventing silent rebaselining. Loading verifies private ownership/modes/canonical paths,
original source/provenance, SQL SHA-256 against the Shelf manifest and all original
configuration/private-webhook-file/cPanel fingerprints. SQL is parsed as data only;
no backup SQL is executed or restored. The only production-side artifact read during
this implementation was that existing checkpoint's fingerprint; no live DB query or
promotion command was run.

The future gate captures ownership/audit evidence and the complete existing protected
history fingerprints in one database read transaction, always rolled back. Dump
TIMESTAMP(0) values are parsed as UTC; live entitlement/audit TIMESTAMP values use
**UNIX_TIMESTAMP**, avoiding AST/UTC string mismatches. Precision/schema are verified,
and the historical SYSTEM/AST offset must still be the measured **−14400 seconds**;
other session semantics fail for explicit review. DATETIME fields are not shifted.
Original and current entitlement raw fingerprints must reproduce their corresponding
canonical row data, with correct numeric/null values and column ordering.

Only the reviewed entitlement **id 5**, with unchanged row count/id/reader/book/**active**
and all non-timestamp fields, can have metadata accepted. All three new timestamps must
be equal and nondecreasing and match exactly one newly recorded completed HTTP-200 run:
**seen=duplicate=1, pages=1, refunded=unmatched=conflict=0, error=null**. The run must
uniquely link to the one preserved refunded Play purchase/Google refund event for that
association and its separate valid Play sale. Existing refund-run audit rows must match
the backup exactly. Missing/ambiguous audit, partial/backward/null/unexplained timestamps,
wrong row/book/account/status/schema or any other protected-table fingerprint difference
stops. Purchase/refund/sales/accounting/consent/agreement/reader/token comparisons stay
exact. The module never updates entitlement timestamps or ownership.

After accepted historical evidence, the actual migration/promotion still compares a
fresh before/after snapshot of **every protected field, including all three entitlement
timestamps**. The old strict fingerprint logic is shared, not weakened. A protected
private report in the new promotion backup records accepted historical columns, safe
entitlement/run ids and UTC epochs while retaining the original checkpoint reference.
No secret values or reader identifiers are logged.

### Bounded refund-polling exclusion without changing schedules

The protected promotion acquires the existing **storage/framework/play-refunds.lock**
non-blockingly before any fresh backup or maintenance. Busy/missing/unsafe/symlink locks
refuse work; no job is killed to obtain a lock. It preserves the lock inode and checks
ownership/mode. Cron is **never edited, disabled, reinstalled or rescheduled**, so there
is no scheduling file/state to restore. Polling resumes through the unchanged cron once
the kernel lock is released. Existing production maintenance or open/deferred controls
are refused before backup; unrelated maintenance is not lifted.

Future ordering is: verify unchanged staged commit/evidence → refund lock/closed-state
preflight → fresh verified production backup → arm cleanup before maintenance down →
coherent historical preservation gate → fresh strict baseline → existing source/migration/
health workflow → exact history/configuration comparison → maintenance up → runtime/HTTPS
verification → release-state success, then lock release. Failed preservation cannot merge
source or run migrations. Successful runtime checks remain required before release-state
success is recorded. The module is an isolated operator correction; it does not change
the already-validated/staged application source.

The operator window is bounded to **10 minutes**. External commands run in isolated
process groups with an independent **/usr/bin/timeout**, at most 120 seconds plus a
5-second kill grace. Signals **INT/TERM/HUP** and deadline expiry abort, stop/reap the
active child group, attempt maintenance-up with a separate bounded 30-second cleanup
budget and release the lock in finally. Repeated signals cannot skip that cleanup;
prior signal handlers are restored. Cleanup failure reports the original failure type
and stops without success evidence. Lock ownership is inherited by independently timed
children: after an uncatchable operator **SIGKILL**, an orphan command retains exclusion
only until its own bounded timeout, then the kernel releases it. No permanent watchdog,
cron edit or scheduled cleanup job is created.

**Limit:** SIGKILL/power loss cannot execute Python maintenance cleanup. Scheduling-lock
release remains kernel/child-timeout controlled, but after such a hard interruption the
operator must inspect the retained backup and maintenance/source state before resuming;
do not claim maintenance-up completed or blindly retry. No synthetic test establishes
live server recovery from such an interruption.

### Focused evidence and next authorized operation

`SHELF_TOOL_TEST_SCOPE=apple-rollout scripts/run_tests.sh` passed **45 focused tests**:
existing 12 operator safeguards plus 33 new validator/window/integration cases. Tests
use fixtures, mock all promotion/DB/backup/migration/provider operations and use real
processes/locks/signals only inside the runner's disposable **/home/shelf/tmp** directory.
Coverage includes allowed/unchanged metadata; ownership/financial/row-set rejection;
null/backward/partial timestamps; timezone/precision/schema errors; dump extended/multiline
formats; checksum/original-checkpoint tampering; wrong/missing/ambiguous audit/link;
busy/unsafe locks; deadline/child timeout; INT/TERM/HUP; cleanup failure and second signal;
SIGKILL with and without an inherited-lock child; strict final timestamp drift; backup,
preservation, staging-evidence and migration failures; and existing-maintenance refusal.
Generated coherent-evidence PHP passed syntax checking without execution. Diff checks
passed. Existing unrelated Python cPanel-handler escape deprecation notices remain.
No PHP app/Flutter suite repeated: validated **64 PHP / 604 assertions, 16 Flutter +
1 staging identity** and existing exact-source staging evidence remain reusable.

No deployment, promotion, migration, live data/ownership write, scheduling control,
provider setting, transaction, build/upload or public release occurred. Last verified
runtime state remains production **c8c2662**, staging **1d3dfa5** (batch 12), Apple sandbox/
owned-book3/real checkout OFF. These are retained evidence, not fresh live checks in this
task. France exclusion/ANSSI cancellation, Apple auth/relay and stopped relay work,
Android/UI pause, pricing-sync, second-phone restore/small-improvement deferrals and
joint Android/iOS public-launch hold remain unchanged. New TestFlight build is still
required later; activation/automatic Apple-test closure remain separately gated.

After separate approval, run the trusted isolated operator (not the old deployed helper):
`python3 -B scripts/staging/workflow.py promote 1d3dfa5abc1bb35097ada6f2a69fb003c2e57b19 --preservation-checkpoint /home/shelf/backups/shelf/20261016-004143/apple-session-rollout-checkpoint`.
Recheck current state and finish any unreached required staging health checks first.
No redeployment or new test transaction is needed merely to repeat completed evidence.

**Exactly one next action:** authorize resuming the backed-up production promotion of
already-staged **1d3dfa5** using this verified isolated operator, all purchasing gates OFF.

## Entitlement timestamp investigation — read-only findings, 15 October local / 16 October 2026 UTC

Resumed clean **b1523eca563ca028e161c2436920380d71d9e6b3** under the owner's
read-only investigation authorization. **No deployment, migration, data repair, provider
change or validator/enforcement change.** Only these two documentation files are changed.
Production remains **c8c266221d972d409ffa947ca87397293c69c004**; staging remains
**1d3dfa5abc1bb35097ada6f2a69fb003c2e57b19**, session migration batch **12**, with
its existing passed rollout evidence. This entry resolves the preceding timestamp-cause
uncertainty; it does not authorize production promotion.

### Findings: real routine refresh, plus a timezone display difference

Verified the SHA-256 of **/home/shelf/backups/shelf/20261016-004143/database.sql**
against its manifest. Parsed the original SQL in memory without restoring/executing it.
There is exactly **one** entitlement row, safe internal entitlement **id 5**. Its id,
reader/book association and **active=true** match the backup. No reader identifier or
personal information was printed. Only **checked_at, created_at, updated_at** changed.

The backup's `mysqldump --single-transaction` writes TIMESTAMP values under
**TIME_ZONE='+00:00'**. Current Laravel query-builder reads use database session/global
**SYSTEM**, system **AST (UTC−04:00)**, while application timezone is **UTC**.
All three columns are **TIMESTAMP(0)**: second precision, no fractional rounding.
The backup prints **2026-10-16 04:30:27 UTC**, equivalent to the original application/
session representation **2026-10-16 00:30:27**. The stopped comparison subsequently saw
**00:45:01** in that same session representation (database UTC **04:45:01**).
After normalization the stored time really advanced **874 seconds (14m34s)**.
Thus timezone display alone does not explain the changed checkpoint fingerprint.
Do not change database/application timezones or rewrite historical timestamps in this task.

`production_history_snapshot()` uses query-builder rows, ordered by primary id, then
PHP `json_encode` and SHA-256; it does not apply Eloquent date casting. It compares the
same session representation before/after. Reconstructing the backup entitlement with
correct numeric/null types, original column order and explicit UTC→AST conversion
reproduces the **exact original preflight entitlement hash**. With one row, row ordering
cannot explain the mismatch. There is no demonstrated precision, serialization or
ordering defect causing this particular failure.

### Demonstrated writer path and operational evidence

The existing ***/15 * * * *** cron entry runs **scripts/run_play_refunds.sh**. Its
existing lock is **storage/framework/play-refunds.lock**. The runner calls
`shelf:check-play-refunds`; no scheduler, job or transaction was manually invoked here.

At **2026-10-16 00:45:01 UTC**, application log and persisted **play_refund_runs id 1337**
record **completed, seen=1, duplicate=1, refunded=0, unmatched=0, conflict=0**.
The run starts/finishes in the same logged second as the changed application timestamps.
Source chain on deployed c8c2662 is:

`VoidedPurchaseService::apply` → `PurchaseService::recordGoogleRefund` → `refreshAccess`.

`recordGoogleRefund()` recognizes the existing refund event/ledger entry, but still calls
`refreshAccess()` for that reader/book before returning the duplicate outcome. The
refresh uses `updateOrInsert` with **active, checked_at, created_at, updated_at** in its
update values. It therefore rewrites even `created_at` on an existing row. That field
currently is not a stable original-creation marker. Changing that behavior is outside
this documentation-only task; do not silently repair its historical values.

Read-only queries confirm exactly one already-refunded Play purchase and its existing
Google refund event for the same entitlement association, plus a separate valid Play
sale. Recalculation keeps the entitlement active. Purchase/refund/ledger rows do not
change when this duplicate is processed. The scheduled poll can therefore change only
these entitlement timestamps without changing the reader's actual ownership or income.

While this investigation remained read-only, the next natural scheduled run,
**id 1338 at 01:00:02 UTC**, again recorded completed/seen=1/duplicate=1/refunded=0/
unmatched=0/conflict=0; all three entitlement timestamps advanced together to **01:00:02**.
This repeated observation, exact cron timing, persisted run audits and deployed code
establish the routine duplicate-refund refresh as the supported cause. No per-statement
binary-log trace was retrieved; attribution is based on those correlated records/code.
No entitlement trigger exists; table engine is InnoDB.

The prior Library probe file was created at **00:45:34 UTC**, after the observed
**00:45:01** change, so that probe cannot explain this stopped checkpoint mismatch.
There is no evidence here of failed transaction rollback. Available production access-log
slice showed earlier successful Library requests at **00:30:20/00:30:27 UTC**, consistent
with the backup's earlier refresh time; that slice is not a complete SQL/writer audit.

### Ownership and financial integrity verified

Direct full-row comparison against the verified SQL backup, after only TIMESTAMP display
normalization and numeric/null type preservation, matches exactly:

| Table | Backup/current rows | Full rows equal |
| --- | --- | --- |
| purchases | 2 / 2 | Yes |
| purchase_events | 3 / 3 | Yes |
| sales_ledger | 3 / 3 | Yes |
| purchase_consents | 3 / 3 | Yes |
| author_share_agreements | 6 / 6 | Yes |
| accounting_entries | 0 / 0 | Yes |
| accounting_shares | 0 / 0 | Yes |

The saved preflight fingerprints for readers, account actions and access tokens also
still match. Entitlement association/status is unchanged; one valid Play sale remains,
one historical refunded purchase remains refunded, and no APP_STORE purchase exists.
There are **zero duplicate sale groups, zero ordinary real-income rows and zero ordinary
accounting entries**. No reset, refund reversal, regrant or ownership modification was
performed. Financial and ownership integrity remain verified within these comparisons.

### Required future promotion gate — proposal only, enforcement unchanged

Safe promotion is **supportable after a scoped validator/operator correction**, not
cleared under the still-strict historical timestamp comparison. Do not merely omit
`book_entitlements`, forgive arbitrary metadata drift or replace the failed checkpoint.
A separately approved correction should implement and focus-test this narrow rule:

1. Compare the complete entitlement row set by stable id and reader/book association;
   require exact row counts, ids, every non-timestamp field and **active**, including
   null semantics. Financial/consent/purchase/refund/agreement/accounting rows remain
   exact comparisons. Normalize TIMESTAMP displays explicitly at their actual precision;
   do not shift DATETIME fields or reinterpret application wall times as database UTC.
2. For this rollout, permit only metadata changes on existing entitlement **id 5**, and
   only when all three new timestamps are equal, nondecreasing and match a persisted
   completed duplicate-only refund run (seen=duplicate, refunded=unmatched=conflict=0,
   no error), uniquely attributable to its already-recorded same-reader/book refund.
   Retain the original checkpoint plus exact field differences and run evidence.
   Missing/ambiguous audit evidence, changed ownership or any financial difference stops
   promotion. No tolerance window for arbitrary timestamps or allowance for other rows.
3. After this historical preflight is accepted, obtain a **fresh verified backup** and
   a fresh coherent baseline. For the bounded promotion/verification window acquire the
   existing refund-runner lock non-blockingly, fail safely if busy, use maintenance to
   stop new reader requests and release the lock in finally. Do not edit cron or kill
   jobs. Capture the baseline after writers are quiescent. Preserve the existing strict
   before/after comparison of **all fields, including all entitlement timestamps** for
   the actual migration/promotion window. Release maintenance on every failure.
4. Reuse existing exact staging/unit evidence, finish any unreached required staging
   health checks, verify the selected source and configuration, then promote only under
   separate authorization. A validator correction must be tested in isolation and must
   not substitute a different unverified application/migration source.

No validator, lock behavior, schedule or deployment enforcement was changed in this
investigation. No unit/phone suite repeated and no Library reconciliation/probe executed.
Production is out of maintenance; **Apple sandbox OFF, owned-book3 test OFF, real checkout
OFF** remain verified. France exclusion/ANSSI cancellation, Apple auth/relay and stopped
relay work, Android/UI pause, pricing-sync, second-phone restore/small-improvement deferrals
and joint Android/iOS public-launch hold remain intact. A new TestFlight build remains
required later; no build/upload/activation/purchase/settings change occurred.

**Exactly one next action:** authorize the isolated, focused-tested validator/operator
correction above; keep production promotion and all purchase activation OFF meanwhile.

## Session safety rollout — staging passed; production STOPPED — 15 October local / 16 October 2026 UTC

Owner authorized the documented staging-first backed-up rollout of validated source
**1d3dfa5abc1bb35097ada6f2a69fb003c2e57b19**, all purchasing gates OFF.
**Staging deployed that exact source; production was NOT promoted.** Production remains
**c8c266221d972d409ffa947ca87397293c69c004**. This entry supersedes the preceding
implementation entry's next-rollout instruction. Do not blindly rerun deployment or
repeat completed checks; inspect the retained checkpoint and resolve the blocker first.

### Backups and protected state

- Fresh verified production preflight backup:
  **/home/shelf/backups/shelf/20261016-004143/**.
- Private preservation/evidence checkpoint:
  **/home/shelf/backups/shelf/20261016-004143/apple-session-rollout-checkpoint/**.
  AES256 configuration archive was decrypted in memory and compared exactly. It protects
  production/staging .env and existing private provider authorization/metadata/key files;
  cPanel handler, release state and aggregate history fingerprints were retained privately.
  No secrets or reader identifiers were printed or committed.
- Verified staging backup:
  **/home/shelf/backups/shelf-staging/20261016-004455/**.
- Both backups passed SQL completion, ZIP integrity and SHA-256 verification. No restore
  drill repeated, production promotion backup taken or production maintenance entered.

Only production's existing **public/.htaccess** cPanel handler was locally modified at
preflight; it is preserved byte-for-byte. Isolated source started clean at 1d3dfa5.
No source/migration/application change beyond the authorized validated source was deployed.

### Exact staging source, migration and checks

Staging **1d3dfa5** passed at **2026-10-16 00:45:13 UTC**. Migration
**2026_10_15_010000_create_apple_purchase_sessions** applied in staging batch **12**;
the new table is empty and no staging migrations are pending. Production has no session
table or new migration applied; no migrations are pending in its retained c8c2662 source.

The isolated operator helper needed a scoped preparation correction before deployment:
recognize already-committed Codemagic/build-number tooling and reuse only exact validated
**1d3dfa5** source/test bytes and its documented **64 PHP / 604 assertions, 16 Flutter
purchase tests + 1 staging-identity** evidence. These unit suites were not rerun.
The helper correction passed **12 mocked automation tests** via
`SHELF_TOOL_TEST_SCOPE=apple-rollout scripts/run_tests.sh`; it rejects source/evidence
mismatches and closed-runtime failures. Existing unrelated Python escape deprecation
notice remains. The helper correction ran from the isolated checkout and is recorded
with this rollout documentation; it was **not a different staged application revision**.

Staging checks passed: independent database/storage/session/cache; forced-disabled Play
sync; logged mail/null queue; synthetic account/isolation probes rolled back and temporary
file cleaned; absent production Apple credentials; applied empty session schema; runtime
Apple/owned-test/real-checkout/sign-in/relay/sync OFF; HTTPS private access/noindex/banner,
catalogue/cover, locked paid text, account/Library protection and denied webhook/private
files. No new provider transaction, session or ownership created. Check evidence:
**/home/shelf/staging-runtime/checks-1d3dfa5abc1bb35097ada6f2a69fb003c2e57b19.log**,
SHA-256 **ec4faca3ecd88b1c0a498fc8b5732a8341a045ba180bc8bc86088cdfa0082723**.
Additional standalone staging accounting/owner-share commands were **not reached**:
the production preservation precondition stopped their wrapper first. Accounting source
and passed regression evidence were reused, not a claim that those extra commands ran.

### Stop condition: production entitlement timestamp mismatch

An actual production Library controller/reconciliation probe confirmed existing verified
owner Book3 **PLAY_STORE** sale/access and a successful Library/lease response using
independent provider proof, with Apple controls closed, no duplicate sales and no Test
income in real totals. It was enclosed in an outer database transaction with rollback
in finally; no token, sale or sandbox purchase was created. **Do not claim complete
preservation from its initial success message:** the subsequent independent whole-history
checkpoint comparison failed before promotion.

Read-only comparison isolates the mismatch to the existing **book_entitlements** row:
row count is still **1**, reader/book/active ownership unchanged and active remains true.
Only **checked_at, created_at, updated_at** differ from the verified SQL backup. All other
protected table counts/fingerprints, including readers/tokens, purchases/events, consent,
sales/refunds, agreements and accounting, still match the checkpoint. The table engine is
InnoDB. The cause/attribution of timestamp drift is **not established**; do not assume
concurrent activity or blame the verification probe without evidence. Rollback code ran,
but full checkpoint preservation was not demonstrated. No repeat reconciliation probe,
manual SQL repair, timestamp restoration, baseline relaxation or production promotion
was attempted after the failure. Retained aggregate evidence includes
`promotion-stopped.json` and `entitlement-column-comparison.json` in the checkpoint.

Owner's explicit STOP rule was followed. Staging stays on the passed revision; production
stays on the previous source. There is no failed production migration or maintenance to
recover from. Do not discard staging or overwrite any existing records to resume.

### Final read-only production state

Production is **healthy and out of maintenance**; HTTPS app-config and iOS purchase-config
both return **200**. iOS enabled/sandbox/production checkout fields are false and SDK key
is null. Runtime **Apple sandbox OFF, owned-book3 test OFF, real checkout OFF, Apple
sign-in/relay OFF and Play sync OFF** on both environments. Production/staging .env,
private webhook authorization/metadata/key files and cPanel handler match the checkpoint.
Bounded RevenueCat GETs returned **200** before/after: the complete saved webhook metadata
matches, including Apple **whintgr769cb03ff2** (app-scoped sandbox, distinct Apple URL and
four events) and Google **whintgr75a4892970** (Both). No provider settings changed.

**New TestFlight build still required** for the session protocol before any future Apple
purchase test. None started or uploaded; installed build 5 unchanged. Durable automatic
closure remains unimplemented; activation/phone acceptance remain separately gated.
France exclusion/ANSSI cancellation, Apple authentication/relay and stopped relay work,
Android/UI pause, pricing-sync, second-phone restore and small-improvement deferrals,
and joint Android/iOS public-launch hold remain unchanged. No activation, transaction,
ownership repair or public release performed.

**Exactly one next action:** investigate the unexpected entitlement timestamp discrepancy
under a separately authorized focused recovery task before resuming production promotion.

## Apple session binding and store-aware gate closure — 15 October local / 16 October 2026 UTC

Owner authorized only the two corrections assessed at **212cd1cc5f6c83d5b827326c200a192af268ac0d**,
focused validation, deployment/rollback preparation, records and push to **ios/foundation**.
Started from that exact clean checkpoint. **Implemented in isolation only; NOT deployed,
NOT activated.** This entry supersedes the assessment's request to authorize implementation;
it does not clear activation or claim live purchase acceptance.

### Correction 1 — authenticated purchase-session binding

Apple consent now requires protocol **purchase_session_version=1**. Inside the existing
reader/book transaction and locks, verified owner/Book3/store/mode/publication/price and
normal duplicate or explicit owned-test consent checks remain mandatory. The server
creates a 256-bit random **shelf_apple_** identity bound to that reader, Book3 and the
specific consent, with a **15-minute** lifetime. A fresh consent invalidates earlier
unconsumed identities. A consumed session or any existing Apple purchase prevents issuing
a second session. The identity is a private capability: do not log, publish or copy it;
authenticated consent responses use the existing private/no-store middleware.

The client validates protocol, reader, book, mode, store and expiry before identifying
the Apple SDK with that session and buying. Numeric Play identity and all existing Play
history remain unchanged. Numeric identification is still used to initialize/list Apple
products, but native Apple buy refuses numeric IDs. Direct identified-to-identified SDK
login is supported without merging customers by [RevenueCat identity guidance](https://www.revenuecat.com/docs/customers/identifying-customers).
No SDK/dependency or provider setting change; no invented StoreKit account-token API.

Confirmation and webhook independently require matching RevenueCat **current AND original
session identity**, exact transaction and millisecond purchase time, app/product mapping,
APP_STORE/SANDBOX/purchased/quantity-one and permanent active entitlement. Session ownership,
consent and expiry are checked before recording and again under lock. The first verified
transaction atomically consumes the session, retaining its exact transaction/time for
historical proof/refund handling. Wrong reader/book/session, revoked/replaced/expired
unconsumed sessions, numeric impersonation and second transactions fail closed. Exact
historical duplicate events/confirmations remain idempotent; they cannot add a sale or
renew a lease without the existing independent reconciliation. Existing pre-session
numeric Apple history can only be independently reconciled/corrected, never used to
create a new unbound sale. No such live history was claimed or created here.

Server confirmation discovers only the authenticated reader's sessions. The consumed
identity is returned privately to that same reader for native restore while configured;
it is never in public config or persisted in the download manifest. Account changes
clear it. Strict original/current identity validation remains authoritative if the
provider aliases or transfers anything. Expired/unbound late purchases are not silently
accepted: preserve their provider evidence for explicit review, never repeat a pending buy.

New reversible migration **2026_10_15_010000_create_apple_purchase_sessions** creates only
an empty table. It records consent/reader/book, private identity, validity/revocation and
unique consumed transaction/time; no old reader, consent, purchase or money row is rewritten.
No cascade deletes evidence. Down migration refuses any retained session evidence and
requires an approved preservation plan; empty-table reversal is tested. **No live migration run.**

### Correction 2 — Play refresh survives Apple closure

Disabled Apple verification returns to the normal independent Play reconciliation rather
than throwing 503 solely because Apple history exists. Library uses the same book-access
filter as direct paid-text/content/cover/audio/artwork requests. With Apple unavailable,
Apple-only historical grants are excluded from Library/download renewal and direct access;
valid Play ownership or an existing reviewer grant still works. Shared entitlement rows
and immutable Apple history are not deleted or rewritten just to close gates. Failed Play
proof cannot fall back to disabled Apple history. Reconciliation with an enabled but
unavailable provider continues to fail closed; no fresh lease is fabricated.

Apple sandbox and owned-book test flags retain existing default **false**. Real checkout
remains OFF. Apple sales/corrections remain forced SANDBOX/Test, excluded from real income
and confirmed author earnings; duplicate/replayed transactions cannot multiply sales or
royalties. These fixes do not erase provider sandbox ownership or historical Test records.

### Focused validation (synthetic only)

- `SHELF_PHP_TEST_FILTER='AppleSandboxPurchasesTest|BookPurchasesTest|AccountingTest' scripts/run_tests.sh`:
  **64 PHP tests / 604 assertions passed**, temporary/in-memory data only.
- `SHELF_PHP_TEST_FILTER='AppleSandboxPurchasesTest|BookPurchasesTest|AccountingTest' SHELF_MOBILE_TEST_SCOPE=ios-purchases scripts/run_tests.sh --mobile`:
  **16 Flutter purchase tests + 1 staging-identity test passed**; PHP at that point
  **63 / 594** (the subsequently added failed-Play-proof case accounts for the final 64 / 604).
  Flutter analysis: no errors/warnings, **21 style infos**. Completed Flutter checks reused
  after the final backend-only case; no broad suite or Android artifact build.
- `SHELF_TOOL_TEST_SCOPE=apple-rollout scripts/run_tests.sh`: **12 mocked automation tests passed**.
  Existing Python cPanel-handler byte-literal escape deprecation notice remains unrelated;
  no runtime handler changed. Tests exercise scoped staging selection, source-byte mismatch,
  test/guard failure refusal and passing evidence only after focused tests/disabled guards.
- Covers nonowner/wrong book, missing/incorrect consent version, numeric/wrong-account/
  cross-session proof, expiry/replacement/replay, exact duplicate idempotency, independent
  proof rejection, Test-only ledger/refund/reversal, closed-gate overlapping Play+Apple and
  refunded Apple history, Play failure, Apple-only Library and direct-content denial,
  account change while consent waits and reversible/evidence-preserving schema.

### Prepared rollout and rollback — instructions only, not executed

1. Obtain separate authorization for **disabled staging-first backend rollout**. Record
   the selected verified commit and previous deployed release from the existing release
   state, preserve unrelated runtime changes, inspect current holds and verify both Apple
   flags, real checkout, sign-in/relay and sync remain false. Use `docs/STAGING.md`.
2. From this trusted isolated checkout, under that later authorization run
   `python3 -B scripts/staging/workflow.py deploy VERIFIED_COMMIT`. Existing workflow backs
   up staging before migration. The prepared **apple-session-safety-disabled** path compares
   deployed source bytes to that commit, runs only the focused PHP purchase/accounting tests,
   validates the applied empty session schema, disabled runtime/HTTPS guards and staging
   isolation. No production Apple credentials are copied to staging. Reuse this commit's
   passed Flutter evidence; do not activate sandbox on staging or manufacture a transaction.
3. Only after passing staging evidence and explicit production authorization, run
   `python3 -B scripts/staging/workflow.py promote VERIFIED_COMMIT`. It requires the same
   verified commit/log, makes a fresh verified production backup under
   `/home/shelf/backups/shelf/`, preserves reader/payment/agreement history, applies the
   committed additive migration and checks disabled runtime/HTTPS guards. Stop on first
   live failure; maintenance is lifted by the workflow's finally block. Do not run the
   old provider-configuration helper or recreate the existing webhook.
4. Before any Apple sessions/history, rollback may return to the **recorded prior deployed
   revision**, keeping all gates OFF and preserving runtime configuration/handler. The
   additive empty table can safely remain. An explicitly approved migration rollback must
   be limited to this empty table after backup; never blanket-roll back a mixed batch.
   After any sessions or Apple history, preserve the schema and these access/session guards;
   do not restore old code that reintroduces stale Apple-only access, drop evidence or
   restore SQL over later activity. Use a separately reviewed forward correction instead.

**A new TestFlight build IS required before any future Apple test.** Installed 1.0.0 (5)
lacks session protocol/SDK identity handling and is intentionally rejected at consent by
the corrected backend. No build number reserved, build/upload or phone test performed.
Actual RevenueCat/StoreKit identity/proof shapes, product retrieval, purchase/restore/
refund/reversal and delivery acceptance still require separately authorized phone work.
The durable 30-minute automatic-closure mechanism remains **unimplemented** and must be
prepared and verified before a separately authorized activation window. These fixes do
not authorize or perform that activation or closure scheduling.

Owner-verified RevenueCat settings stay recorded unchanged: notification tracking OFF;
Keep with original App User ID; different sandbox behavior OFF; Anybody entitlement access.
France exclusion/ANSSI cancellation, Apple authentication/relay and stopped relay task,
Android/UI pause, pricing-sync, second-phone restore and small-improvement deferrals,
and the joint Android/iOS public-launch hold are preserved. No deployment, live migration,
provider edit, purchase, checkout activation, build/upload or public release in this task.

**Exactly one next action:** authorize the disabled staging-first backend rollout of
this verified correction commit, with purchasing kept OFF.

## Confirmed RevenueCat settings and complete sandbox path review — 14 October local / 15 October 2026 UTC

Resumed clean **cb6cb498efd4266802ecf8ad40246ce38e965d70**. Owner supplies
screenshot-verified existing dashboard observations, **no settings changed**:

- Shelf Apple app **Track new purchases from server-to-server notifications OFF**.
- Project **Transferring purchases seen on multiple App User IDs: Keep with
  original App User ID**; **Use a different behavior for sandbox OFF**. Sandbox
  therefore inherits Keep with original App User ID.
- Separate **Allow testing entitlements and in-app currency for: Anybody**.

The two previously unverified dashboard values are now **OWNER-VERIFIED**, not
API-verified; no repeat screenshot/access request needed. Build 5 basic iPhone
installation/launch acceptance remains recorded; purchase acceptance pending.

### Actual meaning of Anybody

Current official [RevenueCat Sandbox Testing Access](https://www.revenuecat.com/docs/projects/sandbox-access)
says Anybody allows non-production purchases to grant configured RevenueCat
entitlements/currency to every App User ID. It covers platform sandboxes as well
as Test Store. This is **not dashboard staff permission**, and does not authorize
Shelf API access. Changing to an allowlist still records sandbox purchases; it
can remove existing nonqualifying sandbox entitlements and leaves already-granted
currency. Consequently do not change this project-wide setting casually or treat
an owner-only allowlist as authentication. No currency feature is implemented by
this Shelf test, and its platform app key is not a RevenueCat Test Store key.

RevenueCat may grant a sandbox entitlement **independently of Shelf consent**.
That is a real provider-level grant/identity-pollution risk. Shelf's normal client
does not use CustomerInfo to unlock books, so this is **not evidence of
unauthorized Shelf paid-text access**. Restriction is in authenticated server
consent, store proof and book access, not in the public SDK key or SDK UI.

### Every relevant path evaluated

Reused cb6cb49 runtime/private-owner/Play-sale/provider/disabled-config findings,
56 PHP tests/511 assertions, 15 Flutter tests and same-source rollout evidence.
Narrow read-only comparison at **2026-10-15 00:38:02 UTC** confirms deployed
purchase/proof/exception/consent/webhook/access/ledger/accounting/API route bytes
match **c8c266221d972d409ffa947ca87397293c69c004**; both Apple gates and real
checkout remain false, no Apple purchase exists. No broad audit/test or synthetic
transaction rerun. Findings below are source/path analysis, not a live exploit.

| Path | Actual safeguard and limitation |
| --- | --- |
| Normal SDK buy | Authenticated consent, explicit owned-test server echo, both gates and fresh owner/book3 eligibility precede SDK buy; CustomerInfo alone never unlocks. |
| Direct SDK calls | Public app key and client-selected App User ID can bypass Shelf's UI/consent to obtain a **RevenueCat** sandbox entitlement under Anybody. Shelf does not control StoreKit or RevenueCat recording through these calls. A caller using their own nonowner ID is rejected by Shelf owner/proof checks. Numeric owner-ID impersonation has the attribution limitation below. |
| Restore | SDK restore may restore RevenueCat state; it grants no Shelf access by itself. Authenticated library/restore uses server reconciliation, exact current/original reader and app/product/SANDBOX proof; Keep-original discourages transfer but does not authenticate SDK IDs. Apple recovery cannot transfer history to another Shelf reader. |
| Webhook | Private distinct Apple authorization, configured gate, app/store/environment/product, numeric identity, exact transaction/time and independent V2 proof; locked owner/fresh test consent rechecked for a new overlapping purchase. Nonowner events rejected; duplicate events/transactions idempotent. Provider webhook authentication proves delivery origin, not that the SDK user ID was chosen by the authenticated owner. |
| Server confirm/Library | Reader-authenticated routes, independent permanent entitlement and transaction proof; no reliance on client receipt or cached SDK entitlement. Ordinary duplicates remain blocked; overlapping test requires existing Play access and no prior Apple purchase. Gate-closure defect below prevents successful Library refresh after Apple history. |
| Account switching/offline | Client clears configuration/test permission/confirmation, serializes SDK sign-out, uses reader/generation checks and per-reader encrypted downloads. Late responses do not cross Shelf accounts. Server token determines paid-text identity; provider aliases/entitlements do not override it. |
| Paid text/audio/media | Authenticated reader middleware and BookAccessService check active server entitlement/reviewer grant. No direct RevenueCat entitlement grants a new server entitlement; existing server entitlement is not itself erased by closing Apple gates. Direct content paths do not run the Library reconciliation guard. |
| Play/accounting | Existing Play sale/access preserved and union-of-valid-store access remains during open-window reconciliation. Apple records forced SANDBOX/Test, excluded by real-income scope; accounting rejects Test owed/payment/real reversal entries. Unique sale/event keys and second-overlap rejection prevent duplicated real royalties. |

### Two remaining safety problems — activation NOT CLEARED

**1. SDK identity is not authenticated purchase intent.**
[RevenueCat's identity guidance](https://www.revenuecat.com/docs/customers/identifying-customers)
allows client-supplied App User IDs; Shelf uses the numeric reader ID and exposes
the SDK key in public purchase config when enabled. Matching current/original
RevenueCat ID and a genuine transaction proves provider attribution, **not**
that a Shelf-authenticated owner initiated that transaction. A modified client
could choose the owner's numeric ID. If a genuine sandbox purchase is attributed
to that ID inside the owner's fresh consent window, current server checks do
not bind the specific transaction to a secret owner test session. This is an
inferred attribution/race risk, not a reproduced exploit or demonstrated paid-text
disclosure: the caller still lacks the owner's Shelf token. It can contaminate
owner provider/history or consume the single test slot. Anybody does not fix it;
allowlisting the same numeric owner ID alone does not fix impersonation either.

**Smallest scoped security correction proposal:** bind the single Apple test to
an unpredictable server-issued owner/Book3 purchase-session identity or supported
StoreKit account token, delivered only after authenticated consent; verify that
binding in confirmation **and webhook** before recording. Do not trust a
client-selected numeric ID as purchase-session authentication. Preserve existing
numeric Play identity/history, strict Apple original/current identity and permanent
proof, and prohibit unbound new Apple sales. Exact SDK/provider support and any
reversible schema/client change need a separately approved implementation task;
no invented API or migration is proposed as already available. Do not silently
switch existing identities or rely on secret public API keys.

**2. Closing Apple gates blocks valid Play Library refresh.** In deployed
AppleSandboxPurchases::reconcile, any recorded APP_STORE purchase with the gate
OFF causes **503**, before PurchaseService performs Play reconciliation and before
Library returns books/lease. This also affects refunded recorded Apple history.
The existing test explicitly expects 503 for recorded Apple history with the gate
OFF. For this dual-store owner, after one accepted Apple transaction, the planned
closure would leave the Play sale/entitlement intact but fail Library/confirm/
restore and download refresh. Earlier cb6cb49's statement that valid Play access
remains was accurate about records/direct entitlement, **insufficient about normal
Library operation**. No such live failure exists now because no Apple purchase
has been made; no data changed to demonstrate it.

**Smallest scoped closure correction proposal:** make disabled-Apple handling
store-aware: independently verify/return valid Play-backed books and leases while
refusing new Apple processing, stale Apple-only grants or Apple-only lease renewal.
Apply the same distinction to direct content authorization; do not expose stale
Apple-only text merely by skipping reconciliation. Preserve all immutable Apple
history and valid Play ownership. Focused regressions must cover overlapping
Play+Apple, Apple-only, refunded Apple history, closure and direct paid-content
requests. No blanket entitlement removal, history deletion, SQL restoration or
provider refund is a valid shortcut.

**Activation is not safe to approve yet.** Confirmed dashboard settings resolve
that earlier blocker; they do not resolve these two code-path issues. No genuine
revenue or duplicate real royalties found, and no ordinary nonowner Shelf unlock
found, but provider entitlement retention and attribution and correct post-test
closure are not fully controlled. A sandbox non-consumable/provider entitlement
and immutable Test purchase can persist after disabling Shelf gates. Closing
checkout is **not refund/revocation** or removal of provider ownership.

### Future bounded activation/cleanup — blocked plan only

Retain the prior exact two-flag backup/encrypted-checkpoint, atomic .env and
runtime verification procedure, but **do not execute it** until the scoped fixes
are approved, tested, staged/promoted and compatible with the installed client
(or separately accepted replacement where needed). Then a separately authorized
window should have a recorded hard deadline **30 minutes after activation**,
with Codex arranging verified automatic closure of both flags before opening them.
That closure mechanism is **not implemented or installed in this task**; no cron,
queue or scheduled task created. It must survive an interrupted interactive
session, refuse concurrent/unexpected config changes and report closure failure.
Close earlier on the first mismatch or completed single test; no automatic reopen.

Only the verified owner and Book3, one cancellation then at most one genuine
TestFlight sandbox transaction under explicit approval; no repeats on pending
confirmation. Verify server-bound transaction, Test-only records, real totals and
Play history. At deadline/end set both gates false, clear config, verify disabled
config/proof/webhook guard **and successful independent Play Library/download
refresh and denial of stale Apple-only direct content**. Remove only window-created
closure scheduling after verified closure; retain evidence/checkpoint and all
purchase/refund/consent history. Leave RevenueCat and Play configuration untouched.
Refund/reversal and removal of provider sandbox ownership require separate explicit
scope; do not promise that timer cleanup erases Apple/RevenueCat non-consumables.

Only these two records changed; no activation, purchase/refund, provider edit,
deployment/migration, build/upload, tester invitation, Play/accounting modification
or public release. France exclusion/ANSSI cancellation, Apple sign-in/relay,
Android/UI pause, pricing-sync, second-phone restore, small-improvement deferrals
and joint Android/iOS launch hold preserved.

**Exactly one next action:** authorize a focused isolated correction of Apple
purchase-session binding and store-aware gate closure before any activation.

## Build 5 installed; final owner Apple purchase preflight — 14 October local / 15 October 2026 UTC

Resumed clean **bdd550af3fc256e756b8ba5a0afe9828b02425ce**. Owner confirms
**TestFlight 1.0.0 (5) installed on their iPhone and opens correctly**. Record
basic installation/launch acceptance only; no product retrieval, purchase,
restore/refund/reversal or notification acceptance follows from this evidence.

### Current read-only evidence

At **2026-10-15 00:23:29 UTC**, production HEAD and staging/production release
state match **c8c266221d972d409ffa947ca87397293c69c004**. Deployed exception, proof,
Apple event handler, consent controller, purchase config and accounting service
bytes match the validated source. Reused **56 PHP tests / 511 assertions, 15
Flutter tests**, exact staging/promotion and backup/history-preservation evidence.
No broad tests, full provider audit, build or deployment repeated.

Private production queries verified one owner admin and one exact-email reader;
reader verified, buying allowed, owner predicate true. Book **3 / shelf_book_3**
is Published with positive price. Existing unrefunded/unrevoked **PLAY_STORE sale**
and active book access remain intact; no APP_STORE purchase exists. No reader
ID/email, purchase history or entitlement changed or printed.

Runtime **Apple sandbox OFF, owned-book3 test OFF, real checkout OFF, Apple
sign-in/relay OFF, Play sync OFF**. Exception is unavailable while OFF. A separate
CLI process set both Apple flags **in memory only** and confirmed configured
provider prerequisites and exact owner eligibility; nonowner, blocked owner and
another book denied. No .env/cache writes, saved models, consent calls or provider
proof calls occurred in that process. Staging separately confirms active staging,
both Apple gates OFF, proof disabled and real checkout OFF. Public production
**GET /api/purchases/config?platform=ios** returned **200**, all checkout fields
false and SDK key null at **00:24:55 UTC**. Disabled webhook guard evidence from
rollout reused; no test event or POST sent in this preflight.

Existing credentials: RevenueCat bounded GETs **200** confirm Apple app
**app8ef018b95b / services.shelf.app**, non-consumable product
**prod0d084e901f / shelf_book_3**, and **whintgr769cb03ff2**, app-scoped sandbox,
URL **https://shelf.services/api/purchases/webhook?source=apple**, exact
non_renewing_purchase/cancellation/expiration/refund_reversed events. Local
protected webhook authorization matches runtime. Apple product GET **200** at
**00:25:13 UTC** confirms **6822080809 / shelf_book_3 / NON_CONSUMABLE /
MISSING_METADATA**. Saved metadata state is not actual StoreKit retrieval;
existing title discrepancy/genuine review image remain separate pending work.
France exclusion evidence and notification V2 configuration reused unchanged.

Ordinary owned-book consent remains **409** for Apple without the explicit test
exception and for Play. Client normal purchase still refuses owned books. The
exception requires both gates, production checkout OFF, non-staging, exact
verified/unblocked owner, active Play sale/access, book3 and no prior Apple book3
purchase. Consent rechecks locked reader/book, uses explicit test wording and
requires the server echo before SDK buy. Existing fresh-consent/time/transaction,
original numeric reader and independent APP_STORE/SANDBOX permanent entitlement
proof remain mandatory; SDK success or Play access alone cannot confirm Apple.
Apple sale/event keys prevent duplicate journal entries and a second overlapping
Apple purchase. Apple records use **SANDBOX / Test**; ordinary ledger scope excludes
them and accounting rejects real earnings/owed/payment entries for Test history.
Thus this path cannot create real revenue or duplicate real author royalties.
Play records and valid union-of-store access remain untouched, including on
Apple cancellation/refund. These safeguards reuse the passed implementation tests.

### Remaining blocker: two saved RevenueCat values are not API-exposed

Used existing V2 credentials before requesting manual inspection. Actual Apple
app response exposes only app metadata and app_store fields **bundle_id,
app_store_connect_api_key_configured, app_store_connect_vendor_number,
custom_url_scheme, subscription_key_configured**. Shelf project list GET **200**
exposes **object, id, name, created_at, icon_url, icon_url_large**. Neither contains
Apple **Track new purchases from server-to-server notifications**, restore
behavior, or sandbox-override fields. Current documented
[app](https://www.revenuecat.com/docs/api-v2/app) and
[project](https://www.revenuecat.com/docs/api-v2/project) resources do not provide
these settings. Unsupported project-detail GETs with/without trailing slash
returned **404**; documented project-list GET succeeded. No settings-write probe,
private dashboard endpoint, token search or provider change was attempted.

**Both actual values remain unverified:** Apple new-purchase notification tracking
must be **OFF**; effective sandbox restore behavior must be **Keep with original
App User ID**. Do not infer defaults from missing fields or subscription-key
configuration. RevenueCat's
[notification guidance](https://www.revenuecat.com/docs/platform-resources/server-notifications/apple-server-notifications)
and [restore guidance](https://www.revenuecat.com/docs/projects/restore-behavior)
explain the numeric-ID/anonymous-ingestion risk and optional sandbox override.
Technical owner isolation is ready, but **not cleared to activate yet** while
these required values remain unknown. No activation or transaction authorized.

Minimum dashboard inspection, **read-only**, existing Shelf project:

1. **Apps & providers → existing Shelf iOS (App Store) → Apple Server to Server
   notification settings**: read **Track new purchases from server-to-server
   notifications**. Required OFF; do not toggle or Save.
2. **Project settings → General → Restore behavior**: read the main selection and
   **Use a different restore behavior for sandbox**. If override is enabled, read
   the sandbox selection; otherwise main selection applies. Required effective
   sandbox value **Keep with original App User ID**. Do not change any setting.

Report those saved values in text; no screenshot or credential request. An
unexpected value needs a separately approved correction, not silent adjustment.

### Safe activation procedure — plan only, NOT EXECUTED

After that blocker is resolved and explicit one-owner/one-book sandbox-window
approval is given, Codex performs the existing fresh verified production backup
and encrypted .env checkpoint under **/home/shelf/backups/shelf/**, records private
pre-test reader/Play entitlement/purchase/event/ledger/accounting fingerprints,
and rechecks deployed source, owner eligibility, provider mapping and OFF gates.
Stop before activation if backup/checkpoint or any prerequisite fails.

As shelf, atomically change **only** these existing production .env flags:
**SHELF_APPLE_SANDBOX_ENABLED=false → true** and
**SHELF_APPLE_OWNED_BOOK3_TEST_ENABLED=false → true**. Require regular file/no
symlink, shelf ownership/0600, exactly one occurrence per field, expected before
values and unchanged content since reading; preserve every other setting. Run
**php artisan config:clear** in **/home/shelf/apps/shelf**. No config helper prepare,
migration, deployment, new key or provider setting change is needed.

Verify actual runtime proof configured and both flags true; exact owner eligible,
nonowner/blocked/other-book denied, real checkout/sign-in/relay/sync false and
staging OFF. Private iOS config GET must advertise sandbox true, production false,
allowlist exactly **[shelf_book_3]**, correct app/store/SDK mapping. Public config
readiness is global, not reader-specific; server consent/proof enforce the
restriction. Close both gates immediately on mismatch; no purchase follows.

### Exact iPhone procedure — later approved window only

Use the installed **TestFlight 1.0.0 (5)** and existing verified **Shelf reader
email/password**, not admin/Apple/relay/anonymous/new account. Keep existing
Apple/TestFlight account, app and downloads. No new sandbox tester or sign-out of
Media & Purchases is needed for this basic TestFlight case. TestFlight uses
sandbox with production Apple Accounts
([RevenueCat](https://www.revenuecat.com/docs/test-and-launch/sandbox/apple-app-store));
no charge is incurred by a genuine sandbox transaction.

After Codex verifies activation, open Shelf, sign in if needed and refresh
My Library. Open **book3**, read its sample and use the separate
**Test Apple purchase — no charge** action. Accept the explicit agreement on the
Apple sandbox test screen. Inspect Apple's purchase sheet for the intended book
and test/no-charge context; authenticate only through the genuine Apple sheet
if requested. If identity/product/environment is unclear, or product unavailable,
stop without confirming; do not change storefronts/providers or repeat buying.
Within explicit test authorization, cancel once to verify existing Play access
stays intact, then complete **at most one** sandbox purchase. This planned
cancellation is not a second transaction. Wait for the app's **Apple sandbox
purchase confirmed** message; do not treat existing Read book access as proof.
If confirmation fails/pends, report it and do not buy again.

Codex then independently checks exact Apple transaction/time, original reader,
RevenueCat SANDBOX proof and permanent entitlement, Shelf Test-only immutable
purchase/event/journal, unchanged Play history/access and real accounting totals.
Record actual webhook delivery separately from REST reconciliation; one does not
prove the other. Restore/refund/reversal/full phone acceptance remain separate;
no second-phone restore or Apple sign-in/relay test is requested.

### Disable and verify afterward — plan only, NOT EXECUTED

Codex atomically returns **both** production flags to false with the same
permissions/concurrency checks, runs **php artisan config:clear**, and verifies
runtime proof/exception OFF; iOS config enabled/sandbox/production false and SDK
key null; staging OFF and real/sign-in/relay/sync OFF. Under that later window's
verification scope, incomplete unauthenticated Apple webhook guard must stop
**503 production / 403 staging** before processing. Keep provider keys/webhooks,
all Play/Apple consent/purchase/refund/journal history, schema and downloads.
Never restore SQL or run migration down merely to close the window. Gate closure
is not a refund. Disabled Apple proof cannot renew an Apple-only offline lease;
existing valid Play access remains. Any unconfirmed Apple reconciliation needs a
later authorized window; do not erase transaction history or initiate another buy.

Only requested records changed. All gates stayed OFF; no transactions, provider
settings, build/upload/tester changes, ownership deletion or public release.
France exclusion, pricing-sync, second-phone restore, Apple authentication/relay,
Android/UI and small-improvement deferrals and joint-launch hold preserved.

**Exactly one next action:** inspect the two RevenueCat settings in the existing
dashboard using the read-only paths above and report their saved values.

## Build 5 assigned to existing Shelf Owner — 14 October local / 15 October 2026 UTC

Resumed clean **b662fbd33107471963b59fd5b2b7c7e4e5ca34c9** under explicit owner
authorization to add **only 1.0.0 (5)** to the existing internal **Shelf Owner**
group. Existing protected ASC key reused; no credentials or tester details printed.

Preflight **2026-10-15 00:12:07 UTC**, authenticated GETs **200**: exact build
**76e0e894-e322-49bc-9874-fbfc770b8a80**, app **6821471140 / services.shelf.app**,
preReleaseVersion **IOS / 1.0.0**, build version **5**, **VALID / INTERNAL_ONLY**,
expired false, usesNonExemptEncryption false and **READY_FOR_BETA_TESTING**.
Compliance decision/readback from the previous task reused without change/audit.

Existing group **1511a217-4f89-4dc7-8b8d-4679ce8a6226 / Shelf Owner** is internal,
linked to that exact app, with all settings equal to the accepted Build 4 private
snapshot. Complete tester GET contains **one** tester; both ID and email exactly
match the recorded owner in
**/home/shelf/tmp/shelf-build4-group-before-20261013.json**. Identity withheld.
Complete group-build list initially contained **4, 3, 2**; Build 5 was absent.
Private pre-write snapshot saved **0600**, outside Git, at
**/home/shelf/tmp/shelf-build5-group-before-20261015.json**.

Only provider mutation:
**POST /v1/betaGroups/1511a217-4f89-4dc7-8b8d-4679ce8a6226/relationships/builds**,
body data containing one resource, type **builds**, ID
**76e0e894-e322-49bc-9874-fbfc770b8a80**; HTTP **204**. This is additive; no
replacement/removal, tester invitation, membership edit or group setting sent.
Already-present handling skips this POST; no repeated write or retry occurred.

**Readback completed 2026-10-15 00:12:14 UTC:**

- Group-side builds and builds relationship GETs both independently confirm
  **5, 4, 3, 2**, exactly the previous build IDs plus Build 5.
- **internalBuildState=IN_BETA_TESTING**, external **NOT_APPLICABLE**;
  existing autoNotifyEnabled true unchanged.
- Build 5 remains **VALID / INTERNAL_ONLY**, all build attributes exactly unchanged,
  including the cleared compliance field.
- Group settings exactly unchanged; the same sole tester ID/email retained.

Private sanitized readback retained **0600** at
**/home/shelf/tmp/shelf-build5-group-after-20261015.json**. All provider readbacks
returned **200**. Assignment and internal testing availability are verified;
installation/launch and Apple purchase/notification acceptance remain pending.
Only the two requested documentation files changed, with scoped diff/whitespace
checks; no source/test change, broad suite or completed-audit rerun.

No build/upload, compliance change, tester invitation, purchase activation,
ownership change, metadata/territory edit, deployment, email or public release.
Real checkout, Apple sandbox/owned-book3 test and Apple sign-in/relay remain OFF.
France exclusion/ANSSI cancellation, pricing-sync and second-phone-restore
deferrals, deferred Apple authentication/relay checks, Android/UI pause,
small-improvement deferrals and joint Android/iOS public-launch hold preserved.
Backend remains c8c2662. The existing auto-notification setting was preserved,
not deliberately changed or used to claim delivery.

**Exactly one next action:** owner opens **TestFlight → Shelf → Update/Install**
for **1.0.0 (5)**, using **Previous Builds → 1.0.0 → build 5** if needed.
Keep the existing installation/account/data; no uninstall or data clearing.
Confirm Build 5 installs and launches; this does not authorize a purchase or
Apple sign-in test. No dashboard assignment or new build is needed.

## Build 5 export compliance cleared — 14 October local / 15 October 2026 UTC

Resumed clean **ada8bc557855f9af37b681b30015ac26278ce6c1** under explicit owner
authorization for **Build 5 only**, reusing the accepted Build 4 non-France
standard-encryption decision. Build 5 source remains owner-reported Codemagic
**8283badf674f94c8cc7e18a5371e35cdc67d5d1c**; no new artifact or binary audit.

### Focused encryption comparison

Compared documented Build 4 source **2702fbb** with Build 5 **8283bad**. All
mobile production changes are confined to owner purchase-test permission,
confirmation and UI in library_controller.dart, purchase_screen.dart and
collection_detail_screen.dart; reviewed diffs introduce no encryption primitive,
key handling or general-purpose encryption feature. Test changes are separate.
Dependency declarations/lockfile, native iOS tree, account/Keychain implementation,
audio cache, repository and **purchases/download_store.dart** are byte-identical.
Thus the documented **cryptography 2.9.0 / AES-256-GCM**, per-reader generated
key, Keychain storage, HTTPS/SDK transport and SHA-256 cache naming assessment
remains applicable. Full prior audit and source test suites were not repeated.
This comparison does not claim independent inspection of Codemagic's IPA.

Rechecked [Apple's current documentation table](https://developer.apple.com/help/app-store-connect/reference/app-information/export-compliance-documentation-for-encryption)
and [BuildUpdateRequest attributes](https://developer.apple.com/documentation/appstoreconnectapi/buildupdaterequest/data-data.dictionary/attributes-data.dictionary).
The accepted standard-algorithm/non-France no-document answer remains supported:
**usesNonExemptEncryption=false**. This does not assert no encryption or OS-only
encryption. The recorded accurate basis remains encryption Yes, standard outside
OS Yes, proprietary No, France No. No ANSSI research, filing or email resumed.

### Actual provider write and independent readback

Existing private ASC key reused. Preflight GETs confirmed app **6821471140 /
services.shelf.app**, **IOS / 1.0.0 (5)**, build
**76e0e894-e322-49bc-9874-fbfc770b8a80**, **VALID / INTERNAL_ONLY**, flag null,
encryption declaration null and **MISSING_EXPORT_COMPLIANCE**. Availability
readback at **2026-10-15 00:08:03 UTC** confirms **174 territories / FRA absent**
for shelf_book_3. App-wide availability remains previously verified unconfigured;
no list was created or changed. Future app distribution must exclude France.
An initial availability GET used v1 and returned **404 PATH_ERROR**; corrected
existing v2 route and complete territory read returned **200**, no provider change.

Private before-state saved **0600**, outside Git:
**/home/shelf/tmp/shelf-build5-compliance-before-20261015.json**.
Immediately rechecked unchanged build attributes and missing-compliance status.
Only mutation: **PATCH /v1/builds/76e0e894-e322-49bc-9874-fbfc770b8a80**,
data type builds, attributes **usesNonExemptEncryption=false**, HTTP **200**.
No relationships or other attributes sent; no retry or other provider write.

**Readback verified by 2026-10-15 00:08:39 UTC:**

- **usesNonExemptEncryption=false**; all other build attributes exactly unchanged.
- **processingState=VALID**, **buildAudienceType=INTERNAL_ONLY**, expired false.
- **internalBuildState=READY_FOR_BETA_TESTING**: MISSING_EXPORT_COMPLIANCE cleared.
- **externalBuildState=NOT_APPLICABLE**, existing autoNotifyEnabled true unchanged.
- Encryption declaration GET **200 / null**, consistent with the no-document route.
- Existing **Shelf Owner / 1511a217-4f89-4dc7-8b8d-4679ce8a6226** remains internal;
  complete group-build GET contains **4, 3, 2**, not 5. Sole tester count **one**;
  tester identity withheld and membership untouched.

Private sanitized after-state retained **0600** at
**/home/shelf/tmp/shelf-build5-compliance-after-20261015.json**.
Compliance is cleared; **Build 5 is still unassigned and installation is pending**.
No government certification, public-review acceptance, phone purchase acceptance
or notification delivery is claimed. Only requested documentation changes,
scoped comparison and diff checks; no broad test or build rerun.

All purchase/owned-test/real-checkout/sign-in/relay gates remain OFF. No deployment,
build/upload, tester assignment/invitation, group creation, metadata/territory
change, transaction, email or public release. France exclusion, pricing-sync and
second-phone-restore deferrals, deferred Apple authentication/relay checks,
Android/UI pause, small-improvement deferrals and joint Android/iOS launch hold
remain binding. Backend remains c8c2662.

**Exactly one next action:** authorize assigning existing **1.0.0 (5)** to existing
**Shelf Owner** internal group only, preserving its one tester and all gates OFF.

## Build 5 Apple receipt and internal readiness — verified 14 October local / 15 October 2026 UTC

Owner reports Codemagic signed internal TestFlight run **#9**, ID
**6acfddac546ce6236ceea2d5**, succeeded on 14 October from source
**8283badf674f94c8cc7e18a5371e35cdc67d5d1c**, producing **Shelf.ipa (29.55 MB)**,
intended **1.0.0 (5)**. That cloud/artifact evidence is owner-reported; Codex did
not independently inspect the IPA, signing output or Codemagic worker logs.
Local clean branch HEAD matched that exact source before this documentation edit.
Apple receipt and readiness below are independently verified, not inferred from
Codemagic success. Earlier candidate/no-upload/diagnostic-next-action entries are
historical and superseded by this receipt; no diagnostic rerun or upload retry is
requested.

Authenticated **GET-only** inspection using the existing protected ASC key at
**2026-10-15 00:01:23–00:02:01 UTC** (14 October local):

| Evidence | Actual Apple value |
| --- | --- |
| App | **6821471140 / Shelf - شیلف / services.shelf.app** |
| Build/upload ID | **76e0e894-e322-49bc-9874-fbfc770b8a80** |
| Version/platform | **1.0.0 (5) / IOS**, independently confirmed through preReleaseVersion |
| Upload | **COMPLETE**, errors/warnings/infos all empty; uploadedDate **2026-10-14T13:02:02-07:00** (20:02:02 UTC) |
| Build processing | **VALID**, uploadedDate **2026-10-14T13:02:54-07:00** (20:02:54 UTC), expired **false** |
| Audience | **INTERNAL_ONLY** |
| Internal eligibility | **internalBuildState=MISSING_EXPORT_COMPLIANCE** |
| External state | **NOT_APPLICABLE** |
| Encryption field | **usesNonExemptEncryption=null**, not false |
| Encryption declaration | GET **200 / data=null** |
| Shelf Owner group | **1511a217-4f89-4dc7-8b8d-4679ce8a6226**, isInternalGroup **true**, hasAccessToAllBuilds **false** |
| Group assignment | Complete group-side build list contains **4, 3, 2**, **not 5** |
| Existing testers | Complete group tester relationship list contains **one**; identity withheld, membership untouched |

All nine Apple GET routes returned **200**; bounded app uploads/builds/groups
and group builds/tester lists reached terminal pages. Uploads are **1 FAILED /
2 COMPLETE / 3 COMPLETE / 4 COMPLETE / 5 COMPLETE**; received builds **2–5 VALID**.
The initial sandboxed connection failed DNS resolution; the approved network
inspection succeeded. No Apple error, provider mutation or credential change.

**Build 5 has finished processing. It is not ready for internal installation:**
export compliance is missing, and it has not been assigned to Shelf Owner.
[Apple's beta-compliance guidance](https://developer.apple.com/help/app-store-connect/test-a-beta-version/provide-export-compliance-information-for-beta-builds/)
requires the build's compliance information before testing. Build 4's previously
cleared declaration does not establish Build 5 clearance. Existing approved
non-France encryption inventory/declaration evidence is retained for a later
specifically authorized compliance action; no encryption answer is saved here.
No public App Review acceptance, installed-build acceptance, purchase acceptance
or notification delivery is claimed.

Only these two requested records changed; scoped diff/whitespace checks replace
an unnecessary source/test/build rerun. No compliance PATCH, tester/build
assignment, invitation, group creation, deployment, gate change, new build,
upload, notification test or transaction occurred. Checkout, Apple sandbox,
owned-book3 test and Apple sign-in/relay remain OFF. France exclusion/ANSSI
cancellation, Android/UI pause, pricing-sync, second-phone restore, Apple auth/
relay and small-improvement deferrals and joint Android/iOS public-launch hold
remain binding. Backend deployment remains c8c2662.

**Exactly one next action:** authorize clearing **only Build 5 export compliance**
using the existing approved non-France declaration, with readback verification
and all checkout gates OFF. Group assignment remains a later separate action.

## Run #8 credential failure — documented CLI path prepared, NOT cloud-verified

14 October 2026. Started clean at **1116317bedde0402a6516c175d66bbf6073e2df2**.
Owner confirms run #8 stopped before compilation because the custom guard could
not access its expected ASC credential environment variables; owner subsequently
saved **SHELF_IOS_BUILD_NUMBER=5**. Run #7's duplicate-4 rejection remains recorded.
Existing integration supports signing/publishing; that fact does not establish
private-variable availability to Python scripts or CLI access in this workflow.
No run, upload, deployment, activation or provider change performed here.

### Actual dependency and official findings

Inspected signed **ios-testflight-internal**: integration name exactly
**shelf_app_store_connect**; variable groups exactly **shelf_ios_foundation** and
**shelf_ios_testflight**. Previously, Python required raw
APP_STORE_CONNECT_KEY_IDENTIFIER / APP_STORE_CONNECT_ISSUER_ID /
APP_STORE_CONNECT_PRIVATE_KEY before making its own JWT/API requests. Both hooks
ran before a signed build; the first stopped #8. This direct credential-exposure
assumption is unsupported by the observed account behavior and is removed.

[Codemagic build versioning](https://docs.codemagic.io/knowledge-codemagic/build-versioning/)
documents existing publishing-integration reuse and the official CLI.
[Publishing](https://docs.codemagic.io/yaml-publishing/app-store-connect/) distinguishes
integration authentication from explicitly supplied credential variables.
[CLI action](https://github.com/codemagic-ci-cd/cli-tools/blob/master/docs/app-store-connect/get-latest-testflight-build-number.md)
supports Apple app ID **6821471140**, **--platform=IOS** and
**--pre-release-version=1.0.0**. CLI authentication still needs authorized ASC
access; its documented resolution uses configured key/issuer/private-key options,
environment or supported private-key files. There is no documented per-command
integration-name flag to invent. Server cannot prove how this Codemagic worker
exposes its existing integration; neither successful publishing nor server API
success proves the custom CLI call works. That limitation remains explicit.

Codemagic **BUILD_NUMBER / PROJECT_BUILD_NUMBER** count workflow/project runs;
run indexes **7/8** are not Apple's CFBundleVersion. Shelf retains explicit
owner-selected **SHELF_IOS_BUILD_NUMBER** with no run-index/numeric fallback.
The CLI's latest TestFlight number is not proof about pending/rejected upload
records; no claim that it reserves numbers or covers all in-progress uploads.

### Refreshed Apple read-only evidence

Existing protected server key, bounded app-scoped builds/uploads GETs **200** at
**2026-10-14 19:23:55 UTC**: received **2,3,4 VALID**; uploads **1 FAILED /
2 COMPLETE / 3 COMPLETE / 4 COMPLETE**, **IOS / 1.0.0**, no pending upload or 5.
**Candidate remains 1.0.0 (5)**. Before any later full run, repeat those two GETs,
require no processing build/pending upload, selected number greater than every
received/uploaded number, and no competing Codemagic signed run. This separates
complete upload evidence from CLI latest-TestFlight evidence. No public endpoint,
credential relay or speculative extra automatic guard was introduced.

### Minimal source correction and focused verification

Replaced only unsupported custom credential/JWT/REST behavior with a small
wrapper invoking:

```bash
app-store-connect get-latest-testflight-build-number 6821471140 --platform=IOS --pre-release-version=1.0.0 --disable-jwt-cache --silent
```

CLI output must be one positive integer; explicit chosen number must exceed it.
Missing CLI, timeout, auth/read failure or malformed result stops; no silent
fallback or raw credential precheck. Logs show only number/safe result; captured
provider output stays withheld. Removed the redundant second pre-compile custom
hook. The single check stays before dependency/configuration/compilation steps.
Signing integration, certificate/profile, internal-only export, publisher,
post-export version check, application/purchase/entitlement source are unchanged.

Prepared manual **ios-asc-access-check** with the exact same integration/groups,
working directory **mobile**, mac_mini_m2 and **5-minute** maximum. Its sole script
runs the same wrapper. **No ios_signing, Flutter/dependency commands, artifacts,
publishing, triggers or tester actions**. This is a prepared diagnostic workflow,
not a started build/run. It does not consume an Apple build number.

**8 synthetic tests passed** through
`SHELF_TOOL_TEST_SCOPE=ios-build-number scripts/run_tests.sh`: explicit number/no
run-index fallback, exact CLI scope/no secret arguments, delegated credential
resolution, duplicate/stale choice denial, withheld auth errors, malformed result,
missing CLI/timeout and identical nonpublishing integration/group path. YAML
parse/signed and diagnostic shell syntax, scoped diff checks passed. No purchase,
backend, Flutter suite or macOS compilation repeated. Synthetic runner responses
are not proof of live Codemagic CLI access.

### Non-publishing validation procedure and remaining limitation

Under separate diagnostic-run authorization, select existing Shelf app,
**ios/foundation at this new source**, workflow **ios-asc-access-check** only.
Keep saved **SHELF_IOS_BUILD_NUMBER=5** and existing integration/groups unchanged.
Expected safe result: CLI TestFlight **IOS/1.0.0 latest 4**, chosen **5**, exit 0.
Confirm the exact source and no publishing steps. Stop on unexpected number/auth/
CLI error; do not try a full signed run to discover whether this path works.
Server-side read-only inspection and synthetic tests cannot verify the worker's
integration injection, installed CLI version or permissions; only this bounded
nonpublishing diagnostic establishes that account-specific path.

If it reports unavailable authentication, the smallest documented configuration
is **reuse the same existing authorized ASC key**, privately from the retained
original, adding only **APP_STORE_CONNECT_KEY_IDENTIFIER**, **APP_STORE_CONNECT_ISSUER_ID**
and **APP_STORE_CONNECT_PRIVATE_KEY** as protected Secret variables to existing
**shelf_ios_testflight**, as described in the official publishing/environment
variable guides. Do not generate/rotate keys, change integration/signing, print
values, copy secrets to Git, assume saved dashboard secrets can be retrieved, or
ask for a new Apple account. This is a conditional supported plan, **not executed
or claimed necessary yet**: first test the documented existing-integration CLI.
After any approved credential exposure correction, repeat only the diagnostic.
A full signed build requires successful diagnostic evidence, fresh upload/queue
preflight and separate owner authorization. **Not READY for another full build.**

**Exactly one safe next owner action:** authorize one **ios-asc-access-check**
nonpublishing diagnostic run from the new ios/foundation source, not a signed
build or upload. No repeated build-number setup request; saved 5 is owner-confirmed.

Checkout/Apple sandbox/owned-test/real/sign-in gates OFF; France exclusion,
internal-only testing, Apple/Google entitlements, Android/UI pause, Apple auth/relay,
pricing-sync, second-phone restore, small improvements and joint launch hold
preserved. Deployed backend remains c8c2662. No cloud access success inferred.

## Codemagic run #7 duplicate build incident — guarded source fix, 14 October 2026

Owner reports run **#7**, source **9c41c3281480465ef79ec27cdfc5649894ec3ed7**,
produced a signed **Shelf.ipa** but publishing failed **409
ENTITY_ERROR.ATTRIBUTE.INVALID.DUPLICATE**: cfBundleVersion must exceed
**previousBundleVersion=4**. Intended number was **5**. No successful new Apple
receipt/installation/purchase is inferred from signed artifact creation.

### Confirmed findings and uncertainty

The exact signed YAML has **no numeric build-number fallback**. Its required
`SHELF_IOS_BUILD_NUMBER` is passed directly to Flutter `--build-number`; any
positive integer passed the old guard, including already-used **4**. Mandatory
export inspection compared CFBundleVersion with that same effective environment
variable; export **manageAppVersionAndBuildNumber=false** remained intact.
Thus the workflow did not enforce the previously verified unused number and
allowed a stale dashboard/run environment value through. The reported upload
error is consistent with effective build number **4**; the run's IPA/logs and
Codemagic saved/run-level variable values are not independently available here.
Do not claim which saved group, app override or run override supplied it, or that
Codemagic itself rewrote the number. The unsafe behavior was acceptance of any
positive number without current Apple collision evidence, not an invented YAML
fallback. Unsigned foundation's fixed number 1 is outside the signed workflow and
unchanged; no automatic increment/fallback was introduced.

### Current Apple evidence and correct candidate

Existing protected-key read-only GETs **2026-10-14 18:43:13 UTC**, scoped to
**6821471140 / services.shelf.app**, both **200**: received builds **2, 3, 4 VALID**;
upload records **1 FAILED / 2 COMPLETE / 3 COMPLETE / 4 COMPLETE**, all
**IOS / 1.0.0**. Complete bounded lists contain no pending upload or build **5**.
**Correct next candidate remains 1.0.0 (5)**, not assumed from previous inspection.
No number reservation, dashboard change, upload retry or provider write occurred.
Collision/processing checks run again in the later authorized cloud run.

### Implemented fail-closed safeguard

New `scripts/ios/check_build_number.py` runs before build preparation and again
immediately before signed IPA compilation. It requires the explicit positive
`SHELF_IOS_BUILD_NUMBER`, existing Codemagic App Store Connect integration access,
exact Apple app/bundle identity and complete bounded GET evidence from both
`/v1/apps/6821471140/builds` and `/v1/apps/6821471140/buildUploads`. Chosen number
must exceed every used/uploaded number, including failed upload numbers. Pending
upload, processing build, unknown/malformed state/number, incomplete pagination,
foreign/repeated page, identity mismatch, missing credentials, auth/transport/
parsing failure all stop clearly. No automatic fallback, number selection or API
write. Only chosen/highest number and a safe status are logged, never credentials.

Reuses documented integration-provided **APP_STORE_CONNECT_KEY_IDENTIFIER**,
**APP_STORE_CONNECT_ISSUER_ID**, **APP_STORE_CONNECT_PRIVATE_KEY**; signing
integration/certificate/profile/publisher are unchanged. Private JWT signing uses
a short-lived restricted temporary file, removed before API checks finish; no
secret is written into artifacts or repository. If the integration does not
expose its existing API credentials to custom scripts, the guard stops with a
clear unavailable-credentials message; do not bypass it or create/rotate a key.
[Codemagic's existing-integration guidance](https://docs.codemagic.io/knowledge-codemagic/build-versioning/)
documents reuse without adding separate environment secrets when configured.
Actual custom-script credential exposure in this cloud account remains a later
run-time check, not claimed proved by the server's separate authenticated access.

The two checks reduce stale-number and concurrent-upload risk but do not reserve
a number. Maintain one signed build/upload at a time; no concurrent pipeline or
retry. Apple remains authoritative at upload. Existing post-export number,
internal-only signing and artifact verification are retained.

### Focused verification and exact minimal dashboard action

**13 synthetic tests passed**, through
`SHELF_TOOL_TEST_SCOPE=ios-build-number scripts/run_tests.sh`. They cover explicit
number parsing, stale/lower numbers, failed-upload collision, successful next
number, processing/pending/unknown states, missing credentials, HTTP denial with
private details withheld, exact read-only app paths/identity, trusted bounded
pagination (including actual terminal links with matching meta.paging.total)
and both workflow placements. A bounded live response-shape check confirmed
Apple omits links.next on terminal pages; the guard requires final item count to
match the reported total, and rejects changed/missing totals or truncated lists.
YAML/signed shell syntax passed;
`git diff --check` passed. No PHP/Flutter suite repeated; no build/upload/provider
call made by the synthetic tests. Prior app, entitlement, accounting, backup and
deployment evidence reused. Changes are limited to the guard, its tests, wrapper
scope, two YAML hooks and these records. Reverting those source changes is possible
in isolation but must not be used to bypass a later failed collision check.

**Exactly one next owner action:** in **Codemagic → Team settings → Environment
variables → shelf_ios_testflight**, edit existing **SHELF_IOS_BUILD_NUMBER** to
**5** and save. Ensure the same name is not overridden to 4 in the Shelf app/run
settings; the effective next run must be 5. Keep API/groups/signing and every other
value unchanged. **Do not start/retry run #7 or upload its existing IPA.** A later
separately authorized run must select the new guarded ios/foundation source;
missing API access or any collision stops before compilation.

Backend remains deployed at **c8c2662**; this task performs no deployment or
activation. Apple sandbox/owned-test/real-checkout/sign-in gates remain OFF.
France excluded/ANSSI cancelled, Android/UI pause, Apple auth/relay deferrals,
pricing-sync, second-phone restore, small improvements and joint Android/iOS
public-launch hold preserved. New internal build/processing/install and actual
owner sandbox purchase/notification acceptance remain pending.

## Next signed internal TestFlight build — prepared 14 October 2026, NOT STARTED

**Exact reviewed candidate source: 14c392c17ab13a3a59bde3e159cc625c0f0a6456**,
branch **ios/foundation**. Checkout was clean at that SHA. All intended owner
Apple Book 3 Flutter changes from **c8c2662** are included byte-for-byte;
`git diff c8c2662 14c392c -- mobile codemagic.yaml` is empty. This documentation
follow-up changes only the two records and retains the reviewed application/YAML.
The reviewed immutable SHA remains a valid build candidate; if Codemagic selects
the later documentation-only branch tip, record its exact clone SHA and verify
application/YAML equality to this candidate before accepting the artifact.

### Actual Apple build-number evidence

Authenticated read-only GET **2026-10-14 18:03:10 UTC**: received builds **2, 3, 4**
are **VALID**, **INTERNAL_ONLY**, **IOS / 1.0.0**. App-scoped uploads GET **200**
at **18:04:03 UTC** returned **1 FAILED / 2 COMPLETE / 3 COMPLETE / 4 COMPLETE**,
all IOS/1.0.0, no pending upload and no 5 in either bounded complete list.
**Next unused candidate: 1.0.0 (5)**. No number was reserved or variable changed.
Recheck builds/uploads immediately before the separately authorized start; stop
on collision or competing upload. Existing protected ASC key reused; no key,
identifier credential or JWT printed/copied. An initial collection-level uploads
GET returned 403; the documented app-scoped
[Apple uploads route](https://developer.apple.com/documentation/appstoreconnectapi/get-v1-apps-_id_-builduploads)
returned 200. The inspection assertion initially treated Apple's nested state
object as a scalar; complete readback establishes the states above. Neither
inspection issue changed provider state or requires new permissions.

### Signed workflow readiness

Workflow **ios-testflight-internal**, Codemagic existing Shelf application,
**mac_mini_m2**, Flutter **3.47.2**, working directory **mobile**, release IPA,
bundle **services.shelf.app**, Team **YSLWJQDH8B**, version **1.0.0** explicitly
supplied by the build command. Shared `pubspec.yaml` remains **1.0.4+13**; workflow
build-name/build-number overrides determine this iOS artifact. No Android version
change needed. Existing integration **shelf_app_store_connect**, certificate
**shelf_ios_distribution**, profile **shelf_ios_app_store**, groups
**shelf_ios_foundation / shelf_ios_testflight** retained; no automatic trigger.

YAML parse, every signed-workflow shell script's `bash -n`, inline Python syntax,
plist purpose strings, native bundle/team/IAP capability and explicit signing/
artifact guards passed without executing build steps. Internal-only export
**testFlightInternalTestingOnly=true**, **manageAppVersionAndBuildNumber=false**,
strict codesign/team/bundle/version checks, `ditto` preservation and explicit
`$CM_BUILD_DIR` artifact/evidence paths intact. Publisher uses existing integration,
**submit_to_testflight=false / submit_to_app_store=false**: the signed manual
workflow uploads the IPA but does not automatically assign testers or submit
public review. Existing successful build-4 signing/export evidence reused;
no new macOS compile, signing credential introspection or binary validation claimed.

RevenueCat dependency remains locked **purchases_flutter 10.13.2**, enforced
lockfile resolution; native iOS selects only **appl_** server-supplied SDK config,
original numeric Shelf reader identity and Book 3 allowlist. No purchase key goes
in a build variable. **SHELF_TEST_MODE=false** and
**SHELF_INTERNAL_TEST_PURCHASES=false** remain correct: native iOS chooses sandbox
while server configuration/consent independently controls availability. Owner
already-owned Book 3 test action, explicit owned-book test consent, server echo,
independent Apple confirmation and normal duplicate protection are present.
Server **SHELF_APPLE_OWNED_BOOK3_TEST_ENABLED=false** and
**SHELF_APPLE_SANDBOX_ENABLED=false** remain OFF; no client override opens them.

Minimal backend recheck passed: release-state still records staging/production
**c8c266221d972d409ffa947ca87397293c69c004**, maintenance absent, runtime Apple
sandbox/owned-test/real-checkout/sign-in/relay false, Apple proof disabled,
production HTTPS app-config healthy and iOS purchase config false with SDK key
null. Reused completed 56 PHP tests/511 assertions, 15 Flutter tests, staging
isolation, ownership/accounting/history, webhook and verified backup evidence.
No broad test, deployment, migration, backup drill or provider audit repeated.

### Exact single-run settings and owner steps — later authorization only

1. Before the authorized run, Codex rechecks the two bounded Apple build/upload
   lists and exact branch application/YAML. In **Codemagic → existing Shelf app**,
   confirm no other queued/running signed build or upload. Existing access does
   not independently expose Codemagic queue/saved variable values here; no token
   search, credential creation or duplicate pipeline is required.
2. **Team settings → Environment variables → existing shelf_ios_testflight**:
   set only **SHELF_IOS_BUILD_NUMBER=5**. Retain existing
   **SHELF_API_BASE_URL=https://shelf.services/api/** in **shelf_ios_foundation**,
   existing Google iOS configuration, groups/integration/signing and all other
   variables. This task has not changed any dashboard value.
3. **Shelf app → Start new build**: select **ios/foundation**, workflow
   **ios-testflight-internal**, reviewed commit above where commit selection is
   available; otherwise use only its verified documentation-only descendant tip.
   Start **once**, under the next explicit build/upload authorization. Do not
   choose unsigned/diagnostic workflows, main/default branch or a PR target.
4. After that later upload, Codex inspects exact clone SHA/IPA/signing/version **5**
   and processing/export-compliance status before any acceptance claim. Assignment
   remains only to existing **Shelf Owner** under the applicable authorization;
   no tester/group/membership changes or external/public distribution. Do not
   restart merely because Apple processing is pending or the test button is absent.

### Pending phone procedure

Once the new build is received, cleared and assigned, install/update via existing
TestFlight without uninstalling or clearing account/download data; confirm **1.0.0
(5)** and basic launch. With gates OFF, Book 3 remains readable through existing
Play ownership and the Apple test action remains hidden. This establishes only
installation/launch, not purchase acceptance. Do not request an Apple sign-in or
relay test, or make a purchase, at this stage.

Later separate activation/transaction authorization must verify tracking OFF /
original App User ID and open both Apple gates with the existing backup/checkpoint
procedure. Use existing owner reader email/password, refresh Library, open Book 3,
read sample and agree through **Test Apple purchase — no charge**. Under that
window's exact authorization, cancellation must preserve Play access; at most one
new verified sandbox purchase must show independent Apple server confirmation,
Test-only immutable history and unchanged Play ownership. Product unavailable or
missing confirmation: stop and report, do not repeat buying/change providers.
Actual notification delivery versus REST, restore/refund/reversal and iPhone
rendering remain pending separate evidence; second-phone restore stays deferred.

No preparation blocker found. Cloud queue/variable checks and final build-number
recheck are start-time prerequisites, not claims of completion. Product title
metadata/genuine review image/public readiness stay open. France excluded/ANSSI
cancelled, Android/UI pause, pricing-sync, second-phone restore, Apple auth/relay,
small improvements and joint Android/iOS public-launch hold all preserved.

**Exactly one next action:** authorize one manual **ios-testflight-internal**
build/upload **1.0.0 (5)** from the verified candidate/identical documentation-only
branch tip, after the immediate collision/queue recheck, all purchase gates OFF.

## Owner Apple Book 3 exception — disabled backend rollout passed, 14 October 2026

Owner authorized the smallest staging-first, backed-up backend rollout, keeping
all purchase activation gates OFF. **Staging and production now run exact source
c8c266221d972d409ffa947ca87397293c69c004.** Release-state confirms both copies;
prior deployed/rollback source is **560ef50ac35aa2db78492b1c7bfb92ee72a69f76**.
No build, upload, transaction, refund, provider setting or public release occurred.

### Backups and preservation

- Fresh production preflight backup:
  `/home/shelf/backups/shelf/20261014-175255/`.
- Private preservation checkpoint:
  `/home/shelf/backups/shelf/20261014-175255/owner-apple-rollout-checkpoint/`.
  Retains the existing cPanel handler and restricted verification fingerprints;
  no secret values or reader data printed or committed.
- Staging backup before deploying the exact source:
  `/home/shelf/backups/shelf-staging/20261014-175413/`.
- Fresh production promotion backup:
  `/home/shelf/backups/shelf/20261014-175454/`.
  Includes `promotion-notes.txt` with prior code and preservation-aware rollback.

All three backups passed SQL completion, ZIP integrity and SHA-256 verification.
This is backup-artifact verification, not a new restore drill. Production's sole
unrelated modification was the recognized cPanel `public/.htaccess` handler;
workflow preservation and final checkpoint comparison passed. Both .env files and
existing private Apple webhook authorization/metadata files are byte-unchanged.
Google configuration remains unchanged with the complete production .env;
no provider writes or repeated provider setup checks were made.

### Staging and production verification

Reused **56 passed PHP tests / 511 assertions and 15 passed Flutter tests** from
c8c2662; no suite or Flutter build repeated. Necessary rollout-validator source
**c9f6728** updates the historical test-reuse gate to this exact validated source,
checks deployed purchase-path bytes, no pending migrations and both Apple gates
OFF, and directly exercises closed consent paths using an unsaved synthetic
reader. Python and embedded PHP syntax plus diff checks passed. This operator
fix runs from the isolated checkout; it is **not a different deployed revision**.

Staging passed at **2026-10-14 17:54:28 UTC**: all compared purchase source/test
paths match c8c2662; independent shelf_staging database/storage, private access
key, noindex/TEST COPY, logged mail/null queue, absent production Apple purchase
credentials, locked paid text, private files denied, accounts/Library protected,
HTTPS health and disabled webhook guards passed. Check evidence is
`/home/shelf/staging-runtime/checks-c8c266221d972d409ffa947ca87397293c69c004.log`,
SHA-256 **0352122603f19e878e3700f1ed2576eb0442ce31195b25d17900c4baae34eefc**.
Promotion required the same staged commit and unchanged evidence hash.

Production promotion passed the existing workflow: backup before maintenance,
fast-forward to the identical source, dependencies/autoload/cache/asset handling,
purchase/accounting/owner-share health checks, history comparison and maintenance
lifted. No new schema migration or data migration; both copies have no pending
migrations and retain the already-applied Apple store-scope schema. Final direct
HTTPS production catalogue/locked-poem checks confirm non-owner paid body remains
null. iOS purchase config returns enabled/sandbox/production false and no SDK key;
Apple webhook is guarded **503 production / 403 staging** before processing.
The new owned-book3 exception and existing Apple sandbox gates are runtime false;
real checkout, Apple sign-in/relay and Play sync remain OFF. Direct consent probes
deny a non-owner, another book and ordinary Apple consent (503 while disabled),
and reject the exception flag on a Google path (422). Owner-only restrictions
with gates configured are covered by the reused passed scoped tests; no gate was
opened even temporarily on either server.

Final private before/after fingerprints match all **11** reader/account/token,
purchase/event/ledger/entitlement/consent/agreement/accounting tables. Existing
owner Play book-3 purchase and valid access independently confirmed, exception
permission false, no Apple purchase history. Test income remains excluded from
ordinary revenue/author totals; all six existing D10 agreements pass. Public
handler, env files, webhook files and packaged APK/AAB/IPA inventory unchanged.
Tracked Flutter source follows the repository revision, but no compiled mobile
artifact was built, uploaded or added to production by this backend rollout.

### Rollback and remaining work

Prior staging release remains at
`/home/shelf/apps/shelf-staging-releases/560ef50ac35aa2db78492b1c7bfb92ee72a69f76/`.
Any separately authorized code rollback uses the prior production revision with
both Apple gates closed, preserving the checkpointed cPanel handler, private
configuration, schema and all later reader/payment history. No blanket SQL
restore or migration-down; rollback notes require considering later activity.
No rollback was needed or executed.

**A new internal TestFlight build is still required.** Installed **1.0.0 (4)**
lacks the owner test action/client confirmation changes. Build preparation must
verify exact branch application/workflow source and the next unused Apple build
number; none reserved or assumed here. Actual iPhone rendering, product retrieval,
Apple sandbox purchase/notification delivery, restore/refund/reversal acceptance
remain pending. Tracking OFF / original App User ID confirmation remains required
before any later separately authorized activation/transaction window. Both Apple
switches need that later explicit authorization; this rollout does not grant it.

France remains excluded and ANSSI work cancelled. Product title discrepancy,
MISSING_METADATA/genuine review image and public-release requirements remain open.
Apple authentication/relay checks, Android/UI pause, pricing sync, second-phone
restore, small improvements and joint Android/iOS public-launch hold preserved.

**Exactly one next action:** prepare one new internal TestFlight build from the
validated application source, verifying the next unused build number and existing
signing/workflow settings, with all purchase gates OFF and without starting it.

## Recovered owner test for an already-owned Book 3 — completed 14 October 2026

Resumed interrupted work at **29d9f32** in `/home/shelf/tmp/shelf-ios-foundation`,
branch **ios/foundation**. Actual recovered HEAD and remote tip were
**29d9f3250fdcd3030907c5ae7d65643e6915b92e**; no later local commits existed.
Inspected the complete pending diff and preserved all **11 modified files plus
one untracked service**; no reset, clean, discarded work or recreated implementation. This authorization covers isolated source, focused
verification, records and branch-only commit/push. **NOT DEPLOYED OR ACTIVATED.**
The historical ownership blocker remains present in deployed 560ef50 and installed
TestFlight **1.0.0 (4)** until separately authorized rollout and a new artifact.

### Completed safeguards

- New **SHELF_APPLE_OWNED_BOOK3_TEST_ENABLED** defaults false. It additionally
  requires configured Apple sandbox (existing SHELF_APPLE_SANDBOX_ENABLED),
  staging inactive and SHELF_REAL_PURCHASES_ENABLED false. Both Apple controls
  remain OFF; no .env/provider setting changed in this task.
- Eligibility requires the verified, buying-allowed exact-owner-email reader,
  Published **3 / shelf_book_3**, positive price, current active entitlement and
  an unrefunded/unrevoked **PLAY_STORE sale** for that same reader/book. An
  account without that sale or with any recorded Apple book-3 purchase cannot
  use the exception. No alias/relay/new account or complimentary grant substitutes.
- Library returns authenticated `apple_owned_book3_test_allowed` and a separate
  `apple_sandbox_book3_confirmed` only after existing reconciliation succeeds.
  Book details adds a 52-pixel LTR **Test Apple purchase — no charge** action only
  for native iOS with current server permission, owned book3 and sandbox checkout.
  Normal Read book/sample and duplicate-purchase paths remain intact. Permission
  is not persisted offline and clears on identity change; failed refresh blocks it.
- The test screen explains existing Play ownership, preserves source title RTL,
  requires the existing sample/all-sales-final agreement, and identifies sandbox/
  no charge. Client sends explicit `owned_book_sandbox_test=true` with APP_STORE/
  sandbox consent and requires the server to echo authorization before SDK buy.
  The server locks reader/book, rechecks eligibility, appends distinct test consent
  wording, and rejects that flag on the Play consent path. Normal owned-book
  consent still returns 409; other books/nonowners/real checkout are rejected.
- Before recording a new overlapping Apple purchase, server requires that same
  test consent **at or before Apple purchase time and no more than 15 minutes
  before it** (existing consent timestamps have second precision). Older Apple
  proof preceding consent and stale consent fail closed. Existing independent V2
  app/product/original-reader/transaction/millisecond-time/permanent-entitlement/
  APP_STORE/SANDBOX checks are retained. SDK success or existing Play access can
  never establish Apple confirmation. A failed confirmation refresh cannot reuse
  a cached confirmation flag. Confirmation retries never initiate another buy.
- Only a proven Apple transaction appends Apple purchase/event/Test journal rows;
  it does not rewrite Play purchase/ledger history. Existing union-of-valid-store
  access remains: cancelling/pending/failing Apple testing leaves Play ownership
  and download intact; Apple refund/reversal never removes valid Play access.
  Duplicate event delivery remains idempotent; a second new overlapping Apple
  transaction is rejected. Sandbox sale/refund/reversal rows remain excluded from
  real revenue and author totals, without new royalty calculation or payout.

### Verification and remaining limits

Focused wrapper invocation:
`SHELF_PHP_TEST_FILTER='AppleSandboxPurchasesTest|BookPurchasesTest|AccountingTest' SHELF_MOBILE_TEST_SCOPE=ios-purchases scripts/run_tests.sh --mobile`.
All tests use synthetic identities/provider responses and disposable SQLite/
Flutter storage under `/home/shelf/tmp`; no live request, purchase or data change.
PHP checks cover both gates, staging, exact owner/verified/unblocked account,
book/store/mode scope, unchanged normal duplicate guard, active Play-sale eligibility,
fresh consent/proof, a second Apple transaction, immutable Play rows, Test-only
income and independent Library Apple confirmation/refund behavior. Flutter checks
cover controller guards, server consent echo, separate proof versus Play access,
account reset, cancellation/pending/failure/download preservation and the actual
Book details → test screen route, agreement, price, confirmation and disabled
repeat button. Final wrapper **passed: 56 PHP tests / 511 assertions, 14 Flutter
iOS purchase tests and 1 staging identity test**. Focused analysis passed with
21 style infos, no errors or warnings; `git diff --check` passed.
The first recovery run exposed widget platform-override cleanup and a stale Play
test expecting 422 for an Apple event; the existing default-OFF Apple guard
returns 503. Restored the override before widget invariants and corrected only
that assertion, then reran the complete focused scope successfully. No live
failure occurred. No dependency
upgrade, broad suite or Android artifact. Source UX checked by widget routing/
layout with 52-pixel actions; real iPhone rendering/StoreKit acceptance is pending.

A **new internal TestFlight build is required**: book-detail routing, controller
and purchase screen are bundled Flutter changes absent from installed build 4.
This task starts/reserves no build, upload or build number. Recheck actual unused
number and exact branch source under later build authorization. No native signing,
SDK/dependency, key, Apple/RevenueCat product or workflow setting change needed.
Real StoreKit retrieval, purchase proof shapes, notification delivery and phone
acceptance remain unverified; tracking OFF / original App User ID dashboard
confirmation is still required before later activation. Product title discrepancy,
MISSING_METADATA/genuine review image and public-release requirements remain open.

### Exact recovered files and completion

Recovered existing changes in:

- `app/Http/Controllers/LibraryController.php`
- `app/Services/Purchases/AppleSandboxPurchases.php`
- `app/Services/Purchases/OwnerAppleSandboxTest.php` (untracked at recovery)
- `config/purchases.php`
- `mobile/lib/purchases/library_controller.dart`
- `mobile/lib/purchases/purchase_screen.dart`
- `mobile/lib/screens/collection_detail_screen.dart`
- `mobile/test/ios_purchases_test.dart`
- `tests/Feature/AppleSandboxPurchasesTest.php`
- `scripts/run_tests.sh`
- `docs/MASTER_RECORD.md`
- `docs/IOS_PURCHASES.md`

The interrupted source implementation was present; final passing verification and
accurate recovery/test records were unfinished. Completed those, corrected the
widget fixture and added the narrowly scoped existing-guard assertion correction
in `tests/Feature/BookPurchasesTest.php`. All 13 final files belong to this scope.
No schema, deployment, provider setting, gate, build, upload or purchase changed.
The validated branch-only commit is titled **Allow owner Book 3 Apple sandbox test
without changing Play ownership**; deployment remains 560ef50, not this source.

### Rollout and rollback — plans only, no deployment in this task

1. Under separate rollout authorization, stage this exact verified commit with
   both Apple gates and real checkout OFF; staging must force Apple off. Reuse the
   existing already-applied store-scope schema: **no new migration**. Verify fresh
   backups and promote the same tested commit through docs/STAGING.md, preserving
   private config, Google integration, ownership and all history. Record exact
   promotion evidence; do not assume current deployment contains these changes.
2. Separately build/install the updated internal iOS artifact with all gates OFF.
   Later explicit activation/transaction approval must include **both** Apple
   sandbox and owned-book3-test switches, fresh verified backup/encrypted .env
   checkpoint and exact runtime owner/mapping checks. The older one-switch plan
   is insufficient for this exception. Keep real/sign-in/relay/sync OFF. Refresh
   Library to obtain current owner permission before opening only book3 test UI.
3. To close an approved window, atomically set **SHELF_APPLE_OWNED_BOOK3_TEST_ENABLED=false**
   and **SHELF_APPLE_SANDBOX_ENABLED=false**, retaining permissions and unrelated
   settings, then clear config cache and verify disabled config/guards. Preserve
   all Play/Apple records, keys, webhooks, schema and downloads; no SQL restoration
   or migration down. Closing gates is not a refund. As already documented,
   disabled Apple verification cannot issue fresh offline leases for recorded
   Apple history; any pending reconciliation needs a separately approved window.
   The Play purchase remains valid and is not revoked by this exception.
   A later code rollback uses the recorded previous deployed revision with gates
   closed and schema/history retained, never blanket restoration over reader data.

**Exactly one next action:** authorize staging-first, backed-up promotion of this
verified backend source with both Apple controls and real checkout OFF.
France excluded/ANSSI cancelled, deferred Apple authentication/relay checks,
Android/UI pause, pricing-sync and second-phone-restore deferrals, small-improvement
pause and joint Android/iOS public-launch hold all remain unchanged.

## Smallest owner-only book-3 sandbox test — prepared, NOT ACTIVATED

13 October 2026: owner confirms **1.0.0 (4) installed and launches**; basic
installation/launch acceptance only. Backend source 560ef50 and separate Apple
sandbox webhook remain ready with all gates OFF. Current staging/runtime/HTTPS/
private config/mapping checks passed, and Apple/Google product attachments to
shelf_book_3 are preserved. France remains excluded, app-wide availability remains
previously unconfigured; no public availability/release permitted.

### Historical blocker before opening a gate — source follow-up below supersedes it

The existing exact-admin-email owner reader already owns **book3**, with existing
Play book3 purchase history. Client LibraryController.purchase() refuses owned
books; server consent also blocks existing access (409). No Apple purchase exists.
Do not remove Play ownership, switch reader identity or bypass server consent.
The current implementation cannot perform the requested new Apple purchase for
that reader. **Next action: authorize focused owner-only Apple sandbox test support
for already-owned book3**, retaining Play access and disabled controls. Any needed
client source/artifact follow-up requires its own authorized task; no rebuild here.

Normal app checkout is server-isolated: verified/unblocked exact owner reader only,
APP_STORE sandbox only, book3/shelf_book_3 only, original numeric customer identity
and independent permanent transaction/entitlement evidence. Read-only process
simulation confirmed non-owner/blocked-owner denial and configured readiness without
changing server settings. Public iOS readiness is not per-reader; consent/proof are
the restriction, not hidden controls. Other books and Google integration unchanged.

### Deferred activation — do not execute while ownership blocker remains

1. Resolve that blocker in the separately approved scope; reuse installed build
   evidence and existing keys/providers. Confirm RC tracking **OFF** and effective
   sandbox restore **Keep with original App User ID**; those settings aren't public
   API fields and aren't established by product/webhook GETs. No settings changes
   or repeated owner request in this preparation task.
2. Under separate owner activation/transaction authorization, run production
   **python3 -B scripts/shelf_daily_backup.py**, verify SQL/content/checksums, save
   encrypted private .env checkpoint, and recheck exact runtime identity/config.
3. Atomically change only production **SHELF_APPLE_SANDBOX_ENABLED=false → true**;
   retain shelf ownership/0600, reject symlinks/duplicates/concurrent changes.
   Run **php artisan config:clear**. Retain real checkout/sign-in/relay/sync OFF,
   staging untouched, owner restriction and exact book3 mapping. Never rerun the
   OFF-only configure helper to activate. No deployment/migration needed merely
   to flip a resolved supported gate.
4. Privately verify actual runtime proof readiness, real OFF, iOS GET
   enabled/sandbox=true, production=false, allowed_product_ids exactly
   [shelf_book_3], key privately matched, normal non-owner consent denied before
   any write, other books rejected, no changes to Google/other entitlements.
   If an unexpected exposure or mismatch appears, close the gate immediately;
   do not proceed to a transaction.

### Smallest phone window and evidence — separately authorized later

Use existing owner reader **email/password**, not Apple/relay/admin/alias login.
Apple account for TestFlight is separate from Shelf reader identity. TestFlight
purchases are sandbox, with no charge; use the existing installation/account.
Do not create a new sandbox account or sign out of Media & Purchases for this
basic case. After refresh, open only book3, read sample/consent and inspect Apple's
sheet. First cancel one sheet and confirm no new Apple ownership; then, if still
within authorization, complete **at most one book3 sandbox purchase**. If product
is unavailable, stop and report; don't edit products, prices, territories or reset
history. Sandbox price/localization quirks do not justify store changes.

Codex verifies independent RevenueCat V2 APP_STORE/sandbox purchase proof for exact
reader/product/original identity/transaction/time and entitlement; Shelf event,
purchase and append-only journal show **Test**, no real revenue, no second sale.
Verify expected paid text/download access without corrupting pre-existing Play
ownership. Capture actual provider webhook delivery versus REST reconciliation as
separate evidence; REST success alone is not webhook delivery. Preserve other
books. Same-account restore/refund/reversal/isolation matrix remains later scope;
second-phone restore is deferred. No Apple sign-in/relay acceptance requested.

### Exact close-window / rollback

Atomically change only **SHELF_APPLE_SANDBOX_ENABLED=true → false**, same owner/
permissions/concurrency safeguards; **php artisan config:clear**. Verify runtime
proof OFF, real/sign-in/relay/sync OFF; public iOS config enabled/sandbox/production
false and public_sdk_key null; staging still OFF. Under separately authorized
verification, incomplete Apple webhook returns its disabled guard before event/
provider/data processing. Keep webhook/key/history/schema/entitlements intact;
no SQL restore, migration-down, refund claim or Play entitlement deletion. Gate
closure is not proof of refund and does not revoke legitimate Play access.

Current Apple product remains NON_CONSUMABLE / MISSING_METADATA, with 174 saved
territories excluding FRA; title discrepancy/genuine screenshot stay pending.
Apple TN3186 does not require IAP review submission for sandbox. Saved purchase
key credentials and notification V2 evidence reused; receipt/product-fetch and
end-to-end delivery acceptance remain unverified. All holds and deferrals preserved.


## Build 4 owner-only TestFlight installation — 13 October 2026

Current build **1.0.0 (4)**, source owner-reported **2702fbb**, Apple build
**60002f22-847e-4d06-9b7c-623eee9aea70**. Processing **VALID**, audience
**INTERNAL_ONLY**; export compliance already cleared and reused unchanged.
On **2026-10-13 07:58:42 UTC**, authorized group-build POST returned **204**;
GET readback confirms assignment to existing **Shelf Owner** internal group
**1511a217-4f89-4dc7-8b8d-4679ce8a6226**, same sole owner tester, same settings,
builds now **4, 3, 2**. Apple internalBuildState **IN_BETA_TESTING**.
No extra tester/group created. Build-side group GET 403 is a route permission
limitation; group-side reads/writes worked, so assignment is independently verified.
No dashboard assignment needed. Owner subsequently confirms iPhone installation/launch; purchase acceptance remains unverified.

**Single next owner action:** iPhone **TestFlight → Shelf → Update** (or Install)
to **1.0.0 (4)**. If needed select **Previous Builds → 1.0.0 → build 4 → Install**.
Keep the existing installation/account; do not uninstall or clear data. Confirm
version/build after installation. This step does not authorize purchases or Apple
sign-in; server Apple sandbox, real checkout and sign-in/relay gates stay OFF.
No new build/upload, invitation, external test or public release.

France remains excluded, ANSSI work cancelled, Android/UI paused; pricing-sync,
second-phone restore and Apple authentication/relay checks remain deferred, and
joint Android/iOS public-launch hold remains binding. Older preparation sections
below are historical; current build is uploaded, compliance cleared and assigned.


12 October 2026: source preparation on `ios/foundation`, based on `a5047f4`.
This guide extends [the existing purchase setup](PURCHASES_TEST_SETUP.md). The
completed readiness audit is reused; Android's acceptance does not establish iOS
acceptance. Only `shelf_book_3` is implemented for owner-only Apple sandbox use.
**No provider setup, activation, deployment, running migration, build, upload or
transaction was performed. Real checkout stays OFF.**

## Implemented behavior

- The existing Flutter app selects `appl_` on native iOS and `goog_` on Android.
  Reader identity stays the numeric Shelf reader ID. SDK success never grants
  access. iOS requests its own configuration, chooses sandbox, offers only book
  3, and obtains account/product/store/mode consent before opening the store.
  Native Runner now declares the In-App Purchase capability; signing identity,
  entitlements, internal export, photo-purpose guards and artifact paths are unchanged.
- New default-false `SHELF_APPLE_SANDBOX_ENABLED` controls Apple sandbox. No Apple
  production switch exists. Staging forces this gate off. Checkout requires the
  verified Shelf reader whose exact email belongs to the owner admin; plus-aliases
  and private-relay addresses are not automatically treated as that owner. An
  existing verified email/password account can be used without enabling Apple
  sign-in. Buying-blocked readers cannot initiate checkout; historical corrections
  and verification still work for the original owner account.
- Authenticated Apple webhooks require a distinct Apple app-scoped authorization
  secret, the configured RevenueCat Apple app, APP_STORE, SANDBOX, product 3,
  decimal Apple transaction ID and exact purchase timestamp. Server-side
  RevenueCat V2 app/product/purchase/entitlement reads must independently confirm
  `services.shelf.app`, a permanent non-consumable, original/current reader ID,
  quantity one, purchased ownership (no Family Sharing), exact transaction/time,
  sandbox environment and active `shelf_book_3` entitlement. Missing, ambiguous,
  transferred, production or malformed evidence fails closed. Provider access
  is bounded and does not follow arbitrary pagination URLs or redirects.
- Transactions remain unique by store/environment/transaction. Apple event keys
  are scoped to app/store, duplicates do not repeat financial entries, and a
  conflicting repeat is rejected. Existing Play history keeps its default store;
  consent and refund tombstones cannot cross stores. Account changes never move
  an Apple purchase merely because emails match.
- Purchase confirmation and restore use the existing Library flow and independent
  REST evidence. SDK restore does not transfer ownership. Refunded transactions
  lock the book and the shared client removes its download on the next successful
  check; samples remain protected by the existing access rules. Provider failure
  gives an error and no fresh offline lease, rather than inventing a revoke.
- `CANCELLATION` appends a refund; `EXPIRATION` appends a revocation. A verified
  `REFUND_REVERSED` appends a compensating Test entry and restores access only to
  the original account with a matching unreversed refund and no revocation. A
  reversal cannot create an account or purchase. REST-owned status alone cannot
  restore a refunded transaction. Stale/conflicting corrections fail closed for
  later reconciliation; duplicate states do not multiply amounts. Refund-before-
  sale tombstones remain locked until a verified reversal.
- REST-discovered refunds record **observation time**, not an invented Apple
  refund time; their event details distinguish them from provider timestamps.
  All original ledger rows remain immutable. Sandbox entries are labelled Test
  and excluded from real income/author totals. Amount, currency and deductions
  unavailable during REST confirmation remain unknown; later events do not
  rewrite them. Reversal offsets the original known amount, or remains unknown.
- Deleted-account Apple recovery is explicitly blocked before Google proof or
  a recovery claim can run. Support-assisted Apple claimant/transaction proof,
  off-server recovery claims and any Family Sharing support are future work.

## Original setup checklist — historical; current status below supersedes completed steps

No dashboard access or completion is assumed. Reuse the existing Shelf project
`projbbce26da`; preserve Play app `appf83cd58c5c`, its keys, products, webhook and
**Keep with original App User ID** restore setting. No separate project or app
binary is needed.

1. **Next single action: obtain the purchase key.** Open
   [App Store Connect](https://appstoreconnect.apple.com/) → **Users and Access** →
   **Integrations** → **In-App Purchase**. Check Active keys first. Reuse an
   existing key only if its In-App Purchase capability and private file are
   confirmed. Otherwise click **Generate In-App Purchase Key** (or **+** beside
   Active), enter **Name: ShelfPurchases**, and click **Generate**. Click
   **Download API Key** once. Store the original `.p8` privately in the owner's
   secure credential backup; never paste its contents in chat or Git. Record the
   actual Key ID and Issuer ID privately; neither is invented here. Do not upload
   it to this server using the old ShelfLogin uploader without a separately
   prepared secure transfer. This is an **In-App Purchase key**, distinct from
   both the existing Codemagic/App Store Connect upload key and `ShelfLogin`
   Sign in with Apple key `2VQ43945YB`. Neither substitutes for it. RevenueCat
   requires the purchase key for this Flutter/StoreKit 2 integration.
   [Official key instructions](https://www.revenuecat.com/docs/service-credentials/itunesconnect-app-specific-shared-secret/in-app-purchase-key-configuration).
2. **Commercial prerequisites before product testing.** On the same website →
   **Business**, confirm the latest **Paid Apps Agreement** is active and required
   **Tax** and **Banking** information is complete/clear. If incomplete, the owner
   supplies their actual legal/tax/bank information using Apple's forms; no values
   can be inferred from code. This status is unverified. Do not test a purchase
   while these prerequisites are incomplete.
   [Provider prerequisite guidance](https://www.revenuecat.com/docs/getting-started/entitlements/ios-products).
3. **Create only the Apple book-3 non-consumable.** App Store Connect → **Apps** →
   **Shelf - شیلف** (Apple ID `6821471140`, bundle `services.shelf.app`) →
   **Distribution** → **Monetization / In-App Purchases** → **+**.
   **Type: Non-Consumable; Reference Name: Shelf book 3; Product ID: shelf_book_3**;
   click **Create**. If that exact product already exists, inspect/reuse it;
   never delete/recreate it. Complete its localization with the actual book title
   and an accurate description of full access to that book, using **+ / Create**
   then **Save**. Set the approved USD **2.99** starting price through **Price
   Schedule → Add Pricing → Base Country or Region: United States → Price: 2.99 →
   Next → Confirm**, then **Save** where shown. Review storefront availability
   with the owner; do not assume Apple's territories equal the approved Play
   list or authorize worldwide availability. Leave **Family Sharing** off. A
   real purchase-screen review screenshot is pending the future build; do not
   substitute a fabricated acceptance image. Product review/public submission is
   separate from this internal sandbox batch.
   [Apple product creation](https://developer.apple.com/help/app-store-connect/manage-in-app-purchases/create-consumable-or-non-consumable-in-app-purchases/).
4. **Connect the existing project to Apple.**
   [RevenueCat dashboard](https://app.revenuecat.com/) → **Shelf** → **Apps &
   providers** → **+ New** / **Add app** → **App Store**. First check for an
   existing Apple configuration; reuse it if correct. Name **Shelf iOS**, bundle
   **services.shelf.app**, App Store app ID **6821471140** where requested.
   Under **In-app purchase key configuration**, select the privately downloaded
   purchase `.p8`, provide its actual Key ID if requested and the Issuer ID from
   Apple's In-App Purchase page. Click **Save changes**, and confirm **Valid
   credentials**. Keep private files/secret keys in RevenueCat's protected setup
   and the owner's secure backup. An optional App Store Connect API key for
   catalogue import is separate; don't replace the existing upload credential.
   [Connect a store](https://www.revenuecat.com/docs/projects/connect-a-store),
   [required purchase key](https://www.revenuecat.com/docs/service-credentials/itunesconnect-app-specific-shared-secret/in-app-purchase-key-configuration).
5. **Attach exactly one product.** RevenueCat → **Product catalog → Products →
   + New / Import**: choose **Shelf iOS**, identifier **shelf_book_3**, type
   **Non-consumable** and click **Add** / **Import** as presented. Record the
   generated Apple app ID and RevenueCat internal product ID (different from
   `shelf_book_3`); do not invent either. **Entitlements → existing shelf_book_3 →
   Attach products**: select only the Apple `shelf_book_3` product, click **Add**.
   Preserve its existing Play attachment. Do not create a global entitlement,
   `default` offering, subscription or consumable.
6. **Notifications and independent verification permissions.** RevenueCat Apple
   app configuration provides its own **App Store Server Notifications URL**.
   Copy that actual URL; App Store Connect → Shelf → **App Information → App Store
   Server Notifications → Sandbox Server URL → Set Up**: URL = that copied value,
   click **Save**. The owner’s actual form offered no version selector; do not
   search for one again. Both Production and Sandbox URLs are now owner-confirmed
   saved; their versions and delivery remain unverified (see current status below).
   Keep RevenueCat's **Track new purchases from server-to-
   server notifications** off; numeric Shelf IDs must first be established by the
   SDK. Add a separate RevenueCat webhook in **Integrations → Webhooks → + New**:
   name **Shelf iOS sandbox**, URL **https://shelf.services/api/purchases/webhook**,
   App **Shelf iOS**, environment **Sandbox**, events **NON_RENEWING_PURCHASE,
   CANCELLATION, EXPIRATION, REFUND_REVERSED**. Authorization = a newly generated
   private secret stored only in the protected server configuration and this
   integration; click **Add webhook** / **Save** as presented. Do not alter the
   Play webhook or send a webhook test in this task.
   The existing server V2 key must have read access to **Customer information /
   purchases and purchase entitlements**, **Project configuration / apps**, and
   **Project configuration / products**. Existing Customer information Read only
   evidence does not establish apps/products permission. Verify least-privilege
   access before using that private key; no purchase/customer write access is
   needed. Never send it to Flutter. Preserve its existing private file/permissions.
   [Purchase REST schema](https://www.revenuecat.com/docs/api-v2/purchase),
   [app schema](https://www.revenuecat.com/docs/api-v2/app),
   [product schema](https://www.revenuecat.com/docs/api-v2/product).

## Earlier provider evidence and access preparation — 12 October 2026

Completed purchase implementation **08a2adf** and its tests are reused. The original
checklist is retained as history, not a request to repeat completed setup.
Paid Apps Agreement, banking, W-9 Active; DSA In Review; ShelfPurchases added with
RevenueCat Valid credentials; Apple book-3 product and reported availability/
pricing/localization completed; both notification URLs saved without a version
selector: **owner/dashboard evidence**. Exact Apple saved values were uninspected at that point; authenticated findings below supersede this limitation.

Independent Shelf RevenueCat V2 GETs verified:

| Reference | Verified value |
| --- | --- |
| Apple app | app8ef018b95b / Shelf (App Store), services.shelf.app |
| Apple product | prod0d084e901f / shelf_book_3, non_consumable |
| Existing entitlement | entlb3d491e78e / shelf_book_3, Apple and original Play attachment only |
| Purchase credential | subscription_key_configured=true; fresh Apple validation not performed |
| Optional ASC import credential | app_store_connect_api_key_configured=false; separate from purchase key |
| Apple SDK key | One appl_ key exists; value withheld, not configured on server |
| Shelf webhook | Play webhook only; separate Apple sandbox webhook still absent |
| Verifier permissions | Apps/products reads and purchases search passed; no permission change needed |

The documented existing private V2 file is protected and reused without printing
its value. Server iOS app/product/SDK/webhook fields and Apple sandbox gate are
absent; no configuration changed. API inspection covers Shelf only.

### Notification versions when the form has no selector

Apple Help still describes a choice, while its [App.Attributes schema](https://developer.apple.com/documentation/appstoreconnectapi/app/attributes-data.dictionary)
exposes independent `subscriptionStatusUrlVersion` and
`subscriptionStatusUrlVersionForSandbox` alongside the production/sandbox URLs.
The [enum](https://developer.apple.com/documentation/appstoreconnectapi/subscriptionstatusurlversion)
allows V1/V2. Neither source establishes an automatic V2 default for the owner's
form. Do not repeat the selector search or change the saved URLs. Authenticated
GET `/v1/apps/6821471140` can inspect both saved versions and compare the saved
URLs privately; null/missing version fields do not establish V2.
[RevenueCat supports both and recommends V2](https://www.revenuecat.com/docs/platform-resources/server-notifications/apple-server-notifications).
Its public app API does not expose notification URLs/versions, purchase tracking
or restore behavior. Saved configuration is separate from delivery. Apple's
[TEST notification](https://developer.apple.com/documentation/appstoreservernotifications/enabling-app-store-server-notifications)
is V2 regardless of the configured version; later TEST success alone cannot prove
the version of ordinary events or end-to-end Shelf purchase/refund delivery.
No TEST request or transaction was sent.

### Completed private access transfer — preparation retained for reference

Reuse the **existing Shelf App Store Connect upload team key** already used by
GitHub/Codemagic, from the owner's secure retained original. Supply its `.p8`,
actual Key ID and Issuer ID privately. **Existing App Manager access** is sufficient
for the requested app/IAP metadata, price schedule and availability GETs. No new
key, Admin/Finance/report access, capability or permission change is requested.
App Manager is not a read-only provider role; this inspection will make only GETs.
Team keys apply account-wide, but requests are limited to Shelf app 6821471140 and
its existing shelf_book_3. Do not use ShelfLogin or ShelfPurchases. No provider
secrets can be recovered from GitHub's saved secret values by this workflow.
If the original upload key is unavailable, report that limitation first.

**Current transfer method — prepared 12 October 2026:** use
[IOS_ASC_KEY_UPLOAD.ps1](IOS_ASC_KEY_UPLOAD.ps1). It replaces the earlier manual SFTP/two-
file request; no local JSON creation, filename typing or key-content
copying is required. Codex automates all possible authorized work; owner actions
are limited to necessary local access/interaction.

Checked Shelf records and configured/inherited upload metadata first: no verified
upload Key ID or Issuer ID is available here. The helper derives Key ID from the
original `AuthKey_<Key-ID>.p8` filename and asks **only the missing Issuer ID** in
a GUI input box. Copy that existing Issuer ID from **Codemagic → Team Settings →
Integrations → Developer Portal → shelf_app_store_connect**. Do not generate or
rotate a key, and do not use the Apple Team ID as Issuer ID. Key ID derivation
matches a filename; Apple/dashboard association remains a later verification.

Select the existing `.p8` used by Shelf’s Codemagic `shelf_app_store_connect`
upload integration, **not ShelfLogin or ShelfPurchases**.

**Windows PowerShell — command 1, download to Downloads (refuses an existing file):**

```powershell
& { $shelfHelper = Join-Path $env:USERPROFILE 'Downloads\IOS_ASC_KEY_UPLOAD.ps1'; if (Test-Path -LiteralPath $shelfHelper) { throw 'Helper already exists; preserved. Stop and report.' }; scp.exe -o StrictHostKeyChecking=yes 'root@157.250.199.106:/home/shelf/tmp/shelf-ios-foundation/docs/IOS_ASC_KEY_UPLOAD.ps1' $shelfHelper; if ($LASTEXITCODE -ne 0) { throw 'Download failed; do not run the helper.' } }
```

**Windows PowerShell — command 2, run:**

```powershell
powershell.exe -NoProfile -STA -ExecutionPolicy Bypass -File "$env:USERPROFILE\Downloads\IOS_ASC_KEY_UPLOAD.ps1"
```

The owner’s original filename, bytes and ACL are preserved. Local links/reparse
points (including parent directories) are refused. Only public receiver code
appears in SSH command arguments; key/identifiers use binary SSH stdin, no tokens,
clipboard or key/metadata output. Existing SSH host trust is required; no automatic
acceptance of unknown/changed hosts. Server opens directory components without
following symlinks, requires existing private parents shelf:shelf/0700, and
exclusively creates **/home/shelf/secrets/apple/shelf-asc-inspection.p8** and
**shelf-asc-inspection.json**, both shelf:shelf/0600. Either existing destination
(including dangling symlink) refuses the upload. Length/SHA-256 and exact metadata
are verified from disk before success. Handled failures clean only newly created
matching-inode files; abrupt interruption/cleanup failure may leave private partial
files, so stop for private inspection rather than overwrite or automatic retry.
No actual credential transfer performed by Codex in this preparation.

13 synthetic checks passed through the focused test-wrapper scope, using the
actual embedded receiver and disposable /home/shelf/tmp storage. Windows picker/
PowerShell runtime and real SSH transfer remain unexecuted; no PowerShell binary
available on this server. No purchase/API checks repeated. After transfer, the
next task is read-only Apple inspection; the helper makes no API call itself.

After access, automate bounded Apple GETs for the existing product, localizations,
price schedule/base territory/current prices, availability/territories and Family
Sharing, plus both notification URL/version fields. Use current schema paths,
follow bounded pagination without unrelated apps, and report saved values rather
than assuming USD 2.99 or worldwide availability from completion reports.

### Current authenticated Apple findings — 12 October 2026

Owner completed the helper; Codex verified both documented credential files are
regular/no symlinks, shelf:shelf/0600, parents 0700, key parses EC P-256, and actual
authenticated Apple GETs succeed (200). Key/identifiers/JWTs withheld; filename
alone is not credential proof. The credential's dashboard name/role was not
independently introspected. No additional credential transfer is needed.

App GET at 23:33:04 UTC confirms **6821471140**, **Shelf - شیلف**, **services.shelf.app**.
Both production **subscriptionStatusUrlVersion=V2** and sandbox
**subscriptionStatusUrlVersionForSandbox=V2** are explicitly saved. **No version
selector action is needed.** Both URLs are present, exactly equal and use HTTPS
api.revenuecat.com with its Apple server-notification endpoint path. Opaque
endpoint identifier withheld. Exact app-specific dashboard destination association
is still based on the owner's supplied confirmation: no independently retained
exact URL or RevenueCat public API field was available for that comparison.
Actual delivery remains unverified; no notification test was requested.

Book 3: Apple IAP **6822080809**, **shelf_book_3**, **NON_CONSUMABLE**,
**MISSING_METADATA**, reference name **Shelf book 3**; Family Sharing and content
hosting false. One **ar-SA** localization, state **PREPARE_FOR_SUBMISSION**, saved
name **څپو کې انڅورونه**, description **شعري ټولګه**. The name differs from the
recorded book title **څپو کې انځورونه**; preserve both as evidence and obtain final
owner wording before any later provider correction. No source text changed.

Price schedule: **USA/USD 2.99**, one manual base price plus **174 automatic
territory prices**, all customer-price/currency fields resolved, no explicit start
or end dates. Examples: Afghanistan USD 2.99, Canada CAD 3.99, UK GBP 2.99, Saudi
Arabia SAR 12.99. Available territories total **175**, including Afghanistan and
excluding Iran; **availableInNewTerritories=true**. This is distinct from the
approved Play territory list and does not establish expanded rights approval.
Final Apple territory/future-territory acceptance remains a public-release matter.

Review screenshot GET returned **data=null**; reviewNote null. The screenshot is
missing required review metadata, while notes are optional
([RevenueCat](https://www.revenuecat.com/docs/getting-started/entitlements/ios-products)).
Apple supplies no complete missing-field diagnosis, so do not claim this is the
only missing field. [Apple TN3186](https://developer.apple.com/documentation/technotes/tn3186-troubleshooting-in-app-purchases-availability-in-the-sandbox)
says sandbox testing does not require product review submission. Actual product
retrieval still needs the later real StoreKit flow; MISSING_METADATA is not a
proven sandbox failure. Keep the real purchase-screen review screenshot pending
the later build, with no fabricated substitute or screenshot request now.

All requests were bounded GETs scoped to Shelf and its existing product. One
availability query rejected an unsupported parameter (400); corrected separate
availability/territories GETs passed 200. No provider state changed. Completed
RevenueCat evidence and purchase implementation/tests were reused, not repeated.

**Next smallest batch, requiring separate authorization:** automate the distinct
Apple-scoped SANDBOX RevenueCat-to-Shelf webhook below, private server configuration
using app8ef018b95b / prod0d084e901f / existing SDK key with gate OFF, and exact
backend staging/backed-up reversible migration/promotion. No Apple product or
notification-version change is needed for that batch. Later separate build,
owner-only sandbox activation and phone purchase/refund/reversal checks establish
product retrieval and actual notification delivery. Keep final metadata/territory
acceptance and genuine review screenshot separate from backend preparation.

### Remaining setup after read-only inspection

Under separate authorization: confirm RevenueCat Track new purchases off and
Keep with original App User ID; create the one distinct Apple sandbox Shelf
webhook described above, preserving Play. Existing documented integration-write
access can automate that later action if still sufficient; no write probe or
permission expansion now. Private server configuration uses the verified IDs and
existing SDK key with Apple sandbox OFF. Backend staging/backed-up reversible
migration/promotion, later internal TestFlight build at a confirmed unused number,
explicit owner-only sandbox activation and actual purchase/restore/refund/reversal/
notification delivery acceptance remain pending. Deferred Apple sign-in/relay
acceptance is not required to use the existing verified owner email/password
account. DSA review remains unresolved for public release. All existing holds and
deferrals remain unchanged.

## Prepared single internal purchase build — 12 October local / 13 October 2026 UTC

Reviewed application/workflow source **dabedf552bd4b47b13e91baccbfac053141a3652**,
branch **ios/foundation**, workflow **ios-testflight-internal**, version **1.0.0**,
proposed build **4**. This preparation is documentation only; final documentation
revision retains that exact application/YAML. Later record the actual clone SHA
from source-commit.txt and validate any intervening source changes before running.
No build/upload, cloud setting edit or build-number reservation occurred.

Apple authenticated GETs on 13 October UTC: received builds **2 and 3 VALID**;
all upload records **1 FAILED / 2 COMPLETE / 3 COMPLETE**, IOS, 1.0.0.
Both bounded lists contain **no 4 and no pending upload**. Build 4 is the next
unused candidate from actual records; recheck both endpoints before the separately
approved run and stop on collision/concurrent upload. App is 6821471140 /
services.shelf.app. Existing protected ASC key reused; no key/JWT printed.

Signed Codemagic YAML passes parse/shell/inline-Python checks. Purchase dependency
is locked to purchases_flutter 10.13.2 and native In-App Purchase capability is
present. Production API/group settings, existing signing integration/certificate/
profile, photo-purpose check, testFlightInternalTestingOnly=true,
manageAppVersionAndBuildNumber=false, strict signing verification, ditto and
explicit $CM_BUILD_DIR artifact/evidence paths are intact. No omission confirmed,
no code/dependency/YAML fix or purchase suite rerun needed. iOS automatically
chooses sandbox; SHELF_INTERNAL_TEST_PURCHASES=false is compatible. The Apple SDK
key is supplied by the configured backend after activation, not a build variable.

For the later authorized run: use existing Shelf application and signed workflow
on the prepared ios/foundation revision; set only **SHELF_IOS_BUILD_NUMBER=4** in
existing **shelf_ios_testflight**, retain **SHELF_API_BASE_URL=https://shelf.services/api/**
and all signing/groups. Verify no competing run and exact source. Codemagic
account variables/queue are not independently visible through documented access;
no token search/creation is required now. After separately authorized upload,
inspect exact IPA/signing/source/version/build evidence and Apple processing, then
assign only to existing **Shelf Owner** internal group and install. No public or
external testing, sign-in activation or purchase activation follows automatically.

### Exact sandbox activation plan — NOT EXECUTED

The existing owner reader is already verified, buying allowed, and its email
exactly matches the one owner admin; this was checked privately without printing
email/ID. Use that reader's **email/password** in Shelf, not admin login or an
Apple/relay/new/plus-alias account. Apple sandbox access is only book **3 /
shelf_book_3**. No account creation or identity linking is required.

1. Obtain separate authorization after the new internal build is received and
   installed. Confirm no competing build/test and necessary RevenueCat dashboard
   tracking **OFF / Keep original App User ID** (not exposed by the public app API).
   Reuse verified provider setup; do not search for a notification-version selector.
2. As shelf, Codex runs **python3 -B scripts/shelf_daily_backup.py** in production,
   verifies its SQL/ZIP/checksums and makes a private encrypted .env checkpoint.
   Read current controls/mapping/webhook and exact owner eligibility privately.
   Require production revision **560ef50**, existing schema/migration, Apple app
   app8ef018b95b, product prod0d084e901f, project projbbce26da, protected V2 key,
   existing appl_ key and separate Apple authorization; no credential replacement.
3. Atomically edit only production **SHELF_APPLE_SANDBOX_ENABLED=false → true** in
   /home/shelf/apps/shelf/.env, retaining shelf ownership/0600 and all other values.
   Refuse duplicate fields, symlinks, concurrent modification or unexpected prior
   state. Run **php artisan config:clear** in that production directory.
   Keep **SHELF_REAL_PURCHASES_ENABLED=false**, **APPLE_SIGN_IN_ENABLED=false**,
   **APPLE_SIGN_IN_RELAY_READY=false**, Play sync disabled and staging untouched.
   Do not use configure_apple_sandbox.py prepare to activate; it is OFF-only setup.
4. Check actual runtime ApplePurchaseProof::configured()=true, real purchase
   productionEnabled()=false and sign-in/relay/sync false. GET
   **https://shelf.services/api/purchases/config?platform=ios** privately: enabled
   and sandbox_checkout_enabled true, production_checkout_enabled false,
   allowed_product_ids exactly [shelf_book_3], correct APP_STORE/numeric identities.
   Validate the key privately; never print the whole config response. Confirm
   staging remains disabled and other settings/Google integration unchanged.
   Stop/close gate on any mismatch; no transaction before explicit test approval.
5. On the owner's iPhone, sign in with that existing reader, refresh, open book 3,
   read sample and consent, and inspect the Apple sandbox sheet. Execute only the
   approved sandbox acceptance cases. Do not test other products or real checkout.

### Exact rollback/close-window plan — NOT EXECUTED

Codex atomically changes **only SHELF_APPLE_SANDBOX_ENABLED=true → false**, retains
permissions/unrelated values, then runs **php artisan config:clear**. Confirm
runtime proof disabled and the same iOS GET advertises enabled/sandbox/production
false and public_sdk_key null. The deliberately incomplete unauthenticated Apple
webhook guard must return 503 before any event/proof/database processing. Staging
stays 403/off; real checkout/sign-in/relay/sync remain OFF. Keep the Apple/Google
webhooks and private keys unchanged. Preserve all purchase/refund/consent/ledger
history, schema and downloaded/account data; never blanket restore SQL or run
migration down to close a test window. Existing Apple history cannot receive fresh
proof/offline leases while disabled; any unfinished reconciliation needs a later
explicitly authorized handling window. Gate closure is not proof of refund.

Phone acceptance still pending: actual product retrieval, server-confirmed paid
text/media/download, cancellation/pending locked behavior, same-account restore,
other-account denial, refund removal, refund reversal without a second sale/real
income, and Apple→RevenueCat→Shelf delivery/live REST shapes. No second-phone
restore request. Genuine review screenshot, title discrepancy/MISSING_METADATA,
final rights/territory/DSA/review acceptance remain pending unchanged.

**Next single action:** authorize one internal Codemagic build/upload **1.0.0 (4)**
from prepared ios/foundation, all gates OFF. Activation/transactions require a
separate later authorization. Existing holds and owner deferrals remain binding.

## Current disabled rollout and webhook state — 12 October local / 13 October 2026 UTC

**Backend deployed on existing staging and production at 560ef50ac35aa2db78492b1c7bfb92ee72a69f76.**
Purchase source/tests remain identical to 08a2adf; completed purchase checks reused.
Eight synthetic rollout-tool checks and actual migration/configuration/HTTPS/
isolation/history-preservation checks passed. Migration production batch **28**,
staging **11**, no pending migrations. Promotion backup
**/home/shelf/backups/shelf/20261013-000201/**; private configuration and original
unrelated production documentation/handler checkpoints in
**/home/shelf/backups/shelf/20261012-235938/apple-purchase-checkpoint/**. Exact
configuration/backup/stop evidence and rollback target are in Master Record.

All required production Apple IDs, existing SDK key and V2 path, and a distinct
private webhook authorization are saved; unrelated .env values and Google
integration unchanged. **Apple sandbox OFF, real checkout OFF, Apple sign-in and
relay OFF.** Staging has no production Apple purchase credentials. Public iOS
purchase config advertises false and exposes no SDK key; Apple events are
intentionally rejected **503 production / 403 staging** while disabled.

**Apple-scoped RevenueCat sandbox webhook configured: whintgr769cb03ff2**,
Shelf iOS sandbox, app8ef018b95b, environment sandbox, NON_RENEWING_PURCHASE /
CANCELLATION / EXPIRATION / REFUND_REVERSED. Actual saved destination:
**https://shelf.services/api/purchases/webhook?source=apple**. This supersedes the
base URL in the historical checklist. A create on Google's same base URL returned
409 with no Apple integration created; the distinct query URL reached the deployed
endpoint and create returned 201, followed by exact GET/list confirmation 200.
Google whintgr75a4892970, Both, unchanged. No broader provider uniqueness rule
assumed. Query text is a routing distinction, never authentication evidence.
Private authorization/file and sanitized integration metadata are shelf-owned/0600
outside Git, at /home/shelf/secrets/revenuecat-apple-sandbox-webhook-auth.txt and
revenuecat-apple-sandbox-webhook.json. No test notification was sent.

**Configuration is complete for the disabled backend; delivery/processing is not
verified or activated.** A later separate internal build/upload and explicit
owner-only sandbox activation must establish actual StoreKit retrieval and
purchase/restore/refund/reversal and notification delivery. Installed build 3
lacks purchase source. Confirm the next unused build number only in that later
batch. Tracking-off/original-ID restore dashboard confirmation remains necessary
before activation where API access cannot expose it; do not recreate credentials,
products or search for a version selector.

Title discrepancy and missing genuine review screenshot remain pending unchanged.
MISSING_METADATA is recorded, without claiming all missing fields diagnosed or
actual sandbox product retrieval established. Final Apple localization/territory/
rights/DSA/review matters remain separate public-release work. No product titles,
prices or territories changed. Current holds/deferrals remain in force.

The operator helper follow-up 598ae45 only adapts provider URL configuration;
purchase application source remains deployed 560ef50. Further documentation
commits are not new deployed revisions. Scripts/ios/configure_apple_sandbox.py
is narrowly scoped: prepare makes a fresh verified backup/encrypted checkpoint
before private configuration; webhook requires matching staged/promoted evidence
and disabled guards, creates only a distinct Apple sandbox integration, refuses
unrecognized existing integrations and verifies all unrelated integrations.
Do not blindly rerun preparation or replace private files after an interrupted
run; inspect its checkpoint and preserve the existing authorization.

## Historical rollout plan and later phone checks — completed rollout superseded above

Keep `SHELF_APPLE_SANDBOX_ENABLED=false`, real checkout OFF and both Apple sign-in
flags false. A later approved rollout must stage the exact commit with synthetic
isolated evidence, back up production, apply the committed reversible migration
`2026_10_12_120000_add_purchase_event_store_scope`, then promote that same verified
commit through the existing deployment process. No running migration occurred.
The migration defaults old events/consents to Play and adds Apple event scope and
millisecond purchase proof. Rollback is reversible before Apple history/consents;
with Apple records it refuses lossy removal and needs a preservation plan.

Prepare these values privately without activating the gate:

| Server field | Value source |
| --- | --- |
| SHELF_APPLE_SANDBOX_ENABLED | false until separately authorized owner-only sandbox activation |
| SHELF_REVENUECAT_PROJECT_ID | Existing Shelf project `projbbce26da` |
| SHELF_REVENUECAT_IOS_KEY | Actual `appl_` public SDK key for Shelf iOS, from RevenueCat Project Settings → API keys |
| SHELF_REVENUECAT_IOS_APP_ID | Generated RevenueCat Apple app ID, not Apple ID `6821471140` or the Play app ID |
| SHELF_REVENUECAT_IOS_BOOK3_PRODUCT_ID | RevenueCat's internal Apple product ID, not the store identifier |
| SHELF_REVENUECAT_IOS_WEBHOOK_AUTH | Exact new private Apple webhook Authorization value |
| SHELF_REVENUECAT_V2_SECRET_KEY_PATH | Existing protected V2 server key file after required read permissions are verified |

No SDK key/dashboard variable changes are required in Codemagic for this batch;
iOS obtains the public app key from the server after configuration. No secrets go
in builds. Preserve the verified signing/photo checks, `ditto` extraction,
internal-only export and explicit `$CM_BUILD_DIR` IPA/evidence paths. This source
needs a later new internal TestFlight build; installed build 3 does not contain
it. Confirm the next unused build number before that later build; no number is
reserved or dashboard value changed here.

On an owner-controlled iPhone, after separate rollout/build/sandbox authorization:
sign in with the existing verified owner Shelf account, open book 3, read its
sample, agree, inspect the Apple sandbox sheet and complete one authorized test.
Confirm server ownership, full paid text/media and download, cancellation/pending
behavior, same-account restore, another account's denial, refund removal and
refund reversal restoration without a second sale or real income. Validate actual
Apple notification → RevenueCat webhook delivery and live V2 response shapes;
synthetic tests do not prove those. Keep each pending result separate from
source verification. A recovery-after-deletion Apple phone test must wait for
reviewed recovery proof; it is not supported. Second-phone restore remains
owner-deferred and is not newly requested.

Checkout OFF, pricing-sync/second-phone-restore deferrals, Apple sign-in/relay
owner deferrals, stopped relay/email task, Android/UI pause, deferred small
improvements and joint Android/iOS public-launch hold remain in force.
