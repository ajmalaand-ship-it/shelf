# PC-B — Catalogue Completion & Editorial Reconciliation Status

PC-B remains broader Product Completion work. This record tracks the completed
first-collection date/place reconciliation, the governing six-book catalogue
scope, and the current protected-source audit. It does not mark PC-B complete.

## Governing complete-catalogue scope — 2026-09-01

Under owner-approved **PC-D-002**, all six identified books belong in the
intended complete پېڅوَل catalogue. Five are Ajmal Aand original-poetry
collections. `سيند په پرخه کې` is a separate translation book of selected
poems by original poet پروین پژواک, translated into Pashto by Ajmal Aand.

### Current six-book status

1. `څپو کې انځورونه` — **IMPORTED AND READY FOR OWNER REVIEW** — 81 draft
   poems.
2. `هېندارې او چینې` — **IMPORTED AND READY FOR OWNER REVIEW** — 67 draft
   poems: 63 Ajmal originals and 4 correctly attributed translations.
3. `د زړه پر پاڼه مې انځور دی ګلاب` — **IMPORTED FROM OWNER-APPROVED
   UNICODE SOURCE — OWNER REVIEW REQUIRED** — 57 draft poems: 56 titled and
   1 genuinely untitled.
4. `دا ښار، هاغه غرونه` — **OWNER-APPROVED AND IMPORT PACKAGE CLOSED** — 22
   draft poems, all explicitly titled; Ajmal completed real-device review in
   Build 10 and confirmed the book works.
5. `دلته ډېر لرې له غرونو` — **SOURCE-BLOCKED**.
6. `سيند په پرخه کې` — **IMPORTED AND READY FOR OWNER REVIEW** — 74 draft
   poems: 73 titled and 1 genuinely untitled, with protected artwork.

Five collections and all 301 poems remain draft/private. `دلته ډېر لرې له
غرونو` remains the only source-blocked book in the approved six-book scope.

## Four-book protected-source audit — 2026-09-01

### د زړه پر پاڼه مې انځور دی ګلاب — Unicode source import 2026-09-02

- Owner-approved exact collection title:
  `د زړه پر پاڼه مې انځور دی ګلاب`. This deliberately overrides only the
  slightly different collection-title wording in the Word source.
- Authoritative Unicode source:
  `storage/app/source/collections/d-zra-par-pana-me-anzor-de-gulab/owner-authoritative-unicode-20260901.docx`
  (74,138 bytes; SHA-256
  `0a7b706c5a6b3894de2d0ec7360f7a0029a856d5d2c9ce68539317f7373d0709`).
- Source validation: valid Microsoft Word DOCX package; 749 text-bearing
  paragraphs and 30,028 Unicode characters; no embedded media; no U+FFFD,
  private-use characters, Arabic presentation forms, abnormal controls, ZWJ,
  or ZWNJ. No OCR was used.
- Deterministic structure: publication information, dedication, and
  attributed critical text were stored as collection front matter; 57 poems
  were imported in source order, comprising 56 titled poems and 1 genuinely
  untitled poem; 25 poems carry exact combined date/place metadata. There
  were no unresolved poem-boundary or title ambiguities.
- Protected original cover:
  `storage/app/source/collections/d-zra-par-pana-me-anzor-de-gulab/original-cover.jpg`
  (1306×1879 sRGB JPEG; SHA-256
  `ab846c44d7894b286eefc257b5124e9ed7c20ed3febb447e141f249672c16d63`).
  A checksum-identical normal application cover copy is attached.
- Import status: one draft/private collection and 57 draft, locked poems;
  zero audio attachments. The public API does not expose the collection; the
  protected owner-preview API does. Owner review remains required.
- Verified pre-import backup:
  `/home/ajmalaand/backups/poetry/20260902-040753`. Pre-import production was
  2 collections / 148 poems with content version 50. Post-import production
  is 3 collections / 205 poems with content version 108. Aggregate
  fingerprints prove that the two prior collections and 148 prior poems did
  not change.

### Historical blocked-source evidence for د زړه پر پاڼه مې انځور دی ګلاب

- Primary source:
  `storage/app/source/catalogue-bundle-20260828/original-poetry/01_d_zra_pa_pana_me_anzor_de_gulab.pdf`
- Format/evidence: 38-page PDF, SHA-256
  `263f0427322ecb7cc9f1e9eebc36a0cab748edbddfdeeef3054956ef724349f6`;
  cover and front/content pages are preserved in source order.
