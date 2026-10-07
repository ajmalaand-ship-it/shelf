# Production purchase verification — constructed, real sales disabled

Owner authorization: 7 October 2026. Build/test the production verification path;
keep real purchases off. Manual Play products/prices/availability for first 100
books remain approved. Automatic sync DEFERRED / NOT COMPLETED; 403 work stopped.

## Production webhook coverage preparation — 7 October 2026

Read-only RevenueCat V2 list on 7 October 2026,
confirmed exactly one webhook for the existing URL:

- Project **Shelf** (`projbbce26da`); Google Play app `appf83cd58c5c`,
  package `services.shelf.app`. Runtime app mapping matches.
- Existing webhook **Shelf sandbox book purchases** (`whintgr75a4892970`),
  URL `https://shelf.services/api/purchases/webhook`.
- Environment **sandbox**; filters `non_renewing_purchase`, `cancellation`,
  `expiration`. Current dashboard change is **PENDING OWNER ACTION**, not done.

**Owner action — one edit:** RevenueCat → Shelf (`projbbce26da`) → Integrations
→ Webhooks → edit **Shelf sandbox book purchases** (`whintgr75a4892970`). Keep
app scope `appf83cd58c5c` / `services.shelf.app`, URL and existing Authorization
unchanged. Set Environment to **Sandbox and Production / Both**. Keep event
coverage **NON_RENEWING_PURCHASE, CANCELLATION, EXPIRATION**, then Save.
Use this existing webhook; no duplicate, credential rotation or app-scope change.
Rollback is the same webhook's Environment back to Sandbox; no history deletion.

