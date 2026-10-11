# Shelf iOS foundation

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

Apple login is not implemented despite the entitlement. Google provider has only
serverClientId; no iOS OAuth client/URL callback configuration is present, while
shared account UI can show Google when the server enables it. Do not tap Google
in this limited test. Email/password is shared source, unverified on iPhone;
Keychain, sign-out, account isolation/deletion and offline behavior require testing.
RevenueCat provider accepts goog_ keys and backend verification is Play-specific;
App Store products/Apple configuration/receipts/restore/refunds are unfinished.
No App Store transaction is part of this workflow. Server-decided existing Library
access is not evidence of Apple purchasing. Native photos/save/share/audio,
RTL/fonts/accessibility and actual iPhone reading remain unaccepted.

The stored profiles/internal export are documented by
[Codemagic's Flutter guide](https://docs.codemagic.io/yaml-quick-start/building-a-flutter-app/).
Upload uses the integration, with submit_to_testflight=false (no external beta
review) and submit_to_app_store=false; owner chooses an internal build manually.
[Codemagic publishing semantics](https://docs.codemagic.io/yaml-publishing/app-store-connect/)

Checkout OFF; pricing sync and second-phone restore deferred; joint public-launch
hold retained. No production deployment, Android changes, signed build, upload,
credential creation or paid activation performed by this task.
