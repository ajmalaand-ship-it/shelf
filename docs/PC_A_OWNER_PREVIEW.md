# PC-A — Protected Owner Preview / Real-Content Visibility

**Stage:** PC — Product Completion & Owner Acceptance

**Status:** TECHNICAL BUILD COMPLETE — REAL-CONTENT OWNER DEVICE REVIEW REQUIRED

## Purpose

PC-A gives Ajmal a private Android review artifact containing the real unpublished System C catalogue without publishing or changing any collection, poem, access flag, cover, metadata or audio. The currently imported `څپو کې انځورونه` and `هېندارې او چینې` collections remain review inputs, not an accepted complete Version 1 catalogue.

## Protection

- A separate `/api/owner-preview` namespace mirrors the reader's required read operations.
- Every preview API request requires an `Authorization: Bearer` token signed with the server-only `POETRY_OWNER_PREVIEW_SECRET`.
- Tokens carry expiry and random nonce data, use HMAC-SHA-256, default to seven days, and cannot be replaced by RevenueCat entitlement.
- The safe Artisan generator writes a mode/token JSON file with mode `0600` and never prints the token.
- Preview responses use private `no-store` caching and a bounded rate limit.
- Preview audio metadata is bearer-protected and yields only a ten-minute signed private stream URL.

## Flutter isolation

- Normal debug continues to select synthetic QA fixtures.
- Owner-preview debug requires explicit `OWNER_PREVIEW` and `OWNER_PREVIEW_TOKEN` compile-time values and uses only the protected real-content API.
- Release/profile builds reject owner preview and use the public API even if preview flags are supplied.
- Public JSON cache, owner-preview JSON cache and debug fixture data are separate. Preview audio also uses a separate cache directory.
- A persistent bilingual **OWNER PREVIEW — UNPUBLISHED CONTENT** banner distinguishes the private artifact on every route.
- Draft/Published, Free/Locked and Translation labels reflect stored state for review; locked preview poems remain fully readable to Ajmal.

## Boundaries

Public API publication filtering and paid-content authorization are unchanged. PC-A creates no accounts, migration, content edit, publication action, RevenueCat change or Google Play action. PC-B and P6 remain not started.
