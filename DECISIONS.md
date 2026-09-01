# System C Decisions

These decisions are approved by the governing Project Constitution v1.3 and Pashto Poetry App Roadmap Revision 3. Changes require approved change control.

## 1. Shared hosting account, isolated systems

Systems A, B, and C may share the `ajmalaand` cPanel account, but remain strictly separate. System C has its own repository, runtime, database and database user, credentials, sessions/cookies, media and storage roots, logs, backups, and application processes. It must not access or integrate with the existing Private Assistant.

## 2. Existing Apache web server

cPanel-managed Apache remains the web server and owns ports 80 and 443. Nginx will not be installed as a competing web-server stack.

## 3. Backend and mobile stack

System C uses a Laravel API with Filament administration and Flutter mobile clients. Android is first and iOS is later. Flutter implementation cannot begin until the Laravel/Filament/API gate passes.

## 4. Pashto-safe database schema

System C uses an isolated MariaDB/MySQL database with `utf8mb4` throughout. The poem body column is `LONGTEXT` so original Unicode Pashto text is preserved.

## 5. Purchases and paid-content authorization

RevenueCat maps entitlements to official Google Play Billing and Apple In-App Purchase products. Laravel verifies entitlement server-side before returning paid text or issuing short-lived signed paid-audio URLs; the mobile client alone is never trusted. Version 1 includes no external-payment links.

## 6. Original recorded voice

Ajmal Aand's recorded voice is primary. AI voice is excluded from Version 1, and AI must not substitute for or rewrite Ajmal's original poetry or recordings. Original poem text and voice recordings require off-server backups.

## 7. Approved reader typography and themes

The approved P0/P2 reader baseline is Noto Nastaliq Urdu for primary poem text and Scheherazade New as the Naskh/alternate font. Reading themes are Light, Sepia, and Dark. Poem reading uses explicit RTL, owner-adjustable font size, and approximately 2.2 line height. The economical Nastaliq/Naskh toggle is included in the owner-authorized P2 implementation.

**PC supersession (August 30, 2026):** Real unpublished content review supersedes the P0/P2 font default without invalidating those technical gates. Vazirmatn 33.003 is now the default UI and reading foundation; Scheherazade New 4.500 remains the traditional Naskh option; Noto Nastaliq Urdu remains an optional literary face. One shared persistent control is available from Home and Reader. Details and licensing evidence are recorded in `docs/PC_TYPOGRAPHY.md`.

**Layout simplification (September 1, 2026):** Normal poetry presentation follows the stored source text and newlines only. The owner-facing `SOURCE`, `COUPLET`, and `FOUR_LINES` controls and automatic two-line/four-line gaps are removed. The `layout_mode` column and compatible API field remain dormant legacy schema/data; existing values are not rewritten and do not drive reader presentation. This owner-approved decision is formally recorded as **PC-D-001 — Source-Text Poetry Presentation for V1** in `docs/governance/PC_D_001_SOURCE_TEXT_PRESENTATION.md`. Special spacing refinement is postponed, is not a PC blocker, and may return only through a new explicit owner-approved product task.

## 8. P2 mobile reader architecture

P2 is explicitly owner-authorized and lives in `mobile/` within the isolated System C repository under Android application ID `com.hindara.pitswal`. It uses a proportionate models/API/repository/cache/screens/settings structure. Public API JSON is cached locally as complete validated snapshots keyed by `content_version`; a failed refresh never replaces a valid cache. Reader preferences remain local with no user account. Populated automated fixtures remain under `mobile/test/`; the owner-approved real-device QA build also has synthetic local fixtures selected only by Flutter's compile-time debug mode. Product/release mode always selects the production API/cache repository, and fixture leakage is covered by automated and built-artifact checks. These QA fixtures are never public or production content. P3 audio completed and P4 sharing was subsequently authorized; P5 purchases remain unstarted.

