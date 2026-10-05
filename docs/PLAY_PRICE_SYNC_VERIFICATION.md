# Step 4 Task 2b verification — 30 September 2026 UTC

- Owner decisions of 29 September recorded in AGENTS section 8 and the Master
  Record addendum: all prices are edited only in admin and automatically sent to
  Play; one service account is shared with RevenueCat. No real money.
- `scripts/run_tests.sh`: PASS, 167 PHP tests, 1831 assertions. No mobile files
  changed, so Flutter tests/builds were correctly skipped.
- Mocked Google API covers signed OAuth credentials, USD/local conversion,
  missing-product creation, price updates, activation, withdrawal/bin/restore,
  duplicate jobs, newest admin price, redacted errors/backoff/recovery,
  unexpected option protection, transactional rollback, private credentials,
  admin status and reversible price migration.
- Backup verified before rollout:
  `/home/shelf/backups/shelf/20260930-042654` (SQL, ZIP and SHA-256 verified).
- Migrations `2026_09_30_020000_create_play_price_sync` and
  `2026_09_30_020100_set_owner_approved_book_test_prices` applied successfully.
  Maintenance ended successfully.
- Existing books 3, 4, 5, 6, 7 and 8 now have USD 2.99 test prices. Each change
  records old/new price, owner ID, Ajmal's approval and execution timestamp.
  Their identities, content, publication states and purchase records are retained.
- Dedicated Shelf `play-prices` queue runner installed once per minute, tested
  directly; nightly backup cron preserved. Previous crontab backed up at
  `/home/shelf/backups/shelf/20260930-042654/shelf-crontab-before-play-sync.txt`.
- Live sync command reports disabled and makes no Google API calls. All six
  statuses: Pending — Waiting for Google Play setup. Public purchase config remains
  disabled, test_mode true and offline_days 30. RevenueCat purchase flow unchanged.
- Real Google/RevenueCat integration remains pending owner credentials/setup.
  [Owner setup instructions](PURCHASES_TEST_SETUP.md) require no hand-created
  Play products or Play price edits.

Undo before credentials exist: restore the previous crontab with `crontab` and
the backed-up file above if removing the runner, then roll back the price migration
through Laravel. It restores the previous prices with reverse audit entries and
refuses to overwrite later owner price edits. Once Google settings have synced,
rollback also requires a corrective sync of the restored admin settings; reverting
the database alone cannot undo Google's accepted prices.

## Step 4 / D9 and 6.4 — bounded investigation, 4 October 2026

Owner authorized finishing automatic product/price integration. Reused preserved
`/home/shelf/tmp/shelf-play-403` investigation at ff814a1, including the original
pricing-only diagnostics, owner confirmation of four permissions/Active account
and linked selling merchant profile, and preserved conversion/PATCH denial
records. The existing GET casing/empty-body fix is already deployed; no setup
step, credential recreation, broad administrator grant, app release or product
write was repeated.

Current effective identity: **shelf-play@shelf-510123.iam.gserviceaccount.com**,
Cloud project shelf-510123, package **services.shelf.app**, OAuth scope
**https://www.googleapis.com/auth/androidpublisher**. Credential material and
tokens were kept private. Fresh bounded probes using the production client's
existing token code:

| Operation | Request/result |
| --- | --- |
| GET /oneTimeProducts/shelf_book_3 | Empty body; HTTP 200, stable shelf_book_3, single buy option, ACTIVE. |
| POST /pricing:convertRegionPrices | Body price={currencyCode:USD,units:"2",nanos:990000000}; HTTP 403, code 403, status PERMISSION_DENIED, message "The caller does not have permission"; no reason/details supplied. Non-mutating conversion only. |

Current official Google reference confirms the conversion endpoint/body/scope.
PATCH uses lowercase /onetimeproducts/{productId}, OneTimeProduct body,
updateMask, regionsVersion and optional allowMissing; the existing client uses
that documented shape. Its earlier 403 is preserved evidence, not a new PATCH
attempt. These methods expose no documented validateOnly option. A product
write cannot diagnose a denial already reproduced by non-mutating conversion.
No new request defect was established. The response establishes a Google-side
pricing authorization denial, not which Console grant/account prerequisite is
responsible; do not claim missing permission or merchant setup as proven.
Successful product read and owner purchase/refund/repurchase do not prove price
management permission. Changing endpoints/prices/availability to bypass this
denial is not justified.

