# Dynamic future-book purchases (development source only)

Branch: purchases/dynamic-books-20261019. No deployment, app build, upload, live
store edit or release-branch merge is authorized in this batch.

## Cause and source boundary

The Flutter LibraryController had a shelf_book_[3-8] regular expression. Apple
config, consent, proof mapping and event routing used ApplePurchaseProof::PRODUCTS,
a six-element list. Build-9 preparation source is
b6339e3bef627f26293a3ba24bde8ab4f25d589f (ios/foundation); its purchase files match
the pre-change main source. Its build-9/iPhone-only native/workflow source remains
unchanged. Apple under-review status is owner-reported; no provider audit repeated.

## Reused purchase flow

1. Admin creates a book, generating its permanent shelf_book_ID mapping. The public
   catalogue includes product_id for existing clients and store_product_ids keyed
   by APP_STORE and PLAY_STORE. Both stores currently use the same permanent SKU;
   RevenueCat's internal prod… identifier is not a store SKU. No new SKU convention,
   schema or historical mapping change is introduced.
2. The client selects its platform's mapping, validates the book identity and
   requests that SKU with Purchases.getProducts and ProductCategory.nonSubscription.
   Apple also checks the fresh catalogue-derived server allowlist. Old responses
   without the new map use the existing shared product_id; an explicit missing
   platform mapping stays unavailable.
3. Exactly one matching StoreProduct, a finite positive store price and its nonempty
   localized priceString are required. Details hide Buy until lookup succeeds;
   missing/unavailable products show availability text. The checkout screen also
   has no Buy button without a matching product. No Admin reference-price fallback.
4. Existing authenticated consent and store/environment gates remain. Purchase uses
   PurchaseParams.storeProduct with the selected native product; native ID mismatch
   is rejected. Identity checks survive asynchronous account changes.
5. Existing server reconciliation grants access only after independent provider
   proof. Apple maps a canonical SKU to an actual database book, then checks the
   configured Apple app/project, non-consumable type, exact reader, transaction,
   purchase time, environment and entitlement. Unknown/noncanonical/global-unlock
   IDs fail closed. New sales still require a published paid book and consent.
6. Restore/refunds retain withdrawn/binned mappings and the original transaction
   identity; numeric customer identities, sandbox/production separation and money
   history stay intact. The legacy owner-only Book-3 session-test path remains
   intentionally scoped to Book 3; it is not the ordinary customer purchase path.

RevenueCat supports this existing direct-product API:
[Making Purchases](https://www.revenuecat.com/docs/getting-started/making-purchases)
and [SDK source](https://github.com/RevenueCat/purchases-flutter/blob/main/lib/purchases_flutter.dart).
No SDK upgrade or default offering is needed.

## Owner workflow after delivery

Prepare and preview a book in Admin as before. Register its exact permanent SKU
in each relevant store and RevenueCat, attach its per-book entitlement, set store
price/regions and obtain the required approval. Publishing changes the shared
catalogue; it does not perform store setup or prove approval. Missing products
remain unavailable to buy. Store availability and actual transaction proof are
separate checks; a sandbox-returned product does not prove production approval.

The current installed releases do not acquire this change from a server update.
One future app update and a separately approved backend staging/production rollout
are needed. After delivery, ordinary future books do not require Flutter changes
or an app rebuild per book. This batch makes no payment or phone acceptance claim.

## Verification

Focused PHP: 89 tests / 1,005 assertions passed; final customer rerun 15 / 241.
Flutter: 41 purchase/widget tests and 1 staging-identity test passed via
scripts/run_tests.sh --mobile. SDK calls are mocked; no store transaction or
release binary was created. Analyzer has no errors/warnings, with existing-style
informational lints retained. IDs 9, 42, 125 and 1001 work through dynamic lookup,
selected product purchase and server proof. Unknown/unavailable/global-unlock
products stay protected. Existing six-book, account, restore/refund and accounting
checks pass. Main, ios/foundation and apple/review-sandbox remain unchanged.