- Existing recovery evidence: 38 rendered page images and 38 OCR text files
  under `storage/app/source/catalogue-bundle-20260828/recovery/c1-*`.
- Historical trust result: **SOURCE-BLOCKED**. The PDF text extraction is mixed/legacy
  encoded and contains substantial control/encoding artifacts; the recovery
  text is OCR-derived. Complete exact Unicode, poem boundaries, titles,
  dates/places, notes, and front matter cannot be imported safely from it.
- Resolution: the owner-supplied DOCX above is now the authoritative Unicode
  source. The old PDF/OCR material was used only as supporting visual evidence
  for one untitled-work boundary and did not supply imported text.

### دا ښار، هاغه غرونه

#### Owner-corrected Unicode source import — 2026-09-03

- Ajmal designated the corrected Word document as authoritative for poem text,
  titles, line breaks, ordering, date/place metadata, front matter, and
  publication information. The protected source is
  `storage/app/source/collections/da-shaar-hagha-gharona/owner-authoritative-unicode-20260902.docx`
  (57,940 bytes; SHA-256
  `6150eaaeeb028a938918cc575e7a2bfc38475b87191e89bebc62a33669028bcd`).
- The valid editable DOCX contains 591 non-empty text-bearing paragraphs and
  19,063 Unicode text characters. Direct XML inspection found no U+FFFD,
  private-use characters, Arabic presentation forms, abnormal controls, ZWJ,
  or ZWNJ. No OCR, PDF hidden text, normalization, spelling modernization, or
  inferred wording was used.
- Deterministic Word page-break structure produced 22 poems in exact source
  order: 22 titled, 0 untitled, and 17 with exact combined date/place
  metadata. Four front-matter sections were preserved: dedication,
  publication information, the attributed عصمت قانع foreword, and Ajmal
  Aand's signed reader introduction. There were no unresolved boundaries or
  structural ambiguities.
- The trustworthy historical cover page from the preserved original PDF was
  retained unchanged as
  `storage/app/source/collections/da-shaar-hagha-gharona/historical-cover-page.png`
  (2479×3508 grayscale PNG; SHA-256
  `0b0632f360200fd993b9059ac1967d19de5698ae3f304d61b7a6aa03d841a9b9`).
  A checksum-identical application cover copy is attached; no cover was
  fabricated or redesigned.
- Verified pre-import backup:
  `/home/ajmalaand/backups/poetry/20260903-010303` (database SHA-256
  `209f789ba413f7b3395934279121f677272051d74a5c2911da71286638a16bc6`,
  media SHA-256
  `1117e3aa58477e0d5664b1ab8ef7b414bda432f499ee8c4afb73611ccde8afbd`).
  Pre-import production was 4 collections / 279 poems at content version 183.
- The checksum-guarded manifest imported one draft/private original-poetry
  collection and 22 draft, locked poems with no audio. The second importer run
  made zero changes. Production is now 5 collections / 301 poems at content
  version 206, and fingerprints for all prior 4 collections / 279 poems are
  unchanged.
- Public APIs hide the collection. The existing protected owner-preview APK,
  without a rebuild, has a valid credential and shows the collection and all
  22 poems.
- Ajmal completed real-device book review in Build 10, confirmed that `دا ښار،
  هاغه غرونه` works, and accepted the imported book. Status: **OWNER-APPROVED
  AND IMPORT PACKAGE CLOSED**. This acceptance changes no publication state;
  the collection and all 22 poems remain private/draft and locked.

#### Historical blocked-source evidence

- Primary source:
  `storage/app/source/catalogue-bundle-20260828/original-poetry/04_da_shaar_hagha_gharona.pdf`
- Format/evidence: 36-page PDF, SHA-256
  `7af7150c259e4156a53b40ce504eae8bb1f645653d732fc22b687c84e0061d89`;
  cover and ordered book pages are preserved.
- Existing recovery evidence: 36 rendered page images and 36 OCR text files
  under `storage/app/source/catalogue-bundle-20260828/recovery/c4-*`.
- Historical trust result: **SOURCE-BLOCKED**. The visible Pashto is not represented by a
  usable authoritative Unicode text layer; prior OCR is not source truth.
  Titles, boundaries, date/place values, notes, and front matter therefore
  cannot be imported without unsafe reconstruction.
- Resolution: the owner-corrected DOCX above is now the authoritative Unicode
  source. The historical PDF supports the original cover and publication
  history only; its hidden text and OCR derivatives did not supply imported
  poetry.
