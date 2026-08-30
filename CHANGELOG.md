# Changelog

All notable repository governance changes are recorded here.

## Unreleased

### Changed

- Corrected the P4 Android export pipeline after owner-device review: export now captures the painted preview instead of a separately inserted translucent overlay, waits for frame completion, validates 1080×1350 PNG bytes and temporary files, renders multi-card sets sequentially, and distinguishes render/file/share/gallery failures in debug diagnostics. P4 owner-device acceptance remains pending.
- Closed the P3 Audio Experience gate on August 29, 2026, after owner testing on real Android hardware confirmed basic play/pause, background and lock-screen playback, sufficient system playback behavior, and offline replay from populated local cache.
- Recorded the accepted P3 baseline and preserved ongoing Filament-managed owner recordings as non-blocking launch content; P4 had not started at that closeout, P5 remains not started, and Google Play submission has not occurred.
- Closed the P2 Flutter Reader gate on August 29, 2026, after owner testing on real Android hardware confirmed the core RTL reader flow, long scrolling, toolbar boundary, title hierarchy, bundled font choices, reader settings, and locked/translation representations.
- Preserved debug-only QA fixture isolation and API publication control over real production content; deferred detailed visual polish and final Play Store screenshots/assets to the P6 launch stage.

### Added

- Initial System C repository governance and safety baseline.
- Product boundaries, architecture decisions, phase-gated roadmap summary, and Phase 0 environment record.
- Ignore rules for secrets, dependencies, runtime data, protected media, build artifacts, signing material, and database dumps.
- Laravel 12 and Filament 5 P1 application foundation.
- Collections, poems, application settings, publication/order/sample controls, and cover/private-audio upload management.
- Public-safe versioned API behavior with locked poem/audio denial.
- Automated Filament, upload, API, publication, and Pashto Unicode round-trip tests.
- P0/P1 reconciliation record with owner, external, runtime, deployment, and governance gaps.
- System C-only production provisioning/deployment scripts and checksummed database/media/source backup verification commands.
- Android-first Flutter reader in `mobile/` with Home, Collections, collection detail, and Unicode poem-reader surfaces.
- Explicit Pashto RTL, bundled Nastaliq/Naskh fonts with license notices, Light/Sepia/Dark palettes, persistent font controls, and resilient `content_version` caching.
- Flutter model, API-cache, offline, locked-state, translation-attribution, typography-preference, and reader widget tests.
- P3 spoken-poetry playback with play/pause/seek, elapsed/duration state, loading/buffering/error/retry UI, background media controls, interruption handling, and one global player.
- Simultaneous streaming/disk caching with stable recording identities, offline replay, replacement invalidation, partial-download recovery, and bounded retention.
- Debug-only generated non-voice audio acceptance fixture; it is explicitly synthetic and excluded from release content.
- P4 poem-card workflow with exact contiguous 1–4-line selection, full accessible-text mode, preview, three literary designs, RTL Nastaliq rendering, truthful translation metadata, and branded watermarking.
- Deterministic stanza-aware multi-card pagination and one-page-at-a-time 1080×1350 PNG rendering for bounded long-poem memory use.
- Android single/multi-card sharing through `share_plus`, scoped gallery saving through `gal`, safe filenames, recoverable errors, and stale temporary-file cleanup.

### P4 owner authorization

- Ajmal explicitly authorized P4 on August 29, 2026. The technical build is implemented; owner real-device share-card acceptance remains required. P5 has not started and no store submission occurred.

### P3 owner decision

- Ajmal explicitly authorized P3 and designated original voice recordings as ongoing Filament-managed content. Production zero-audio state is valid, and the former 10-recording pre-launch expectation is superseded as a launch blocker.

### Security

- System-specific host-only session defaults.
- Private audio storage with short-lived signed delivery for available free audio.
- Full body and audio URL withholding for locked poems pending the P5 entitlement integration.
- Share cards consume only currently accessible API text; locked full text cannot be reconstructed or exported. Modern Android receives no broad storage permission, and legacy write access is capped to API 28 and below for gallery saving.
