# PC-D-001 — Source-Text Poetry Presentation for V1

**Date:** 2026-09-01

**Status:** OWNER APPROVED / GOVERNING FOR CURRENT PC WORK

**System:** System C — پېڅوَل

**Stage:** PC — Product Completion & Owner Acceptance

## Decision

1. Source-text presentation is the accepted Version 1 baseline. Canonical poem
   text remains plain Unicode source text, and normal reading preserves its
   authored newlines.
2. Automatic `COUPLET` / `FOUR_LINES` presentation is removed from the owner
   interface and normal mobile rendering.
3. Special half-line bayt, paragraph, or poem-section spacing is
   **POSTPONED** and is not a Product Completion blocker.
4. Existing poem text is not rewritten to create presentation spacing.
5. Dormant legacy schema fields may remain for migration-history and API
   compatibility, but they do not define current product behavior.
6. Any future reintroduction requires a new explicit owner-approved product
   task.

## Reason

Repeated real-content owner testing showed that the automatic and manual
spacing experiments increased complexity, caused defects, and did not provide
an owner-acceptable literary result. The simpler source-text behavior is the
owner's accepted Version 1 presentation.

This decision supersedes only the earlier PC typography working requirement
for owner-controlled `SOURCE`, `COUPLET`, and `FOUR_LINES` modes and half-line
gaps after two-line or four-line groups. Historical records remain evidence of
what was previously attempted and planned.

## Current checkpoint

- Deployed System C commit:
  `0c011b10da011f8cbab050338f2c11d8731a4088`.
- All 148 production poems currently use simple source presentation.
- Automatic layout controls have been removed from the owner workflow and
  normal reader rendering.
- No poetry record or poem body was rewritten for this presentation change.
- Spacing refinement is **POSTPONED**.
- PC remains **ACTIVE**.
- P6 remains **NOT STARTED** and is not authorized by this decision.

## Governance boundaries

This is a PC owner product decision and roadmap clarification. It creates no
constitutional amendment and does not change System C architecture, isolation,
privacy, security, authorship, payment architecture, P5/P6 sequencing, or any
A-003 requirement. Project Constitution v1.3 and Amendment A-003 remain
approved and governing.

## Next Product Completion priority

The next major Product Completion stage is **PC-B — Catalogue Completion &
Editorial Reconciliation**:

- select the intended Version 1 catalogue;
- address remaining source-blocked original collections only from trustworthy
  sources or approved transcription;
- decide the treatment of `سيند په پرخه کې`;
- reconcile the 47 missing first-collection date/place values;
- confirm or revise the current 15 free-sample decisions;
- complete missing covers, front matter, and metadata;
- preserve truthful translation attribution; and
- verify the off-server source archive.

PC-B implementation is not started by this decision record.
