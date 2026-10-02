# Shelf single-book purchases — owner-only test setup

This implementation accepts Google Play **SANDBOX** purchases only. No real
payments are enabled. Staging is required before the Master Record's launch/testing
boundary. Prices are set ONLY in the Shelf admin. The owner approved USD 2.99
test prices for the six existing books; later price edits also belong only in
the admin. Author percentages remain owner decisions.

## Google Play Console

1. Create the Shelf Play app, upload the signed AAB to owner-only internal testing,
   and add the owner's account as internal tester and license tester.
2. Create ONE new service account with Play access, following the shared setup
   below. It is used by both Shelf's price sync and RevenueCat.
3. Create the new Shelf RevenueCat project and connect that same service account.

Do not create products or edit prices in Play Console. Shelf does this automatically.

- Android application/package: `services.shelf.app`.
- Current internal-test release: `1.0.3`, version code `12`.
- Use Play App Signing. The newly generated Shelf **upload** key signs the AAB;
  Google Play's separate **app signing** certificate signs installed Play builds.
- Upload key alias: `shelf-upload`; private files are outside git under
  `/home/shelf/android/keys/`. Verified backup is under `/home/shelf/backups/keys/`.
- Upload certificate SHA-1:
  `DA:24:FF:12:6D:3A:D7:D2:83:E3:A3:36:B6:8A:A9:03:50:42:38:CB`.
- Upload certificate SHA-256:
  `20:65:57:2E:CB:0F:17:4A:9E:66:96:02:23:2F:FC:EA:31:33:53:03:D3:FF:01:1C:AA:EB:72:16:FD:B6:9E:77`.
- Shelf creates one **one-time/in-app product** per book: `shelf_book_<database id>`.
  Existing book product IDs are `shelf_book_3`, `shelf_book_4`, `shelf_book_5`,
  `shelf_book_6`, `shelf_book_7`, and `shelf_book_8`. Confirm IDs in the admin.
  Enter and edit each USD price ONLY in the admin; Google supplies local prices.
- Add the owner's Google account as both an internal tester and a license tester.
  Purchase only when Play explicitly shows its test payment method; cancel otherwise.
- Add Google Play's **app signing SHA-1** to the Android OAuth client in Google
  project `shelf-510123` before using Google sign-in in a Play-installed build.
  The upload certificate does not substitute for this certificate.
- Google OAuth server/web client ID:
  `1045363818950-dtah1fosh1m4g7r7s8d32atr11s7mgc0.apps.googleusercontent.com`.

## A new RevenueCat project for Shelf

Never use the former Pitswal project, keys, products or offerings.

1. Add the Google Play app `services.shelf.app` to the new Shelf project and
   connect the SAME service account used by Shelf below.
2. After Shelf sync creates the products, import them into RevenueCat. Create a **separate entitlement for each
   book**, with its identifier exactly equal to that book's product ID, and attach
   only that product. No global entitlement or `default` offering is used.
3. Set restore behavior to **Keep with original App User ID**. Shelf logs in to
   RevenueCat with the reader's numeric database ID converted to a string.
   Never configure transfer to another reader.
4. Add webhook URL `https://shelf.services/api/purchases/webhook`, sandbox events,
   `NON_RENEWING_PURCHASE`, `CANCELLATION`, and `EXPIRATION` (plus `TEST` if desired).
   Use a newly generated secret Authorization header; its complete value must
   exactly match the server setting below. Keep this value out of chat and git.
5. Supply these NEW values privately in the server environment:

   | Setting | Required value |
   | --- | --- |
   | `SHELF_PURCHASES_ENABLED` | `true`, only after all new Shelf values are configured |
   | `SHELF_REVENUECAT_ANDROID_KEY` | New Android public SDK key beginning `goog_` |
   | `SHELF_REVENUECAT_SECRET_KEY` | New server secret with subscriber-read REST access |
   | `SHELF_REVENUECAT_SECRET_KEY_PATH` | Preferred alternative: private `0600` file containing a **V1** secret key for the existing `/v1/subscribers` verifier |
   | `SHELF_REVENUECAT_V2_SECRET_KEY_PATH` | Separate private V2 key for catalogue/webhook configuration; never used as the V1 verifier |
   | `SHELF_REVENUECAT_WEBHOOK_AUTH` | Exact newly chosen webhook Authorization header |
   | `SHELF_REVENUECAT_APP_ID` | New RevenueCat Google Play app ID from its dashboard |

