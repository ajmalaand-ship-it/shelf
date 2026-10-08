# Play release preparation — 8 October 2026

Preparation only, not submitted or deployed. Checkout OFF; no upload/public release.
Preserve verified AAB 1.0.5 (14), source 2c8d763 and existing checksum. Six books,
owner-confirmed rights, USD 2.99 starting prices and existing 174 regions approved;
AF/IR absent from verified explicit list, not worldwide coverage. Manual products
and RevenueCat mappings already verified; automatic sync remains DEFERRED.

## Signing correction

Owner saved Shelf Android Play, services.shelf.app, in shelf-510123 with SHA-1
**BE:61:2C:CD:0B:79:68:E3:AA:1E:2A:C7:E6:CF:B8:CB:61:8F:DE:24**, copied from
Play “App signing key — In use”. Owner-provided Cloud screenshot confirms that
value and “OAuth client saved”. Configuration owner-confirmed, not an independent
provider read or successful phone sign-in. Other OAuth clients unchanged.
DA:24:FF:12:6D:3A:D7:D2:83:E3:A3:36:B6:8A:A9:03:50:42:38:CB is the upload
certificate. Earlier instruction/record using it for Play signing was incorrect,
superseded. Do not infer the role of original C9:B6 fingerprint. Actual Google
sign-in in a Play-installed build remains unverified. OAuth-only correction does
not change packaged source/configuration or require an AAB rebuild.

## Copy-ready English listing

Name: **Shelf**

Short description (74 characters):

> Discover Pashto and Farsi books, read samples, and build your own library.

Full description:

```text
Shelf is a bookstore and personal library for Pashto and Farsi books.

Discover books by author and category, search the catalogue, and read a free sample before choosing a book. Shelf starts with six Pashto books; available titles and languages depend on the catalogue.

Each paid book is purchased separately through Google Play. There are no subscriptions or all-books passes. Sign in with email and password or Google to manage your account and personal library.

Bought books appear in My Library. Download eligible books for offline reading. Offline access requires a successful server check at least every 30 days. Refunded or revoked books and downloads are removed at the next successful check.

Choose an English or Pashto interface and adjust reader settings. Book text stays in its original language. Listen to audio where it is included.

Browse books and read samples without an account. Book purchases are separate from downloading the app, and Google Play shows the price in your local currency.

You can request account deletion from Account in the app or at https://shelf.services/account/delete. Confirm the email link to complete deletion. Deletion removes your profile and book access; necessary transaction and accounting evidence is retained. Contact support for verified purchase recovery; a matching email alone does not restore purchases.

Privacy: https://shelf.services/privacy
Support: ajmalaand@gmail.com
```

Launch-use draft: purchasing remains disabled now; do not publish copy as if live
checkout has been enabled. No promise of Farsi launch inventory, worldwide
coverage, unlimited offline ownership, all books free, or completed phone acceptance.
Category suggestion: Books & Reference; app, not game. Contains ads: No (no ad SDK
or ads found). In-app purchases: Yes. Target-age/content-rating answers need owner
knowledge of the selected literary content; do not infer a children’s audience.
Screenshots/feature graphic and Console listing completeness not inspected or
invented. Reuse approved assets where available; no new artwork generated.

## Data safety form — prepared selections

Declare collection: **Yes**. Encryption in transit: **Yes** for production app
flows (HTTPS Shelf/Google/RevenueCat; localhost-only cleartext QA is not a remote
production collection endpoint). This is not a claim of end-to-end encryption.
Deletion requests: **Yes**; account creation: **Username and password**, **OAuth**
(email is the username). No passkeys/phone sign-in. Independent security review:
**No** (no such evidence). Account-deletion URL: https://shelf.services/account/delete.
Do not claim all data is erased: retained purchase/refund/accounting evidence and
backup/deletion exceptions are explicit. No standalone partial-data-deletion
feature was found; do not select one as an implemented feature.

