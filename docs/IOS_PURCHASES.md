# Shelf — one-book Apple sandbox setup

12 October 2026: source preparation on `ios/foundation`, based on `a5047f4`.
This guide extends [the existing purchase setup](PURCHASES_TEST_SETUP.md). The
completed readiness audit is reused; Android's acceptance does not establish iOS
acceptance. Only `shelf_book_3` is implemented for owner-only Apple sandbox use.
**No provider setup, activation, deployment, running migration, build, upload or
transaction was performed. Real checkout stays OFF.**

## Implemented behavior

- The existing Flutter app selects `appl_` on native iOS and `goog_` on Android.
  Reader identity stays the numeric Shelf reader ID. SDK success never grants
  access. iOS requests its own configuration, chooses sandbox, offers only book
  3, and obtains account/product/store/mode consent before opening the store.
  Native Runner now declares the In-App Purchase capability; signing identity,
  entitlements, internal export, photo-purpose guards and artifact paths are unchanged.
- New default-false `SHELF_APPLE_SANDBOX_ENABLED` controls Apple sandbox. No Apple
  production switch exists. Staging forces this gate off. Checkout requires the
  verified Shelf reader whose exact email belongs to the owner admin; plus-aliases
  and private-relay addresses are not automatically treated as that owner. An
  existing verified email/password account can be used without enabling Apple
  sign-in. Buying-blocked readers cannot initiate checkout; historical corrections
  and verification still work for the original owner account.
- Authenticated Apple webhooks require a distinct Apple app-scoped authorization
  secret, the configured RevenueCat Apple app, APP_STORE, SANDBOX, product 3,
  decimal Apple transaction ID and exact purchase timestamp. Server-side
  RevenueCat V2 app/product/purchase/entitlement reads must independently confirm
  `services.shelf.app`, a permanent non-consumable, original/current reader ID,
  quantity one, purchased ownership (no Family Sharing), exact transaction/time,
  sandbox environment and active `shelf_book_3` entitlement. Missing, ambiguous,
  transferred, production or malformed evidence fails closed. Provider access
  is bounded and does not follow arbitrary pagination URLs or redirects.
- Transactions remain unique by store/environment/transaction. Apple event keys
  are scoped to app/store, duplicates do not repeat financial entries, and a
  conflicting repeat is rejected. Existing Play history keeps its default store;
  consent and refund tombstones cannot cross stores. Account changes never move
  an Apple purchase merely because emails match.
- Purchase confirmation and restore use the existing Library flow and independent
  REST evidence. SDK restore does not transfer ownership. Refunded transactions
  lock the book and the shared client removes its download on the next successful
  check; samples remain protected by the existing access rules. Provider failure
  gives an error and no fresh offline lease, rather than inventing a revoke.
- `CANCELLATION` appends a refund; `EXPIRATION` appends a revocation. A verified
  `REFUND_REVERSED` appends a compensating Test entry and restores access only to
  the original account with a matching unreversed refund and no revocation. A
  reversal cannot create an account or purchase. REST-owned status alone cannot
  restore a refunded transaction. Stale/conflicting corrections fail closed for
  later reconciliation; duplicate states do not multiply amounts. Refund-before-
  sale tombstones remain locked until a verified reversal.
- REST-discovered refunds record **observation time**, not an invented Apple
  refund time; their event details distinguish them from provider timestamps.
  All original ledger rows remain immutable. Sandbox entries are labelled Test
  and excluded from real income/author totals. Amount, currency and deductions
  unavailable during REST confirmation remain unknown; later events do not
  rewrite them. Reversal offsets the original known amount, or remains unknown.
- Deleted-account Apple recovery is explicitly blocked before Google proof or
  a recovery claim can run. Support-assisted Apple claimant/transaction proof,
  off-server recovery claims and any Family Sharing support are future work.

## Original setup checklist — historical; current status below supersedes completed steps

No dashboard access or completion is assumed. Reuse the existing Shelf project
`projbbce26da`; preserve Play app `appf83cd58c5c`, its keys, products, webhook and
**Keep with original App User ID** restore setting. No separate project or app
binary is needed.

