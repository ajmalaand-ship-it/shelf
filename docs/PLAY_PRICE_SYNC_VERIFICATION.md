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

**One necessary Console action:** Play Console → Help → Contact us: submit the
following report asking Google to identify the pricing authorization restriction.
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
