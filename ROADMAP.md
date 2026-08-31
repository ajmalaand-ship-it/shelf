# Ajmal Aand — Pashto Poetry App Roadmap Revision 3

**Title:** Product Completion Before Release

**Status:** APPROVED AND GOVERNING

**Approved by:** Ajmal Aand

**Approval date:** August 30, 2026

Revision 3 preserves completed P1–P5 technical foundations and inserts mandatory PC — Product Completion & Owner Acceptance before P6. Revision 2 remains historical and is superseded where Revision 3 differs. `PROJECT_CONSTITUTION.md` controls if the documents conflict.

**Governing sequence:** P0 foundation → P1–P5 technical foundations → PC Product Completion & Owner Acceptance → P6 Android Release Preparation & Launch → P7 iOS later.

## P0 — Content, identity, and store setup

Choose the app identity and first collection; prepare 30–50 proofread Unicode poems, select 10–15 free samples, establish Ajmal's original voice as the only production narration source, choose fonts and reading themes, and maintain off-server backups of poem text and every recording once supplied. Start Google Play developer verification and line up closed-test testers, confirming current Play Console requirements when registering. P0 runs in parallel with P1.

**Gate:** Launch content is ready for administration entry, the Play account is registered and verifying, and the tester group is lined up.

**Current approved preparation baseline:** The public identity is `پېڅوَل` with slogan `اجمل اند بشپړه شاعري`. The reader typography decision is Noto Nastaliq Urdu for primary body text and Scheherazade New as the Naskh alternate, with Light, Sepia, and Dark themes, explicit RTL, font-size control, and approximately 2.2 poem line height. The safely structured private catalogue currently contains 148 works and 15 configured free samples. By explicit owner decision on August 29, 2026, Ajmal's original recordings are ongoing content added through Filament over time; the former expectation of 10 recordings before launch is superseded and is not a launch blocker. Every actual recording still requires protected storage and verified off-server backup once supplied. Google Play developer-account evidence and a closed-test tester group remain owner/external gate actions.

**Status: OPEN / PARTIAL.** Real launch catalogue/source recovery, protected owner-facing review, off-server owner verification and external store/tester actions remain unresolved. P0 incompleteness does not invalidate the technical foundations already built.

## P1 — Laravel and Filament backend

Build the isolated Laravel API and Filament administration with collections, poems, and application settings; Pashto-safe `utf8mb4` storage and `LONGTEXT` poem bodies; cover/audio uploads; ordering, status, sample, and product mapping fields; and HTTPS API responses that distinguish excerpts/locked content from full content.

**Gate:** Collections, poems, covers, and audio can be managed in Filament, and the HTTPS API returns correct JSON with Pashto intact.

## P2 — Flutter reader

After P1 passes, build the Android-first Flutter home, collection, poem list, and reader experience with real explicit-RTL Pashto text, bundled suitable fonts, font controls, reading themes, and a simple content-version cache. Test on a real small-screen Android device.

**Gate:** Poems are readable, unclipped, attractive, and correctly RTL on a real Android device.

**Status: Complete — real-device technical gate passed August 29, 2026.** Ajmal accepted the P2 core reader operational gate after testing the final QA APK on real Android hardware. The Android-first Flutter project is in `mobile/` under package `com.hindara.pitswal` and uses the public Laravel API with a `content_version`-keyed JSON cache, locally bundled Noto Nastaliq Urdu and Scheherazade New fonts, explicit RTL, Light/Sepia/Dark reader palettes, persistent font-size control, and a Nastaliq/Naskh toggle. Debug QA fixtures remain isolated from release builds, and real production content remains governed by API publication state. Final real-content/product acceptance belongs to PC; Play Store screenshots/assets belong to P6. P3 audio subsequently passed its real-device gate on August 29, 2026.

## P3 — Audio experience

Add streaming with local caching, play/pause/seek and loading/error states, duration display, interruption handling, and background playback within the poem screen. Free audio uses its normal route; paid audio uses short-lived signed URLs after entitlement verification.

**Gate:** Reading and Ajmal's voice playback work smoothly together, and cached audio replays without a network request.

**Status: Complete — owner real-device audio gate passed August 29, 2026.** P3 was explicitly authorized by Ajmal and its technical implementation was completed in commit `d06d7f579ebae80c69e392a08e21032b43cf9c42`. Ajmal then tested the debug APK on real Android hardware and accepted the P3 gate after confirming basic playback, play/pause, background and lock-screen playback, sufficient system playback behavior, and offline replay from a previously populated cache. The accepted baseline uses one global `just_audio` player with play/pause/seek/duration, streaming, bounded local caching, offline replay, `audio_session` focus/interruption handling, and `just_audio_background` system media controls. Poems without audio remain clean; locked audio remains protected; authoritative replacement/removal invalidates stale cache; no microphone, storage, location, or camera permission is requested; AI voice remains excluded; and synthetic QA audio remains debug-only and excluded from release. Production may validly contain zero or only some real recordings. Ajmal's original recordings remain ongoing owner-managed content added through Filament, and an incomplete planned recording batch is not a launch blocker. P4 and the P5 technical foundation were subsequently authorized; no Google Play submission has occurred.

## P4 — Share and download cards

Generate branded PNG cards from selected couplets, with polished backgrounds, watermarking, sharing, and gallery saving. Paginate full or long poems into multiple cards to avoid device texture failures.