Read-only production snapshot: SHELF_PLAY_SYNC_ENABLED resolves false;
staging=false; play-prices jobs=0. All six books 3–8 remain Published, not
deleted, with canonical shelf_book_<id> IDs and approved USD 2.99 admin prices.
Book 3 sync metadata Error, revision 5, attempts 1 (old attempt); books 4–8
Pending, revision 2, attempts 0. Synced price/active fields remain null.
These are local statuses, not proof of remote absence for books 4–8.
The installed cron runner remains gated by disabled sync; no queue processing,
remote writes, database edits or sync-status changes occurred in this task.

**One necessary owner action (read-only):** Play Console → Users and permissions,
search exact identity shelf-play@shelf-510123.iam.gserviceaccount.com, then
**Export user list**. Provide only its CSV row: Email, Status, App permissions,
Account permissions, Expiration time. This establishes the exact current grant
without repeating the previous permission setup. Google maps pricing/products
to CAN_MANAGE_PUBLIC_LISTING on services.shelf.app (Manage store presence), or
its account-wide equivalent if already granted. No new global/admin grant is
requested. If the export proves active access and the documented permission,
that is new evidence for a Google authorization escalation rather than another
speculative setup retry. Do not retry sync until the denial is resolved; enable
only after all approved settings/actions are reviewed and verified remotely.

No application code change or deployment was justified. Deployed staging and
production remain **c887f91**; starting documentation revision is **93f73df**.
This investigation's records are committed separately, without claiming a code
release. Synchronized records and cPanel PHP handler are preserved. No broad
automated tests, phone tests, backup recovery or test notification.

Next unattended offsite backup remains **5 October 03:30 America/Lower_Princes /
07:30 UTC**; last observed success is the controlled 4 October 23:39:27 UTC run,
not unattended evidence. Actual alert delivery and replacement-host recovery
remain unverified; D14 retention awaits owner decision; second-phone restore is
explicitly deferred to pre-release, NOT PASSED. Step 4 and Step 5 remain open.
Next work is to resolve this verified pricing authorization block using the
permission export, then complete D9/6.4; no unrelated building task started.