- Manual transcription preparation (2026-09-01): the owner-approved,
  page-based workspace was generated directly from the authoritative PDF at
  300 DPI. It contains all 36 ordered page images, a checksum/dimensions
  manifest, 36 empty UTF-8 transcription templates, and transcription rules.
  No OCR was used and no poetry was transcribed or imported.
- Protected owner package:
  `/home/ajmalaand/backups/poetry/owner-downloads/da-shaar-hagha-gharona-transcription-pack-20260901-161820.zip`
  (SHA-256
  `b91611b67f48a87454a9c59a1c9ccac3f2c17c295ce573801e41cdfe59e26b39`).

### دلته ډېر لرې له غرونو

- Primary sources:
  `storage/app/source/catalogue-bundle-20260828/original-poetry/05_dalta_der_lare_la_gharono/content.pdf`,
  `frontmatter.pdf`, and `original-cover.jpg` in the same directory.
- Format/evidence: 38-page content PDF, 4-page front-matter PDF, and original
  2048×1592 JPEG cover. SHA-256 values respectively:
  `40306f94f22548801d08df7c9a589a510508d9bb4945477dbac76e462ea5b04d`,
  `0f6ff3d3568963b5357999e063ea743c3b77010a1047d47d487b52efcd18f07f`,
  and `17bafbf9c87a2eecba4bf1138cb8bc0a6b9069b1339ad09ee9bbe19558b504c0`.
- Existing recovery evidence: 38 rendered content spreads, 76 split-page OCR
  files, 4 rendered front-matter pages, and 8 split-page OCR files under
  `storage/app/source/catalogue-bundle-20260828/recovery/c5-*`.
- Trust result: **SOURCE-BLOCKED**. The content and front-matter PDFs have no
  usable Pashto Unicode text layer. The cover is trustworthy as an asset, but
  a cover alone cannot authorize a text import. Poem boundaries, titles,
  metadata, and front matter would require transcription.
- Unblock with: the original Unicode/editable document or owner-approved
  manual transcription and proofreading of both content and front matter.

### سيند په پرخه کې

#### Poem artwork capability and transcription checkpoint — 2026-09-02

- Ajmal approved the minimal additive `poems.artwork_path` field. A verified
  System C backup was created at
  `/home/ajmalaand/backups/poetry/20260902-083908` before migration; migration
  `2026_09_02_084459_add_artwork_path_to_poems_table` added one nullable string
  column. Production remained 3 collections / 205 poems and every existing
  poem retained a null artwork path.
- Artwork now uses dedicated private storage, signed access governed by the
  poem's draft/free/locked boundary, owner-preview authorization, a simple
  Filament `Artwork / انځور` field, and optional responsive Flutter reader
  rendering. Private storage paths are not returned by either API.
- The 44 preserved spread scans were split into 74 protected upright poem-page
  working derivatives. Every poem page was visually verified at full
  resolution in source order; the separate 75th internal artwork candidate is
  the front-matter publisher emblem, not a poem. The old PDF layer was used
  only as a positional transcription aid, never as authority.
- Final result: 74 verified poems (73 titled, 1 genuinely untitled), zero
  unresolved readings, and 74 deterministic source-page artwork associations.
  The scan review preserved the three visible stanza gaps and corrected one
  character omitted by the aid layer; no text was guessed or modernized.
- Fresh verified pre-import backup:
  `/home/ajmalaand/backups/poetry/20260902-093801` (database SHA-256
  `dc5b808a54f4ceff82af12fa2cc574f5ebe9115386f7601b904417220979254c`,
  media SHA-256
  `1a360174d697fd90ebc161ff07eb3808e56efab5b3c80939b4ec2b629fbd32ed`).
- The checksum/member-guarded manifest imported one draft/private translation
  collection and 74 draft, locked poems, all attributed to original poet
  پروین پژواک and Pashto translator Ajmal Aand. The second importer run made
  no changes. Production is now 4 collections / 279 poems; the protected
  fingerprints of the prior 3 collections / 205 poems are unchanged.
- The cover and 74 original-quality illustrations are attached through
  protected storage. Public APIs hide the draft book; protected owner preview
  displays its Unicode text and signed artwork. Private owner-preview APK build
  9 is at
  `/home/ajmalaand/backups/poetry/owner-downloads/sind-pa-parkha-ke-owner-preview-build9-20260902-0946.apk`
  (SHA-256
  `a20c84898ab57377329ac2c139f462adf5e82263df10c3e136f5d43cf63a3eb0`).

#### Private artwork inventory — 2026-09-02