1. **Next single action: obtain the purchase key.** Open
   [App Store Connect](https://appstoreconnect.apple.com/) → **Users and Access** →
   **Integrations** → **In-App Purchase**. Check Active keys first. Reuse an
   existing key only if its In-App Purchase capability and private file are
   confirmed. Otherwise click **Generate In-App Purchase Key** (or **+** beside
   Active), enter **Name: ShelfPurchases**, and click **Generate**. Click
   **Download API Key** once. Store the original `.p8` privately in the owner's
   secure credential backup; never paste its contents in chat or Git. Record the
   actual Key ID and Issuer ID privately; neither is invented here. Do not upload
   it to this server using the old ShelfLogin uploader without a separately
   prepared secure transfer. This is an **In-App Purchase key**, distinct from
   both the existing Codemagic/App Store Connect upload key and `ShelfLogin`
   Sign in with Apple key `2VQ43945YB`. Neither substitutes for it. RevenueCat
   requires the purchase key for this Flutter/StoreKit 2 integration.
   [Official key instructions](https://www.revenuecat.com/docs/service-credentials/itunesconnect-app-specific-shared-secret/in-app-purchase-key-configuration).
2. **Commercial prerequisites before product testing.** On the same website →
   **Business**, confirm the latest **Paid Apps Agreement** is active and required
   **Tax** and **Banking** information is complete/clear. If incomplete, the owner
   supplies their actual legal/tax/bank information using Apple's forms; no values
   can be inferred from code. This status is unverified. Do not test a purchase
   while these prerequisites are incomplete.
   [Provider prerequisite guidance](https://www.revenuecat.com/docs/getting-started/entitlements/ios-products).
3. **Create only the Apple book-3 non-consumable.** App Store Connect → **Apps** →
   **Shelf - شیلف** (Apple ID `6821471140`, bundle `services.shelf.app`) →
   **Distribution** → **Monetization / In-App Purchases** → **+**.
   **Type: Non-Consumable; Reference Name: Shelf book 3; Product ID: shelf_book_3**;
   click **Create**. If that exact product already exists, inspect/reuse it;
   never delete/recreate it. Complete its localization with the actual book title
   and an accurate description of full access to that book, using **+ / Create**
   then **Save**. Set the approved USD **2.99** starting price through **Price
   Schedule → Add Pricing → Base Country or Region: United States → Price: 2.99 →
   Next → Confirm**, then **Save** where shown. Review storefront availability
   with the owner; do not assume Apple's territories equal the approved Play
   list or authorize worldwide availability. Leave **Family Sharing** off. A
   real purchase-screen review screenshot is pending the future build; do not
   substitute a fabricated acceptance image. Product review/public submission is
   separate from this internal sandbox batch.
   [Apple product creation](https://developer.apple.com/help/app-store-connect/manage-in-app-purchases/create-consumable-or-non-consumable-in-app-purchases/).
4. **Connect the existing project to Apple.**
   [RevenueCat dashboard](https://app.revenuecat.com/) → **Shelf** → **Apps &
   providers** → **+ New** / **Add app** → **App Store**. First check for an
   existing Apple configuration; reuse it if correct. Name **Shelf iOS**, bundle
   **services.shelf.app**, App Store app ID **6821471140** where requested.
   Under **In-app purchase key configuration**, select the privately downloaded
   purchase `.p8`, provide its actual Key ID if requested and the Issuer ID from
   Apple's In-App Purchase page. Click **Save changes**, and confirm **Valid
   credentials**. Keep private files/secret keys in RevenueCat's protected setup
   and the owner's secure backup. An optional App Store Connect API key for
   catalogue import is separate; don't replace the existing upload credential.
   [Connect a store](https://www.revenuecat.com/docs/projects/connect-a-store),
   [required purchase key](https://www.revenuecat.com/docs/service-credentials/itunesconnect-app-specific-shared-secret/in-app-purchase-key-configuration).
5. **Attach exactly one product.** RevenueCat → **Product catalog → Products →
   + New / Import**: choose **Shelf iOS**, identifier **shelf_book_3**, type
   **Non-consumable** and click **Add** / **Import** as presented. Record the
   generated Apple app ID and RevenueCat internal product ID (different from
   `shelf_book_3`); do not invent either. **Entitlements → existing shelf_book_3 →
   Attach products**: select only the Apple `shelf_book_3` product, click **Add**.
   Preserve its existing Play attachment. Do not create a global entitlement,
   `default` offering, subscription or consumable.
6. **Notifications and independent verification permissions.** RevenueCat Apple
   app configuration provides its own **App Store Server Notifications URL**.
   Copy that actual URL; App Store Connect → Shelf → **App Information → App Store
   Server Notifications → Sandbox Server URL → Set Up**: URL = that copied value,
   click **Save**. The owner’s actual form offered no version selector; do not
   search for one again. Both Production and Sandbox URLs are now owner-confirmed
   saved; their versions and delivery remain unverified (see current status below).
   Keep RevenueCat's **Track new purchases from server-to-
   server notifications** off; numeric Shelf IDs must first be established by the
   SDK. Add a separate RevenueCat webhook in **Integrations → Webhooks → + New**:
   name **Shelf iOS sandbox**, URL **https://shelf.services/api/purchases/webhook**,
   App **Shelf iOS**, environment **Sandbox**, events **NON_RENEWING_PURCHASE,
   CANCELLATION, EXPIRATION, REFUND_REVERSED**. Authorization = a newly generated
   private secret stored only in the protected server configuration and this
   integration; click **Add webhook** / **Save** as presented. Do not alter the
   Play webhook or send a webhook test in this task.
   The existing server V2 key must have read access to **Customer information /
   purchases and purchase entitlements**, **Project configuration / apps**, and
   **Project configuration / products**. Existing Customer information Read only
   evidence does not establish apps/products permission. Verify least-privilege
   access before using that private key; no purchase/customer write access is
   needed. Never send it to Flutter. Preserve its existing private file/permissions.
   [Purchase REST schema](https://www.revenuecat.com/docs/api-v2/purchase),
   [app schema](https://www.revenuecat.com/docs/api-v2/app),
   [product schema](https://www.revenuecat.com/docs/api-v2/product).

## Earlier provider evidence and access preparation — 12 October 2026

Completed purchase implementation **08a2adf** and its tests are reused. The original
checklist is retained as history, not a request to repeat completed setup.
Paid Apps Agreement, banking, W-9 Active; DSA In Review; ShelfPurchases added with
RevenueCat Valid credentials; Apple book-3 product and reported availability/
pricing/localization completed; both notification URLs saved without a version
selector: **owner/dashboard evidence**. Exact Apple saved values were uninspected at that point; authenticated findings below supersede this limitation.

Independent Shelf RevenueCat V2 GETs verified:

| Reference | Verified value |
| --- | --- |
| Apple app | app8ef018b95b / Shelf (App Store), services.shelf.app |
| Apple product | prod0d084e901f / shelf_book_3, non_consumable |
| Existing entitlement | entlb3d491e78e / shelf_book_3, Apple and original Play attachment only |
| Purchase credential | subscription_key_configured=true; fresh Apple validation not performed |
| Optional ASC import credential | app_store_connect_api_key_configured=false; separate from purchase key |
| Apple SDK key | One appl_ key exists; value withheld, not configured on server |
| Shelf webhook | Play webhook only; separate Apple sandbox webhook still absent |
| Verifier permissions | Apps/products reads and purchases search passed; no permission change needed |

The documented existing private V2 file is protected and reused without printing
its value. Server iOS app/product/SDK/webhook fields and Apple sandbox gate are
absent; no configuration changed. API inspection covers Shelf only.

### Notification versions when the form has no selector

Apple Help still describes a choice, while its [App.Attributes schema](https://developer.apple.com/documentation/appstoreconnectapi/app/attributes-data.dictionary)
exposes independent `subscriptionStatusUrlVersion` and
`subscriptionStatusUrlVersionForSandbox` alongside the production/sandbox URLs.
The [enum](https://developer.apple.com/documentation/appstoreconnectapi/subscriptionstatusurlversion)
allows V1/V2. Neither source establishes an automatic V2 default for the owner's
form. Do not repeat the selector search or change the saved URLs. Authenticated
GET `/v1/apps/6821471140` can inspect both saved versions and compare the saved
URLs privately; null/missing version fields do not establish V2.
[RevenueCat supports both and recommends V2](https://www.revenuecat.com/docs/platform-resources/server-notifications/apple-server-notifications).
Its public app API does not expose notification URLs/versions, purchase tracking
or restore behavior. Saved configuration is separate from delivery. Apple's
[TEST notification](https://developer.apple.com/documentation/appstoreservernotifications/enabling-app-store-server-notifications)
is V2 regardless of the configured version; later TEST success alone cannot prove
the version of ordinary events or end-to-end Shelf purchase/refund delivery.
No TEST request or transaction was sent.

### Completed private access transfer — preparation retained for reference

Reuse the **existing Shelf App Store Connect upload team key** already used by
GitHub/Codemagic, from the owner's secure retained original. Supply its `.p8`,
actual Key ID and Issuer ID privately. **Existing App Manager access** is sufficient
for the requested app/IAP metadata, price schedule and availability GETs. No new
key, Admin/Finance/report access, capability or permission change is requested.
App Manager is not a read-only provider role; this inspection will make only GETs.
Team keys apply account-wide, but requests are limited to Shelf app 6821471140 and
its existing shelf_book_3. Do not use ShelfLogin or ShelfPurchases. No provider
secrets can be recovered from GitHub's saved secret values by this workflow.
If the original upload key is unavailable, report that limitation first.

**Current transfer method — prepared 12 October 2026:** use
[IOS_ASC_KEY_UPLOAD.ps1](IOS_ASC_KEY_UPLOAD.ps1). It replaces the earlier manual SFTP/two-
file request; no local JSON creation, filename typing or key-content
copying is required. Codex automates all possible authorized work; owner actions
are limited to necessary local access/interaction.

Checked Shelf records and configured/inherited upload metadata first: no verified
upload Key ID or Issuer ID is available here. The helper derives Key ID from the
original `AuthKey_<Key-ID>.p8` filename and asks **only the missing Issuer ID** in
a GUI input box. Copy that existing Issuer ID from **Codemagic → Team Settings →
Integrations → Developer Portal → shelf_app_store_connect**. Do not generate or
rotate a key, and do not use the Apple Team ID as Issuer ID. Key ID derivation
matches a filename; Apple/dashboard association remains a later verification.

Select the existing `.p8` used by Shelf’s Codemagic `shelf_app_store_connect`
upload integration, **not ShelfLogin or ShelfPurchases**.

**Windows PowerShell — command 1, download to Downloads (refuses an existing file):**

```powershell
& { $shelfHelper = Join-Path $env:USERPROFILE 'Downloads\IOS_ASC_KEY_UPLOAD.ps1'; if (Test-Path -LiteralPath $shelfHelper) { throw 'Helper already exists; preserved. Stop and report.' }; scp.exe -o StrictHostKeyChecking=yes 'root@157.250.199.106:/home/shelf/tmp/shelf-ios-foundation/docs/IOS_ASC_KEY_UPLOAD.ps1' $shelfHelper; if ($LASTEXITCODE -ne 0) { throw 'Download failed; do not run the helper.' } }
```

**Windows PowerShell — command 2, run:**

```powershell
powershell.exe -NoProfile -STA -ExecutionPolicy Bypass -File "$env:USERPROFILE\Downloads\IOS_ASC_KEY_UPLOAD.ps1"
```

The owner’s original filename, bytes and ACL are preserved. Local links/reparse
points (including parent directories) are refused. Only public receiver code
appears in SSH command arguments; key/identifiers use binary SSH stdin, no tokens,
clipboard or key/metadata output. Existing SSH host trust is required; no automatic
acceptance of unknown/changed hosts. Server opens directory components without
following symlinks, requires existing private parents shelf:shelf/0700, and
exclusively creates **/home/shelf/secrets/apple/shelf-asc-inspection.p8** and
**shelf-asc-inspection.json**, both shelf:shelf/0600. Either existing destination
(including dangling symlink) refuses the upload. Length/SHA-256 and exact metadata
are verified from disk before success. Handled failures clean only newly created
matching-inode files; abrupt interruption/cleanup failure may leave private partial
files, so stop for private inspection rather than overwrite or automatic retry.
No actual credential transfer performed by Codex in this preparation.

13 synthetic checks passed through the focused test-wrapper scope, using the
actual embedded receiver and disposable /home/shelf/tmp storage. Windows picker/
PowerShell runtime and real SSH transfer remain unexecuted; no PowerShell binary
available on this server. No purchase/API checks repeated. After transfer, the
next task is read-only Apple inspection; the helper makes no API call itself.

After access, automate bounded Apple GETs for the existing product, localizations,
price schedule/base territory/current prices, availability/territories and Family
Sharing, plus both notification URL/version fields. Use current schema paths,
follow bounded pagination without unrelated apps, and report saved values rather
than assuming USD 2.99 or worldwide availability from completion reports.

### Current authenticated Apple findings — 12 October 2026

Owner completed the helper; Codex verified both documented credential files are
regular/no symlinks, shelf:shelf/0600, parents 0700, key parses EC P-256, and actual
authenticated Apple GETs succeed (200). Key/identifiers/JWTs withheld; filename
alone is not credential proof. The credential's dashboard name/role was not
independently introspected. No additional credential transfer is needed.

App GET at 23:33:04 UTC confirms **6821471140**, **Shelf - شیلف**, **services.shelf.app**.
Both production **subscriptionStatusUrlVersion=V2** and sandbox
**subscriptionStatusUrlVersionForSandbox=V2** are explicitly saved. **No version
selector action is needed.** Both URLs are present, exactly equal and use HTTPS
api.revenuecat.com with its Apple server-notification endpoint path. Opaque
endpoint identifier withheld. Exact app-specific dashboard destination association
is still based on the owner's supplied confirmation: no independently retained
exact URL or RevenueCat public API field was available for that comparison.
Actual delivery remains unverified; no notification test was requested.

Book 3: Apple IAP **6822080809**, **shelf_book_3**, **NON_CONSUMABLE**,
**MISSING_METADATA**, reference name **Shelf book 3**; Family Sharing and content
hosting false. One **ar-SA** localization, state **PREPARE_FOR_SUBMISSION**, saved
name **څپو کې انڅورونه**, description **شعري ټولګه**. The name differs from the
recorded book title **څپو کې انځورونه**; preserve both as evidence and obtain final
owner wording before any later provider correction. No source text changed.

Price schedule: **USA/USD 2.99**, one manual base price plus **174 automatic
territory prices**, all customer-price/currency fields resolved, no explicit start
or end dates. Examples: Afghanistan USD 2.99, Canada CAD 3.99, UK GBP 2.99, Saudi
Arabia SAR 12.99. Available territories total **175**, including Afghanistan and
excluding Iran; **availableInNewTerritories=true**. This is distinct from the
approved Play territory list and does not establish expanded rights approval.
Final Apple territory/future-territory acceptance remains a public-release matter.

Review screenshot GET returned **data=null**; reviewNote null. The screenshot is
missing required review metadata, while notes are optional
([RevenueCat](https://www.revenuecat.com/docs/getting-started/entitlements/ios-products)).
Apple supplies no complete missing-field diagnosis, so do not claim this is the
only missing field. [Apple TN3186](https://developer.apple.com/documentation/technotes/tn3186-troubleshooting-in-app-purchases-availability-in-the-sandbox)
says sandbox testing does not require product review submission. Actual product
retrieval still needs the later real StoreKit flow; MISSING_METADATA is not a
proven sandbox failure. Keep the real purchase-screen review screenshot pending
the later build, with no fabricated substitute or screenshot request now.

All requests were bounded GETs scoped to Shelf and its existing product. One
availability query rejected an unsupported parameter (400); corrected separate
availability/territories GETs passed 200. No provider state changed. Completed
RevenueCat evidence and purchase implementation/tests were reused, not repeated.

**Next smallest batch, requiring separate authorization:** automate the distinct
Apple-scoped SANDBOX RevenueCat-to-Shelf webhook below, private server configuration
using app8ef018b95b / prod0d084e901f / existing SDK key with gate OFF, and exact
backend staging/backed-up reversible migration/promotion. No Apple product or
notification-version change is needed for that batch. Later separate build,
owner-only sandbox activation and phone purchase/refund/reversal checks establish
product retrieval and actual notification delivery. Keep final metadata/territory
acceptance and genuine review screenshot separate from backend preparation.

### Remaining setup after read-only inspection

Under separate authorization: confirm RevenueCat Track new purchases off and
Keep with original App User ID; create the one distinct Apple sandbox Shelf
webhook described above, preserving Play. Existing documented integration-write
access can automate that later action if still sufficient; no write probe or
permission expansion now. Private server configuration uses the verified IDs and
existing SDK key with Apple sandbox OFF. Backend staging/backed-up reversible
migration/promotion, later internal TestFlight build at a confirmed unused number,
explicit owner-only sandbox activation and actual purchase/restore/refund/reversal/
notification delivery acceptance remain pending. Deferred Apple sign-in/relay
acceptance is not required to use the existing verified owner email/password
account. DSA review remains unresolved for public release. All existing holds and
deferrals remain unchanged.

## Later backend rollout and phone checks — not authorized by this batch

Keep `SHELF_APPLE_SANDBOX_ENABLED=false`, real checkout OFF and both Apple sign-in
flags false. A later approved rollout must stage the exact commit with synthetic
isolated evidence, back up production, apply the committed reversible migration
`2026_10_12_120000_add_purchase_event_store_scope`, then promote that same verified
commit through the existing deployment process. No running migration occurred.
The migration defaults old events/consents to Play and adds Apple event scope and
millisecond purchase proof. Rollback is reversible before Apple history/consents;
with Apple records it refuses lossy removal and needs a preservation plan.

Prepare these values privately without activating the gate:

| Server field | Value source |
| --- | --- |
| SHELF_APPLE_SANDBOX_ENABLED | false until separately authorized owner-only sandbox activation |
| SHELF_REVENUECAT_PROJECT_ID | Existing Shelf project `projbbce26da` |
| SHELF_REVENUECAT_IOS_KEY | Actual `appl_` public SDK key for Shelf iOS, from RevenueCat Project Settings → API keys |
| SHELF_REVENUECAT_IOS_APP_ID | Generated RevenueCat Apple app ID, not Apple ID `6821471140` or the Play app ID |
| SHELF_REVENUECAT_IOS_BOOK3_PRODUCT_ID | RevenueCat's internal Apple product ID, not the store identifier |
| SHELF_REVENUECAT_IOS_WEBHOOK_AUTH | Exact new private Apple webhook Authorization value |
| SHELF_REVENUECAT_V2_SECRET_KEY_PATH | Existing protected V2 server key file after required read permissions are verified |

No SDK key/dashboard variable changes are required in Codemagic for this batch;
iOS obtains the public app key from the server after configuration. No secrets go
in builds. Preserve the verified signing/photo checks, `ditto` extraction,
internal-only export and explicit `$CM_BUILD_DIR` IPA/evidence paths. This source
needs a later new internal TestFlight build; installed build 3 does not contain
it. Confirm the next unused build number before that later build; no number is
reserved or dashboard value changed here.

On an owner-controlled iPhone, after separate rollout/build/sandbox authorization:
sign in with the existing verified owner Shelf account, open book 3, read its
sample, agree, inspect the Apple sandbox sheet and complete one authorized test.
Confirm server ownership, full paid text/media and download, cancellation/pending
behavior, same-account restore, another account's denial, refund removal and
refund reversal restoration without a second sale or real income. Validate actual
Apple notification → RevenueCat webhook delivery and live V2 response shapes;
synthetic tests do not prove those. Keep each pending result separate from
source verification. A recovery-after-deletion Apple phone test must wait for
reviewed recovery proof; it is not supported. Second-phone restore remains
owner-deferred and is not newly requested.

Checkout OFF, pricing-sync/second-phone-restore deferrals, Apple sign-in/relay
owner deferrals, stopped relay/email task, Android/UI pause, deferred small
improvements and joint Android/iOS public-launch hold remain in force.
