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

P2 is explicitly owner-authorized and lives in `mobile/` within the isolated System C repository under Android application ID `com.hindara.pitswal`. It uses a proportionate models/API/repository/cache/screens/settings structure. Public API JSON is cached locally as complete validated snapshots keyed by `content_version`; a failed refresh never replaces a valid cache. Reader preferences remain local with no user account. Populated automated fixtures remain under `mobile/test/`; the owner-approved real-device QA build also has synthetic local fixtures selected only by Flutter's compile-time debug mode. Product/release mode always selects the production API/cache repository, and fixture leakage is covered by automated and built-artifact checks. These QA fixtures are never public or production content. P3 audio, P4 sharing, and P5 purchases remain unstarted.
