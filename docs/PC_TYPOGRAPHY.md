# PC Typography — Real-Content Font and Poetry Layout Package

**Task:** SYSC-PC-TYPOGRAPHY-01

**Stage:** PC — Product Completion & Owner Acceptance

**Status:** TECHNICAL BUILD COMPLETE — OWNER REAL-CONTENT DEVICE REVIEW REQUIRED

## Font system

- Vazirmatn 33.003 variable (100–900) is the default application and reading font. The UI scale uses real 400, 500, 600 and 700 instances.
- Scheherazade New 4.500 is the traditional Naskh reading option. Regular, Medium, SemiBold and Bold files are bundled, though poetry body text uses Regular.
- Noto Nastaliq Urdu variable is the literary/calligraphic reading option. It is no longer the default UI face.
- Each family is distributed under SIL Open Font License 1.1 and retains its bundled OFL notice.

Gulzar was evaluated from the official Google Fonts source but rejected because the representative Pashto corpus produced missing glyphs. Awami Nastaliq was rejected for the Flutter product because its stable release requires Graphite shaping. Noto Naskh Arabic was tested as a complete comparison baseline; Scheherazade New was retained for its traditional extended-Arabic design, maintained 4.500 release, real weights, and complete tested Pashto coverage.

## Final heh investigation

All 148 current preview poems were inspected without modifying text. Their heh inventory uses U+0647 ARABIC LETTER HEH (5,204 occurrences); no U+06C0/U+06C1/U+06C2/U+06D5 substitutions, Arabic presentation-form characters, replacement characters, or heh-adjacent ZWJ/ZWNJ controls were found. HarfBuzz shaping of representative words and Pashto letters produced no missing glyph in the three selected fonts or either Naskh baseline.

The broken-looking forms are therefore a font-design/contextual-glyph appearance issue in the prior faces, not evidence of Flutter dropping shaping or stored text corruption. Vazirmatn provides a clearer final/connected U+0647 form for the default experience. No production poem wording or codepoint was changed.

## Product rules

- Home and Reader open the same persisted reading-preferences control.
- General UI and metadata remain Vazirmatn for consistency and usable real weights. The selected reading family applies to poem text, poem titles, and share-card poetry.
- Reader titles are larger and separated from secondary translation/date/place/status metadata. The existing source date/place value is displayed only when stored; missing first-collection values remain PC-B work.
- `SOURCE` preserves source newlines and blank stanza boundaries with no automatic grouping.
- `COUPLET` adds a half-line visual gap after each two non-blank source lines.
- `FOUR_LINES` adds a half-line visual gap after each four non-blank source lines.
- Grouping is renderer-only; it never inserts whitespace into the stored poem body.
- Existing poems migrate safely to `SOURCE`. Filament provides an owner-controlled presentation selector.
- Poem and collection create/update/delete events now invalidate `content_version`, including order, publication, free/locked and layout changes.

## Real-content QA matrix

Protected local review selected record IDs only: titled 7, untitled 4, long 74, multi-stanza 95, stored date/place 85, translation 143, and final-heh example 4. Poem bodies were not copied into this document or reports.

P6 remains not started. PC-B catalogue recovery remains out of scope.

## Owner device mismatch correction — SYSC-PC-TYPOGRAPHY-01A

The first owner screenshot showed the superseded two-option selector even though the delivered APK contained the three-font source and assets. The original and corrected builds both initially identified as Android version 1.0.0 (code 1), which made an older installed artifact impossible to distinguish on device. The correction uses fully named, vertically listed font choices, a labeled Home button, explicit migration of the legacy `nastaliq` preference to `literary`, and an owner-preview-only `PC-TYPOGRAPHY-01A • BUILD 2` banner marker. The replacement preview APK is built as version code 2 so Android installs it as an unambiguous upgrade; release builds retain no preview marker.