The server never accepts a client claim of ownership: an authenticated sandbox
webhook and an independent RevenueCat subscriber REST check are required. SDK
success alone shows “Confirming…” and never opens a book. Restore rechecks existing
confirmed purchases; it neither transfers ownership nor fabricates a sale. Missing
or unavailable setup leaves purchases disabled, while samples remain available.

## One shared service account and automatic price sync

Use a new service account in Google Cloud project `shelf-510123`, not old Pitswal
credentials. Enable the Google Play Android Developer API and the APIs/roles in
[RevenueCat's credential guide](https://www.revenuecat.com/docs/service-credentials/creating-play-service-credentials).
Invite its email through Play Console's Users and permissions. Grant access to
Shelf, including **Manage store presence** (product/price writes), **View app
information**, **View financial data**, and **Manage orders and subscriptions**
(RevenueCat purchase validation). Complete RevenueCat's notification setup too.
Upload that account's JSON credential file directly to RevenueCat; use the same
account's JSON privately on the Shelf server. Never paste it into chat or git.

Shelf's credential file should be owned by `shelf:shelf`, mode `0600`, inside a
private `0700` directory outside the repository, for example
`/home/shelf/secrets/google-play-service-account.json`. Set server environment:

| Setting | Value |
| --- | --- |
| `SHELF_PLAY_SERVICE_ACCOUNT_PATH` | Absolute private JSON file path above |
| `SHELF_PLAY_SYNC_ENABLED` | `true` only after the account and Play app are ready |

Until these are supplied, every book shows **Pending: Waiting for Google Play
setup**. No Google API call is made. Saving price, title or publication state
records durable pending work and queues it after the admin transaction commits.
The dedicated `play-prices` database queue runs once per minute through
`scripts/run_play_price_sync.sh`; failed calls retry after 1 minute, 5 minutes,
15 minutes, 1 hour, then every 6 hours. Worker interruption is recovered from the
durable pending row after 5 minutes. New saves reset retries and use the latest
admin state, never an old queued price. Duplicate jobs are harmless.

Operators can queue/retry with `php artisan shelf:sync-play-prices` (all books)
or `php artisan shelf:sync-play-prices 3` (one book), and inspect with
`php artisan shelf:sync-play-prices --status`. The command accepts no price.

The modern Google one-time-product API upserts the stable product, with a single
permanent `buy` option (legacy-compatible for the existing RevenueCat app).
Google's `convertRegionPrices` calculates local prices from the USD base. Only
Published, priced books are activated; Draft, Ready, Withdrawn and binned books
are not for sale. Deactivation never deletes products, purchases or buyer access.
Admin status is **Synced / Pending / Error** with a plain message. “Synced” means
Google accepted the product/settings; Play propagation and RevenueCat entitlement
configuration can still take time. Buying remains SANDBOX-only and separately gated.

Before credentials exist, rollback restores the previous six prices through the
data migration and records reverse audit entries. It refuses to overwrite later
price edits. After products have synced remotely, rollback also needs a corrective
sync; a database rollback alone does not undo Google's accepted settings.

## Offline and money records

Successful server checks renew an offline lease for at most 30 days. Failed checks
never renew it. Refunds/revocations remove private downloads at the next successful
check; online media/text requests lose access immediately. Signing out or switching
readers removes the previous reader's local copies. Encrypted offline book/manifest
files and reader-specific storage are separate from samples and owner preview.

Financial history is append-only. Deleting a reader removes login/personal data and
entitlements, retaining anonymous numeric references for sales/refunds. Agreement
versions are snapshotted at purchase; later changes do not rewrite earnings.
Fees, taxes and unsettled net earnings remain unknown/provisional. There are no payouts.

The purchase migration is reversible while financial history is empty. It refuses
to drop tables containing purchases, to protect R8. After real history exists, any
rollback requires a separately approved preservation migration rather than deletion.

## Verification

Run `scripts/run_tests.sh` for server-only sync changes; use `--mobile` only when
mobile files changed. The backup-first live rollout can run
`php artisan shelf:check-purchases --simulate-after-backup`: synthetic accounts,
purchase/duplicate/refund/revoke/restore events traverse the live HTTP kernel with
mocked RevenueCat REST responses inside a rolled-back database transaction. This
does not contact Play, charge money, persist readers, or enable a fake website verifier.
Real Play/RevenueCat integration and phone behavior require the owner's new setup.

Sources: [RevenueCat restore behavior](https://www.revenuecat.com/docs/projects/restore-behavior),
[one-time purchases](https://www.revenuecat.com/docs/platform-resources/non-subscriptions),
[webhook fields](https://www.revenuecat.com/docs/integrations/webhooks/event-types-and-fields),
[Android app signing](https://developer.android.com/studio/publish/app-signing).

Price-sync API references: [product upsert](https://developers.google.com/android-publisher/api-ref/rest/v3/monetization.onetimeproducts/patch),
[automatic local prices](https://developers.google.com/android-publisher/api-ref/rest/v3/monetization/convertRegionPrices),
[activate/deactivate](https://developers.google.com/android-publisher/api-ref/rest/v3/monetization.onetimeproducts.purchaseOptions/batchUpdateStates).

## Connection evidence — 30 September 2026

### Retry after valid credentials and internal release — 1 October 2026 UTC

The owner reported RevenueCat **Valid credentials** at approximately 21:55 ET
on 30 September and internal-track publication of AAB `1.0.3 (12)`.
The exact archived AAB above contains `com.android.vending.BILLING` and the
correct package. Google requires a published build containing its Billing
Library to enable billing features; another upload for this permission is not
indicated. Phone installation and internal/license tester membership still need
the owner's confirmation.

Investigation reproduced the earlier **400**, independently of the later 403:
the client sent a JSON `[]` body with GET, and Google returned HTTP 400 HTML.
It also read `/onetimeproducts/…`; even a body-free GET returned HTTP 404 HTML.
The documented read URL is `/oneTimeProducts/…`, with an empty request body;
PATCH intentionally uses `/onetimeproducts/…`. Both client defects are corrected.
Regression tests now reject GET bodies and incorrect read casing. The old
message grouped 400 and 409; there is no evidence that a distinct 409 occurred.
The original failure happened at the initial read, before conversion or upsert.

Correctly cased, body-free GETs returned structured product-not-found 404s for
all six IDs; the modern catalogue list returned 204 with no products. **No
partly created products were found. None of the six products is active.** The
legacy product-list endpoint returned 403, “Please migrate to the new publishing
API”; Shelf already uses the modern one-time-product API, not that legacy API.

The correctly documented `/pricing:convertRegionPrices` still returned 403,
“The caller does not have permission”. Valid RevenueCat purchase credentials
do not prove product/price-management permission. Check Play Console → Users
and permissions → the shared service account → Shelf app access and **Manage
store presence**, as well as the other permissions listed above. Missing
permission versus propagation is not proven from the response. Do not manually
create products or change prices, and do not start a retry loop.

The requested `python3 -B scripts/connect_purchase_test.py` retry made and
verified backup `/home/shelf/backups/shelf/20261001-030723/` before application
sync-status changes. It passed the rolled-back live purchase simulation, then
stopped at book 3 with the access-denied message. No later book was attempted;
scheduled sync remains disabled. Prices, source text, existing readers and money
history were unchanged. Once permission is confirmed, rerun that backup-first
operator script; it uses the latest admin prices and must succeed on all six
before phone buying is ready.

Read-only RevenueCat V2 verification confirmed `shelf_book_3` through
`shelf_book_8` in app `appf83cd58c5c`, each a one-time product with a same-named
entitlement attached exclusively to that product. Identifiers match the intended
Play IDs; an actual Play-product match cannot yet be confirmed because those
products do not exist. Recheck Play ACTIVE states and USD prices, then RevenueCat
matching, after successful sync.

Validation: `scripts/run_tests.sh` passed **173 PHP tests / 2,095 assertions**.
No mobile files changed, so Flutter tests were skipped. Live simulation passed
purchase, duplicate, isolation, withdrawal, restore, refund, revoke and admin
denial checks, with synthetic rows rolled back. Test mode remains enabled;
no real payments were attempted. Before a phone purchase: successful product
sync, owner internal/license tester setup, Play-installed `1.0.3 (12)`, reader
sign-in (email/password works without the Play OAuth signing-certificate step),
and an explicitly displayed Google test payment method are required. Google
sign-in additionally needs the Play app-signing SHA-1 registered as described above.

References: [Google product reads](https://developers.google.com/android-publisher/api-ref/rest/v3/monetization.onetimeproducts/get),
[product upsert](https://developers.google.com/android-publisher/api-ref/rest/v3/monetization.onetimeproducts/patch),
[price conversion](https://developers.google.com/android-publisher/api-ref/rest/v3/monetization/convertRegionPrices),
[billing setup](https://developer.android.com/google/play/billing/getting-ready),
[RevenueCat service-account permissions](https://www.revenuecat.com/docs/service-credentials/creating-play-service-credentials).

The following evidence describes the earlier connection attempt and is retained
as history; the retry findings above supersede its initial diagnosis.

The owner supplied the new Shelf service account, public Android SDK key and V2
configuration credential, then supplied a separate V1 secret verifier key. All
three private credentials are under `/home/shelf/secrets/` (directory `0700`,
files `0600`). The server reads the V1 verifier from its private file; the V2
configuration key is never substituted into the V1 subscriber endpoint.

RevenueCat Shelf project `projbbce26da`, Google Play app `appf83cd58c5c`:
products `shelf_book_3` through `shelf_book_8` each have a separate same-named
entitlement attached only to their own non-consumable product. No offering is
needed: the app looks up each store product directly. The sandbox-only webhook
`whintgr75a4892970` targets `https://shelf.services/api/purchases/webhook`, scoped
to this app, with NON_RENEWING_PURCHASE, CANCELLATION and EXPIRATION. Its random
Authorization value is stored in `.env` and privately in
`/home/shelf/secrets/revenuecat-webhook-auth.txt`; no dashboard entry is needed.
The creation response reordered events; the verification now compares sets.
V1 subscriber authentication passed. The app fetches its public SDK key through
`/api/purchases/config`; it does not need a compiled key or offering.

Verified backup before connection/sync metadata updates:
`/home/shelf/backups/shelf/20260930-211054/`, including rollout/rollback notes.
No catalogue prices, source text, reader accounts or financial history were edited.
The live kernel simulation passed purchase confirmation, duplicate events and
transactions, other-reader denial, withdrawn buyer access, restore, refund,
revocation and admin denial. All synthetic rows rolled back and preexisting
counts/content version were verified unchanged. HTTPS passed enabled sandbox
configuration, six public books, locked non-sample body, protected Library and
owner preview, unsigned-webhook denial and private-file denial.

Google sync stopped at book 3: the existing client returned its 400/409 safe
product/price error. A subsequent **read-only** diagnostic found no existing
product (404) and price conversion denied (403). This is consistent with the
new-account permission/propagation delay, but the initial 400/409 is not proven
to have the same cause. There were no further Google writes or retries. Books
4–8 remain Pending. `SHELF_PLAY_SYNC_ENABLED=false` prevents scheduled remote
retries; sandbox purchases are configured but actual buying waits for Play products.
This shell also cannot read the crontab because of PAM policy; the existing
schedule was not changed or independently verified in this task.

After the credential warning/delay clears, rerun the backup-first operator script
as Shelf from this repository:

```bash
python3 scripts/connect_purchase_test.py
```

It stops at the first unsuccessful book and keeps scheduled sync disabled on
failure. Only when all sellable books sync does it enable scheduled admin price
sync. If the original product/price error persists after Google permits requests,
stop and diagnose that separate blocker before retrying. Do not create products
or change prices manually in Play Console.

Validation: `scripts/run_tests.sh --mobile`: 173 PHP tests / 2,095 assertions and
134 Flutter tests passed. Tests cover denied-sync early stop, private-key fail
closed, purchase/refund/isolation/offline checks and purchase layouts on small
and large phones. At that milestone physical-phone purchase/restore/refund testing was pending.
The 2 October owner evidence below supersedes that status; second-phone restore
is deferred to pre-release and is not passed.

Built and signature-verified `1.0.3` (version code `12`) artifacts:

| Artifact | Private server path | SHA-256 |
| --- | --- | --- |
| Internal-test AAB | `/home/shelf/apps/shelf/storage/app/private/owner-aabs/shelf-internal-test-20260930-210956.aab` | `722fd0cc7280d07eb2524e05cd8fa341d52dea14d29f7863b86181d1bfb5fa57` |
| Owner-preview APK | `/home/shelf/apps/shelf/storage/app/private/owner-apks/shelf-owner-preview-20260930-211145.apk` | `5b6374840e6d37913b410559dd0605afb4300892be8e02059f7ff5ffb8274c6c` |

Owner-preview access expires **7 October 2026 at 21:09:17 UTC**. The AAB contains
no owner-preview token. Both are private server files and are not committed.

On the owner's PC in PowerShell, download both with one command:

```powershell
scp "shelf@157.250.199.106:/home/shelf/apps/shelf/storage/app/private/owner-aabs/shelf-internal-test-20260930-210956.aab" "shelf@157.250.199.106:/home/shelf/apps/shelf/storage/app/private/owner-apks/shelf-owner-preview-20260930-211145.apk" .
```

In Play Console → Shelf → Testing → Internal testing → Create new release,
upload the AAB and roll it out only to the owner. Install through the internal
tester opt-in link with the license-test account. Real store integration must
be tested with that Play-installed build, not the sideloaded owner-preview APK.
After Google sync succeeds, sign in to the owner reader account, accept the
sample-first agreement and buy **only when Google explicitly shows a test
payment method**. Verify My Library, cancellation, refund, account isolation,
offline expiry. Second-phone restore is now deferred to the pre-release checklist
by the 2 October owner decision; do not request it now or mark it passed.
No real money or public release.

For rollback, set both `SHELF_PURCHASES_ENABLED=false` and
`SHELF_PLAY_SYNC_ENABLED=false`, then run `php artisan optimize:clear`.
Retain provider catalogue/webhook settings and all financial history; never
delete records to undo configuration.

API setup references: [RevenueCat product creation](https://www.revenuecat.com/docs/api-v2/product),
[entitlement attachment](https://www.revenuecat.com/docs/api-v2/entitlement),
[sandbox webhook creation](https://www.revenuecat.com/docs/api-v2/integration).


## First test purchase fixes — 2 October 2026

Ordinary `SalesLedger` queries include only purchases explicitly marked PRODUCTION.
This default covers amounts, author totals, dashboard queries and income exports;
SANDBOX and unknown modes fail closed. There are currently no separate income
widgets or export routes to bypass that boundary. New financial features must use
this model default, never raw `DB::table('sales_ledger')` totals. Income currencies
remain separate. `withTestPurchases()` is reserved for explicitly labelled history,
idempotency and refund/access checks, never income calculations. History JSON
includes `income_mode` and `is_test`. The admin retains the test price/transaction,
shows **Test** and **Test — no income** for earnings, owed amounts and payouts.
New sandbox sale/refund/revoke entries use earnings status `test` and do not compute
author earnings. Prior immutable ledger entries and agreement snapshots are retained.
No structure or data migration is needed.

The Library publishes verified ownership before saving the local manifest, so the
book page immediately changes to **Open owned book** on server confirmation. A
store success alone still grants no access. Existing startup, app-resume and periodic
server checks now expose refresh failures with a do-not-buy-again message. Library
covers use the authenticated, host-checked media client and downloaded covers when
available; cover widgets are isolated by reader/book/download state. The Library
uses pull-to-refresh and a small refresh icon, retains the single header account
icon, and replaces the download action with **Downloaded ✓** / **Remove download**
after downloading. Source text, RTL book layout and 30-day offline limits are unchanged.

Release checks include real-income isolation and labelled history. Promotion uses
`scripts/staging/workflow.py promote COMMIT` only after deployment and passing checks
at the identical commit. It takes a verified fresh backup and fingerprints all past
reader/payment/agreement records before/after. Rollback is the prior recorded code
commit; no financial rows should be edited or restored over later purchases.

App version: **1.0.4 (13)**. The production AAB updates Shelf through Google Play;
Shelf Test is separate and has no production reader accounts or purchases. The owner
must check the installed app on real phones; automated screenshots cover Pashto and
English at 320×568 and 430×932, with owned/downloaded/refresh-error states.

## Owner phone evidence and reconciliation — 2 October 2026

Owner confirmed: refund removed book/download and locked paid text while sample
worked; declined test payment stayed locked; repurchase restored Library and
paid poem reading. Focused read-only server snapshot at 04:52:11 UTC corroborates
purchase 5/refund ledger 14 and separate repurchase 6/ledger 15; active entitlement,
all Test, zero real-income rows. Latest scheduled refund audit 04:45:01 UTC:
HTTP 200, one duplicate, no extra refund. No new purchase/refund/poll was triggered.

[Current reconciliation and next building task](STATUS_RECONCILIATION_2026-10-02.md)
separates construction from pre-release acceptance. **Second-phone restore is
DEFERRED, NOT PASSED, not requested now.** Product/price sync's last 403 remains
unresolved and sync disabled; it is distinct from the working refund API.
Regular off-server backup/recovery remains unfinished. Ownership refresh and
admin Allowed/Blocked fixes are present in deployed code, inspected directly.
No broad test rerun, rebuild, deployment or production data change in this task.
