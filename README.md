# System C — Pashto Poetry Platform

This repository is the separate System C service for `poetry.ajmalaand.com`. It is isolated from System A (Portfolio Service), System B (Career Command Center), and the existing Private Assistant.

The approved product stack is a Laravel API, Filament administration, and Flutter mobile clients. Android launches first; iOS follows later. Flutter work must not begin until the Laravel/Filament/API Phase 1 gate has passed and been demonstrated.

## Version 1 boundaries

Version 1 excludes user accounts, social features, AI voice, subscriptions, external-payment links, and advanced DRM. Purchases use official app-store billing through RevenueCat, with entitlement decisions enforced on the server.

Ajmal Aand's original Pashto poetry and recorded voice remain his authored work. They must not be rewritten, replaced, or substituted by AI. Original poem text and voice recordings require off-server backups.

## Build discipline

- Follow `PROJECT_CONSTITUTION.md` and the approved Pashto Poetry App Roadmap Revision 2.
- Stop at each phase gate and demonstrate it before moving forward.
- Preserve real Unicode Pashto text, explicit RTL presentation, and `utf8mb4` storage; poem bodies use `LONGTEXT`.
- Keep databases, users, credentials, sessions, media, logs, backups, and runtime paths separate from Systems A and B.
- Never commit secrets, private poems, manuscripts, recordings, or production data.

This baseline contains governance documentation only. It does not yet contain Laravel, Filament, or Flutter application code.
