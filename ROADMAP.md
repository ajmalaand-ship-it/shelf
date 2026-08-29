# System C Roadmap Summary

This is a concise summary of the approved **Pashto Poetry App Roadmap Revision 2**. The approved source under `docs/governance` controls details, sequence, and gates; `PROJECT_CONSTITUTION.md` controls if they conflict. Phase gates must be demonstrated before proceeding, and Flutter work cannot begin before the Phase 1 backend gate passes.

## P0 — Content, identity, and store setup

Choose the app identity and first collection; prepare 30–50 proofread Unicode poems, select 10–15 free samples, establish Ajmal's original voice as the only production narration source, choose fonts and reading themes, and maintain off-server backups of poem text and every recording once supplied. Start Google Play developer verification and line up closed-test testers, confirming current Play Console requirements when registering. P0 runs in parallel with P1.

**Gate:** Launch content is ready for administration entry, the Play account is registered and verifying, and the tester group is lined up.

**Current approved preparation baseline:** The public identity is `پېڅوَل` with slogan `اجمل اند بشپړه شاعري`. The reader typography decision is Noto Nastaliq Urdu for primary body text and Scheherazade New as the Naskh alternate, with Light, Sepia, and Dark themes, explicit RTL, font-size control, and approximately 2.2 poem line height. The safely structured private catalogue currently contains 148 works and 15 configured free samples. By explicit owner decision on August 29, 2026, Ajmal's original recordings are ongoing content added through Filament over time; the former expectation of 10 recordings before launch is superseded and is not a launch blocker. Every actual recording still requires protected storage and verified off-server backup once supplied. Google Play developer-account evidence and a closed-test tester group remain owner/external gate actions.

## P1 — Laravel and Filament backend

Build the isolated Laravel API and Filament administration with collections, poems, and application settings; Pashto-safe `utf8mb4` storage and `LONGTEXT` poem bodies; cover/audio uploads; ordering, status, sample, and product mapping fields; and HTTPS API responses that distinguish excerpts/locked content from full content.

**Gate:** Collections, poems, covers, and audio can be managed in Filament, and the HTTPS API returns correct JSON with Pashto intact.

## P2 — Flutter reader

After P1 passes, build the Android-first Flutter home, collection, poem list, and reader experience with real explicit-RTL Pashto text, bundled suitable fonts, font controls, reading themes, and a simple content-version cache. Test on a real small-screen Android device.

**Gate:** Poems are readable, unclipped, attractive, and correctly RTL on a real Android device.

**Status: Complete — real-device gate passed August 29, 2026.** Ajmal accepted the P2 core reader operational gate after testing the final QA APK on real Android hardware. The Android-first Flutter project is in `mobile/` under package `com.hindara.pitswal` and uses the public Laravel API with a `content_version`-keyed JSON cache, locally bundled Noto Nastaliq Urdu and Scheherazade New fonts, explicit RTL, Light/Sepia/Dark reader palettes, persistent font-size control, and a Nastaliq/Naskh toggle. Debug QA fixtures remain isolated from release builds, and real production content remains governed by API publication state. Detailed visual polish and final Play Store screenshots/assets remain P6 launch-stage work. P3 audio work has not started.

## P3 — Audio experience

Add streaming with local caching, play/pause/seek and loading/error states, duration display, interruption handling, and background playback within the poem screen. Free audio uses its normal route; paid audio uses short-lived signed URLs after entitlement verification.

**Gate:** Reading and Ajmal's voice playback work smoothly together, and cached audio replays without a network request.

**Status: Technical build implemented — owner real-device audio acceptance required.** P3 was explicitly authorized by Ajmal on August 29, 2026. The reader uses one global `just_audio` player with `LockCachingAudioSource`, `audio_session` spoken-audio focus/interruption handling, and `just_audio_background` media controls. Audio cache identity comes from the backend recording path hash, cache size is bounded, and authoritative replacement/removal changes `content_version`. Production having zero recordings is valid; no audio poem is degraded, and new owner recordings require no app update. P4 remains not started.

## P4 — Share and download cards

Generate branded PNG cards from selected couplets, with polished backgrounds, watermarking, sharing, and gallery saving. Paginate full or long poems into multiple cards to avoid device texture failures.

**Gate:** Cards are polished, branded, readable, and shareable, and long poems do not produce blank or crashed output on low-cost devices.

## P5 — Payments and locked content

Configure one non-consumable Unlock All Poetry product and the `unlock_all` RevenueCat entitlement, Google Play Billing, sample/locked UI, restore purchases, and sandbox testing. The app sends RevenueCat's anonymous user ID; Laravel verifies entitlement server-side and returns full paid text and signed audio only when entitled. No external payment method is mentioned in the app.

**Gate:** A fresh install receives samples only; purchase, reinstall, and restore unlock content; direct API access without entitlement returns locked excerpts only.

## P6 — Android launch

Prepare Android launch assets, store copy, Pashto screenshots, privacy policy, and terms. Complete the closed test begun in P0 under the then-current Google Play requirements, fix real-device issues, and obtain production approval.

**Gate:** The Android application is approved and live in Google Play.

## P7 — iOS launch later

After Android is stable, establish the Apple developer setup, build iOS, map the same RevenueCat entitlement to Apple In-App Purchase, prepare App Store materials and review notes, and submit for review.

**Gate:** The iOS application is approved and live in the App Store.
