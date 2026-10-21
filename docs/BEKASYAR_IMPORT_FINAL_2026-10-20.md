# Bekasyar Draft 13 — final import preparation

**Final proposed count: 183 poems (48 + 47 + 77 + 11). Ready for owner approval; no live import executed.**

This resolves the earlier dry run. Author عبدالمالک بېکسيار and existing book
**13**, **دا ته د کومو غرونو شور يې؟**, remain the target. No new book is created.
Draft status, author/credits, cover, USD 3.99 price, rights and existing/bin content
are unchanged. No publication, mobile build, store action or deployment occurred.

## One source-proven boundary correction

The earlier 182 count followed `***` lines alone. Original **Nazmona.docx,
paragraph 104** begins **ژوند**, centered with **40 half-points (20-point)**
title formatting. The preceding separately titled **د ابد دپاره** has ended.
The original Word layout places ژوند as a separate title above the table poem,
matching the typography of the other named poems, including هوس and تصوير.

This is the direct-source exception authorized by the owner, not an inferred
split based on theme or a rewritten title. Old segment **176** becomes:

| Final sequence | Supplied title |
| --- | --- |
| 176 | د ابد دپاره |
| 177 | ژوند |

Old sequences 177–182 become 178–183; every other boundary is unchanged. No
characters or verses were removed. There are **172 genuinely untitled ghazals**
and **11 source-titled نظم segments**. Untitled title fields remain null; internal
numbers identify order. Source title lines remain verbatim in the bodies as well
as supplying title metadata, so no original heading or indentation is discarded.

## Table reading order resolved