Verified collection is separated below from unresolved provider handling. Sharing
is **UNRESOLVED for every row**, not a verified No. Proposed Required/optional and
purposes describe the inspected flows; final declaration must cover provider facts.
Non-ephemeral is established for stored account/purchase records; search processing
is memory-only in application code, but hosting-log retention makes its ephemeral
answer unresolved until log handling is confirmed.

| Google data type | Collected | Shared | Required/optional | Collection purposes | Evidence |
| --- | --- | --- | --- | --- | --- |
| Personal info: Name | Yes | UNRESOLVED | Optional | Account management; App functionality | Optional registration name; Shelf stores it. Google token can contain profile claims but Shelf does not store Google name/photo. |
| Personal info: Email address | Yes | UNRESOLVED | Optional to use app; required for account | Account management; App functionality; Fraud prevention, security and compliance | Verification/login/reset/delete email, hosting mail. Guest samples work. |
| Personal info: User IDs | Yes | UNRESOLVED | Optional to use app; required for account/ownership | Account management; App functionality; Fraud prevention, security and compliance; Analytics for RevenueCat purchase account association | Shelf reader ID; hashed Google subject; exact reader ID sent to RevenueCat. |
| Financial info: Purchase history | Yes | UNRESOLVED | Required when using purchase/restore (use Required per SDK guidance) | App functionality; Analytics; Fraud prevention, security and compliance | Google/RevenueCat receipts; server ownership, refunds, accounting. SDK collection cannot be disabled within purchase service. |
| App activity: In-app search history | Yes | UNRESOLVED | Optional | App functionality | Search query q transmitted to API. No saved search-history table, but request URLs can enter hosting logs; do not claim ephemeral without log evidence. |
| App activity: App interactions | Request transmission verified; hosting retention unresolved | UNRESOLVED | Required for service requests | App functionality; Fraud prevention, security and compliance | Requests identify book/content/media endpoints, filters and times in hosting operation. No behavioral advertising/profile system; no local reading-position upload found. |
| App info and performance: Diagnostics | Provider log content/retention UNRESOLVED | UNRESOLVED | UNRESOLVED | App functionality; Analytics (diagnose faults); Fraud prevention, security and compliance | Hosting diagnostic/request/error metadata; not a claim of crash-reporting SDK. |
| Device or other IDs | UNRESOLVED; no device ID collection call found | UNRESOLVED | UNRESOLVED | Depends on actual provider handling | An IP address alone is not evidence of a unique device/browser/app identifier. No advertising-ID collection call found. |

**No final sharing answer is confirmed.** Potential service-provider exceptions require evidence that hosting/mail, RevenueCat and encrypted OneDrive
backup are acting as processors for Shelf, and Google sign-in/store interaction is
user-initiated as Google describes. Source contains no RevenueCat attribution-ID
collection, setEmail/setDisplayName/customer attributes, or advertising integration
calls. Existing provider catalogue evidence does not audit every dashboard
integration or processor contract. Before final submission, owner must establish processor roles and disclose
any independently configured non-processor integrations; if present, select Shared
for their actual data and purpose. These are unresolved factual dependencies, not
permission to invent a “no sharing” guarantee. No new provider checks here.

Source-only No findings for other categories: precise or
approximate location (IP not geolocated in Shelf code; provider inference unresolved), payment-card details,
contacts, calendar, health, sensitive profile fields, installed-app inventory,
web-browsing history, SMS, user photo/video/audio/document uploads and crash logs
(no crash uploader found). Do not confuse publisher covers/audio/downloaded books
with user-uploaded media. Share-card export is local and user initiated. Optional
support emails are processed outside the app by the chosen mail provider; disclose
support-message processing in policy, not access to the user's email inbox.
If hosting or a dashboard integration infers location, corresponding location
answers must change; source inspection cannot certify host internals.

RevenueCat explicitly recommends purchase history, non-ephemeral, Required, App
functionality + Analytics. Google allows optional collection when users can use
an app signed out. The SDK-specific Required recommendation is kept for purchase
history; email/name/account ID choices use Shelf's guest path. Do not hide purchase
collection because checkout is currently OFF: restore/test distributed versions
and intended release behavior also matter to the form.

## Source evidence inspected

