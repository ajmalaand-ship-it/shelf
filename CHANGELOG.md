# Changelog

- Fixed collection-cover uploads whose Filament UI advertised 5 MB while the
  cPanel EA-PHP request layer still rejected files above its 2 MB default.
  System C's document-root PHP configuration now accepts uploads through 5 MB
  within the existing 8 MB POST envelope, with `.user.ini` covering the active
  cPanel handler and guarded `.htaccess` directives covering LSAPI/mod_php.
  Livewire temporary uploads enforce the same 5 MB ceiling instead of their
  12 MB default. JPEG, PNG, and WebP image validation remains fail-closed;
  oversized or invalid files are rejected before collection data changes, and
  failed replacements retain the existing cover.

- Imported `دلته ډېر لرې له غرونو` from Ajmal's owner-approved authoritative
  Unicode DOCX as the sixth private collection with 41 locked poems: 17
  titled, 24 genuinely untitled, 37 exact date/place values, and one
  truthfully attributed translation whose source does not name the original
  author. The checksum-guarded importer was already rerun idempotently; final
  production verification confirms 6 collections / 342 poems at content
  version 248, the prior five collection fingerprints are unchanged, the
  public API hides the book, and protected owner-preview Build 10 can discover
  it through its versioned six-collection catalogue flow. Owner review remains
  required; PC-B and PC remain active, and P6 has not started.

- Imported `دا ښار، هاغه غرونه` from Ajmal's owner-corrected authoritative
  Unicode DOCX as one private collection with 22 explicitly titled, locked
  poems, 17 exact date/place values, four source-supported front-matter
  sections, and the unchanged trustworthy historical cover. The guarded
  importer is idempotent, the public API hides the book, owner preview shows
  it without an APK rebuild, and all prior collection fingerprints remain
  unchanged. Ajmal completed real-device review in Build 10, confirmed the
  book works, and accepted the import package for closeout.

- Fixed protected owner-preview build identity with the preview-only `SIND-ARTWORK • BUILD 10` marker and Android version `1.0.1` (code 10). Home now renders every collection returned by the repository in existing order instead of silently limiting the list to three; regression coverage includes six collections so future catalogue additions appear without another limit change.

- Completed `سيند په پرخه کې` from the preserved full-resolution scans: 74 poems (73 titled, 1 untitled) were visually verified line by line and imported as a private, locked translation collection attributed to original poet پروین پژواک and Pashto translator Ajmal Aand. Attached the verified cover and 74 protected original illustrations, preserved three visible stanza gaps, resolved all aid-layer uncertainties from the scans, and left zero guessed or unresolved text. The checksum/member-guarded importer is idempotent; a verified pre-import backup exists, the prior 3 collections / 205 poems retained identical fingerprints, public APIs hide the draft, and protected owner-preview build 9 displays the book with artwork.

## 2026-09-02 — Owner Unicode import: د زړه پر پاڼه مې انځور دی ګلاب

- Securely preserved and checksum-verified the owner-supplied authoritative
  DOCX and original cover in private System C source storage.
- Imported 57 source-ordered poems (56 titled, 1 untitled) plus supported
  publication information, dedication, attributed critical text, and 25 exact
  combined date/place values as draft/private and locked content.
- Attached a checksum-identical application cover copy, added guarded cover
  support to the idempotent manifest importer, and kept the collection out of
  the public API while making it available to protected owner preview.
- No OCR was used and no prior collection or poem was modified.

All notable repository governance changes are recorded here.

## Unreleased

### `دا ښار، هاغه غرونه` transcription package prepared — 2026-09-01

- Added a protected-source transcription-package generator that renders PDF
  pages without OCR, creates checksum/dimensions metadata and empty UTF-8 page
  templates, and refuses sources outside System C's protected source tree.
- Prepared and verified the owner-only 36-page manual transcription package
  for `دا ښار، هاغه غرونه`; no poetry was transcribed or imported and no
  production data changed.

### Changed

