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
and large phones. Physical-phone purchase/restore/refund testing is still pending.

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
offline expiry and restore on another phone. No real money or public release.

For rollback, set both `SHELF_PURCHASES_ENABLED=false` and
`SHELF_PLAY_SYNC_ENABLED=false`, then run `php artisan optimize:clear`.
Retain provider catalogue/webhook settings and all financial history; never
delete records to undo configuration.

API setup references: [RevenueCat product creation](https://www.revenuecat.com/docs/api-v2/product),
[entitlement attachment](https://www.revenuecat.com/docs/api-v2/entitlement),
[sandbox webhook creation](https://www.revenuecat.com/docs/api-v2/integration).
