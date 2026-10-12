# Shelf iOS sign-in — source completion and remaining setup

11 October 2026. Apple and Google sign-in source is prepared on ios/foundation
at **32ca4a04929ad065af21d85241c5ca3a749b5007**.
Disabled backend rollout at 31d123aaee8310b71e9550dfa1e13047e44541db completed
under separate owner authorization on 12 October UTC; both Apple gates remain false.
No provider/signed-iPhone acceptance, further activation, build or upload is authorized. Build 1.0.0 (2)
processed, reached Shelf Owner internally and was installed/tested by the owner:
“seems good.” This is limited general phone acceptance of that older source.
The sign-in changes are not in installed build 2. Small Android/iOS improvements
are deferred until before launch; the joint public-launch hold remains.

The existing Apple App ID, entitlement, signing identities, Google web client,
email/password, secure reader tokens, owner/reader isolation and purchases are
reused. Apple native sign-in requests only email, binds a server-generated
five-minute one-use nonce, and sends its identity token/code for server validation.
The server checks signature, issuer, exact native audience, expiry, subject and
nonce, exchanges the code, checks the returned subject, and encrypts the refresh
token. Identity is the provider subject; matching email never automatically links
an existing reader. A collision says to use the existing method. Explicit account
linking is not implemented. Already-linked Google readers retain their identity.
New external-domain Google accounts retain the explicit email-confirmation flow.

Apple Hide My Email addresses are kept as supplied by the verified token; Shelf
never guesses or asks for the underlying address. Repeat Apple login uses the
subject even if Apple omits email. Password reset/deletion email goes to the stored
relay address. Deletion revokes Apple authorization first; failed revocation keeps
the account and tokens, and existing deletion-journal/financial preservation stays.
Google on iOS uses the existing server web audience and a separate iOS client plus
callback scheme; absent configuration hides that option. Android's provider remains.
Picker cancellation changes no server reader session. Both methods use the existing
server session/offline-identity path; neither grants book ownership.

## Owner setup update — 11 October 2026

Owner confirms Google iOS client creation and `SHELF_GOOGLE_IOS_CLIENT_ID` saved
in Codemagic group `shelf_ios_testflight`; Apple Sign in key **ShelfLogin**
downloaded privately; `shelf.services` relay registration with green SPF.
Actual relay delivery and real-provider/signed-iPhone authentication remain
unverified. These dashboard values have not been independently reread.

The private upload destination `/home/shelf/secrets/apple` was absent on initial
inspection and is now empty, owned by **shelf:shelf**, mode **700**. Use
[IOS_APPLE_KEY_UPLOAD.ps1](IOS_APPLE_KEY_UPLOAD.ps1) in Windows PowerShell to pick
the new ShelfLogin `.p8` from Downloads. It retains the original filename,
refuses overwrites and creates a **shelf:shelf / 600** file over trusted SSH.
The script restricts the selected local file to the current Windows user and
requires the server's SSH host key to be already trusted; it never accepts an
unknown/changed host automatically. Keep the original in encrypted private
storage with a secure backup, separately labelled from the upload API key.
No key has been transferred by Codex. The actual uploaded filename, rather than
the earlier proposed `sign-in-with-apple.p8`, will be used in any later approved
server configuration. Do not rename or upload the App Store Connect key instead.
No `.env` change, activation, deployment, build or Apple upload is authorized here.

## Current deployed status and next owner step — 12 October 2026 UTC

**Both staging and production: 31d123aaee8310b71e9550dfa1e13047e44541db**.
Owner confirms ShelfLogin / Key ID 2VQ43945YB / Sign in with Apple /
YSLWJQDH8B.services.shelf.app. Production has only the six approved Apple settings;
client/team/key/original path resolve correctly, configured=true,
**enabled=false / relay-ready=false**. Google Web/APP_KEY/all unrelated settings
unchanged; key/handler unchanged. Staging has no production Apple signing settings,
uses independent APP_KEY, log mail and disabled queue. Apple HTTPS endpoints 404,
accounts/Google advertised available, real checkout OFF and sync disabled.