## 9. P2 real-device acceptance

On August 29, 2026, Ajmal accepted the P2 core reader operational gate after testing the final QA APK on real Android hardware. The accepted baseline is package `com.hindara.pitswal`; operational Home → Collections → collection → poem list → reader navigation; RTL Pashto; Noto Nastaliq Urdu and Scheherazade New; Light, Sepia, and Dark reader palettes; font-size control; the Nastaliq/Naskh toggle; and the versioned public API/cache architecture. Debug QA fixture isolation remains mandatory, and real production content remains controlled by API publication state. This acceptance does not authorize publication of collections, backend changes, P3 work, or store submission. Detailed visual polish and final Play Store screenshots/assets remain later P6 launch-stage work.

## 10. Ongoing owner recordings and P3 audio architecture

Ajmal explicitly authorized P3 on August 29, 2026, and decided that his original voice recordings are ongoing content managed through Filament before and after publication. The earlier 10-recording launch expectation is superseded and no longer blocks launch. Production may validly contain zero recordings; poems without audio remain complete reader experiences. Actual recordings remain Ajmal's irreplaceable content, use private System C storage and automatic backup scope, and must receive verified off-server backup once they exist. AI voice and fabricated owner recordings remain prohibited.

P3 uses one global `just_audio` player, `LockCachingAudioSource` for simultaneous streaming/cache, `audio_session` for spoken-audio focus and interruptions, and `just_audio_background` for Android background/media controls. The backend continues to issue short-lived signed URLs only for accessible free audio and denies locked audio before P5. A stable hashed recording identity invalidates replaced cache entries without exposing private paths; audio changes increment public `content_version`. The local cache is bounded and failed partial downloads are discarded. Synthetic generated tone audio exists only in debug QA fixtures and is explicitly not Ajmal's voice. P4, P5, P6, purchases, and store submission remain outside this decision.

## 11. P3 real-device audio acceptance

On August 29, 2026, Ajmal tested the P3 debug APK on real Android hardware and accepted the Audio Experience gate. Owner testing confirmed basic playback and play/pause, background and lock-screen playback, sufficient system playback behavior, and offline cached replay after prior playback. The accepted baseline includes the `just_audio`-based single player; play/pause/seek/duration; streaming; bounded local cache and offline replay; interruption/audio-focus handling; background/system controls; clean no-audio poems; locked-audio protection; and recording replacement/removal cache invalidation. It also preserves no microphone, storage, location, or camera permissions, no AI voice, and debug-only QA audio excluded from release.

Production may contain zero or only some real recordings. Ajmal's recordings remain ongoing owner-managed content added through Filament, and an incomplete planned recording batch is not by itself a launch blocker. This acceptance does not start P4 or P5, require production audio changes, or authorize Google Play submission.

## 12. P4 share-card architecture

Ajmal explicitly authorized P4 on August 29, 2026. P4 adds a secondary reader action for sharing or saving designed PNG poem cards without changing the readable poem itself. A user may select exactly 1–4 contiguous non-empty lines or use the full currently accessible poem. Selection copies the existing Unicode text verbatim; it does not rewrite, normalize, translate, or manufacture an untitled heading. Locked works remain limited to the excerpt returned by the API, and translated works retain original-poet and Pashto-translator attribution.

The deterministic paginator measures RTL Noto Nastaliq Urdu text against a fixed card content area, preserves lines and short stanzas where practical, splits oversized content only at line or word boundaries, and emits numbered pages without blank, dropped, or duplicated content. Each 360×450 logical card is rendered independently at pixel ratio 3 to a 1080×1350 PNG, so a long poem is never painted as one unbounded image. The three approved designs are warm parchment, dark literary, and light poetic; every card carries `پېڅوَل`, `اجمل اند` or truthful translation/QA attribution, optional `Pitswal`, collection metadata where available, and a subtle identity watermark.