Official sources checked during this task:
[price conversion](https://developers.google.com/android-publisher/api-ref/rest/v3/monetization/convertRegionPrices?hl=en),
[product PATCH](https://developers.google.com/android-publisher/api-ref/rest/v3/monetization.onetimeproducts/patch),
[Play permissions](https://support.google.com/googleplay/android-developer/answer/9844686),
[permission CSV export](https://support.google.com/googleplay/android-developer/answer/10019561).

## Owner-confirmed app permissions and independent transport — 4 October 2026

Supersedes the earlier permission-export request: owner inspected the exact
shelf-play@shelf-510123.iam.gserviceaccount.com entry for Shelf
(services.shelf.app). Checked: View app information, View financial data,
Manage orders and subscriptions, Manage store presence; View app quality
information and Manage policy declarations greyed checked. App Admin unchecked;
all account-level permissions unchecked. No permission changed. Treat app-level
Manage store presence as granted, not missing; no broader Admin/global grant or
another CSV request is justified by this evidence. Existing merchant-profile
confirmation remains preserved.

At **2026-10-04 23:55:00 UTC**, bounded independent native PHP cURL probes used
one fresh token obtained by the existing service-account OAuth code, without
Laravel HTTP request serialization. Empty-body product GET returned 200;
POST price conversion with the documented USD 2.99 Money body returned the
same 403 PERMISSION_DENIED, "The caller does not have permission", no reason
or details. Sync still resolves false. No retries, product writes or altered
prices/availability; credentials remain unchanged. Safe private evidence:
/home/shelf/tmp/shelf-play-sync-20261004/independent-safe-evidence.json.

This strengthens the finding of Google pricing authorization denial and weakens
the request-serialization hypothesis. It does not prove the precise Google
backend restriction or account prerequisite. Correct caller/package/scope,
working authentication/product reads, owner's app-level grant, prior linked
merchant confirmation and independent request reproduction leave no evidenced
local repair. Google documents Manage store presence as allowing pricing and
in-app products, with app grants supported. Its setup guide explicitly says
Cloud-project linking is no longer required. Do not recreate keys, re-link
projects, re-upload releases or toggle permissions speculatively.

**Historical support draft (superseded by the Explorer comparison below):**
Play Console → Help → Contact us; earlier report retained as investigation history.
No agent message has been sent to support; the owner submits it. Do not attach
service-account JSON, bearer tokens, keys, .env or reader/order data.

> Google Play Developer API pricing authorization denied despite app-level grant.
> Package: services.shelf.app (Shelf). Service account:
> shelf-play@shelf-510123.iam.gserviceaccount.com, Cloud project shelf-510123.
> OAuth scope: https://www.googleapis.com/auth/androidpublisher.
> Owner verified app-level View app information, View financial data, Manage
> orders and subscriptions and Manage store presence. No App Admin or account-
> level permissions; merchant profile was previously confirmed linked.
> On 2026-10-04 at 23:55:00 UTC, the same service token returned HTTP 200 for
> GET /androidpublisher/v3/applications/services.shelf.app/oneTimeProducts/shelf_book_3
> (empty request body), but HTTP 403 for
> POST /androidpublisher/v3/applications/services.shelf.app/pricing:convertRegionPrices
> with {"price":{"currencyCode":"USD","units":"2","nanos":990000000}}.
> Full error: {"error":{"code":403,"message":"The caller does not have permission",
> "status":"PERMISSION_DENIED"}}. No reason/details returned. Independently
> reproduced through Laravel HTTP and native cURL. Prior onetimeproducts PATCH
> was also denied. Please identify the effective backend permission/account
> prerequisite denying monetization for this app-level-authorized caller and
> the least-privilege correction. We are not requesting broad Admin access.

Deployed code remains c887f91; documentation follows 6025bc0 in a separate
commit. No application fix/deployment, broad tests, phone/backup recovery or
notifications. The 5 October 03:30 America/Lower_Princes / 07:30 UTC unattended
backup is still future at this check; controlled recovery is not unattended
proof. Actual alert delivery, replacement-host recovery remain unverified;
D14 undecided; second-phone restore deferred, NOT PASSED. Steps 4 and 5 open.
Next: obtain Google's specific restriction/correction, then resume bounded
verification and approved D9 syncing; automatic sync remains disabled.

References: [service-account setup and no linking requirement](https://developers.google.com/android-publisher/getting_started),
[app-level grants](https://developers.google.com/android-publisher/api-ref/rest/v3/grants?hl=en),
[pricing permission](https://support.google.com/googleplay/android-developer/answer/9844686),
[conversion request/scope](https://developers.google.com/android-publisher/api-ref/rest/v3/monetization/convertRegionPrices?hl=en).

## Owner APIs Explorer success / exact server comparison — 5 October 2026 UTC

Owner reports Google APIs Explorer successfully returned convertedRegionPrices,
convertedOtherRegionsPrice and regionVersion={"version":"2026/01"} for
services.shelf.app using precisely:

POST https://androidpublisher.googleapis.com/androidpublisher/v3/applications/services.shelf.app/pricing:convertRegionPrices

```json
{"price":{"currencyCode":"USD","units":"2","nanos":990000000}}
```

This is owner-reported successful calculation evidence; no products were changed.
At **2026-10-05 00:17:11 UTC**, one native cURL reproduction with the existing
server service-account credential and the exact endpoint/body above returned
**HTTP 403**. Sanitized complete error fields:

```json
{"code":403,"message":"The caller does not have permission","status":"PERMISSION_DENIED"}
```

No reason/errors array or details supplied. Safe private evidence:
/home/shelf/tmp/shelf-play-sync-20261004/explorer-comparison-safe.json.

| Dimension | Server probe | Successful owner Explorer test |
| --- | --- | --- |
| Principal | shelf-play@shelf-510123.iam.gserviceaccount.com | Owner browser identity; distinct from server account; exact email not requested/recorded |
| OAuth | Service-account JWT grant; androidpublisher scope | Explorer browser OAuth; effective scope not supplied in owner evidence |
| Package, endpoint and Money body | Exact request above | Same request above |
| Credential Cloud project | shelf-510123 | Explorer client/consumer project not established |
| Explicit quota override | No quota_project_id in credential; GOOGLE_CLOUD_QUOTA_PROJECT absent; no x-goog-user-project or API key sent | Header/API-key/consumer configuration not established |
| Outcome | 403 PERMISSION_DENIED | Successful three-field conversion, regionVersion 2026/01 |

The custom server client neither uses ADC nor delegates to the owner's identity.
Its token is freshly acquired with the existing private key; no browser token was
requested/copied. Cloud's general quota documentation uses a service account's
associated project by default when no override is specified. The lack of an
explicit quota header alone does not establish a configuration fault; the exact
Play consumer-project resolution is not visible in this response. No speculative
quota header, project/IAM grant, API key or credential change was made.

The comparison demonstrates a caller/client-dependent outcome for the same
valid calculation request. It changes both principal and client environment,
so it proves neither a specific missing Play permission nor a quota-project
cause. App-level Manage store presence remains owner-confirmed granted. Prior
service-account product GET 200 and conversion/PATCH denial evidence are retained.
Owner-user success supports functioning calculation for this package; it does
not prove all service-account/account prerequisites or product writes work.

**Historical next action (superseded by the implementation audit below):** submit the revised paired-results report
below via Play Console → Help → Contact us, asking Google to identify the exact
service-account restriction and compare the consumer/quota context. No permission
toggle or broader Admin access is justified. Agent has not contacted support.

> Package services.shelf.app (Shelf): identical convertRegionPrices request
> succeeds in Google APIs Explorer as the owner, but fails with our server
> service account shelf-play@shelf-510123.iam.gserviceaccount.com.
> POST https://androidpublisher.googleapis.com/androidpublisher/v3/applications/services.shelf.app/pricing:convertRegionPrices
> Body: {"price":{"currencyCode":"USD","units":"2","nanos":990000000}}.
> Owner Explorer returned convertedRegionPrices, convertedOtherRegionsPrice and
> regionVersion {"version":"2026/01"}. On 2026-10-05 00:17:11 UTC our server
> returned HTTP 403: {"error":{"code":403,"message":"The caller does not have permission",
> "status":"PERMISSION_DENIED"}}, without reason/details. Existing credentials,
> Cloud project shelf-510123, service-account JWT OAuth grant, scope
> https://www.googleapis.com/auth/androidpublisher. No API key or explicit
> x-goog-user-project/quota override. Server product GET previously returned 200;
> conversion denial reproduced through Laravel and native cURL.
> Owner verified app-level View app information, View financial data, Manage
> orders and subscriptions, Manage store presence for this exact account/package.
> No App Admin/account-wide permissions. Selling merchant profile previously
> confirmed linked. The Explorer test changes both identity and client environment;
> we do not know its effective scope or consumer/quota-project configuration.
> Please identify the effective monetization restriction, including any consumer-
> project prerequisite, and least-privilege correction. No keys/tokens attached.

Sync remains disabled. No products, prices, availability, permissions or
credentials changed; no code deployment or broad tests. Deployed code c887f91,
prior documentation ee8d0b2, new records committed separately. Existing handler
and synchronized documents preserved. Automatic backup next due 5 October
03:30 America/Lower_Princes / 07:30 UTC; unattended success remains unverified
at this check. Actual alert delivery/replacement-host recovery unverified;
D14 undecided; second-phone restore deferred, NOT PASSED. Steps 4/5 stay open.

References: [conversion request](https://developers.google.com/android-publisher/api-ref/rest/v3/monetization/convertRegionPrices?hl=en),
[quota-project selection](https://docs.cloud.google.com/docs/quotas/set-quota-project).


## Shelf implementation/configuration audit — 5 October 2026 UTC

No conversion/product request repeated in this audit. No established local defect;
no application changes, deployment, products/prices/permissions/credential edits,
support contact or notifications. The previous owner Explorer success and exact
server denial remain paired evidence, without assigning a permission cause.

### Actual admin-to-worker path

- `CollectionForm.php` supplies the admin USD price; transactional EditCollection/
  CreateCollection saves reach `Collection::booted()` saving/saved hooks
  (`app/Models/Collection.php:53,90`). Price/title/publication/deletion changes
  persist price audit and durable revisioned sync intent after commit.
- `PlayPriceSync::request/enqueue` (`app/Services/Play/PlayPriceSync.php:12,29`)
  schedules book IDs only on dedicated database `play-prices`; configuration
  disabled means no dispatch. Worker reads the current book under locks, rather
  than an old serialized price, in `run` at line 49. Retry records retain safe
  messages. Publication controls activation; no owner price is invented.
- Every-minute cron invokes `scripts/run_play_price_sync.sh`, production-only
  root check, working directory /home/shelf/apps/shelf, flock, fresh CLI pending
  command then short-lived dedicated queue worker. Initial cron working directory
  /home/shelf and PATH /usr/local/bin:/usr/bin:/bin were reproduced for the
  configuration-only probe; the actual sync entry point was not run.
- `SyncPlayBook::handle` reaches the same `GooglePlayClient::sync`. Product GET,
  conversion POST, product PATCH, and separate state POST all use `request()`
  at line 80 and `token()` at line 22. Same base URL/package, bearer authentication,
  JSON Accept, redirect policy/timeouts and client; only method/path/body/query
  differ. GET sends no JSON body. Modern GET/state use oneTimeProducts; PATCH
  uses onetimeproducts exactly as documented. USD 2.99 converts to units "2",
  nanos 990000000. No separate pricing credentials, audience or OAuth scope.

### Runtime evidence and limits

Private configuration-only probe bootstrapped the deployed production app twice:
ordinary CLI and clean noninteractive cron environment as shelf. Both PHP 8.3.35,
production base, .env, uncached configuration, staging false, sync false,
services.shelf.app. Credential file hashes matched without recording their value
here; both decoded identity matches owner-inspected
shelf-play@shelf-510123.iam.gserviceaccount.com and project shelf-510123.
Neither inherited SHELF_PLAY_SYNC_ENABLED, SHELF_PLAY_SERVICE_ACCOUNT_PATH,
APP_ENV, APP_CONFIG_CACHE, GOOGLE_APPLICATION_CREDENTIALS or GOOGLE_CLOUD_QUOTA_PROJECT.
Probe performs no token exchange, HTTP or database writes.

`config/play_sync.php` is the sole production loader, explicit private path,
fixed approved package. `token()` validates file location/permissions/type,
signs service-account JWT iss matching that email, no delegated subject,
androidpublisher scope, OAuth token audience. No ADC or quota-project environment
handling in this custom client. Config/env overrides could matter to Laravel
bootstrap, but none appeared in these two actual contexts; config cache absent.
Staging AppServiceProvider overrides sync false/credentials null deliberately.
Promotion workflow optimize:clear; purchase setup initially disables sync,
uses a subprocess-only enabled override and enables persistently only after
all approved synchronizations succeed. Original fc10347 implementation and
later d178bc0 endpoint/body correction inspected; no repeated setup.

Web document root resolves the same production public directory and cPanel PHP83
handler is preserved. Source wiring shares config, but no Shelf-owned PHP process
was present during bounded host process inspection; actual web process environment
and effective web config are **not directly verified**. No temporary production
route/file was added. This limitation cannot explain the existing CLI-only denial
by itself. Cron starts fresh PHP processes, no long-lived price worker config.
Last measured price queue zero; sync remains disabled in both fresh probes.

### Pricing and documented permissions

D9 requires admin-only USD input and Google's automatic local prices. Conversion
is a chosen implementation of that requirement, not a universal Play API mandate.
The modern OneTimeProduct PATCH takes explicit regional price/availability and
regionsVersion; no documented autoConvertMissingPrices option. Legacy
inappproducts.patch does have that option, but previous legacy API investigation
was denied with a migrate-to-new-API message. Replacing the modern workflow or
freezing owner Explorer prices is not an established fix, and prior modern PATCH
403 remains independently unresolved. Keep existing approved pricing/availability.

Official method references require androidpublisher scope. Setup guide requires
API enabled, service account invited with appropriate Play permissions, and no
longer requires linking Play to a developer Cloud project. Owner confirmed merchant
setup historically. Play permission guide explicitly covers edit pricing/in-app
products under Manage store presence and supports app-specific access. The method
references do not publish a finer conversion-specific Play permission or demand
App Admin/account-wide access. Public docs cannot prove Google's effective grant
or prerequisite evaluation for this specific call.

Remaining hypotheses: effective Google monetization grant/account prerequisite
not reflected in inspected grants; consumer-project routing differing from Explorer;
less likely unseen web-only overrides (not a cause established for CLI failure).
No permission toggle, Cloud IAM grant or credential replacement justified.

**ONE next discriminating check, not executed:** one calculation-only request
with the existing server service-account token, same endpoint/body/client, adding
only `x-goog-user-project: shelf-510123` as a request-local header; no persistent
config change. Success would implicate consumer-project handling and justify
focused investigation before any sync. A structured SERVICE_DISABLED or
USER_PROJECT_DENIED/serviceusage.services.use error would identify a Cloud
consumer prerequisite for that explicit-header variant, not prove the original
Play denial had that cause. The same generic 403 would show explicit routing to
the intended project did not resolve it, reducing this hypothesis without proving
a missing Play permission. Cloud documentation notes serviceusage.services.use
is required for explicit quota-project use; do not grant it speculatively.
Support draft above is held pending this additional evidence; nothing sent.

References: [conversion](https://developers.google.com/android-publisher/api-ref/rest/v3/monetization/convertRegionPrices),
[modern PATCH](https://developers.google.com/android-publisher/api-ref/rest/v3/monetization.onetimeproducts/patch),
[legacy auto conversion](https://developers.google.com/android-publisher/api-ref/rest/v3/inappproducts/patch),
[setup](https://developers.google.com/android-publisher/getting_started),
[Play permissions](https://support.google.com/googleplay/android-developer/answer/9844686),
[consumer project](https://docs.cloud.google.com/docs/quotas/set-quota-project).


## Explicit consumer-project comparison — 5 October 2026, 00:29:38 UTC

Owner authorized the single proposed calculation-only comparison. Existing server
service-account credential, androidpublisher scope, native cURL client, endpoint
and exact USD 2.99 body retained; only request header added:
`x-goog-user-project: shelf-510123`. Fresh token obtained through the existing
loader; no credential/token values printed or stored. No API key added.

Result: **HTTP 403 PERMISSION_DENIED**, with a new structured error:

```json
{
  "code": 403,
  "status": "PERMISSION_DENIED",
  "reason": "USER_PROJECT_DENIED",
  "domain": "googleapis.com",
  "metadata": {
    "containerInfo": "shelf-510123",
    "consumer": "projects/shelf-510123",
    "service": "androidpublisher.googleapis.com"
  }
}
```

Sanitized message: caller lacks permission to use project shelf-510123; Google
names `serviceusage.services.use`, available via Service Usage Consumer or a custom
role. This identifies the Cloud prerequisite for **this explicit-header variant**.
It does not establish that the original header-free generic permission denial was
caused by that prerequisite, nor demonstrate that satisfying it would authorize
Play pricing or product PATCH. No speculative IAM grant or persistent header fix.
The original denial and owner Explorer success remain distinct evidence.

Practical implication: the header-only variant is blocked before it can establish
whether explicit consumer selection fixes pricing. Do not implement this header
or change permissions solely from this result. The held support draft can now
include both error forms when escalation is separately chosen; nothing sent.
The preceding proposed check is now executed; no repeated request or further
Cloud/Play write performed. Private sanitized evidence:
`/home/shelf/tmp/shelf-play-sync-20261004/consumer-project-safe.json`.
Sync confirmed false before the request and in its output. Products, prices,
permissions, credentials and deployed code c887f91 unchanged. No tests,
deployment or broad documentation reconciliation for this diagnostic.
