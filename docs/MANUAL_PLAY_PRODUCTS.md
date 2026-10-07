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

## One owner checklist

Verified 7 October 2026, 20:10:10 UTC: production read-only database transaction
and complete paginated Play oneTimeProducts GET for services.shelf.app.
All six books Published, none deleted. Play list contains only shelf_book_3.
All are **paid with free samples**, not free books. USD 2.99 is the recorded
owner-approved **test** price, not a new real-sales approval.

| Book ID | Exact catalogue title | Free/paid | Existing Shelf/RevenueCat product mapping | Approved test price | Play exists? |
| --- | --- | --- | --- | --- | --- |
| 3 | څپو کې انځورونه | Paid; free sample | shelf_book_3 | USD 2.99 | Yes; buy ACTIVE; US USD 2.99 AVAILABLE; legacy-compatible |
| 4 | هېندارې او چینې | Paid; free sample | shelf_book_4 | USD 2.99 | No |
| 5 | د زړه پر پاڼه مې انځور دی ګلاب | Paid; free sample | shelf_book_5 | USD 2.99 | No |
| 6 | سيند په پرخه کې | Paid; free sample | shelf_book_6 | USD 2.99 | No |
| 7 | دا ښار، هاغه غرونه | Paid; free sample | shelf_book_7 | USD 2.99 | No |
| 8 | دلته ډېر لرې له غرونو | Paid; free sample | shelf_book_8 | USD 2.99 | No |

These are existing mappings, **not proposed IDs**. No extra book or product ID
is proposed. Preserve shelf_book_3 and all mappings. There is no missing title,
ID or test-price decision for book 4. Territories and final launch prices/catalogue
are not newly approved here; retain the existing owner-only testing boundary.
Play availability in every reader country is not established by a US listing.

## Next missing paid product — owner steps

1. Play Console → Shelf (services.shelf.app) → Monetize with Play → Products →
   One-time products → Create product. First check that shelf_book_4 is still absent.
2. Product ID **shelf_book_4**; title **هېندارې او چینې**; description may be
   “Full book in your Shelf Library.” This short listing description is proposed,
   not book source text. Create a **Buy** purchase option, ID **buy**, with legacy
   compatibility enabled and multi-quantity disabled. It is a permanent book unlock.
3. Set approved test price **USD 2.99**. Review Google's local prices and the
   existing intended test regions; save and activate the Buy option for testing.
   Keep the app on owner-only internal testing; use only test payment methods.
4. In RevenueCat, check existing shelf_book_4 product is attached to the existing
   shelf_book_4 entitlement. Keep IDs unchanged. In Shelf, check availability and
   Google’s localized checkout price before a sandbox purchase.

Console guidance: [Google one-time products](https://support.google.com/googleplay/android-developer/answer/16430488),
[product creation](https://support.google.com/googleplay/android-developer/answer/1153481).
Publishing/withdrawal or price changes require a manual Console availability/price
review; an admin save does not do it. Existing buyers retain server-verified access.

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

Next separate construction: production-mode purchase verification and sale
recording behind disabled controls (Step 4 / §6.4); current implementation is
sandbox-only. No real-payment activation approved. D14 retention still needs
owner policy. Second-phone restore and final phone/launch/recovery acceptance
remain separate pre-release checks, not passed. Steps 4 and 5 stay open.
