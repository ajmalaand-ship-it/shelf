# Shelf iOS foundation

Current primary signed route: see **Primary signed route: GitHub Actions — 11 October 2026** and its complete Windows/browser guide below. Codemagic sections remain historical setup evidence.

See the authoritative 10 October iOS entry in MASTER_RECORD.md for source,
owner-confirmed setup, evidence, boundaries and unfinished work.

Connect **https://github.com/ajmalaand-ship-it/shelf**, branch **ios/foundation**,
to Codemagic; project directory **mobile**, YAML at repository root. Select the
branch and check for configuration. Do not start a build or enable billing,
automatic triggering/publishing, signing or store distribution in this task.
Workflow **ios-foundation** deliberately compiles unsigned and does not export
an installable IPA. No Apple credential is needed merely to connect GitHub.
GitHub repository-access authorization is private and limited to Shelf.

For a later authorized compilation, set **SHELF_API_BASE_URL** privately in
Codemagic to the approved HTTPS API address. Existing default checkout flags are
false; neither this variable nor a native build enables real purchases. Signing
certificates/profiles/App Store API keys must stay in the provider's protected
credential storage, never committed. Do not add Android signing files or server
.env to the cloud configuration. No native plugin or signing success is claimed
until the actual macOS build passes. CocoaPods lockfile cannot be finalized on
this Linux host; capture it after the first approved native resolution, review
versions/privacy manifests and commit before reproducible signed distribution.

Source verification: **python3 -B scripts/check_ios_foundation.py**. Shared checks
use **scripts/run_tests.sh --mobile** on this server. macOS compilation and iPhone
behavior must be recorded separately; unsigned .app artifacts prove neither.