`share_plus` supplies Android single/multi-file sharing. `gal` writes saved cards through Android media APIs without modern broad-storage access; `WRITE_EXTERNAL_STORAGE` is capped to Android 9/API 28 and below solely for legacy gallery saving. Share files remain in an app-controlled temporary directory long enough for receivers and are removed by 24-hour stale cleanup; save staging files are removed after gallery insertion, while user-saved gallery images are never deleted. P4 adds no analytics, tracking, contacts, camera, microphone, location, or modern media-scanning permission. Debug QA poetry stays compile-time debug-only and cannot be selected in release. P5, purchases, P6 submission, social features, video, and AI voice have not started.

Following the first P4 owner-device export failure, PNG generation uses the actual laid-out and painted preview boundary rather than a second translucent overlay. Pages are prepared and captured sequentially; PNG signature/dimensions and temporary-file readability are verified before sharing or gallery insertion. Debug-only stage diagnostics contain operational metadata but no poem text or secrets.

## 13. P4 real-device share-card acceptance

On August 29, 2026, Ajmal tested the export-fix debug APK on real Android hardware and accepted the P4 Share / Download Poem Image gate. Owner testing confirmed that Android share-sheet export, Save to Gallery, and generated PNG output work on the device. The accepted baseline is the poem-reader Share/Download entry; exact Unicode-preserving contiguous 1–4-line selection; full accessible-poem export; deterministic pagination; sequential 1080×1350 PNG rendering; three designs; `پېڅوَل`, `Pitswal`, and Ajmal identity; truthful untitled and translation handling; locked-text protection; and debug-QA release isolation. No broad modern storage, microphone, camera, contacts, or location permission was introduced.

This acceptance closes P4 only. P5 and P6 have not started, Google Play submission has not occurred, and no real production collection was published for testing.

## 14. P5 unlock-all architecture

Ajmal explicitly authorized P5 on August 29, 2026. Version 1 uses one Google Play one-time product, `pitswal_unlock_all_v1`, attached as a non-consumable to RevenueCat entitlement `unlock_all` and exposed through offering `default`. Price is never hardcoded and remains an owner decision in Play Console. The app uses RevenueCat-generated anonymous App User IDs and has no user accounts. `purchases_flutter` supplies CustomerInfo, localized product metadata, purchase, and user-triggered restore behavior; Flutter entitlement state alone never authorizes backend content.

Laravel receives the anonymous identifier only as `X-RC-User-Id`, validates it, hashes it for cache/log references, and uses the server-only RevenueCat secret with `GET /v1/subscribers/{app_user_id}` to inspect the `unlock_all` entitlement. Positive results cache for 24 hours; negative and provider-failure results cache for 5 minutes. `X-RC-Refresh: 1` clears the keyed result after purchase/restore. Provider absence, timeout, authorization failure, unknown customer, malformed response, or outage fails closed. Free samples remain available; publication state always takes precedence; entitled locked audio receives only a ten-minute signed media URL.

Public and entitled JSON caches are separate, with paid cache names scoped to a hash of the anonymous App User ID. Paid audio cache identity includes the same entitlement context. A currently entitled purchaser may retain reasonable offline access; a fresh or currently unentitled context never selects those paid caches. This is practical access control rather than advanced DRM, and provider refresh/cache expiry eventually reflects refunds or revocations.

Debug purchase simulation is compile-time excluded from product builds and can unlock synthetic QA text only; it never generates backend entitlement or reveals production locked text. P5 adds Google Play Billing and RevenueCat purchase-history/anonymous-identifier processing, disclosed in the privacy policy. P6 Data Safety and legal/store finalization remain not started.

## 15. P5 real-provider setup deferred to release preparation

