# Shelf single-book purchases — owner-only test setup

This implementation accepts Google Play **SANDBOX** purchases only. No real
payments are enabled. Staging is required before the Master Record's launch/testing
boundary. Prices and author percentages must be entered by the owner; none are
invented by the implementation.

## Google Play Console

- Android application/package: `services.shelf.app`.
- Current release: `1.0.2`, version code `11`.
- Use Play App Signing. The newly generated Shelf **upload** key signs the AAB;
  Google Play's separate **app signing** certificate signs installed Play builds.
- Upload key alias: `shelf-upload`; private files are outside git under
  `/home/shelf/android/keys/`. Verified backup is under `/home/shelf/backups/keys/`.
- Upload certificate SHA-1:
  `DA:24:FF:12:6D:3A:D7:D2:83:E3:A3:36:B6:8A:A9:03:50:42:38:CB`.
- Upload certificate SHA-256:
  `20:65:57:2E:CB:0F:17:4A:9E:66:96:02:23:2F:FC:EA:31:33:53:03:D3:FF:01:1C:AA:EB:72:16:FD:B6:9E:77`.
- Create one **one-time/in-app product** per book: `shelf_book_<database id>`.
  Existing book product IDs are `shelf_book_3`, `shelf_book_4`, `shelf_book_5`,
  `shelf_book_6`, `shelf_book_7`, and `shelf_book_8`. Confirm IDs in the admin.
  Set each owner's chosen USD price; Google Play supplies the local displayed price.
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
   complete RevenueCat's Play service-account connection.
2. Import each one-time product above. Create a **separate entitlement for each
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
   | `SHELF_REVENUECAT_WEBHOOK_AUTH` | Exact newly chosen webhook Authorization header |
   | `SHELF_REVENUECAT_APP_ID` | New RevenueCat Google Play app ID from its dashboard |

The server never accepts a client claim of ownership: an authenticated sandbox
webhook and an independent RevenueCat subscriber REST check are required. SDK
success alone shows “Confirming…” and never opens a book. Restore rechecks existing
confirmed purchases; it neither transfers ownership nor fabricates a sale. Missing
or unavailable setup leaves purchases disabled, while samples remain available.

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

Run `scripts/run_tests.sh --mobile`. The backup-first live rollout can run
`php artisan shelf:check-purchases --simulate-after-backup`: synthetic accounts,
purchase/duplicate/refund/revoke/restore events traverse the live HTTP kernel with
mocked RevenueCat REST responses inside a rolled-back database transaction. This
does not contact Play, charge money, persist readers, or enable a fake website verifier.
Real Play/RevenueCat integration and phone behavior require the owner's new setup.

Sources: [RevenueCat restore behavior](https://www.revenuecat.com/docs/projects/restore-behavior),
[one-time purchases](https://www.revenuecat.com/docs/platform-resources/non-subscriptions),
[webhook fields](https://www.revenuecat.com/docs/integrations/webhooks/event-types-and-fields),
[Android app signing](https://developer.android.com/studio/publish/app-signing).
