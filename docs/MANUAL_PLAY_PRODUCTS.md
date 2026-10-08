# Manual Google Play products — first 100 Shelf books

> Production server verification construction (7 October 2026) is now built
> behind an OFF-by-default gate. Synthetic focused tests pass; no actual real
> purchase or launch approval. See [verification and remaining launch gates](PRODUCTION_PURCHASE_VERIFICATION.md).
> Earlier sandbox-only implementation/next-construction statements are historical;
> manual Play pricing remains approved and automatic sync remains deferred.

Owner decision, 7 October 2026: products, prices and availability are managed
in Play Console. Automatic sync **DEFERRED / NOT COMPLETED**, disabled; stop
403 investigations/retries. This supersedes earlier admin-only price instructions.
Shelf admin records the approved USD reference; Google supplies checkout prices.
No products/prices/availability or permissions changed by this task.

## One owner checklist — selected launch books 3–8

Owner approves all six existing books for initial launch at **USD 2.99 each**;
individual prices may change later. All are paid books with free samples.
Shelf references verified 8 October 00:39:22 UTC already equal USD 2.99; no admin
save needed. Credits, agreements, purchase history and mappings are unchanged.

**Play status last verified 7 October 2026, 20:10:10 UTC.** Fresh read-only list on
8 October returned HTTP 503; no retry/write. Check Console before creating anything.

| Book | Exact title | Exact existing product mapping | Buy-option ID | Approved USD price | Play status (last verified) |
| --- | --- | --- | --- | --- | --- |
| 3 | څپو کې انځورونه | shelf_book_3 | buy (existing) | 2.99 | Exists; buy ACTIVE; US USD 2.99 AVAILABLE |
| 4 | هېندارې او چینې | shelf_book_4 | buy (planned; not created) | 2.99 | Missing |
| 5 | د زړه پر پاڼه مې انځور دی ګلاب | shelf_book_5 | buy (planned; not created) | 2.99 | Missing |
| 6 | سيند په پرخه کې | shelf_book_6 | buy (planned; not created) | 2.99 | Missing |
| 7 | دا ښار، هاغه غرونه | shelf_book_7 | buy (planned; not created) | 2.99 | Missing |
| 8 | دلته ډېر لرې له غرونو | shelf_book_8 | buy (planned; not created) | 2.99 | Missing |

Product IDs are existing Shelf/RevenueCat mappings, not newly invented IDs.
The missing products' buy-option IDs are planned creation values using the existing
buy convention; they do not assert that those options already exist. Preserve
shelf_book_3 and all existing products/options; never create duplicates.
Regions and outstanding rights remain separate owner decisions. Existing US
availability does not approve launch regions. Catalogue/price approval does not
authorize release or enabling real checkout; automatic sync remains DEFERRED.

## Next concrete owner action

Play Console → **Shelf (services.shelf.app)** → **Monetize with Play → Products →
One-time products**: check **shelf_book_4** is absent, then prepare its draft:
**هېندارې او چینې**, product **shelf_book_4**, Buy option **buy**, **USD 2.99**.
Keep legacy compatibility enabled and multi-quantity disabled for permanent books.
Leave launch regions/activation pending separate approval. If the product already
exists, reuse it and check its mapping instead. Repeat for the other missing rows.
Keep each existing RevenueCat product attached to its same-named entitlement.
No app upload, publication, provider write or price change was performed here.

[Google one-time product guidance](https://support.google.com/googleplay/android-developer/answer/16430488).
Shelf admin reference changes do not update Play; review prices/availability
manually in Console. Google's localized checkout price remains authoritative.

## Temporary permission removal — tracked, not done

Owner-reported ACCOUNT-level grants added around **2026-10-05 00:38 UTC** for
shelf-play@shelf-510123.iam.gserviceaccount.com:

- View app information and download bulk reports.
- View financial data, orders, and cancellation survey responses.
- Manage orders and subscriptions.
- Manage store presence.

Status: **REMOVAL PENDING OWNER CONFIRMATION**; current grant state not reread.
Owner: Play Console → Users and permissions → existing service account →
Account permissions: remove only these four temporary grants, save. Preserve
Shelf/services.shelf.app app-level View app information, View financial data,
Manage orders and subscriptions, and Manage store presence. Do not remove the
user/credential or re-invite. Record confirmation date after removal; continue
using existing credentials for RevenueCat and refund verification. Agent has
not changed permissions and will not resume pricing diagnostics.

## Implementation and remaining construction

Checkout already uses RevenueCat StoreProduct.priceString from Google, and Buy
is disabled until the exact product is returned, consent given and account ready.
The controller checks shelf_book_<book-id> before purchase; server consent checks
publication/account eligibility, and independent server confirmation grants access.
No purchase, acknowledgement, entitlement, refund or accounting logic changed.
Missing Play products cannot supply checkout products; catalogue publication alone
is not evidence of purchase availability.

Manual mode blocks new sync intents, enqueue, retained jobs and direct sync;
historical sync metadata remains untouched. Admin shows Manual and DEFERRED,
not old successful/pending sync as a current result. Approved reference-price
changes retain normal audit entries. Credentials and refund GETs are preserved.
No schema or scripted data change; cPanel handler and prior uncommitted records
are preserved. Focused tests: 52 tests, 375 assertions in isolated checkout.
Staging/promotion evidence is recorded separately after actual deployment.

Production verification/release purchase support and the production AAB are
prepared; do not reopen that construction. D14 financial retention remains
unresolved; live provider erasure and targeted phone/launch/recovery acceptance
remain unverified/deferred. Steps 4 and 5 remain open; real checkout OFF.