On August 29, 2026, Ajmal approved the status **TECHNICAL BUILD COMPLETE — REAL PROVIDER/SANDBOX ACCEPTANCE DEFERRED TO RELEASE PREPARATION**. This is a deliberate sequencing decision, not abandonment and not full P5 completion. Creation and activation of `pitswal_unlock_all_v1`, the owner-selected price, real RevenueCat project/app and Google Play connection, attachment of entitlement `unlock_all`, production public and server keys, real sandbox purchase, reinstall/Restore Purchases, and final paid-audio entitlement testing are deferred to final Android/Google Play release preparation.

Until that setup exists, production must remain fail-closed for locked content, free/public behavior remains available, and no fake entitlement may be enabled in a release build. The release-stage blocker is: **Complete Google Play + RevenueCat real configuration and sandbox purchase/restore gate before final closed testing/production submission.** P5 cannot be marked complete and release readiness cannot be approved until that gate passes.

## 16. A-003 and Roadmap Revision 3 approved

On August 30, 2026, Ajmal Aand approved **Amendment A-003 — System C Product Completion Gate and Release Sequencing** and **Ajmal Aand — Pashto Poetry App Roadmap Revision 3 — Product Completion Before Release**, stating:

> “I approve A-003 and Roadmap Revision 3.”

A-003 is **APPROVED AND GOVERNING** and updates the Project Constitution to v1.3. Revision 3 is **APPROVED AND GOVERNING** and supersedes Revision 2 where their sequencing differs. Existing P1–P5 technical work remains valid, but technical capability does not equal finished-product acceptance. Mandatory PC — Product Completion & Owner Acceptance now precedes P6; synthetic QA cannot substitute for final real-content acceptance; and Ajmal must explicitly accept **Product Complete / Ready for Release Preparation** before P6 begins.

P0 remains open/partial where real catalogue, source recovery, owner preview and external launch inputs remain unresolved. Original recordings are ongoing editorial content and no fixed count blocks PC. P5 real Google Play/RevenueCat configuration and sandbox acceptance remain deferred to P6 and mandatory before final closed testing or production submission. P6 and P7 remain not started. **PC is the active stage; PC-A — Protected Owner Preview / Real-Content Visibility was designated the first eligible implementation area.** No architecture, security, privacy, authorship, system-isolation or paid-content-protection boundary changes.

## 17. PC-A protected owner preview architecture

PC-A uses a separate `/api/owner-preview` read namespace protected by a seven-day HMAC bearer token derived from the server-only `POETRY_OWNER_PREVIEW_SECRET`. It may read draft and locked real content for owner review while returning the stored publication/free/translation metadata. Public controllers and publication filtering remain unchanged. Preview audio metadata requires the bearer token and issues a ten-minute signed private stream URL; no private path is exposed.

Flutter keeps QA, owner-preview and public modes separate. Owner preview requires both debug compilation and explicit build configuration, uses its own JSON and audio-cache namespaces, and shows a persistent bilingual unpublished-content banner plus restrained status labels. Release mode takes precedence over preview flags and cannot select the preview repository. This is a review capability only: it publishes nothing, changes no content, and does not accept the two imported collections as the final Version 1 catalogue.

## 18. PC-D-001 and next Product Completion priority

On September 1, 2026, Ajmal approved source-text/newline presentation as the
Version 1 baseline after repeated real-content testing. At deployed commit
`0c011b10da011f8cbab050338f2c11d8731a4088`, all 148 production poems use this
simple presentation, automatic layout controls are absent, and no poetry
records were rewritten.

PC remains **ACTIVE**. The next major Product Completion priority is **PC-B —
Catalogue Completion & Editorial Reconciliation**: select the intended V1
catalogue; resolve source-blocked originals only from trustworthy sources or
approved transcription; decide the treatment of `سيند په پرخه کې`; reconcile
47 missing first-collection date/place values; confirm or revise the current 15
free-sample decisions; complete missing covers, front matter, and metadata;
preserve truthful translation attribution; and verify the off-server source
archive. PC-B implementation has not started. P6 remains **NOT STARTED**.