Focused gate passed **43 PHP / 386 assertions** and deployed staging isolation/
HTTPS checks; existing Flutter evidence reused. Auth migration production batch
**27**, staging **10**, no pending migrations; history unchanged, health passed,
maintenance lifted. Initial gate confused sandbox enablement with real checkout;
corrected in isolation and reverified before final backed-up promotion, preserving
sandbox. Production backups/checkpoint and exact evidence are in Master Record;
final promotion backup **/home/shelf/backups/shelf/20261012-035221/**, private
configuration/key checkpoint **/home/shelf/backups/shelf/20261012-034809/ios-auth-checkpoint/**.
Earlier future/pending rollout instructions below are historical where superseded.
Documentation follow-up is not a new deployed application commit.

**Next owner action:** authorize a narrowly scoped owner-only native Apple
rehearsal on a signed iPhone using existing Shelf App ID **services.shelf.app** and
team **YSLWJQDH8B**, with no public-backend activation. It needs a reviewed local
rehearsal entry point using the existing AuthenticationServices implementation,
a cryptographically random local nonce, and the owner's choice **Hide My Email**.
Confirm a signing/install route for that rehearsal before executing it; no current
build/store upload is authorized. Retain only the actual relay email privately,
discard native tokens/codes without logging, and create no Shelf reader/session.
Do not turn either backend gate on merely to retrieve an address. This rehearsal
entry point does not exist yet; the normal challenge/login endpoints remain gated.
If the owner already has an active Shelf Sign in with Apple address, reuse that
address instead; do not revoke any real account to force a new first response.

Once that address is privately available, separately authorize **one owner-only
production relay message** through the existing Laravel mailer/sender, as specified
in the delivery procedure below. Keep its address/raw headers out of chat/git.
Owner confirms inbox receipt; inspect actual envelope sender and SPF/DKIM evidence
privately. Green registration SPF alone is not receipt; an @icloud.com alias does
not prove app relay. Both flags stay false if address/delivery evidence is missing.
Later activation and signed-phone reset/deletion/revocation/provider acceptance
remain separate steps. No new Services ID, Apple key or Google client is needed.

All public launch/UI/checkout holds and pricing-sync/second-phone-restore deferrals
remain. No email, real provider call, purchases, cloud build or store upload occurred.

## Rollout gate correction during verification — 12 October 2026 UTC

First staged candidate 15d873c passed 43 PHP/386 assertions and staging isolation.
Its production fast-forward/migration/history checks passed, migration batch 27,
maintenance lifted. A new post-check then falsely treated the existing sandbox
purchase enable setting as real checkout. Read-only diagnosis confirms production
healthy: Apple/relay false, Google configured, sandbox preserved, **real purchase
acceptance false**, sync false. Release-state completion was not written by the
failed check. This is a gate implementation error, not an actual purchase/control
failure. Corrected isolated gate reads `purchases.production_enabled` to verify
real checkout OFF without disabling the existing sandbox integration. Corrected
commit must pass staging then fresh backed-up promotion; no bypass of evidence.
No Apple .env settings were written before this diagnosis/correction.

## Authorized disabled backend rollout preparation — 12 October 2026 UTC

Owner authorizes staging verification and backed-up production promotion with
Apple disabled. Dashboard evidence confirms **ShelfLogin**, **2VQ43945YB**,
**Sign in with Apple**, association **YSLWJQDH8B.services.shelf.app**. This supersedes
the earlier dashboard-confirmation blocker; real Apple code exchange still remains
unverified. The verified original key path remains unchanged. No new Apple key,
Google client, upload integration or signing identity is required.

Prepared focused `ios-auth-backend-disabled` staging gate in workflow.py: only
IosSignInTest, ReaderAccountsTest and BookPurchasesTest through scripts/run_tests.sh,
actual staged schema/config checks, isolated staging lifecycle and HTTPS probes.
Existing Flutter/Android source evidence is reused; no Flutter run/build. The gate
requires no staging Apple credentials, both gates false, logged mail/null queue,
checkout/sync OFF. Promotion history comparison excludes only NULL newly added
Apple columns; non-null identities/tokens still affect the historical fingerprint.
Production HTTPS Apple login/challenge must return 404 and config advertises false.

Production local AGENTS/Master Record insertions were reconciled in isolation
without losing their content. Before removing their tracked working diffs for the
established fast-forward workflow, preserve exact originals, full diff/status and
cPanel handler in a verified production-backup checkpoint. Retain handler bytes
unchanged throughout. Separately encrypt and verify .env/private Apple key backup
locally; no offsite upload. Staging and production use the identical committed
candidate. Only after promotion write the six owner-approved Apple settings
privately, keeping both enabled/relay-ready **false**, preserve every other .env
value including Google Web client/APP_KEY, clear cached configuration and verify
actual deployed controls. Record completion and exact backup/migration evidence
in a documentation-only follow-up; that follow-up does not replace the verified
application commit or trigger another deployment/test run.

No actual relay email, Apple authorization, real provider login or signed iPhone
acceptance follows from disabled rollout. See the owner-only relay procedure below:
if no Shelf relay address already exists, a separately approved native authorization
rehearsal must obtain one while the public backend stays disabled. The current
normal flow cannot do this because its challenge requires both gates true. Do not
mark relay ready to work around that guard; the rehearsal is not implemented now.
No email/cloud build/store upload/Android rebuild/purchase in this task. All holds,
Android/UI pause and deferrals remain binding.

## Uploaded key verification and backend rollout plan — 11 October 2026

Owner reports upload success. Independently verified one regular file at
`/home/shelf/secrets/apple/AuthKey_2VQ43945YB.p8`: **shelf:shelf / 600**, parent
Apple directory **shelf:shelf / 700**, no symlink. OpenSSL private-key consistency
check passed. It is unencrypted PKCS#8 PEM, **EC P-256 / prime256v1**, compatible
with the implementation's ES256 client-secret signing. PHP OpenSSL parsing and
an in-memory SHA-256 sign/verify also passed. No private/public key components,
signature, JWT or contents were printed, copied, modified or committed.
These checks establish local format/usability, not Apple's capability, key status,
Key ID or association. Filename text is not authority for `APPLE_SIGN_IN_KEY_ID`.
The older transfer-pending/proposed-path entries above are historical.

### Required configuration before a separately approved rollout

On https://developer.apple.com/account select team **YSLWJQDH8B** →
**Certificates, Identifiers & Profiles → Keys → ShelfLogin**. Owner must confirm
its actual **Key ID**, **Sign in with Apple** capability and configured primary
App ID **services.shelf.app**, and that it is active. This is a read-only check;
no Save/recreation/revocation is needed if correct. Supply the Key ID as metadata,
never the `.p8` contents. Keep the existing App Store Connect upload API key and
Codemagic integration unchanged. Successful real code exchange will provide
provider acceptance evidence later; local parsing does not provide it.

Future private production `.env` mapping (NOT written now):

| Setting | Value |
| --- | --- |
| APPLE_SIGN_IN_CLIENT_ID | services.shelf.app |
| APPLE_SIGN_IN_TEAM_ID | YSLWJQDH8B |
| APPLE_SIGN_IN_KEY_ID | actual dashboard-confirmed Key ID; not inferred from filename |
| APPLE_SIGN_IN_PRIVATE_KEY_PATH | /home/shelf/secrets/apple/AuthKey_2VQ43945YB.p8 |
| APPLE_SIGN_IN_RELAY_READY | false until the delivery check below passes |
| APPLE_SIGN_IN_ENABLED | false through rollout; true only in separately approved activation |

Reuse existing `READER_ACCOUNTS_ENABLED`, `GOOGLE_WEB_CLIENT_ID`, Android client,
mail transport, APP_KEY and reader/purchase configuration; do not print or rotate
them. Keep the Apple private key on the server, never in Flutter/Codemagic.
Owner already saved `SHELF_GOOGLE_IOS_CLIENT_ID` in `shelf_ios_testflight`; no new
backend Google iOS audience or client secret is required. Later confirm sender
configuration using redacted metadata only; preserve staging's forced log mail.
Owner keeps encrypted independent key backup. Existing encrypted offsite source
includes `/home/shelf/secrets/apple` (production secret traversal excludes only
backup/staging), but recovery of THIS new key is not yet evidenced. Local daily
SQL/media backup does not itself back up this key or `.env`; explicitly protect
private configuration/key with an approved encrypted backup and record its path.
Do not initiate a backup upload as part of this documentation task.

### Ordered backend rollout, after separate authorization

1. Pin the release to an exact full commit containing implementation
   **32ca4a04929ad065af21d85241c5ca3a749b5007** and any reviewed rollout-helper work.
   Current subsequent changes are documentation/AGENTS only. Record the final
   release SHA, then use that identical SHA on staging and production. Do not
   silently merge iOS into main or alter the selected build source.
2. Read `python3 -B scripts/staging/workflow.py status`; currently both deployed
   commits are **5f5c8f6087d5d908b96558863129b8a09b44fcf8**, with passed
   review-3-backend-dependencies checks. Preserve the prior release/rollback SHA.
   Production currently has unrelated local **AGENTS.md**, **MASTER_RECORD.md**
   and **public/.htaccess** changes. The promote helper refuses more than its
   verified cPanel-handler exception: preserve/reconcile the documentation changes
   through a reviewed plan before promotion; never reset/stash away owner work.
3. Before staging deploy, prepare/review a narrowly scoped iOS-auth branch in
   `workflow.py checked()`, using `scripts/run_tests.sh` without `--mobile` and
   `SHELF_PHP_TEST_FILTER=IosSignInTest|ReaderAccountsTest|BookPurchasesTest`.
   The current helper has no iOS-auth gate and otherwise falls through to broad
   tests/Flutter because this release differs in mobile source. Do not claim a
   focused gate exists or reuse old staging evidence for a new commit. This is
   independent later source work, not performed by this documentation update.
   Reuse the completed **43 PHP / 386 assertions**, **32 Flutter + 1 staging
   identity**, Google plist fixture and analyzer evidence from IOS_SIGN_IN.md;
   repeat only the focused backend/environment checks necessary for promotion.
4. As shelf, from the trusted release checkout, run
   `python3 -B scripts/staging/workflow.py deploy RELEASE_SHA` with the reviewed
   focused gate. It backs up staging first, deploys, migrates and records checks.
   Apply only existing reversible migration
   `2026_10_11_120000_add_apple_reader_identity.php`: nullable unique Apple subject
   hash, encrypted refresh-token field and expiring challenge table. No reader,
   purchase, ownership, financial or catalogue rewriting. Keep Apple disabled and
   relay-ready false; no live Apple credential/provider exchange on staging.
   Verify staging isolation/logged mail, disabled Apple API configuration,
   email/password, existing Google policy/collision behavior, deletion and purchase
   isolation with synthetic fixtures. Use independent staging DB/config/APP_KEY.
5. After passing staging at RELEASE_SHA and explicit production approval, protect
   private configuration/key, then run
   `python3 -B scripts/staging/workflow.py promote RELEASE_SHA`. Its fresh verified
   SQL/media backup precedes maintenance/migration, checks historical reader/money
   fingerprints and restores service with `up` in its failure path. Record exact
   backup path, migration batch and both deployed SHAs. Configure only approved
   Apple fields privately in this later task; both gates remain false. Clear stale
   Laravel configuration through `optimize:clear` and verify effective values as
   booleans/redacted metadata, not a configuration dump. Verify existing account,
   purchase/webhook, catalogue/locked text and health behavior, disabled Apple,
   and private-file denial. No build, upload or purchases follow automatically.
6. Complete the owner-only relay check below before enabling either gate. Only
   after separate activation approval set relay-ready and enabled true, refresh
   configuration, and verify availability. Signed iPhone Apple/Google acceptance
   requires a later authorized internal build at an unused build number >2.
   Keep internal-only export, ditto, signing/photo-purpose checks and explicit
   artifact paths. Build 2 remains limited acceptance of older source.

Stop at the first live failure, restore service if maintenance was entered and
report. Before any Apple identities exist, roll back code to the recorded prior
release through the approved workflow and leave gates false; inspect the migration
batch before any rollback. Once identities exist, disable the gate but preserve
subject/encrypted-token records; never drop the migration or restore an older DB
across subsequent reader/purchase activity without a preservation plan.

### Practical owner-only relay delivery check (later authorization required)

Obtain privately an **owner-controlled, active Sign in with Apple address for
Shelf**, ending `@privaterelay.appleid.com`, with forwarding enabled. Never guess
it from the owner's real email or use somebody else's address. Apple's green SPF
registration is preliminary evidence only. An iCloud+ `@icloud.com` Hide My Email
alias is not equivalent evidence for the Sign in with Apple relay.

With separately authorized production mail access, send exactly one harmless
plain-text message to that address through Shelf's existing Laravel mailer and
actual `noreply@shelf.services` sender, subject **Shelf relay delivery check**,
body **This is the owner-only Shelf email delivery check. No action is required.**
Use no account token, reset link, purchase or database edit for this probe. Keep
address, transport secrets and raw headers private. Owner confirms inbox receipt;
inspect sanitized sending/result metadata and private headers for actual envelope
sender and SPF/DKIM authentication/alignment. Record timestamp, received/not
received and authentication outcome, not the address/headers. SMTP acceptance
alone does not pass delivery; failure leaves both gates false. Staging never sends.

If the owner does not yet have a Shelf relay address, record this as a bootstrap
blocker: both current Apple challenge and login require `AppleIdentity::enabled()`.
Do not set relay-ready true merely to get an address. A later separately approved
owner-only native authorization rehearsal must obtain Shelf's real relay address
without activating the public backend or creating a reader; its token/code must
stay private and not be logged or retained. This requires a reviewed rehearsal
path, which is not implemented/authorized now. After mail delivery passes, later
signed-phone acceptance must still exercise genuine reset/deletion email and
revocation on a disposable owner Apple reader, with no purchases or real-account
deletion. This separates mail readiness from full account acceptance.

Checkout OFF, pricing-sync/second-phone-restore deferrals, Android/UI pause,
deferred improvements and joint public-launch hold remain unchanged.

## One consolidated owner setup guide

Independent source work and focused checks are complete. These are the remaining
setup items, for the next separately approved activation/phone-validation task.
No dashboard was changed by Codex. Generated client/key IDs must come from the
owner's actual dashboard; no example credential should be pasted. Private key
contents must stay out of chat, git, app artifacts and screenshots.

1. **Google iOS client:** open [Google Cloud Clients](https://console.cloud.google.com/auth/clients?project=shelf-510123).
   Select project **Shelf / shelf-510123** → **Google Auth Platform → Clients**.
   Reuse an existing **iOS** client if its **Bundle ID** is **services.shelf.app**;
   never replace an Android or Web client. If missing, choose **Create client**,
   **Application type: iOS**, **Name: Shelf iOS**, **Bundle ID: services.shelf.app**.
   If shown, **App Store ID: 6821471140**, **Team ID: YSLWJQDH8B**.
   Click **Create**; if editing an existing iOS client, click **Save** only for
   those exact fields. Copy its generated **Client ID** (ending in
   `.apps.googleusercontent.com`). Existing consent/test-user and web/Android
   clients stay unchanged; use the already permitted owner Google account.
2. **Codemagic public client value:** open [Codemagic](https://codemagic.io/apps)
   → Shelf application → **Environment variables** → group **shelf_ios_testflight**.
   Add **Name: SHELF_GOOGLE_IOS_CLIENT_ID**, **Value: the actual iOS Client ID from
   step 1**, group **shelf_ios_testflight**. Click **Add** / **Save** as shown.
   The workflow writes **GIDClientID** and derives the reversed callback scheme;
   do not supply a guessed scheme or a new web client. The existing server
   **GOOGLE_WEB_CLIENT_ID** is returned by Shelf and reused automatically.
   This setup applies to **ios-testflight-internal** in Codemagic; the prepared
   GitHub fallback would need equivalent configuration if separately chosen later.
3. **Apple Sign in with Apple key:** open [Apple Developer account](https://developer.apple.com/account)
   → team **YSLWJQDH8B** → **Certificates, Identifiers & Profiles → Keys**.
   Reuse a privately retained key only if **Sign in with Apple** is enabled and
   associated with primary App ID **services.shelf.app**. The App Store Connect
   upload API key is not assumed to have this capability. If no appropriate key
   exists, click **+**, **Key Name: Shelf account sign-in**, check **Sign in with
   Apple**, click **Configure**, choose primary App ID **services.shelf.app**,
   click **Save**, **Continue**, **Register**, then **Download**. Retain its actual
   **Key ID** and downloaded `.p8` privately; do not revoke or replace existing
   certificates/profiles/upload integration. The native flow needs no new Services
   ID, website callback or client secret in the app.
4. **Apple relay email:** on the same Apple site → **Certificates, Identifiers &
   Profiles → Services → Sign in with Apple for Email Communication → Configure**.
   **Email Sources → +**, enter **shelf.services**, click **Next**, verify the
   source, then **Register**. Repeat for **noreply@shelf.services** if not covered
   by the existing domain entry. Keep delivery-failure notifications enabled.
   Apple's registration confirmation uses **Register**, not the notification
   settings' **Save** button. Confirm the SPF check passes; the later activation
   task must verify Shelf's actual envelope sender/DKIM alignment and delivery to
   an owner Hide My Email address. Do not invent DNS records or mark delivery
   passed from a dashboard alone.
5. **Private server preparation, then separately approved rollout:** supply the
   Apple `.p8` through a private file-transfer channel, not chat. Proposed private
   server location: **/home/shelf/secrets/apple/sign-in-with-apple.p8**; the rollout
   task must secure and back it up without printing it. Required server mapping:

   | Setting | Value |
   | --- | --- |
   | APPLE_SIGN_IN_CLIENT_ID | services.shelf.app |
   | APPLE_SIGN_IN_TEAM_ID | YSLWJQDH8B |
   | APPLE_SIGN_IN_KEY_ID | actual Key ID from step 3 |
   | APPLE_SIGN_IN_PRIVATE_KEY_PATH | approved private server file path |
   | APPLE_SIGN_IN_RELAY_READY | true only after registered/authenticated sender and owner relay delivery evidence |
   | APPLE_SIGN_IN_ENABLED | true only during separately approved activation |

   No server settings are saved in this batch. The new reversible migration adds
   nullable Apple subject/encrypted-token fields and a short-lived challenge table;
   it must pass isolated/staging checks and backed-up same-commit promotion under
   R3 before activation. Staging remains isolated and never sends real mail; relay
   delivery proof is a later explicitly approved owner-only production mail check.