Official references inspected 10 October:
- [Codemagic Flutter YAML and repository connection](https://docs.codemagic.io/yaml-quick-start/building-a-flutter-app/)
- [Flutter iOS setup](https://docs.flutter.dev/platform-integration/ios/setup)
- [Flutter native channels and implicit engine lifecycle](https://docs.flutter.dev/platform-integration/platform-channels)
- [Secure storage Keychain setup](https://pub.dev/packages/flutter_secure_storage)
- [Gallery save plugin setup](https://github.com/natsuk4ze/gal)
- [Native share requirements](https://pub.dev/packages/share_plus)

## Prepared GitHub Actions fallback (10 October)

`.github/workflows/ios-foundation.yml` uses a deliberate **draft PR creation**
from this repository's `ios/foundation` to `main` as the build request. It runs
only on `opened`, checks out the exact head SHA and never builds a merged main
tree. Pushes do not launch it. No default-branch change/merge is needed.

Next execution action: [compare main with ios/foundation](https://github.com/ajmalaand-ship-it/shelf/compare/main...ios/foundation?expand=1)
→ **Create draft pull request**, title **Run unsigned Shelf iOS foundation**.
Do not merge. No build has been launched here. Static checks passed, native
compilation remains unverified. Existing Codemagic queue/configuration unchanged.

GitHub SSH repository access passed. Authenticated API access is unavailable:
Actions permissions and account billing usage GETs returned **401**. Enabled
Actions policy and remaining usage/spending controls are unknown. Repository
metadata currently reports **public**, contrary to prior private-repository notes.
No account/settings/visibility changes made. See Master Record for exact evidence.

[GitHub trigger requirements](https://docs.github.com/en/actions/reference/workflows-and-actions/events-that-trigger-workflows)
explain the PR-head checkout and conflict caveat;
[manual dispatch requirements](https://docs.github.com/en/actions/how-tos/manage-workflow-runs/manually-run-a-workflow)
require default-branch presence for workflow_dispatch.

## Successful unsigned build and prepared signed workflow — 10 October 2026

Owner-provided Codemagic evidence: **6acad0a1546ce6236ced5f95**, finished,
green success, source **869ebde**, branch **ios/foundation**, **Mac mini M2**.
Execution **4m25s**, including unsigned compilation **2m03s**. The preceding
queue delay is separate; its duration is not supplied. This is owner-confirmed
native compilation evidence, not independently fetched logs, signing, upload,
provider login/purchase or iPhone acceptance. The successful build is not repeated.

Prepared **ios-testflight-internal** in root codemagic.yaml. Same machine,
Flutter 3.47.2, Xcode latest, CocoaPods default and locked Flutter dependencies
as the successful workflow; exact unsigned workflow mapping retained. It requires
stored Apple Distribution certificate/profile, uses an internal-only export,
builds a signed IPA, verifies package/version/team/entitlements/signature and
retains dependency/signing evidence. A later authorized manual start uploads to
App Store Connect with neither beta-review nor App Store submission. No automatic
trigger or automatic tester assignment. Public distribution is disabled by the
export itself and publishing settings. GitHub fallback unchanged, PR not merged.

Current stopping point: preparation only. The steps below are the complete guide
for a **separately authorized owner credential setup**, not actions performed here.
Do not start a signed build or upload in this task. No credential contents belong
in chat, this repository, the server .env, screenshots or build artifacts.

### Owner signing setup guide

1. **Apple API access and key — https://appstoreconnect.apple.com/**
   Sign in to the existing Apple account; do not create an account or app.
   Open **Users and Access → Integrations → App Store Connect API → Team Keys**.
   If API access is not enabled, the **Account Holder** must choose **Request
   Access**, accept the terms and **Submit**; wait for Apple's approval.
   Creating a team key requires **Account Holder or Admin**. Reuse an appropriate
   existing dedicated key if already configured; otherwise **Generate API Key**
   (or **+**), **Name: Shelf Codemagic**, **Access: App Manager**, **Generate**.
   **App Manager is the minimum key role required by Codemagic's publishing guide**;
   do not grant the integration Admin just for upload. A team key applies across
   the account's apps; Apple does not offer per-app restriction for team keys.
   Note its **Issuer ID** and **Key ID** privately. **Download API Key**, confirm
   **Download** if shown, and retain the .p8 securely (downloadable once).
   Success: the dedicated key appears in **Active** with **App Manager** access.
   This API key is separate from the app-signing certificate/private key.
   [Apple API steps and roles](https://developer.apple.com/help/app-store-connect/get-started/app-store-connect-api/)

2. **Save the integration — https://codemagic.io/**
   In the existing Shelf team, use **Team settings → Team integrations → Developer
   Portal → Manage keys → Add key**. A **Codemagic team admin** manages signing
   identities. Enter **App Store Connect API key name: shelf_app_store_connect**
   (exactly, matching YAML), Apple's **Issuer ID**, **Key ID**, and use
   **Choose a .p8 file** to upload privately. Click **Save**.
   Success: **shelf_app_store_connect** appears among saved Developer Portal keys;
   if validation reports an error, stop and report only its non-secret message.
   A saved row does not prove a signed build or upload.
   [Codemagic signing setup](https://docs.codemagic.io/yaml-code-signing/signing-ios/)

3. **Store an Apple Distribution signing certificate in Codemagic**
   Under **Team settings → codemagic.yaml settings → Code signing identities →
   iOS certificates**, reuse a valid existing Shelf distribution certificate with
   its private key, if available. Upload its .p12/.pem, enter its password privately
   if protected, **Reference name: shelf_ios_distribution**, **Add certificate**.
   If none exists, the later authorized setup can choose **Generate certificate**,
   **Reference name: shelf_ios_distribution**, **Certificate type: Apple
   Distribution**, **App Store Connect API key: shelf_app_store_connect**, then
   **Create certificate**. Download the generated certificate once and retain its
   password privately; follow the upload fields above to **Add certificate**.
   Success: the stored distribution certificate is valid, unexpired and belongs
   to **YSLWJQDH8B**, under that exact reference. Never revoke other certificates
   to make room; report Apple's certificate-limit error if encountered.
   [Codemagic first-signing sequence](https://docs.codemagic.io/yaml-quick-start/first-signed-build/)

4. **Provision the existing app — https://developer.apple.com/account/**
   The owner uses the existing Individual membership (**Account Holder**; Apple's
   minimum website role for this profile action is **Admin**). Open
   **Certificates, Identifiers & Profiles → Profiles → +**; under Distribution
   select **App Store Connect**, **Continue**. Select the existing explicit App ID
   **services.shelf.app**, **Continue**, select the distribution certificate from
   step 3, **Continue**. **Profile Name: Shelf iOS App Store**, **Generate**,
   **Download**. It must belong to **YSLWJQDH8B** and support the existing
   **Sign In with Apple** entitlement; do not disable that capability or register
   another identifier. No device UDID is needed for this App Store profile.
   In Codemagic **Code signing identities → iOS provisioning profiles**, upload
   the .mobileprovision using **Choose a .mobileprovision file**,
   **Reference name: shelf_ios_app_store**, **Add profile**.
   Success: **App Store** type, correct team and bundle, valid expiry and a **green
   certificate checkmark** matching the stored distribution certificate.
   [Apple profile creation](https://developer.apple.com/help/account/provisioning-profiles/create-an-app-store-provisioning-profile/)

5. **Retain API configuration and set the upload version**
   In the existing Shelf Codemagic application's **Environment variables**, retain
   **SHELF_API_BASE_URL=https://shelf.services/api/** in **shelf_ios_foundation**.
   Add **SHELF_IOS_BUILD_NUMBER** in group **shelf_ios_testflight**, then use the
   variable row's **Add/Save** control (label depends on whether adding/editing).
   This is a positive integer for iOS only. Use **1 only after checking the
   existing Shelf app's TestFlight tab has no uploaded builds**; otherwise use a
   number greater than all existing uploaded iOS builds. Do not guess from the
   Android version or the unsigned compilation. YAML sets marketing version
   **1.0.0** through build arguments; native/pubspec source is unchanged.
   Success: both groups and their exact values are saved and the refreshed branch
   YAML lists **ios-testflight-internal**. No signing key belongs in these groups.
   The workflow has no triggers: pushing does not start it. **Stop here**;
   signed Start new build/upload requires separate authorization.

6. **Later upload and owner-only installation confirmation**
   After a separately authorized signed build, check its signed IPA, signing
   verification and publishing logs, then Apple **My Apps → Shelf - شیلف →
   TestFlight** for the matching **1.0.0/build number**. An internal-only build
   should be marked **internal**. Green compilation alone does not mean Apple
   accepted or processed the upload. Resolve **Missing Compliance** from the
   actual encryption inventory; no exemption is guessed in source. Shelf uses
   HTTPS and Dart cryptography for offline encryption, not just UI locking.
   [Apple export compliance](https://developer.apple.com/help/app-store-connect/manage-app-information/overview-of-export-compliance/)
   For later owner testing, **Internal Testing → +**, name **Shelf Owner**, keep
   automatic distribution disabled, **Create**. **Invite Testers → select owner only → Add**;
   **Add Builds → matching internal build → Next**. Enter **What to Test**:
   "Store, search, free samples, reader fonts/RTL, share cards, photos and audio.
   Checkout is off. Apple/Google login and App Store purchases are unfinished."
   Click **Add**.
   Success: that build is available to the owner in TestFlight on their iPhone.
   No external testers/beta review/public App Store submission.
   [Apple internal-testing steps](https://developer.apple.com/help/app-store-connect/test-a-beta-version/add-internal-testers)

### Limited first-phone scope and remaining blockers

No new compile-source blocker was found for Store/search/free-sample/reader/native
UX inspection. Successful compilation supports that narrow finding only. Signing
certificate/profile/API integration existence and Apple upload/processing acceptance
are still unverified. CocoaPods lockfile and native privacy manifests from the
successful run have not been supplied; do not invent a lockfile or approval.
The signed workflow retains Podfile.lock and manifest paths for subsequent review.

**Updated 11 October:** Apple processed **1.0.0 (2)**, assigned to **Shelf Owner**,
and the owner installed/tested it: “seems good.” Limited phone acceptance only;
individual sign-in, deletion, offline, reading and provider checks are not inferred.
Apple/Google iOS sign-in source has since been implemented; it is not in build 2.
Missing provider setup and backend promotion still block end-to-end activation.
Use the consolidated [iOS sign-in setup/validation guide](IOS_SIGN_IN.md).
App Store purchases remain unfinished and outside this sign-in batch. Small
Android/iOS improvements are deferred until before launch; checkout stays OFF
and joint public release remains held.

The stored profiles/internal export are documented by
[Codemagic's Flutter guide](https://docs.codemagic.io/yaml-quick-start/building-a-flutter-app/).
Upload uses the integration, with submit_to_testflight=false (no external beta
review) and submit_to_app_store=false; owner chooses an internal build manually.
[Codemagic publishing semantics](https://docs.codemagic.io/yaml-publishing/app-store-connect/)

Checkout OFF; pricing sync and second-phone restore deferred; joint public-launch
hold retained. No production deployment, Android changes, signed build, upload,
credential creation or paid activation performed by this task.

## Primary signed route: GitHub Actions — 11 October 2026

Prepared `.github/workflows/ios-testflight.yml`, workflow **Shelf signed iOS
internal TestFlight**, with helper `scripts/ios/github_testflight.py`. It starts
only by **workflow_dispatch**, accepts a full selected `ios/foundation` source
SHA and an explicit unused iOS build number, and checks out that exact commit.
It never uses a PR merge tree or `pull_request_target`. It reuses the passed
GitHub `macos-15` / Flutter 3.47.2 setup and pinned checkout/artifact actions.
Native hosted image versions can change; no unobserved Xcode version is invented.
The unsigned workflow and Codemagic configuration remain preserved.

Existing Apple Distribution certificate, explicit App Store profile and App Store
Connect team API key are supplied through the six secrets below. No new credential
is created. The helper removes secrets from child-process environments, imports
the identity into a temporary keychain, checks the profile/team/expiry/certificate,
and alters signing settings only in Runner configurations in the ephemeral runner
checkout. It exports internal-only with no automatic version rewriting, verifies
IPA CRC/signatures/package/version/team/entitlements/profile/signer, and calls
Apple binary validation before upload. API build-number checks before compilation
and immediately before upload fail closed on unavailable or duplicate evidence.
No API for review, release, certificate creation or tester assignment is called.

Cleanup runs in Python finally (including handled cancellation) and an independent
GitHub always() step, restores the original keychain search list and removes only
this run's imported profiles/keychain/p12/p8/decoded files. Cleanup failures fail
the job; an abrupt runner loss is additionally covered by GitHub-hosted VM disposal.
Artifacts contain IPA and non-secret verification/export/Podfile.lock evidence;
private credentials and decoded profiles are not artifact paths.

GitHub is now the prepared primary signed route because the owner reports the
signed Codemagic attempt remains queued. **No cancellation is confirmed**, and
no Apple received-build state has been inspected using owner credentials here.
The saved Codemagic build number 1 is not imported as a GitHub default. The helper
rejects **1** unless the owner explicitly confirms Codemagic cancellation before
upload AND Apple has not received 1; it also checks Apple's API. Any other number
must also be owner-confirmed unused and pass the API check. No build is started
or cancelled in preparation. An upload error/timeout is not proof of no upload;
inspect Apple before choosing any number or rerunning.

### Complete Windows/browser setup guide for the GitHub primary route

**Current boundary:** this guide is ready for owner setup. Preparation has not
registered the workflow on main, saved GitHub secrets, cancelled Codemagic, started
a build or uploaded anything. Use existing accounts/files; no new Apple credential,
app or paid-service activation is needed. Steps 1–5 prepare access/configuration;
step 6 is a later separately authorized build, not an instruction to start now.

1. **Resolve Codemagic and Apple build-number state first.**
   In [Codemagic](https://codemagic.io/), open the existing Shelf signed attempt.
   Under a separately authorized cancellation step, use its **Cancel build**
   control, confirm **Cancel** if shown, then wait for a terminal **cancelled**
   status. Inspect the attempt's steps/publishing log: cancellation must have
   happened before upload to permit reusing number 1. This task has not cancelled
   it; a queued screenshot or cancel click alone is not confirmation.
   In [App Store Connect](https://appstoreconnect.apple.com/), open **Apps →
   Shelf - شیلف → TestFlight → iOS** and check all received builds, including
   Processing and failed/invalid uploads. Use **1 only when both cancellation
   before upload and no received 1 are confirmed**. Otherwise choose a fresh
   positive number greater than the received iOS build numbers; do not assume
   cancellation or derive a number from Android. Keep this decision until step 6.

2. **Locate the existing signing files on Windows.**
   Use the already downloaded **Apple Distribution .p12**, its saved certificate
   password, the **services.shelf.app App Store .mobileprovision**, and the existing
   App Store Connect **AuthKey_… .p8**, **Key ID** and **Issuer ID** from the prior
   owner setup. The .p12 must contain the private signing key, not just a public
   .cer. Team is **YSLWJQDH8B**. Profile must match that certificate, be unexpired,
   App Store type and retain Sign In with Apple. Existing API key role is
   **App Manager**, as required by Codemagic's upload configuration.
   If a file/password is unavailable, stop and identify which item is missing;
   do not create/revoke certificates or keys as a workaround. Keep values local.

3. **Create exactly six GitHub repository secrets.**
   Open [Shelf Actions secrets](https://github.com/ajmalaand-ship-it/shelf/settings/secrets/actions):
   **Shelf repository → Settings → Secrets and variables → Actions → Secrets**.
   For each row below, click **New repository secret**, enter the exact **Name**,
   put the corresponding value in **Secret**, then click **Add secret**. If the
   exact name already exists, use its edit/pencil control and **Update secret**.
   Do not enter these under Variables. Success is a saved secret-name row; GitHub
   does not reveal its value again. Never paste secret values into chat.

   | Exact GitHub secret Name | Value to put privately in Secret |
   | --- | --- |
   | `SHELF_IOS_DISTRIBUTION_P12_BASE64` | Base64 of the existing .p12 file |
   | `SHELF_IOS_DISTRIBUTION_P12_PASSWORD` | The password protecting that .p12; enter directly in the browser |
   | `SHELF_IOS_APP_STORE_PROFILE_BASE64` | Base64 of the existing .mobileprovision file |
   | `SHELF_APP_STORE_CONNECT_KEY_ID` | Existing Apple Key ID, entered directly |
   | `SHELF_APP_STORE_CONNECT_ISSUER_ID` | Existing Apple Issuer ID, entered directly |
   | `SHELF_APP_STORE_CONNECT_PRIVATE_KEY` | Entire existing .p8 PEM text, including BEGIN/END lines; not Base64 |

   **Windows PowerShell — .p12 preparation, clipboard only:**

   ```powershell
   $shelfP12Path = Read-Host 'Full path to the existing Apple Distribution .p12'
   $shelfP12Encoded = [Convert]::ToBase64String([IO.File]::ReadAllBytes($shelfP12Path))
   if ($shelfP12Encoded.Length -gt 48000) { throw 'Encoded certificate exceeds the prepared secret size limit; stop here.' }
   Set-Clipboard -Value $shelfP12Encoded
   Remove-Variable shelfP12Encoded
   ```

   Paste into `SHELF_IOS_DISTRIBUTION_P12_BASE64` → **Add secret**.
   Enter the certificate password directly in the browser's **Secret** field
   for `SHELF_IOS_DISTRIBUTION_P12_PASSWORD` → **Add secret**. Do not type that
   password in PowerShell or use the Apple-account password in its place.

   **Windows PowerShell — provisioning profile, clipboard only:**

   ```powershell
   $shelfProfilePath = Read-Host 'Full path to the existing App Store .mobileprovision'
   $shelfProfileEncoded = [Convert]::ToBase64String([IO.File]::ReadAllBytes($shelfProfilePath))
   if ($shelfProfileEncoded.Length -gt 48000) { throw 'Encoded profile exceeds the prepared secret size limit; stop here.' }
   Set-Clipboard -Value $shelfProfileEncoded
   Remove-Variable shelfProfileEncoded
   ```

   Paste into `SHELF_IOS_APP_STORE_PROFILE_BASE64` → **Add secret**.

   **Windows PowerShell — existing .p8, clipboard only:**

   ```powershell
   $shelfP8Path = Read-Host 'Full path to the existing App Store Connect .p8'
   Set-Clipboard -Value ([IO.File]::ReadAllText($shelfP8Path))
   ```

   Paste into `SHELF_APP_STORE_CONNECT_PRIVATE_KEY` → **Add secret**. Add the
   existing Key ID and Issuer ID in their respective rows → **Add secret** each.
   Finish with `Set-Clipboard -Value ''`. These commands print no file contents,
   create no encoded files and do not upload anything by themselves. Base64 is an
   encoding, not encryption; keep clipboard history/sharing private.
   No keychain-password secret is needed: the runner generates an ephemeral one.

   **Success:** all six exact names are listed under Repository secrets. Secret
   presence is not proof of credential validity; future signing checks that.

4. **Register only the manual workflow file on the existing default branch.**
   GitHub requires workflow_dispatch to exist on the default branch. The prepared
   file currently lives on **ios/foundation**; pushing it there does not satisfy
   registration and cannot provide a reliable Run workflow button alone.
   Do not change the default branch or merge ios/foundation into old main.

   Under the later authorized workflow-registration step, open
   [the prepared signed workflow](https://github.com/ajmalaand-ship-it/shelf/blob/ios/foundation/.github/workflows/ios-testflight.yml),
   use **Raw / Copy raw file** to copy the complete non-secret YAML. Open the
   [repository](https://github.com/ajmalaand-ship-it/shelf), select **main** in the
   branch selector → **Add file → Create new file**. Enter filename
   **.github/workflows/ios-testflight.yml**, paste the exact prepared YAML,
   **Commit changes…**. Message: **Register manual Shelf iOS TestFlight workflow**.
   Select **Commit directly to main**, then **Commit changes**.
   If main protection blocks that action, stop for a workflow-only registration
   task; do not merge the existing iOS PR or relax protection. If the file already
   exists on main, review it against the prepared file instead of overwriting it
   blindly. No helper/mobile/source/main merge is needed: the workflow checks out
   the selected iOS source before using its helper. The YAML has no push/PR trigger,
   so this one-file registration does not start a build.

   **Success:** main contains exactly this manual workflow file; GitHub **Actions**
   lists **Shelf signed iOS internal TestFlight** with **Run workflow** available.
   main's application source remains untouched. Registration has not been performed
   by this preparation task. Keep the iOS draft PR unmerged.

5. **Check access and select the exact source without starting it.**
   Repository write access is required to dispatch. In **Settings → Actions →
   General**, check existing Actions and allowed-action policy permits the two
   pinned GitHub actions. Do not enable paid billing, buy minutes, relax security
   policy or change GITHUB_TOKEN to write. Account usage/policy remains unavailable
   to this agent's authenticated API; no entitlement/capacity is assumed.
   The workflow uses **contents: read** only. The prepared workflow uses the six
   repository secrets; it does not use Codemagic integration/reference names.
   Obtain the full **40-character SHA** of the reviewed preparation commit or a
   reviewed later ios/foundation commit containing both new files. Copy it from
   **Code → ios/foundation → commit details → Copy full SHA**. Older unsigned-only
   commits do not contain the signed helper. The workflow verifies HEAD equality
   and that the selected commit belongs to ios/foundation.
   **Stop here:** credentials and manual registration are prepared. No build
   or upload is authorized in this preparation task.

6. **Later, separately authorized execution and confirmation.**
   In **Actions → Shelf signed iOS internal TestFlight → Run workflow**, choose
   **Use workflow from: ios/foundation** (not main). Enter:
   - **source_sha:** reviewed full SHA from step 5.
   - **build_number:** explicit unused positive number from step 1; no default.
   - **apple_build_number_unused:** true only after Apple received-build check.
   - **codemagic_cancelled_before_upload:** true only after terminal cancellation
     before upload is confirmed. Required for number 1; never preselect on assumption.
   The final **Run workflow** click deliberately starts signing/build/upload and
   is not part of this preparation. Selecting main skips the signed job by design.
   API number checks fail closed if Apple access is denied, unavailable or duplicate.
   After a later authorized run, verify the artifact's source SHA/build number and
   signature report, cleanup result, Apple upload result, and then App Store Connect
   **Shelf - شیلف → TestFlight** shows that same build marked **internal**.
   Apple processing/export-compliance and owner installation are separate checks;
   green compilation alone does not establish them. Assign the owner manually
   using the internal-group steps already documented above; no automatic tester
   invitation, beta review or public release. Login/purchases/iPhone acceptance
   remain unfinished.

Official references checked 11 October:
- [GitHub manual dispatch/default-branch requirement](https://docs.github.com/en/actions/how-tos/manage-workflow-runs/manually-run-a-workflow)
- [GitHub Apple certificate/profile and cleanup guidance](https://docs.github.com/en/actions/how-tos/deploy/deploy-to-third-party-platforms/sign-xcode-applications)
- [GitHub secrets and browser Add secret fields](https://docs.github.com/en/actions/how-tos/write-workflows/choose-what-workflows-do/use-secrets)
- [Apple API authentication](https://developer.apple.com/documentation/appstoreconnectapi/generating-tokens-for-api-requests)
- [Apple build listing](https://developer.apple.com/documentation/appstoreconnectapi/get-v1-builds)
- [Apple upload requirements](https://developer.apple.com/help/app-store-connect/manage-builds/upload-builds/)
- [Apple internal-only tester limits](https://developer.apple.com/help/app-store-connect/test-a-beta-version/add-internal-testers)

Checkout OFF; pricing-sync and second-phone restore deferrals and joint public-launch
hold preserved. No new certificate, build, upload, production deployment, Android
change or paid activation in this task.
