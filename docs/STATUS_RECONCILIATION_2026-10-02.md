# Shelf status reconciliation — 2 October 2026

## Later accounting construction update

The owner subsequently approved the recommended accounting task. The missing
6.9/D10 owner workflows are built: Accounting journal, Author balances and Sales
ledger source-history filters/links. Unknown net figures remain unknown; Test is
excluded; adjustments and payments/reversals are append-only. No real payments
or automatic payouts are enabled. See [accounting tools and verification](ACCOUNTING.md).
Accounting was deployed at 5ff131f and remains in deployed/pushed 5f75fa0.
OneDrive encrypted upload/download/decryption and isolated recovery passed on
3 October (34 tables, 774 files). The first unattended run failed after upload
on 4 October; the manual run is still the last success. Actual alert delivery
and replacement-host recovery remain unverified. Product sync 403 is unresolved;
second-phone restore is deferred, not passed. Neither Step 4 nor Step 5 is complete.
Next building priority: repair unattended backup/recovery (Step 5 / 6.11).
See OFFSERVER_BACKUP.md and Master Record Part 10 for current evidence.

The remainder below is the historical documentation-only reconciliation before
that implementation; its "not begun" recommendation and Git ancestry warning
have been superseded by the approved accounting branch merge and delivery.


Documentation-only task. Governing sources: AGENTS.md, Master Record v2.3 and
owner addenda, including the current construction-priority direction. No deploy,
rebuild, new implementation, purchase, refund, provider poll or production data
mutation; no broad automated suites rerun. Code inspection and one completed
read-only database snapshot were used. An initial snapshot query used nonexistent
entitlement timestamp columns, returned no report and was rolled back; the
corrected snapshot used the committed checked_at schema. No server defect or
schema change was involved.

## Owner-confirmed phone evidence

These are the owner's 2 October Google Play phone results, not automated results:

- Refund: book removed from My Library; app reported download removed; paid
  content locked again; free sample still worked.
- Declined test payment: book remained locked.
- Successful repurchase: book returned to My Library; paid poem opened.

Second-phone restore: **DEFERRED to pre-release / NOT PASSED**. Do not request it
now. These results do not establish second-phone restore, all offline expiry
cases, all book/language layouts, or an owner visual check of admin badges.

## Focused server findings

Snapshot: **2026-10-02 04:52:11 UTC**, reader **3**, book **3** / shelf_book_3.
Reader buying permission is Allowed. No credentials, raw order IDs or purchase
tokens are recorded here; database IDs identify the history.

| Record | Time UTC | Finding |
| --- | --- | --- |
| Purchase 5 / event 17 | Purchase 00:07:30; purchase event 00:07:33 | PLAY_STORE / SANDBOX; NON_RENEWING_PURCHASE. Provider timestamp retained; it differs from the owner's approximate first-purchase clock time. |
| Ledger 13 | 00:07:30 | Original sale USD +2.990000, Mode Test. Historical earnings_status unknown is retained; estimates/owed/payments are null and no real income is counted. |
| Event 18 / ledger 14 | 04:27:08 | GOOGLE_VOIDED_PURCHASE, source google_voided_purchases, matched by order_id; one refund USD -2.990000, Mode Test, earnings_status test. This is provider void time, not proof of the exact polling execution time. |
| Purchase 6 / event 19 / ledger 15 | Purchase 04:45:21; event 04:45:25 | Separate SANDBOX repurchase; NON_RENEWING_PURCHASE; USD +2.990000, Test, earnings_status test. |
| Current entitlement | checked/updated 04:45:55 | Active for reader 3/book 3 after repurchase. Original refunded purchase remains in immutable history. |
| Income boundary | snapshot | Ordinary SalesLedger query returns **0** real-income rows for these purchases. Every history row is Test; no author income, confirmed owed or payment is created. |
| Agreement snapshot | all three ledger entries | Agreement 1: 100% to author 1 (Ajmal), net amount received, effective 1 October; retained on refund and repurchase. This is an agreement snapshot, not a calculated or payable Test author share. |
| Latest scheduled refund audit | finished 04:45:01 | Completed HTTP 200; seen 1, duplicate 1, refunded 0, unmatched 0, conflict 0. Repeated polling did not duplicate the refund. |

The record sequence supports refund then repurchase. There are two verified
purchases and one refund, with no additional successful purchase/event in this
reader/book history. A declined store attempt is not expected to create a
confirmed sale; the phone outcome is the evidence for that decline. No new API
request for paid text was made: paid-reading/sample outcomes above are owner
phone evidence, combined with existing server access tests and current entitlement.

## Were the requested fixes delivered?

**Ownership refresh: implemented and deployed.** Inspected
mobile/lib/purchases/library_controller.dart: confirmed purchase requests
library/confirm, updates books and notifies listeners before manifest persistence.
LibraryScope is an InheritedNotifier; collection_detail_screen.dart depends on
it and changes Buy book to Open owned book when ownership is confirmed. Startup
identity load calls refresh; WidgetsBindingObserver handles resumed; failures
publish clear do-not-buy-again messages. Store success alone cannot unlock.
The owner's repurchase result supports automatic Library/reading recovery, but
no stronger claim about measured UI timing is made. Existing library_ux_test.dart
covers notification, download and error states; not rerun for this documentation task.