- mobile/pubspec.lock: purchases_flutter 10.13.2, google_sign_in_android 7.2.17;
  google_sign_in 7.2.0. No Firebase, Ads, Sentry or analytics dependencies found.
- mobile/lib/accounts/account_service.dart and account_screen.dart; backend
  AuthController, GoogleIdentity and AccountActions: account fields, secure local
  tokens, hashed credentials, Google verification, one-hour email actions/deletion.
- mobile/lib/purchases/book_purchase_provider.dart, library/download services:
  identified per-reader RevenueCat, no attribute/advertising-ID collection calls,
  exact-book receipts, restore and protected account-isolated offline downloads.
- mobile/lib/repository/poetry_repository.dart and CatalogueFilters: remote searches;
  local settings/share-card outputs are not an off-device collection SDK.
- Android manifest: Internet/billing/media playback and older gallery-write
  permission, no contacts/location/microphone/ad-ID permission in app manifest.
  Network security permits cleartext only for 127.0.0.1. Existing AAB build/signature
  evidence reused, not rebuilt or broad-tested.
- RevenueCatClient metadata removal, deletion journal/outbox, D14 runbook:
  profile removal, retry/read-back, retained pseudonymous transaction evidence,
  restore suppression and encrypted backups. Actual provider erasure unverified.

## Public pages checked, read-only

8 October 2026 05:05:24 UTC, anonymous HTTPS GET, no form submission:

| Actual URL/contact | Result | Assessment |
| --- | --- | --- |
| https://shelf.services/privacy | 200, no redirect | Shelf policy accessible; account/security/deletion/backup exceptions present. |
| https://shelf.services/account/delete | 200, no redirect | Shelf-branded email form, one-hour confirmation, removed/retained data and support contact present. Not a new end-to-end deletion test. |
| https://shelf.services/support | 404 | Probed candidate only; no route in source. Never use it as a support URL. |
| ajmalaand@gmail.com | Present on privacy/deletion pages | Existing support contact; mailbox deliverability/response not tested. |

There is no existing dedicated public support URL. Shelf root is an API response,
not a help site. Console support email can use the existing address; do not invent
a support web page. Public privacy/deletion links work without an account.

Precise corrections required before launch (not deployed in this task):
1. Add a visible privacy-policy link inside the mobile app. rg/source inspection
   found no privacy URL/link or privacy text entry anywhere in mobile/lib. Google
   requires in-app privacy access; public web availability alone does not satisfy it.
   This future mobile source change would require focused validation and a new
   artifact; preserve AAB 14 now, do not claim it contains the fix.
2. Policy's “no advertising or analytics SDKs” should distinguish no advertising/
   reading-behavior analytics from RevenueCat purchase analysis. Policy should name
   RevenueCat as transaction processor, reader-ID association and purchase analytics.
3. Add remote search queries and request/book/filter diagnostics disclosure. Existing
   “do not collect your reading history” must be limited to no maintained reading-
   progress/history profile, not a denial of logged book requests or searches.
4. Public policy last-updated September 29 is stale relative to D14 text; date it
   when revised. “Purchases currently limited to owner testing” is accurate now;
   replace only as part of a separately approved real-sale rollout, never silently.
5. Retention remains “under review” for financial history. Settle owner policy before
   final launch privacy wording; never apply routine 90-day logs/backups to money
   records. Provider erasure claims should describe retries/verification procedure,
   not promise that all provider data has already been erased.
6. Historical preparation finding: Support contact exists on web but purchase errors say “contact Shelf support”
   without a concrete address/action in mobile. Add a clear support entry alongside
   privacy in a later approved mobile task. No need to invent a support URL.

Suggested policy additions (draft, not published):

> Shelf processes your catalogue searches to return matching books. Hosting may
> retain request URLs, book/filter identifiers, IP addresses, times and diagnostic
> errors to deliver and protect the service. We do not maintain a reading-progress
> history profile or use these requests for advertising.

> Google Play processes payment details. RevenueCat processes your Shelf reader
> identifier and purchase information for purchase verification, restoration and
> purchase analytics. Shelf retains transaction, refund and accounting evidence;
> account deletion does not erase necessary transaction evidence. Financial-record
> retention remains unresolved and is not governed by the routine log/backup limits.

