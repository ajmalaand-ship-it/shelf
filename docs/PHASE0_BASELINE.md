# Phase 0 Repository and Hosting Baseline

This document records the P0-02C baseline for System C only. It contains no credentials, secret values, private poetry, manuscripts, or audio content.

## Observed

The following facts were recorded by the approved environment inspection supplied for this task:

- Repository path: `/home/ajmalaand/apps/poetry`.
- Repository purpose: System C — Pashto Poetry Platform.
- Domain: `poetry.ajmalaand.com`; HTTPS is installed.
- Hosting account: `ajmalaand` on a cPanel server.
- Server software: AlmaLinux 9.8, cPanel 136, Apache 2.4.68, and MariaDB 10.11.18.
- Apache owns ports 80 and 443.
- PHP 8.3.32 and Composer 2.9.8 are available.
- Node, npm, Dart, and Flutter are not installed.
- PHP extensions `bcmath`, `gd`, `sodium`, and `zip` were not detected during inspection.
- Codex CLI is installed for the `ajmalaand` user.

These observations are a point-in-time record, not authorization to install software or change server configuration.

## Approved Decision

- Systems A, B, and C may share the cPanel account, but System C remains isolated by repository, runtime, database and database user, credentials, sessions/cookies, media/storage roots, logs, backups, and application processes.
- The existing Private Assistant under `/home/personal` is outside scope and must not be accessed, modified, or integrated.
- cPanel Apache remains the web server; Nginx will not be installed.
- The stack is Laravel API, Filament administration, isolated MariaDB/MySQL using `utf8mb4` with poem body `LONGTEXT`, and Flutter mobile clients.
- Android is first; iOS is later. Flutter work cannot begin until the Laravel/Filament/API gate passes.
- RevenueCat and official app-store billing provide purchases. Server-side entitlement verification protects full paid text, and paid audio uses short-lived signed URLs.
- Version 1 excludes user accounts, social features, AI voice, subscriptions, external-payment links, and advanced DRM.
- Ajmal's original Pashto poetry and recorded voice must not be rewritten or substituted by AI.
- Original poem text and Ajmal's voice recordings require maintained off-server backups.

## Not Yet Verified

- The future System C document root, deployment layout, runtime permissions, cron/scheduler arrangement, and production log paths.
- Creation and isolation of the System C database and database user; none are created by this task.
- Laravel, Filament, or Flutter project setup and dependency compatibility; no application code or packages exist yet.
- Availability of all PHP extensions required by the selected Laravel/Filament versions. The four extensions listed above were not detected and must be reassessed before dependency installation.
- Pashto `utf8mb4` round-trip behavior and the final `LONGTEXT` migration, because no database or schema exists yet.
- Filament authorization, upload validation, protected-media storage, locked/excerpt behavior, entitlement denial, and signed-media behavior.
- The existence and restore testing of off-server backups for original poem text and voice recordings.
- App name, brand direction, first collection, proofread launch corpus, free samples, voice-recording count, font choices, and themes.
- Google Play account verification, tester availability, and the exact current closed-testing requirements. These must be checked in Play Console at the appropriate P0 store step.
- Apple and RevenueCat requirements applicable at their later configuration and release phases.

## Baseline constraint

This task creates documentation only. It does not create application code, environments, databases, users, services, packages, store products, or server configuration.
