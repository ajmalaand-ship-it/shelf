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

**Verified 8 October 2026 at 03:21:47 UTC (7 October local).** One read-only
Google oneTimeProducts list returned HTTP 200, with no further page; no retry.
Owner screenshot independently confirms six products with one active option.
The earlier books 4–8 missing status is superseded.

| Book | Exact title | Product / entitlement lookup key | Verified buy-option ID | Verified US price | Verified Play status |
| --- | --- | --- | --- | --- | --- |
| 3 | څپو کې انځورونه | shelf_book_3 | buy | USD 2.99 | ACTIVE; US AVAILABLE |
| 4 | هېندارې او چینې | shelf_book_4 | buy | USD 2.99 | ACTIVE; US AVAILABLE |
| 5 | د زړه پر پاڼه مې انځور دی ګلاب | shelf_book_5 | buy | USD 2.99 | ACTIVE; US AVAILABLE |
| 6 | سيند په پرخه کې | shelf_book_6 | buy | USD 2.99 | ACTIVE; US AVAILABLE |
| 7 | دا ښار، هاغه غرونه | shelf_book_7 | buy | USD 2.99 | ACTIVE; US AVAILABLE |
| 8 | دلته ډېر لرې له غرونو | shelf_book_8 | buy | USD 2.99 | ACTIVE; US AVAILABLE |

All six options are legacy-compatible; multi-quantity is not enabled. All six
have the same 174 explicit regions AVAILABLE, plus newRegionsConfig AVAILABLE.
US prices were checked directly, not inferred from the screenshot or Shelf prices.
Owner now approves keeping these existing 174 available regions for all six
launch books, at USD 2.99 each. This is not worldwide coverage: AF and IR were
absent from the verified explicit list. Product settings do not prove app-track
distribution. No availability in AF/IR is claimed.

Fresh RevenueCat GETs verified the existing Shelf Play app appf83cd58c5c/package
services.shelf.app in project projbbce26da. Every product is active, one-time,
non-consumable, and attached alone to its matching same-named entitlement.
Shelf's read-only database inventory maps each book to that exact product ID and
USD 2.99 reference. Server verification requires the same product/entitlement key
and a permanent entitlement. **No missing, duplicate or incorrect connection found.**

| Book | RevenueCat product ID | RevenueCat entitlement ID |
| --- | --- | --- |
| 3 | prod03ffc5153f | entlb3d491e78e |
| 4 | prodecb275eeea | entl2dfbb286c0 |
| 5 | prod5fd4f31cc2 | entlaabc5a9b3a |
| 6 | prodb325aa52b6 | entl06c6eb0bc9 |
| 7 | prod2619318d70 | entl5061f62b7b |
| 8 | prod4681d9138c | entl77a53b0648 |

Evidence: /home/shelf/tmp/shelf-product-check-20261008/evidence.json (catalogue
metadata only). Real checkout OFF; automatic sync DEFERRED / DISABLED.
No credentials, provider products, prices, regions, mappings or database rows changed.
No rebuild, deployment, purchase or broad tests; completed product creation is not reopened.

## Owner-confirmed wording and approved regions — 8 October UTC / 7 October local

Owner confirms book 3's placeholder name and description were corrected in Play
Console following the recorded replacement: **څپو کې انځورونه** / **Full book in
your Shelf Library.** Status: OWNER-CONFIRMED, not independently reread.
Owner approves retaining the verified existing 174 available regions for all six
launch books and reaffirms USD 2.99 per book. AF/IR were absent from the verified
explicit list; this is not worldwide coverage. No provider checks repeated.

## D16 rights clearance — owner-confirmed

Owner confirms permission to sell all six selected books through Shelf in the
approved 174 regions, including covers and any included audio. This chat
confirmation is the evidence; no supporting documents are invented. Catalogue,
USD 2.99 starting prices and regions already approved. AF/IR absent from the
verified explicit list; not worldwide coverage. Do not request this confirmation
again. Real checkout OFF; no public-launch approval.

## Single next unfinished release task

Corrected OAuth certificate and completed release listing/Data safety preparation:
see [PLAY_RELEASE_REVIEW.md](PLAY_RELEASE_REVIEW.md). BE:61:2C:CD:0B:79:68:E3:AA:1E:2A:C7:E6:CF:B8:CB:61:8F:DE:24
is owner-confirmed Play app-signing SHA-1; DA:24 is upload SHA-1, earlier record
incorrect. No certificate role inferred for C9:B6. Phone sign-in unverified.
AAB 1.0.5 (14) preserved. Next owner action: approve scoped privacy/support fixes
identified by the review; no repeat product checks or provider edits. Checkout OFF.

Shelf admin reference changes do not update Play. Google's localized checkout
price remains authoritative.

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
All six selected Play products now exist with verified mappings; catalogue
publication alone is not evidence of end-to-end purchase availability.

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
