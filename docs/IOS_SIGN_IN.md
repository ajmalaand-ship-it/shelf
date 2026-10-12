# Shelf iOS sign-in — source completion and remaining setup

11 October 2026. Apple and Google sign-in source is prepared on ios/foundation
at **32ca4a04929ad065af21d85241c5ca3a749b5007**.
Nothing here claims provider or signed-iPhone acceptance, authorizes a build,
changes production settings or authorizes a backend rollout. Build 1.0.0 (2)
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