**Admin Allowed/Blocked: implemented and deployed.** Inspected
app/Filament/Resources/Readers/ReaderResource.php: TextColumn buying_blocked,
label Buying, badge, Allowed/success (green) or Blocked/danger (red). The old
boolean icon is absent. Commit e4a286f delivered this; 69b4690/d12a770 delivered
Library/ownership UX. Current deployed backend source 2f0c2bf contains both.
Installed app behavior is evidenced by the owner, not inferred from a build alone.

## Unfinished BUILDING work

| Established plan | What remains to build/integrate | Evidence and boundary |
| --- | --- | --- |
| Step 4 / 6.4 and 6.9 | Production-mode verified sales path and complete sales/author accounting: estimates, confirmed amounts owed, manually recorded payments with date/reference, adjustments and per-currency owner reporting. | PurchaseService::receive accepts SANDBOX only; RevenueCatClient confirms sandbox only. Agreements/snapshots and ledger history exist; Sales admin lists fields, but no complete settlement/owed/payment recording or financial reporting workflow exists. Do not interpret intentional null Test earnings as a defect or enable real payments during construction. |
| Step 4 / D9 | Finish automated admin-to-Play product/price integration across sellable books and safely enable its dedicated runner after success. | Implementation/mock checks exist. Last real price-conversion diagnostic was HTTP 403; original 400/409 product error is separately unresolved. Scheduled product/price sync remains disabled; books 4–8 last recorded Pending. Successful book 3 buying and Voided Purchases HTTP 200 do not prove product/price permissions. No retry or new Google call here. |
| Step 5 / 6.11 | Regular off-server backup and a repeatable recovery workflow with simple owner instructions. | Daily verified on-server backups exist; historical baseline PC copy is not a regular off-server backup. Current backup script performs local backup/verification/retention, not remote copying or database recovery. A tested isolated restore must follow construction of this workflow. |
| Step 5 / D14, 6.10 | Complete retention/deleted-account policy implementation and release-ready privacy/support wording after the owner decides D14. | Account lifecycle/deletion pages exist; retention durations remain an owner decision. Never invent financial/security retention or delete records during reconciliation. |

Staging separation/release scripts, catalogue/authors/categories/search, samples,
accounts, sandbox Library/offline and refund detection are implemented; they
should not be rebuilt solely because historical Part 10 is dated 28 September.
Author accounts, automatic payouts, iOS and subscriptions remain outside Version 1.

## Pre-release checks and owner decisions (not construction gates now)

- **Second-phone restore: deferred, not passed.** Carry it forward in the checklist;
  no phone request now and no declaration that all Step 4 cases are accepted.
- Preserve uncompleted acceptance for real Pashto Word import/phone reading,
  Pashto/Farsi poetry/prose/mixed text, fonts, samples/audio and final UX. Existing
  features are built; missing owner acceptance evidence is not missing implementation.
  Request targeted checks only when they resolve an essential concrete risk.
- Automate remaining account isolation, restore ownership mapping, withdrawal,
  failed/pending/cancelled payment and 30-day offline/clock/sign-out cases when
  relevant to a change; reuse existing evidence rather than repeat broad suites.
- Production Play listing/signing-key Google OAuth setup, production provider
  permissions/configuration, privacy/support/deletion/retention review and final
  owner acceptance belong before launch. No real-money sale is authorized now.
- D14 retention and D16 final launch catalogue/rights remain owner decisions.
  D7–D13 and D15 are decided in later addenda even where the original decision
  table still says Open. Unknown original poet of book 8's لمر ګلی stays unresolved;
  never guess or alter source. Old app retirement requires its planned owner-approved
  release-stage work; it remains untouched.
- Exercise the off-server recovery workflow after it is built; a ZIP/checksum pass
  is not proof of recoverability. Obtain launch acceptance only at release readiness.

## ONE recommended next building task

**Finish Step 4 sales and author-share accounting (Master Record 6.9).**
Build immutable financial entries and owner views separating estimated earnings,
confirmed amounts owed and manually recorded payments by currency, with agreement
versions, net/gross basis and deductions preserved. Keep unknown store fees/taxes/net
amounts unknown or provisional until actual settlement evidence exists. Include
refund/adjustment handling and Test exclusion throughout. Prepare the real-sale
verification path using isolated fixtures/staging, while keeping live purchases
sandbox-only until explicit real-money approval. No automatic payouts.

This recommendation is within the established Step 4 plan, addresses unfinished
construction rather than demanding another phone test, and does not depend on
first resolving the product-sync 403. Implementation is not begun or approved
by this documentation task.

## Documentation-only Git boundary

GitHub main was a624ea7 at inspection; deployed source is 2f0c2bf with newer
implementation ancestors not yet pushed. This documentation branch is based on
GitHub main so the push contains only AGENTS.md/docs changes. Documented source
references describe deployed source and may be absent from GitHub main until an
explicitly approved code push. No merge, deployment or rebuild of this documentation
branch into the running production checkout is performed. Reconcile that Git
ancestry before the next implementation release; never overwrite deployed code
with the older-code documentation branch.