- Recorded owner-approved PC-D-002: the intended complete پېڅوَل catalogue contains all six identified books—five Ajmal Aand original-poetry collections plus the separate translation book `سيند په پرخه کې`, selected poems by original poet پروین پژواک translated into Pashto by Ajmal Aand. Audited all four not-yet-imported protected source sets; each remains source-blocked because the preserved scanned/legacy PDFs lack complete authoritative Unicode text and deterministic poem structure. No OCR-derived import, production write, or publication occurred.
- Reconciled the 47 authoritative combined date/place values missing from the 81-poem `څپو کې انځورونه` production collection using checksum-verified source manifest sequence identity, a verified pre-write System C backup, missing-only transaction updates, and aggregate post-write fingerprints. No conflicts or ambiguities remained; no poem text or other collection changed; broader PC-B catalogue work remains open.
- Recorded owner-approved **PC-D-001 — Source-Text Poetry Presentation for V1**: plain Unicode source text/newlines are the accepted baseline; automatic `COUPLET`/`FOUR_LINES` and special half-line spacing are postponed and are not PC blockers. The deployed checkpoint is `0c011b10da011f8cbab050338f2c11d8731a4088`; all 148 production poems use simple source presentation without poetry-record rewrites. PC remains active, PC-B Catalogue Completion & Editorial Reconciliation is next, and P6 remains not started.
- Added PC-A protected owner preview: expiring server-authenticated draft/full-text read APIs, signed private preview audio, debug-only real-content Flutter mode, isolated preview caches, persistent non-release banner, and stored Draft/Published plus Free/Locked/Translation review labels. Public publication behavior remains unchanged.
- Recorded Ajmal Aand's August 30, 2026 approval of Amendment A-003 and Poetry Roadmap Revision 3, updating the governing Constitution to v1.3 and inserting mandatory PC Product Completion & Owner Acceptance before P6.
- Made PC the active System C stage and PC-A Protected Owner Preview / Real-Content Visibility the first eligible implementation area without starting it. P0 remains open/partial, P1–P5 technical foundations remain valid, and P6/P7 remain not started.
- Preserved P5's fail-closed real-provider deferral for P6 and superseded the fixed ten-recording launch blocker: original recordings remain ongoing owner-managed editorial content, while synthetic QA cannot replace final real-content acceptance.
- Recorded Ajmal's approved P5 sequencing decision: technical implementation remains complete, while real Google Play product/pricing, RevenueCat configuration/credentials, and purchase/restore/paid-audio sandbox acceptance are deliberately deferred to Android release preparation. P5 is not fully complete; this provider gate is mandatory before final closed testing or production submission, and production remains fail-closed meanwhile.
- Implemented the authorized P5 technical foundation: RevenueCat anonymous purchase/restore state, localized pricing, real locked-reader actions, entitlement-aware text/audio caches, server-authoritative `unlock_all` verification, fail-closed paid text/audio, bounded entitlement caching, and refreshed purchase privacy disclosure. External RevenueCat/Google Play configuration, owner pricing, and real sandbox acceptance remain required; P6 has not started.
- Corrected the P4 Android export pipeline after owner-device review: export now captures the painted preview instead of a separately inserted insufficiently painted overlay, waits for frame completion, validates 1080×1350 PNG bytes and temporary files, renders multi-card sets sequentially, and distinguishes render/file/share/gallery failures in debug diagnostics.
- Closed the P4 Share / Download Poem Image gate on August 29, 2026, after Ajmal confirmed on real Android hardware that share-sheet export, Save to Gallery, and generated PNG output work. At that closeout P5 and P6 had not started; no Google Play submission or real-collection publication occurred.
- Closed the P3 Audio Experience gate on August 29, 2026, after owner testing on real Android hardware confirmed basic play/pause, background and lock-screen playback, sufficient system playback behavior, and offline replay from populated local cache.
- Recorded the accepted P3 baseline and preserved ongoing Filament-managed owner recordings as non-blocking launch content; P4 and P5 had not started at that closeout, and Google Play submission had not occurred.
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

### P4 owner authorization and acceptance

- Ajmal explicitly authorized P4 on August 29, 2026. Technical implementation commit `9c1d6884ddb4cedd5db44794f759e57049eea94a` and export-fix commit `d56cb621cb636f2734073b52c77518f91830075a` established the accepted baseline; the owner real-device gate passed the same day.

### P3 owner decision

- Ajmal explicitly authorized P3 and designated original voice recordings as ongoing Filament-managed content. Production zero-audio state is valid, and the former 10-recording pre-launch expectation is superseded as a launch blocker.

### Security

- System-specific host-only session defaults.
- Private audio storage with short-lived signed delivery for available free audio.
- Full body and audio URL withholding for locked poems pending the P5 entitlement integration.
- Share cards consume only currently accessible API text; locked full text cannot be reconstructed or exported. Modern Android receives no broad storage permission, and legacy write access is capped to API 28 and below for gallery saving.
