# Bekasyar book import — dry-run result, 20 October 2026

**Proposed total: 182 non-empty poems. Not yet safe to apply. No live content changed.**

Existing book: **13**, **دا ته د کومو غرونو شور يې؟**, Draft, poetry, Pashto;
author credit **عبدالمالک بېکسيار** (author ID 3). Title and credit identify the
existing book; no duplicate book was created. Its cover, USD 3.99 price, author,
rights, publication status and all existing content remain unchanged.
Read-only snapshot found **zero current works and six soft-deleted works**
(350–355); they remain in the bin, unchanged.

## Source and counts

Archive: `/home/shelf/tmp/Shelf_Word_Files_Converted_DOCX.zip`

SHA-256: `76b49b2c0ae5d21be7b5dff760925c76df67500654a6881e243709e95de2678f`

| Order | File | Non-empty poems | Internal sequence | Separators | Empty segments | Embedded separators |
| --- | --- | ---: | --- | ---: | ---: | ---: |
| 1 | A.Bekasyar...1.docx | 48 | 1–48 | 49 | 2 | 27 |
| 2 | A.Bekasyar...2.docx | 47 | 49–95 | 48 | 2 | 11 |
| 3 | A.Bekasyar...3.docx | 77 | 96–172 | 79 | 3 | 31 |
| 4 | Nazmona.docx | 10 | 173–182 | 9 | 0 | 3 |
| Total | | **182** | | **185** | **7** | **72** |

`Pushtai aw kitab pezhandana.docx` is front matter, **zero poems**, retained
separately, with no proposed changes to book metadata. Its title-page/book-description
text differs in places (including شور / شوې); it is not used to correct the Draft.
It contains text boxes, alternate representations and a move-to revision. Raw
front-matter extraction may repeat alternate text; it is archival, not an import
proposal. Original ZIP remains untouched.

Separators were accepted only as an entire logical line containing `***`, with
optional ASCII spaces/tabs. Embedded Word paragraph line breaks were respected;
72 separators share a paragraph with other logical lines. No inline-star ambiguity
was found. Four internal intervals between separators contain only whitespace;
three final intervals are empty/whitespace after terminal separators. All seven
intervals are recorded, not silently discarded; none contains poem text. Their
empty placeholders are not proposed as poems. Original segment locations remain
in the JSON review.

## Text and title preservation

No normalization, trimming of poem text, character repair, OCR or title generation
was performed. The review retains leading/trailing blank lines, spaces and tabs.
Main-body paragraph counts: **359 / 492 / 607 / 305**. Text-node character counts:
**19,749 / 18,039 / 32,402 / 5,514**, total **75,704**, including separator text.
Every main-body text node is accounted for in paragraph extraction, and every
logical line reconstructs the input sequence exactly when recorded separators
and empty segments are reinserted. This verifies the supplied DOCX extraction,
not completeness against an unavailable pre-conversion original.

The first 172 ghazal candidates have no Heading 1 titles. They remain untitled;
sequence numbers are internal identification only. The ten Nazmona segments begin
with supplied title-like lines: اختر، تړاو، باران، د ابد دپاره، هوس، تصوير، کيسه،
تپوس، مڼې، خداى پاماني. All styles are Normal; these lines remain in the body
review rather than silently being removed or promoted to title fields. The JSON
uses null titles throughout because it is an extraction review, not an approved
import manifest. Any final mapping must preserve the supplied titles and text.

## Genuine representation problems

1. **Six footnotes:** file 1 IDs 2/3 belong to sequence 12, ID 4 to sequence 43;
   file 2 IDs 2/3 belong to sequences 54/65; file 3 ID 2 belongs to sequence 133.
   All exact note paragraphs and logical reference locations are saved in JSON.
   File 1 note ID 3 contains only **`(`**. It appears incomplete, but the missing
   text cannot be established from this archive. It must not be repaired or
   dropped. The faithful representation of references and notes in the reader
   needs agreement before import; the standard importer omits them.
2. **Ten meaningful tables:** Nazmona tables 1–9 belong to sequence **176**,
   **د ابد دپاره**. Several have two non-empty verse columns separated by an
   empty cell; tables 8/9 also have refrain rows. The tables declare RTL visual
   order. Plain cell-by-cell concatenation does not preserve their spatial verse
   arrangement and may change intended line order. Table 10 belongs to sequence
   **181**, **مڼې**, and contains a one-cell dedication. All cell paragraphs,
   row order and table/cell properties are saved. The review TXT lists XML cell
   order only; **it is not a faithful final rendering of these tables**. Confirm
   the intended verse order/representation for 176; retain the dedication in 181.