- Owner artwork sources were received as two page-image DOCX packages plus an
  original cover and preserved under the protected System C source folder
  `storage/app/source/collections/sind-pa-parkha-ke/`.
- Ajmal confirmed that he has permission to use the artwork from this book in
  the app.
- The artwork-only inventory and private owner-review package were prepared
  from all 44 ordered embedded spread images (88 visible book pages). No OCR
  was used and no poetry text was imported.
- Protected owner package:
  `/home/ajmalaand/backups/poetry/owner-downloads/sind-pa-parkha-ke-artwork-review-pack-20260902-030149.zip`
  (SHA-256
  `1bdd04743c24a65784ce95324920577f8f8d0d5b07d457c4af725c4edb0ea2ac`).
- The DOCX sources remain image/page based and are not considered trustworthy
  editable poem text. The book itself has not been imported and remains
  source-blocked for text import.
- Ajmal reviewed the first artwork inventory and selected 32 illustrations for
  a smaller clean-crop review. A separate protected clean-review set preserves
  every original candidate and provides crop-only derivatives plus
  original-to-clean comparison sheets. This is not final artwork selection or
  app integration; no poetry text was imported and no database, collection,
  poem, publication, or application state changed.
- Clean-crop owner package:
  `/home/ajmalaand/backups/poetry/owner-downloads/sind-pa-parkha-ke-clean-artwork-review-20260902-040608.zip`
  (SHA-256
  `c4e60413df281c65099a0f4360d1b716c1cb18fea68803c99adb97a699a51fa1`).

- Primary sources:
  `storage/app/source/catalogue-bundle-20260828/translations/sind_pa_parkha_ke_part1.pdf`
  and `sind_pa_parkha_ke_part2.pdf`.
- Format/evidence: ordered 24-page and 20-page scanned PDFs, SHA-256
  `b95f1038d2701c5902f0cbe16fcac622cf4f3ae442199bcc47a0e0a369f90014`
  and
  `0d4fb651362c24340b00404083be408982d8024627777d974ac0ed10572186a3`.
  The cover/front matter in part 1 and the source-bundle manifest support the
  book title, original poet پروین پژواک, and Pashto translator Ajmal Aand.
- Trust result: **VISUALLY VERIFIED FROM PRESERVED SCANS**. Ajmal directed that
  the preserved visible page images are authoritative and approved manual
  transcription from them. All 74 poem pages were checked line by line before
  import; no hidden/OCR reading overrode a scan.
- Schema fit: the owner-approved nullable poem artwork field is in production.
  Every imported work uses `work_type=TRANSLATION`,
  `original_author=پروین پژواک`, and `translator=Ajmal Aand`; the book is not
  represented as Ajmal's original poetry.

No repeated OCR was run during this audit. Existing OCR derivatives were
treated only as recovery evidence, never as import authority.

## Date/place reconciliation — 2026-09-01

- Authoritative manifest:
  `storage/app/source/collections/tsapo-ke-anzorona/import-manifest.json`.
- Authoritative source checksum matched the private source PDF recorded by the
  manifest.
- Matching used the original deterministic collection plus continuous manifest
  sequence / production `sort_order` identity. Exact title and body hashes were
  refusal guards, never fuzzy matching inputs.
- Source works: 81.
- Source records containing combined date/place metadata: 47.
- Safely restored: 47.
- Already present before reconciliation: 0.
- Conflicts, ambiguities, invalid source values, or missing production records:
  0.
- Verified backup:
  `/home/ajmalaand/backups/poetry/20260901-194656`.
- Production poem count before and after: 81.
- Content version advanced from 3 to 50 through the existing poem-save
  invalidation behavior.
- Post-write dry-run: 47 already present and matching; 0 safe missing values.
- No poem body, title, excerpt, collection, order, publication/access state,
  layout, audio, translation attribution, or other collection changed.

The established schema stores date and place together in one
`source_date_place` field, so the 47 updates are reported as combined
date/place values rather than inferred separate date and place counts.

## Remaining PC-B catalogue work

- complete outstanding owner review and editorial reconciliation for the
  other imported collections;
- obtain a trustworthy Unicode source or approve manual
  transcription/proofreading for the remaining source-blocked book,
  `دلته ډېر لرې له غرونو`;
- confirm or revise the current 15 free-sample decisions;
- complete missing covers, front matter, and remaining metadata;
- preserve and verify truthful translation attribution; and
- verify the off-server source archive.

This reconciliation does not mark PC or PC-B complete. P6 remains not started.