All ten original tables explicitly have `w:bidiVisual w:val="true"`: logical
cell zero is physically rightmost. The original row/cell structure and manual
breaks establish reading **across from right to left, then downward**, rather
than down an entire column before reading the other column. This follows the
[WordprocessingML bidiVisual definition](https://learn.microsoft.com/en-us/dotnet/api/documentformat.openxml.wordprocessing.bidivisual?view=openxml-3.0.1).

Tables 1–9 now belong to final **177 / ژوند**. Table 10, a single-cell dedication,
belongs to **182 / مڼې** and stays with that poem. Refrains and repeated source
lines are retained each time they occur; they are not treated as duplicates.

For example, the first table reads in this order, using the source text exactly:

```text
دا ژوند عجب ژوندون دى
ارمان ارمان کې ومره
عجيبـه يـــو قـانــون دى
ارمان ارمان کې ومره
```

The final stanza aligns two right-cell lines with two left-cell verse lines.
The left cell stores `د وخت په چار ناچار يم بس کړى مې تړون دى` as one logical
string, wrapping visually into two verse lines. The final source mapping inserts
that visual line boundary before `بس`, retaining even the original separating
space as trailing space on the first part. Its non-empty reading order is (the trailing source space is retained in the
manifest, omitted from this displayed example):

```text
حيران زه بېکسيار يم
د وخت په چار ناچار يم
له هر سړي وېزار يم
بس کړى مې تړون دى
ارمان ارمان کې ومره.
```

All other explicit source lines remain intact. Empty lines/paragraphs are
retained in the textual mapping, including empty table-cell positions; they are
not new poems. Original row/cell data remain in the original source ZIP and the
initial structured review. The final manifest records every reordered table line.

Local Word-rendering proofs are in `review/render/nazmona-noto-page-7.png`,
`...page-8.png`, and `...page-9.png`. Original Pokhto/Tahoma fonts are unavailable
here; proofs explicitly use **Noto Naskh Arabic fallback** and evaluator marks.
They establish readable column/verse placement alongside the original XML;
they are not original-font pagination or phone acceptance. An initial missing-SSL
renderer dependency and unsupported output option were resolved locally. No
source file was changed or uploaded to a document service. The evaluator omits
later document material; table 10 is verified directly from its unambiguous
single-cell original XML, not from truncated rendered output.

## Footnotes resolved without editorial invention

All **six notes** are preserved as supplied, after the poetry block of their
respective works, separated by blank lines and without invented headings or
numbering. This makes them readable through the existing body field, rather
than relying on metadata the mobile reader does not currently display.
Original printed callouts remain in the main text; exact source note/reference
IDs are retained in the manifest and originals.

| Final sequence | Source | Note IDs |
| --- | --- | --- |
| 12 | A.Bekasyar...1.docx | 2, 3 |
| 43 | A.Bekasyar...1.docx | 4 |
| 54 | A.Bekasyar...2.docx | 2 |
| 65 | A.Bekasyar...2.docx | 3 |
| 133 | A.Bekasyar...3.docx | 2 |

Note 3 in file 1 remains exactly **`(`**. It is flagged in the import audit for
later separately authorized editorial correction. Nothing is reconstructed.
Its presence is **not an unresolved import decision** under the owner's instruction.

## The six overlaps resolved

These are the earlier, now-binned import of the **first source file**, not
additional duplicate poems inside the supplied archive. Splitting the binned
bodies at their preserved separator lines gives exact source characters, with
whitespace-only differences, in this order:

| Existing bin ID | Source poems contained | Finding |
| --- | --- | --- |
| 350 | 1–5 | Five poems previously merged |
| 351 | 6–27 | Twenty-two poems previously merged |
| 352 | 28–45 | Eighteen poems previously merged |
| 353 | 46 | One previously imported poem |
| 354 | 47 | One previously imported poem |
| 355 | 48 | One previously imported poem |

All 48 match their respective source segments; there is no unexplained extra
text in the six records. They are preserved in the bin, never restored,
rewritten or deleted. All source poems remain in the proposed new import.
No complete duplicate poems were found within the source; repeated refrains
are original poem text and are preserved.

## Prepared command and safeguards

Application code stays in the isolated checkout
`/home/shelf/tmp/bekasyar-import-20261020`; nothing was installed in production.
The command uses the existing `ImportWordDocument` service and its Eloquent
book/content models. A scoped reviewed-reader adapter supplies the approved
exact bodies while that existing service archives each original DOCX and records
actor, time, checksums and new work IDs. The ordinary Admin Word reader is unchanged.
The entire four-file import is one outer database transaction.

The command defaults to **dry run** and requires the exact approved manifest
SHA-256. It checks Draft 13's full baseline metadata, credits/categories and all
six existing/bin rows. Any unexpected content or conflicting edit stops it.
It archives the unchanged ZIP (including title/front matter) and approved manifest
privately, in addition to the original per-file DOCX archives. Failed attempts
remove only their own new files. Concurrent/repeated submissions cannot create
another set of works; an exact completed import is a no-op, and an edited or
reversed import refuses automatic replay.

`--apply` requires an owner actor (the sole owner is selected automatically unless
an owner ID is supplied). It first runs the existing verified SQL/ZIP/SHA-256
backup script successfully **in the same command**, then runs the dedicated
reversible Laravel data migration. No raw SQL or schema change is used.
The migration is deliberately outside automatic deployment migrations, so code
deployment alone cannot import the book. New works are hidden drafts, no sample,
SOURCE layout; the book remains Draft.

Reversal is guarded: only the exact new import works are moved to the bin; it
refuses if their text, metadata, visibility or the original book/bin state changed.
It keeps original sources and the import audit. No old bin record or reader data
is deleted. Reversal also requires a fresh verified backup. An approved later
re-import after reversal would require a separately reviewed preservation plan.

## Final artifacts

- `final/manifest.json`: immutable reviewed plan, source/order mapping, exact
  bodies, footnotes, title metadata, one boundary correction and frozen baseline.
- `final/poems-final.txt`: all **183** proposed bodies in order, internally numbered.
- `baseline-final.json`: private read-only full Draft 13 baseline.
- Original ZIP SHA-256 remains
  `76b49b2c0ae5d21be7b5dff760925c76df67500654a6881e243709e95de2678f`.
- Final manifest SHA-256:
  **`5afdd6c4292cf73cac9a341b199e7129aa68c734e85bac26b120dc3be0344e6f`**.

After owner approval and normal staging-first deployment of the identical verified
code, the production command below previews the final plan. It has not been
executed with `--apply`.

READ-ONLY — server as shelf, in the deployed Shelf repository:

```bash
php artisan shelf:import-bekasyar \
  /home/shelf/tmp/bekasyar-import-20261020/final/manifest.json \
  /home/shelf/tmp/Shelf_Word_Files_Converted_DOCX.zip \
  --approved-sha256=5afdd6c4292cf73cac9a341b199e7129aa68c734e85bac26b120dc3be0344e6f
```

CHANGES — **only after owner approval**: the same command with `--apply` creates
and verifies the fresh backup before importing. For an approved guarded reversal,
add both `--apply --reverse`; no reversal has been executed live.

```bash
php artisan shelf:import-bekasyar \
  /home/shelf/tmp/bekasyar-import-20261020/final/manifest.json \
  /home/shelf/tmp/Shelf_Word_Files_Converted_DOCX.zip \
  --approved-sha256=5afdd6c4292cf73cac9a341b199e7129aa68c734e85bac26b120dc3be0344e6f \
  --apply
```

## Evidence and readiness

Every source text character is conserved in the final per-poem mapping; intentional
table line reordering and the one source-proven split are recorded. All original
files, front matter, exact notes and XML remain untouched. No normalization, OCR,
word correction or invented title was used. Page-number headers/footers remain
archival matter rather than poems, as recorded in the initial audit.

Focused checks run through `scripts/run_tests.sh`: Bekasyar source mapping/import
and existing Word import regressions, including a rehearsal with the **actual
183 bodies** against an isolated synthetic target/database. It verifies exact
stored bodies, 172 null titles, source archives and audit, all-or-nothing failure,
unchanged old bin records, replay without duplicates, guarded reversal and refusal
to overwrite later edits. No mobile tests/builds were needed or run.

A final invocation of the prepared command against the real Draft is dry-run only;
its current metadata/bin fingerprints must still match at actual execution.
Final checks/results and isolated commit are recorded at completion. No staging
or production deployment, live import, backup or reversal has occurred in this task.

**No editorial ambiguity remains.** The final count is **183** because of the
directly evidenced missing separator before ژوند. The next decision is owner
approval of this final import, followed by the normal staged-code/verified-backup
execution gate. Phone reading/layout acceptance is not claimed by this preparation.

Final execution evidence: **18 tests / 614 assertions passed**, including actual
183-body rehearsal and existing Word import regressions. PHP syntax and diff
whitespace checks passed. Final real-Draft Artisan output:

```text
Draft 13: 183 poems (48 + 47 + 77 + 11). Original/bin content is protected.
Dry run only; no changes.
```

Only this task's downloaded rendering library and package cache were removed;
original ZIP, full reviews, final manifest and saved layout proofs remain.