## Owner decisions still needed

- Financial-record retention policy (including justified exceptions); no period or
  jurisdictional/legal conclusion chosen here. Owner may obtain accounting/legal
  advice. This is the one pending policy decision affecting final retention wording.
- Approve listing copy, target age/content-rating facts, publicly responsible
  developer identity and continued use of existing support email. Privacy HTML meta
  says “published by Hindara”; ensure the actual Console developer/entity and policy
  identify the responsible party consistently, without guessing that identity.
- Confirm whether owner-configured integrations beyond known Shelf webhook/Play
  exist and whether processors handle data only for Shelf. This resolves sharing
  answers; no credentials or provider edit required.

No new decision required for catalogue, rights, prices, regions, manual products,
RevenueCat book mapping or corrected certificate. No repeat confirmations.

## Corrections implemented — 8 October 2026

Settings now includes English/Pashto Privacy policy and Support entries. Production
privacy opens https://shelf.services/privacy using the configured API origin;
mail opens mailto:ajmalaand@gmail.com. Email is visible in Settings, selectable in
the dialog and copyable even when no email app opens. Browser failure has copyable
privacy URL fallback. These are mobile source additions only: AAB 1.0.5 (14) does
not contain them. A later replacement build and phone check are required; no build now.

Public policy now dates the revision October 8, explains remote searches and
potential request logs, reader-ID/purchase processing and RevenueCat purchase
analytics. No ad SDK or maintained reading-progress profile claim substituted for
no request collection. Provider erasure is described as queue/retry/read-back,
not completion. Financial retention remains under review. Deletion/recovery behavior
unchanged. Historical findings above are preserved; items 1–3, 5 provider wording
and 6 support access now have source fixes; website deployment evidence follows in
Master Record. Purchases remain owner-test-only in policy while checkout OFF.

Exact Google categories correction: account identifiers = Personal info/User IDs;
purchases = Financial info/Purchase history; remote queries = App activity/In-app
search history; endpoint access = App activity/App interactions. Generic request
errors do not establish uploaded crash logs; actual performance diagnostic logs
must be mapped to App info and performance/Diagnostics after provider facts.
IP-based location is declared only if location is inferred. Device or other IDs
requires an actual device/browser/app identifier, not a blanket label for any IP.
Sharing, host logs/retention/geolocation, SDK/provider identifiers/integrations and
processor roles remain unresolved, explicitly separated from source-verified flows.
Current official Google definitions and RevenueCat guide reread 8 October; no
provider/Console edits or submission. Do not submit “Not shared” from this draft.

## One concrete next owner action

Provide existing hosting/provider and RevenueCat integration information (no secrets)
to resolve log retention, any location/device identifiers and processor/sharing roles.
Codex can then finalize the unresolved Data safety cells. Financial retention policy,
target audience/content rating and developer identity remain owner decisions;
catalogue/rights/price/regions need no repeat confirmation.

## Official guidance used (checked 8 October 2026)

- [Google Data safety definitions, purposes, service-provider exceptions and optional collection](https://support.google.com/googleplay/android-developer/answer/10787469?hl=en).
- [RevenueCat Google Play disclosure guidance](https://www.revenuecat.com/docs/platform-resources/google-platform-resources/google-plays-data-safety).
- [Google account-deletion requirements and retained-data exceptions](https://support.google.com/googleplay/android-developer/answer/13327111?hl=en).
- [Google User Data/privacy policy requirements](https://support.google.com/googleplay/android-developer/answer/10144311?hl=en).
- [Google listing fields/limits](https://support.google.com/googleplay/android-developer/answer/9859152?hl=en-en).
- [Google Play services core SDK disclosure scope](https://developers.google.com/android/guides/play-data-disclosure). This says core SDKs collect none; it does not certify every authentication SDK. [Google authentication overview](https://developer.android.com/identity/credential-manager) and Shelf's actual Google token flow were reviewed; no claim of zero Google authentication processing.