RevenueCat documents environment/app/type filters and identifies non-renewing
purchase and cancellation/refund events:
[webhook settings](https://www.revenuecat.com/docs/integrations/webhooks),
[event definitions](https://www.revenuecat.com/docs/integrations/webhooks/event-types-and-fields).
Shelf has permanent per-book non-consumables, not subscriptions; retain the
existing expiration/revocation coverage without adding unsupported lifecycle or
transfer events. Existing 15-minute Google voided-purchase polling is preserved.

Local endpoint `POST /api/purchases/webhook` already checks configured credentials,
exact Authorization with hash_equals, validated fields, numeric reader identity,
exact app/PLAY_STORE and SANDBOX/PRODUCTION environment. New sales require server
REST evidence to agree; failed/unknown evidence never unlocks. Event/transaction
conflicts and duplicates preserve append-only history. With real-sale gate OFF,
new PRODUCTION NON_RENEWING_PURCHASE is rejected (422, no unlock/sale), whereas
CANCELLATION/EXPIRATION for an existing matching real purchase still process,
including deleted-reader history. Broadening delivery does not enable checkout
or real-sale acceptance. Staging still rejects all webhooks.

No implementation change needed. Existing 340634b staged/promotion evidence
(62 PHP / 474 assertions and 20 Flutter checks) already covers authentication,
gate-off real-sale rejection, sandbox confirmation/duplicates, verified real sale,
refunds with sales off/deleted readers, restore/isolation and immutable accounting.
Reviewed evidence and code; no repeated test suite, synthetic live delivery,
Android build/upload, product/price/provider write, permission or secret change.
No production delivery/configuration success is claimed until the owner saves
and a bounded read-only check confirms the setting. A real purchase/delivery
acceptance test remains separately launch-approved. Runtime release stays 340634b;
this task only synchronizes documentation, no application rollout or DB change.

### D14 and D16: construction versus launch

- **D14 = account deletion and data retention.** Owner decisions still needed:
  how long sales, security and deleted-account records are retained, and what
  deletion means for restoring purchases (including any retained identity link
  and how that is explained to readers). These block the corresponding retention/
  deletion-policy implementation and final privacy wording. Existing account
  deletion keeps financial history; do not invent retention periods or change it.
- **D16 = final launch catalogue**, the choice of books included at launch. It is
  not a webhook/environment switch or a missing generic payment feature. It
  blocks final book-specific launch preparation/acceptance, not reusable purchase
  construction. Known rights/credits, samples, store availability and mappings
  must be confirmed for the chosen books. Launch prices/regions need their own
  owner approval under D9/payment setup; USD 2.99 on books 3–8 is TEST ONLY.
- Neither decision blocks this webhook preparation or already-built production
  verification/Android source. Both must be resolved before public launch; launch
  approval, approved release build, manual catalogue completion and targeted
  acceptance remain separate. Last verified Play inventory: shelf_book_3 exists,
  shelf_book_4 through shelf_book_8 absent. Do not infer all six must launch.

Automatic sync DEFERRED / NOT COMPLETED; 403 work stopped. Steps 4 and 5 remain
open. Second-phone restore remains DEFERRED / NOT PASSED; grant-removal owner
confirmation and targeted recovery/phone acceptance remain unchanged.

## Verification and controls

- PurchaseWebhookController still authenticates the existing provider authorization
  before processing, validates fields and rejects other apps/stores/identities.
  No mobile request can supply an environment that grants access.
- Server-only RevenueCat GET still requires the exact original reader identity.
  The matching book's non-subscription transaction must have a valid timestamp,
  play_store and a strict boolean is_sandbox. True is SANDBOX; false PRODUCTION.
  The authenticated webhook environment must agree. Missing/non-boolean/conflicting
  classification, invalid dates, ambiguous matches, wrong reader/product mapping,
  promo/global entitlements, refunded transactions and provider failures fail closed.
- API v1 non-subscription IDs are RevenueCat IDs rather than Google orders. Retain
  the existing authenticated-event store/product/time correlation, within one second;
  require a unique REST time match. Do not claim independent Google-order-ID
  verification from that API. Ambiguous matches require investigation, not guessing.
- Purchases are keyed by store/environment/transaction with reader/book conflicts
  rejected; event IDs and append-only sale/refund keys prevent duplication.
  Reordered refund tombstones are scoped to reader/book/environment/transaction.
- `SHELF_REAL_PURCHASES_ENABLED` defaults false; exact boolean true required.
  Staging forces it false and rejects real events even if overridden in-process.
  Server configuration exposes separate production/sandbox checkout availability.
  Consent rejects production checkout while the real gate is OFF, before payment.
  Explicit sandbox checkout is owner-only, including when real sales later open.
  Checkout mode authorizes an operation; verified provider evidence alone labels
  transactions. Legacy installed owner-test clients retain their consent behavior.
- Existing recorded real purchases remain verifiable/restorable if buying is later
  disabled; their authenticated refunds/revocations still work, including deleted
  accounts. Malformed/outage rechecks do not renew offline leases or append guesses
  about revoked ownership. Confirmed absent/refunded provider evidence still revokes.

## Money and acknowledgement

Real sale currency/amount come from the authenticated provider event, not the admin
reference price or client. Capture the applicable historical agreement unchanged.
Store fees/taxes/net received, estimates, confirmed owed and payments stay unknown
until actual supporting figures exist. No invented net amount or automatic payout.
Sandbox rows remain Test and excluded by ordinary SalesLedger and accounting queries,
even if the real-sale gate is enabled in an isolated test. Refunds are new entries.

Existing RevenueCat SDK purchase/restore calls and automatic acknowledgement are
preserved; no direct acknowledge/consume call added. Google's existing 15-minute
voided-purchase poll remains independent of disabled product-price synchronization.
No production SDK acknowledgement or charged purchase is claimed tested here.

Provider references checked 7 October 2026:
[webhook authentication and environments](https://www.revenuecat.com/docs/integrations/webhooks),
[REST customer fields](https://www.revenuecat.com/docs/api-v1/customer-info-model),
[automatic completion](https://www.revenuecat.com/docs/getting-started/making-purchases),
[non-consumable configuration](https://www.revenuecat.com/docs/getting-started/entitlements/android-products).

## Focused verification and rollout

61 PHP tests / 461 assertions through scripts/run_tests.sh with a focused filter:
BookPurchasesTest, VoidedPurchasesTest, AccountingTest, PlayPriceSyncTest.
Synthetic provider data tests gate default/client flags, staging override rejection,
real versus Test classification, malformed dates/flags/outages/refunds, localized
EUR amounts, immutable agreements and unknown deductions, duplicate/conflicting
reader/book/environment/event IDs, refund-before-sale, restore, direct paid-content
access/isolation, deleted-account refunds, and unknown rechecks preserving history.
Existing sandbox, refund polling, accounting and manual-sync tests pass.
No mobile files changed; no Flutter/phone/broad-suite rerun.

Use committed staging workflow deploy/check/promote. It records focused test log
checksum and exact commit; staging isolation/HTTPS and purchase-gate checks must
pass. Production promotion makes a fresh verified SQL/ZIP/SHA-256 backup, verifies
real gate OFF and previous reader/payment/agreement fingerprints unchanged,
checks health/access, and preserves the cPanel handler. Exact successful commits,
backup path and timestamps are in /home/shelf/staging-runtime/release-state.json.
No migration/data rewrite is needed. Undo code via approved staging promotion of a
reviewed rollback; retain all history. Backup restoration must not overwrite newer
reader/money activity.

## Required before real sales

1. Explicit owner launch/real-money approval; this construction authorization does
   not permit real payments, public release or changing the server gate.
2. Build and validate the implemented release-mode Android source in a separately
   approved release task. No APK/AAB was built or installed in this task. Public
   release builds must omit SHELF_INTERNAL_TEST_PURCHASES (default false); existing
   owner-preview/internal-test build scripts explicitly opt in to license testing.
3. Manually complete/review launch book products, prices, regions and non-consumable
   RevenueCat products/entitlements with exact IDs; verify checkout availability.
   No Console/provider product change is performed here; last inventory remains
   book 3 present, 4–8 missing, until a later verified owner update.
4. Review existing authenticated app-scoped webhook to include production events
   using the exact owner edit above (still sandbox-only until saved/verified). Preserve credentials,
   correct identities, refund polling and acknowledgement; verify launch operations.
5. Complete targeted pre-release acceptance: signing/OAuth/listing, privacy/support/
   deletion and owner D14 retention policy, launch catalogue/rights D16, remaining
   phone reading/import/offline/account/withdrawal checks, second-phone restore
   (DEFERRED / NOT PASSED), backup alerts and recovery acceptance.
6. Only the approved launch rollout may enable the real-sale server setting and
   perform the explicitly approved real purchase/refund checks. Never enable it
   on staging. Steps 4 and 5 remain open.

Temporary four account-level Play grants still await owner removal confirmation;
app-level access and credentials preserved. No permissions/prices/products changed.


## Android release construction — 7 October 2026

Source now supports production checkout without requiring test_mode=true. Default
builds refuse new checkout unless the fresh server production gate is true. Before
calling the SDK, fetch config again, check availability, then authenticate consent
for the exact reader, canonical book product and checkout mode. Rejected/malformed
consent, unavailable products, wrong mappings and account changes stop checkout.
Google's priceString remains the displayed price; server confirmation alone unlocks.
Restore uses SDK configuration independently of new-sale permission. Purchase,
acknowledgement, refund, offline and accounting logic remain unchanged.

Existing internal-test AAB/owner-preview scripts explicitly set
SHELF_INTERNAL_TEST_PURCHASES=true; staging selects test checkout and enforces its
staging_ identity. This flag cannot make a charged transaction Test, alter server
verification or enable real-sale acceptance. Owner license-test checkout still
requires Google's test payment methods: an internal test track alone does not
prove a sandbox payment. See [Google testing guidance](https://developer.android.com/google/play/billing/test).
No new test app, release upload, provider changes or phone tests. Installed Android
apps are unchanged; only committed source and server availability/consent API are
updated. The disabled real-sale gate cannot authorize release-mode checkout.

Evidence: 62 PHP tests / 474 assertions, 19 focused Flutter tests plus one
staging-identity check passed through scripts/run_tests.sh --mobile.
Focused checks cover default release denial, allowed synthetic production checkout,
stale gate denial before SDK purchase, failed/mismatched consent, localized prices,
small/large screen buy controls, restore while buying is off, reader isolation,
refund/download removal and server-only unlocking. Staging workflow records exact
results and backup in release-state.json; no schema/history edits. Real provider
checkout remains untested until an explicitly approved release acceptance task.
