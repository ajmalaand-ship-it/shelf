# System C Decisions

These decisions are approved by the governing project constitution and Pashto Poetry App Roadmap Revision 2. Changes require approved change control.

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

## 8. P2 mobile reader architecture

P2 is explicitly owner-authorized and lives in `mobile/` within the isolated System C repository under Android application ID `com.hindara.pitswal`. It uses a proportionate models/API/repository/cache/screens/settings structure. Public API JSON is cached locally as complete validated snapshots keyed by `content_version`; a failed refresh never replaces a valid cache. Reader preferences remain local with no user account. Populated automated fixtures remain under `mobile/test/`; the owner-approved real-device QA build also has synthetic local fixtures selected only by Flutter's compile-time debug mode. Product/release mode always selects the production API/cache repository, and fixture leakage is covered by automated and built-artifact checks. These QA fixtures are never public or production content. P3 audio subsequently completed; P4 sharing and P5 purchases remain unstarted.

## 9. P2 real-device acceptance

On August 29, 2026, Ajmal accepted the P2 core reader operational gate after testing the final QA APK on real Android hardware. The accepted baseline is package `com.hindara.pitswal`; operational Home → Collections → collection → poem list → reader navigation; RTL Pashto; Noto Nastaliq Urdu and Scheherazade New; Light, Sepia, and Dark reader palettes; font-size control; the Nastaliq/Naskh toggle; and the versioned public API/cache architecture. Debug QA fixture isolation remains mandatory, and real production content remains controlled by API publication state. This acceptance does not authorize publication of collections, backend changes, P3 work, or store submission. Detailed visual polish and final Play Store screenshots/assets remain later P6 launch-stage work.

## 10. Ongoing owner recordings and P3 audio architecture

Ajmal explicitly authorized P3 on August 29, 2026, and decided that his original voice recordings are ongoing content managed through Filament before and after publication. The earlier 10-recording launch expectation is superseded and no longer blocks launch. Production may validly contain zero recordings; poems without audio remain complete reader experiences. Actual recordings remain Ajmal's irreplaceable content, use private System C storage and automatic backup scope, and must receive verified off-server backup once they exist. AI voice and fabricated owner recordings remain prohibited.

P3 uses one global `just_audio` player, `LockCachingAudioSource` for simultaneous streaming/cache, `audio_session` for spoken-audio focus and interruptions, and `just_audio_background` for Android background/media controls. The backend continues to issue short-lived signed URLs only for accessible free audio and denies locked audio before P5. A stable hashed recording identity invalidates replaced cache entries without exposing private paths; audio changes increment public `content_version`. The local cache is bounded and failed partial downloads are discarded. Synthetic generated tone audio exists only in debug QA fixtures and is explicitly not Ajmal's voice. P4, P5, P6, purchases, and store submission remain outside this decision.

## 11. P3 real-device audio acceptance

On August 29, 2026, Ajmal tested the P3 debug APK on real Android hardware and accepted the Audio Experience gate. Owner testing confirmed basic playback and play/pause, background and lock-screen playback, sufficient system playback behavior, and offline cached replay after prior playback. The accepted baseline includes the `just_audio`-based single player; play/pause/seek/duration; streaming; bounded local cache and offline replay; interruption/audio-focus handling; background/system controls; clean no-audio poems; locked-audio protection; and recording replacement/removal cache invalidation. It also preserves no microphone, storage, location, or camera permissions, no AI voice, and debug-only QA audio excluded from release.

Production may contain zero or only some real recordings. Ajmal's recordings remain ongoing owner-managed content added through Filament, and an incomplete planned recording batch is not by itself a launch blocker. This acceptance does not start P4 or P5, require production audio changes, or authorize Google Play submission.