## Validation and next phone step

Source verification: 43 focused PHP tests / 386 assertions; 32 focused Flutter
checks plus one staging-identity check; Google plist callback/configuration fixture;
static analysis with no errors/warnings (existing style infos), plist/YAML/shell
and diff checks. Tests use synthetic provider keys/responses and disposable
storage/in-memory DB. Temporary test fixtures are cleaned by scripts/run_tests.sh.
No native Swift/Xcode compile or real-provider request is claimed.

After setup and approved backend promotion, separately authorize one signed
internal build from the corrected source with a confirmed unused build number
**greater than 2**. Keep internal-only export, signing checks, photo-purpose guard,
explicit artifact paths, checkout OFF and existing integration. Do not rebuild
unsigned compilation simply for preparation. Inspect signing/artifacts/Apple
processing, assign it to **Shelf Owner**, and update through TestFlight.

On that signed iPhone, check Apple Sign in with Hide My Email, cancellation,
sign-out/repeat login (same reader and Library), Google selection/cancellation/
repeat login, and email/password. Use dedicated owner test identities; an email
collision must keep the old account/purchases and advise the existing method.
Check switching identities does not show another reader's Library/offline data.
A disposable Apple test account must receive relay reset/deletion mail and be
deleted through existing confirmation with Apple revocation evidence. Do not
delete a real/purchased owner identity merely for a test. No purchases in this batch.

Explicit linking, ongoing Apple account/revocation notifications, final provider
privacy/disclosure review and wider device acceptance remain separate release
checks; existing server session expiry is unchanged. No App Store purchase work,
financial-record rewrite or automatic ownership transfer is included.
Rollback before activation is a normal git revert. Once Apple readers exist,
keep their identity/token records until an approved preservation plan; disabling
the feature/code does not authorize dropping columns or altering their purchases.

Primary references: [Apple token verification](https://developer.apple.com/documentation/signinwithapple/verifying-a-user),
[Apple key setup](https://developer.apple.com/help/account/capabilities/create-a-sign-in-with-apple-private-key),
[Apple relay setup](https://developer.apple.com/help/account/capabilities/configure-private-email-relay-service/),
[Google iOS setup](https://developers.google.com/identity/sign-in/ios/start-integrating),
[Flutter Google iOS integration](https://pub.dev/packages/google_sign_in_ios).
