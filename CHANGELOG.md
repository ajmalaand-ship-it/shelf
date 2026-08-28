# Changelog

All notable repository governance changes are recorded here.

## Unreleased

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

### Security

- System-specific host-only session defaults.
- Private audio storage with short-lived signed delivery for available free audio.
- Full body and audio URL withholding for locked poems pending the P5 entitlement integration.