**Gate:** Cards are polished, branded, readable, and shareable, and long poems do not produce blank or crashed output on low-cost devices.

**Status: Complete — owner real-device share-card gate passed August 29, 2026.** Ajmal explicitly authorized P4, whose technical implementation was completed in commit `9c1d6884ddb4cedd5db44794f759e57049eea94a`. Owner-device testing then exposed an Android export defect in the separate insufficiently painted overlay capture path; commit `d56cb621cb636f2734073b52c77518f91830075a` corrected it by capturing the actual preview `RepaintBoundary`, waiting for frame/paint completion, validating PNG signature, dimensions, and temporary files, and distinguishing share/gallery failures internally. Ajmal tested the corrected APK on real Android hardware and accepted the gate after confirming both Android share-sheet export and Save to Gallery.

The accepted P4 baseline includes the reader Share/Download entry; exact Unicode-preserving contiguous 1–4-line selection; full accessible-poem export; deterministic multi-card pagination for long works; sequential low-memory 1080×1350 PNG rendering; three restrained designs; `پېڅوَل`, `Pitswal`, and Ajmal identity; no manufactured untitled-work headings; truthful translation attribution; and locked-content protection. Debug QA content remains excluded from release. P4 adds no broad modern storage, microphone, camera, contacts, or location permission. At P4 closeout, P5 and P6 had not started; the P5 technical foundation was subsequently completed, while P6 remains not started. No Google Play submission or real-production collection publication occurred for this gate.

## P5 — Payments and locked content

Configure one non-consumable Unlock All Poetry product and the `unlock_all` RevenueCat entitlement, Google Play Billing, sample/locked UI, restore purchases, and sandbox testing. The app sends RevenueCat's anonymous user ID; Laravel verifies entitlement server-side and returns full paid text and signed audio only when entitled. No external payment method is mentioned in the app.

**Gate:** A fresh install receives samples only; purchase, reinstall, and restore unlock content; direct API access without entitlement returns locked excerpts only.

**Status: TECHNICAL BUILD COMPLETE — REAL PROVIDER/SANDBOX ACCEPTANCE DEFERRED TO RELEASE PREPARATION.** Ajmal explicitly authorized P5 on August 29, 2026. The implemented identifiers are Google Play one-time product `pitswal_unlock_all_v1`, RevenueCat entitlement `unlock_all`, and offering `default`. Flutter uses `purchases_flutter` with RevenueCat-managed anonymous App User IDs, localized store pricing, purchase/restore controls, entitlement-aware API headers, and isolated public/paid text and audio caches. Laravel independently checks RevenueCat, caches positive results for 24 hours and negative results for 5 minutes, supports an explicit post-purchase refresh, and fails closed for every locked-text/audio provider failure. Free samples and Filament publication/free flags remain authoritative.

By owner sequencing decision on August 29, 2026, creation/activation and pricing of the real Play product, RevenueCat project/app and Play connection, real entitlement attachment, production keys, and purchase/restore/paid-audio sandbox testing are intentionally deferred to P6 Android Release Preparation. This is not abandonment and does not make P5 complete. Until then, production remains fail-closed for locked content, free/public behavior remains available, and release builds cannot use fake entitlement. The product must be configured as non-consumable in RevenueCat so Google Billing Client 8 can restore it for an anonymous reinstall. P6 remains not started and no Google Play submission has occurred.

## PC — Product Completion & Owner Acceptance

Complete and review the actual Version 1 product before release work: provide protected unpublished owner preview of the real catalogue, resolve catalogue/source/editorial scope, review normal user-facing flows with real content, decide any remaining Version 1 product options, and close actionable product-completeness defects. Synthetic QA remains useful for capability testing but cannot substitute for this real-content gate.

**Gate:** Ajmal explicitly accepts **Product Complete / Ready for Release Preparation**. Only that approval permits P6 to begin.

**Status: ACTIVE.** **PC-A — Protected Owner Preview / Real-Content Visibility** is technically implemented. **SYSC-PC-TYPOGRAPHY-01** replaces the real-content typography baseline with Vazirmatn as the default UI/reading foundation, retained Scheherazade New and Noto Nastaliq Urdu alternatives, owner-controlled source/couplet/four-line presentation, and automatic editorial content-version invalidation; it requires Ajmal's fresh real-content device review. The two currently imported collections remain review content rather than an accepted final Version 1 catalogue. PC-B and P6 have not started.

## P6 — Android Release Preparation and Launch

Prepare Android launch assets, store copy, Pashto screenshots, privacy policy, and terms. Complete the closed test begun in P0 under the then-current Google Play requirements, fix real-device issues, and obtain production approval.

**Release-stage blocker:** Complete Google Play + RevenueCat real configuration and sandbox purchase/restore gate before final closed testing/production submission. This includes the owner-selected price, production public/server keys, reinstall/Restore Purchases validation, direct-API locked-content denial, and final paid-audio entitlement verification.

**Gate:** The Android application is approved and live in Google Play.

**Status: NOT STARTED.** P6 cannot begin until PC passes through explicit Ajmal approval. It is release preparation and launch work, not a container for unfinished product development.

## P7 — iOS launch later

After Android is stable, establish the Apple developer setup, build iOS, map the same RevenueCat entitlement to Apple In-App Purchase, prepare App Store materials and review notes, and submit for review.

**Gate:** The iOS application is approved and live in the App Store.

**Status: NOT STARTED.**
