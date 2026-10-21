# Bekasyar import completed — 21 October UTC / 20 October local 2026

**183 poems imported into existing Book 13. Status remains Draft. Ready for owner review.**

Private owner preview: https://shelf.services/admin/collections/13/preview

## Execution

Application commit **2dd64f660cb552cb9648c7a0310bea0b81d7596b** was deployed to
staging through `scripts/staging/workflow.py`, then promoted to production at
that identical verified commit. The prepared importer/model/reader/test files
match **e0ce2e72f03a58953716ae26a8648901960c97bc** exactly; the owner's requested
**18 passing tests / 614 assertions** were reused without rerunning them.

Staging lacked Book 13 and author 3. A committed, reversible, staging-only
migration copied only the approved Draft baseline, author metadata, credit,
category association and six existing bin rows. The cover was independently
copied and checksum-verified. Other staging books were not refreshed or changed.
The actual staged import, private preview and no-op retry all passed before
production promotion. No production book data was changed by staging.

The production Artisan command then made a fresh verified backup and applied
`2026_10_21_000001_import_reviewed_bekasyar_poems`, production **batch 31**.
No database structure changed. The original existing book was used; no second
production book was created.

| Item | Verified result |
| --- | --- |
| New poems | **183**: 48 / 47 / 77 / 11 in approved file/source order |
| New work IDs | **356–538** |
| New sort positions | **7–189**, after the six preserved bin positions |
| Untitled works | **172**, null literary titles |
| Source-titled works | **11**, source-supplied text preserved |
| Current book | **13**, دا ته د کومو غرونو شور يې؟ |
| Publication status | **Draft** |
| New visibility/sample | All 183 hidden; no samples selected |
| Prior bin records | **350–355**, all six unchanged |
| New source import audits | **4**, one per original content DOCX |
| Literal incomplete note | Sequence 12 retains `(` exactly; audit editorial flag retained |
| Retry | “Already imported exactly; no changes.” |

The immutable manifest is unchanged:
`5afdd6c4292cf73cac9a341b199e7129aa68c734e85bac26b120dc3be0344e6f`.
Original ZIP SHA-256 is unchanged:
`76b49b2c0ae5d21be7b5dff760925c76df67500654a6881e243709e95de2678f`.

Original per-file DOCX archives, the unchanged complete ZIP (including front
matter), approved manifest, actor/time/checksum audit and IDs are retained
privately through the existing Word import service. Existing originals were not
changed. The source-proven separate ژوند boundary and resolved RTL table order
are those approved in the [final preparation report](BEKASYAR_IMPORT_FINAL_2026-10-20.md).
No normalization, correction, invented title, duplicate deletion or source rewrite
occurred during execution. All six exact footnotes remain with their own works.

## Backups

- Staging before code deployment: `/home/shelf/backups/shelf-staging/20261021-003039/`.
- Staging before the add-only Draft copy: `/home/shelf/backups/shelf-staging/20261021-003052/`.
- Staging immediately before import: `/home/shelf/backups/shelf-staging/20261021-003104/`.
- Production before promotion: `/home/shelf/backups/shelf/20261021-005039/`.
- **Production immediately before live import:** `/home/shelf/backups/shelf/20261021-005159/`.

Every backup passed the existing SQL, ZIP CRC/test and SHA-256 verification.
The fresh production pre-import backup was independently rechecked after import
using the existing verifier and passed again. No restore or rollback was needed.
The approved guarded reverse migration remains available; it refuses later edits,
requires another fresh verified backup and preserves original sources/audit.
Never restore a backup over later reader/payment activity without a preservation plan.

## Verification

Both staging and production were checked against the approved manifest for every
stored body and its exact source order, title state, layout, visibility and sample
state. Exact escaped bodies appeared in order across **all 19 actual private
Admin preview pages**. Guest preview access was denied; private/no-store headers
were present. This is server-rendered owner-preview verification, not a new phone
layout acceptance claim.

All six original bin rows and full book metadata (except normal content-change
actor/time stamps) match the frozen baseline. Author/credits, categories, rights,
cover checksum, price and other settings are unchanged. Other book rows and
protected author/category/account/purchase/refund/ledger/agreement/settings/history
fingerprints stayed unchanged. The standard promotion checks also passed and
production maintenance was lifted normally.

Direct production requests returned **404** for the Draft collection, its content
list and a new poem's public text endpoint. New content was not published.
Real checkout and automatic Play sync remain **OFF**; no store products were
registered or changed. No mobile build, store submission or unrelated book edit.

Actual retry of the exact production `--apply` command reported an exact prior
import and added nothing. Final read-back still shows 183 current poems, six bin
records and four new audits. No source/Draft mismatch or live failure occurred.

Private evidence retained under `/home/shelf/tmp/bekasyar-import-20261020/`:
`staging-import-verification.json`, `production-import-verification.json`,
`production-import-summary.json`, and both preservation checkpoints. Staging's
release evidence is `/home/shelf/staging-runtime/checks-2dd64f660cb552cb9648c7a0310bea0b81d7596b.log`.
The approved source, final manifest and review files remain available there.

**Problems: none affecting the import.** The intentionally incomplete `(` remains
flagged for later separately authorized editorial correction. The book is ready
for the owner's private review; publishing remains a separate owner decision.

Completion records are committed/pushed separately from the deployed application.
Existing remote history and the previously approved mobile support source-only
correction are preserved; this task makes no new mobile change or deployment.
