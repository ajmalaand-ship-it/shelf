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
- The owner-approved default poem font size is 16. A fresh install, or an
  installation with no saved `reader.font_size`, starts at 16. The established
  user-adjustable range remains 18–38; changing the default does not redefine
  that prior range. Existing saved sizes in that range are preserved, including
  24: the former implementation did not persist its automatic 24 default, so a
  stored 24 can only be treated safely as a deliberate selection. Obsolete
  saved values are clamped to 18–38 at runtime. Loading a missing or clamped
  value does not rewrite the preference key.
- General UI and metadata remain Vazirmatn for consistency and usable real weights. The selected reading family applies to poem text, poem titles, and share-card poetry.
- Reader titles are larger and separated from secondary translation/date/place/status metadata. The existing source date/place value is displayed only when stored; missing first-collection values remain PC-B work.
- `SOURCE` preserves source newlines and blank stanza boundaries with no automatic grouping.
- `COUPLET` adds a half-line visual gap after each two non-blank source lines.
- `FOUR_LINES` adds a half-line visual gap after each four non-blank source lines.
- Grouping is renderer-only; it never inserts whitespace into the stored poem body.
- The reader uses 12 logical pixels of horizontal padding on each side and the
  available device width up to the existing 720-pixel readability cap. Source
  newlines remain authoritative; lines are never merged or edited. A line that
  still cannot fit wraps naturally inside the viewport without clipping or
  horizontal overflow.
- Existing poems migrate safely to `SOURCE`. Filament provides an owner-controlled presentation selector.
- Poem and collection create/update/delete events now invalidate `content_version`, including order, publication, free/locked and layout changes.

## Real-content QA matrix

Protected local review selected record IDs only: titled 7, untitled 4, long 74, multi-stanza 95, stored date/place 85, translation 143, and final-heh example 4. Poem bodies were not copied into this document or reports.

P6 remains not started. PC-B catalogue recovery remains out of scope.

## Owner device mismatch correction — SYSC-PC-TYPOGRAPHY-01A

The first owner screenshot showed the superseded two-option selector even though the delivered APK contained the three-font source and assets. The original and corrected builds both initially identified as Android version 1.0.0 (code 1), which made an older installed artifact impossible to distinguish on device. The correction uses fully named, vertically listed font choices, a labeled Home button, explicit migration of the legacy `nastaliq` preference to `literary`, and an owner-preview-only `PC-TYPOGRAPHY-01A • BUILD 2` banner marker. The replacement preview APK is built as version code 2 so Android installs it as an unambiguous upgrade; release builds retain no preview marker.

## Reader UX completion — SYSC-PC-READER-UX-02

One persisted reading-font preference now controls collection/book titles on
Home and the all-collections view, the collection title and poem display labels
on collection contents, and true poem titles and poem bodies in Reader.
Utility controls, attribution, status, and date/place metadata remain
Vazirmatn. The same three-choice `لیکبڼه` sheet is available from Home,
collection contents, and Reader, and every literary label listens to the shared
settings notifier for immediate updates.

Untitled poem listings derive their display-only label from the first non-empty
source excerpt line. The model title remains null/empty and no visible
`بې سرليکه` fallback is used. Reader does not promote that derived line: it
shows a subtle document-outline indicator with accessibility semantics in the
title area. The poem page's core order is true title or untitled indicator,
poem body, then exact stored date/place. Date/place is smaller and muted; an
absent value creates no placeholder. Reader actions use compact icon-plus-text
labels `شریکول` and `لیکبڼه`. The owner-preview-only identification marker is
`PC-READER-UX-02 • BUILD 3`; it is unreachable in product/release mode.