3. Headers/footers contain page-number text (including repeated numeric fields),
   not identified poetry. They are recorded separately, never silently omitted
   from the audit or inserted into poems. Main content has no drawings, images,
   tracked revisions, text boxes or font-symbol elements found by the audit.

**Missing text:** no supplied main-body text node was lost by the dry-run
extraction. Six footnotes, ten tables and page-number matter would be excluded
by an ordinary import; all are retained in review artifacts. The lone `(` note
is a source concern, not evidence that Codex knows its intended completion.
Front-matter alternative text/revision remains unresolved but outside the poem
import. No claim is made that this conversion matches earlier Word originals.

## Duplicate checks

No identical full bodies, whitespace-only full-body duplicates, repeated first
lines or near-duplicate candidates were found within the 182 segments. Near
comparison ignores whitespace only for comparison, requires length ratio at
least .75 and SequenceMatcher similarity at least .80; it cannot guarantee the
absence of every literary duplicate. Original strings remain unchanged.

| Bin work ID | Candidate sequence sharing first line | Full-body comparison |
| --- | ---: | --- |
| 350 | 1 | Different beyond whitespace |
| 351 | 6 | Different beyond whitespace |
| 352 | 28 | Different beyond whitespace |
| 353 | 46 | Matches when whitespace is folded |
| 354 | 47 | Matches when whitespace is folded |
| 355 | 48 | Different beyond whitespace |

No stored work exactly matches the untrimmed extracted body byte-for-byte. Bin
matches are reported, not restored, overwritten, deleted or silently excluded
from the proposed source count.

## Artifacts and verification

Isolated checkout: `/home/shelf/tmp/bekasyar-import-20261020`.

- `review/dry-run.json`: complete paragraphs, logical lines, original segment
  positions, checksums, table structures, notes, auxiliary text and candidates.
- `review/poems-review.txt`: all 182 candidate bodies, internally numbered;
  table rendering limitation above applies. Consult JSON for full structure.
- `draft-snapshot.json`: private read-only Draft/current/bin snapshot.
- `scripts/book_import/bekasyar_dry_run.py`: repeatable read-only analyser;
  no database connection, apply option or import behavior.

READ-ONLY with respect to source and live data; writes only private review files:

```bash
cd /home/shelf/tmp/bekasyar-import-20261020
python3 -B scripts/book_import/bekasyar_dry_run.py \
  /home/shelf/tmp/Shelf_Word_Files_Converted_DOCX.zip \
  draft-snapshot.json review
```

Focused synthetic verification through `scripts/run_tests.sh`, filter
`BekasyarDryRunTest`, passed (1 test): logical versus inline stars, embedded
separators, exact Unicode/whitespace, table cells, reference location across a
separator in the same paragraph and empty segments. No mobile change/test/build.
Checksums and all-line reconstruction passed for each supplied content document.
Initial DB read needed sandbox escalation; a query relationship typo was
corrected to `credits.author` before successful read-only inspection. No live
server/database defect or data modification occurred.

## Next gate

The safe-import condition in the task is **not met**, so no Artisan apply command
has been prepared or executed. Resolve poem 176's table representation and the
six notes (specifically whether the literal incomplete `(` must be retained as
supplied, or whether the owner has an authoritative replacement). Then prepare
an add-only, transaction-safe, repeatable Laravel command through existing
content models/services, keeping source originals/checksums, actor audit and
idempotency, with guards for Draft 13 and unchanged current/bin content.
Existing `DocxReader`/`ImportWordDocument` cannot be used unchanged: they skip
tables and notes. `poetry:import-manifest` is also not an approved shortcut: it
can create a book and lacks this task's exact identity/source/representation gate.

A later apply must have owner approval of the resolved dry run, fresh verified
backup in `/home/shelf/backups/shelf/` in the same run, and the applicable R3
reversible-migration safeguard for shell-scripted data changes. Preparation must
be isolated and verified on staging before any live code deployment. No backup,
migration, deployment, publication, mobile build or store action occurred here.
Rollback for this dry run is simply to stop using its isolated review artifacts;
there are no live changes to undo. No push/main change was requested or performed.
