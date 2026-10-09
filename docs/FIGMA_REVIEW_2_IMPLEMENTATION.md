# Shelf Figma Review 2 — shared Flutter implementation

Owner approval and implementation authorization: owner task received 8 October
2026; implementation/verification continued 9 October local time.
[Approved Review 2](https://www.figma.com/design/NVSrbPa6eJJVUfIK3vdUeJ?node-id=7-5),
file NVSrbPa6eJJVUfIK3vdUeJ, board 7:5.

The Figma connector returned high-fidelity context and screenshots for the
six-screen board, with separate Book details, Library and Search context.
The board's 390×844 frames guide proportions; no fixed phone-sized layout,
status-bar imitation, placeholder cover artwork, demonstration reader text,
fixed price or illustrative 32% progress is included in the app.

## Source and boundaries

Isolated checkout: /home/shelf/tmp/shelf-figma-review-2, branch ui/figma-review-2,
based on 72cabf0. Its baseline mobile tree matches deployed 34799b8.
Read-only release-state status confirms staging/production at
34799b8efc93351767c05ecd516711a5badb6c0c, with reviewer-access checks.
The current working Master Record and AGENTS amendments were copied into the
isolation checkout, preserving the newer joint-launch and financial-policy
records. No older documentation branch was applied to the running checkout.
Production's unrelated documentation and public/.htaccess edits remain untouched.

Scope: Store, Book details, My Library, Search, Reader and Reading preferences,
using shared Flutter styling/widgets for Android and iOS. No backend/admin,
schema, account, provider, source-content or production-setting changes.
No staging/production deployment, APK/AAB/IPA build, store upload or transaction.
Checkout remains OFF; pricing sync DEFERRED/disabled; second-phone restore
owner-deferred, not passed and not a new launch blocker. Android and iOS launch
together after their respective acceptance and final owner launch approval.

## Changes

- Shared paper #FAF6EF, ink #29231D, brand #80501D, tint #F0E5D3,
  muted #796F62 and line #E4DACC colors; outlined fields/cards, 8/12/16
  corner radii, 20-point screen margins and content-sized touch targets.
- Store search entry, working poetry/prose/All chips and book rows preserving
  existing catalogue/category/newest ordering and author links.
- Book details original-cover presentation, complete existing metadata/front
  matter/contents, and persistent sample/owned/purchase-status actions.
- Library original private/offline cover loader, authors/translators, real
  owned/downloaded states, All/Downloads filter, Read, download/remove, accurate
  30-day offline help, restore/refresh and real empty/error/loading messages.
- Search outlined input, wrapping compact filters, actual result count and
  book rows; existing query/language/category/type filtering and errors retained.
- Reader book heading, quieter toolbar with labelled semantic icon controls,
  sample state, wider margins and Contents return action. Exact readable text,
  established poetry line metrics, saved font/size, artwork, audio, sharing,
  locked notice and owner-preview behavior remain.
- Scrollable reading preferences with live font/size/palette preview, incremental
  size buttons and existing slider, all three existing fonts, selectable visual
  light/sepia/dark swatches and persisted settings. Reader/preview share colors.
- English navigation now mirrors LTR; Pashto navigation remains RTL. Book
  source and credits remain RTL independently of interface language.

Prices come only from the existing store product lookup; unavailable/anonymous
lookups do not invent a price. Purchase execution retains existing availability
and consent gates. Access and offline ownership remain server-controlled.
Original dynamic covers use contain sizing without redrawing or distortion.
Bundled Vazirmatn remains the interface font; existing reader font choices remain.
Native shared Flutter icon controls are retained instead of adding Figma raster
assets or a new icon/rendering dependency.

## Deliberate mockup omissions/adaptations

- No saved book-progress/resume functionality exists: omit percentage/progress
  bar and Continue reading claim; retain working Read book.
- No existing daily-feature scheduling or featured-book source exists: omit
  “book of the day” and invented selection; retain existing catalogue ordering.
- Search author matching remains the existing text search/author pages;
  no unsupported separate exact-author search API/filter was fabricated.
  Existing category filter is retained alongside language/type.
- Reader next/previous item arrows have no existing ordered navigation binding:
  omit inert arrows and retain real Contents return and selection.
- Keep established saved/default reading size and safe poetry line metrics,
  rather than force every reader to the demonstration font/size.
- Extra existing account/language/settings/privacy/support, credits/front matter,
  restore, audio/sharing and offline controls survive mockup omissions.

## Verification and acceptance

Final focused result: **64 Flutter tests plus 1 staging-identity test passed**,
and the required PHP smoke check passed **1 test / 17 assertions**.
Flutter analysis completed with **zero errors**, retaining three existing warnings
and 61 style notices (64 findings total); warnings are the unchanged repository
entitlement field and unused imports in book-pagination/Library tests.
The default strict analysis exit is nonzero for those existing findings; final
analysis used --no-fatal-infos --no-fatal-warnings and still fails on errors.
No dependency lockfile or app-version change. git diff --check passed.
[Selected rendered screens](FIGMA_REVIEW_2_RENDER_REVIEW.md) are saved as
test-only documentation evidence.

Run through scripts/run_tests.sh --mobile using the review-2 focused scope;
PHP is limited to PrivacySupportTest as the required smoke check, not a repeat
purchase/backend audit. The runner creates and removes disposable test files
under /home/shelf/tmp and never uses the live database.

Evidence log: /home/shelf/tmp/shelf-figma-review-2/checks.log.
Flutter analysis log: /home/shelf/tmp/shelf-figma-review-2/analysis.log.
Selected rendering artifacts: docs/review-2/ in this isolated checkout;
the runner's generated screenshot directory was temporary and was cleaned up.
Render tests exercise RTL/LTR at 320×568 and 390×844 with text scales 1.0/1.8;
Library checks additionally cover 320×568/430×932, ownership, download removal,
refresh failures and immediate server-confirmed action changes.
Fixtures and covers in render artifacts are synthetic or cover fallbacks;
they are test-only evidence, not catalogue replacements.

Initial tests found stale layout expectations and a synthetic cover fixture
network-cache call. These were corrected; source/access assertions remain.
Visual review found header truncation, chip-font fallback and offscreen test
tap problems; these were corrected before final verification.

Android phone acceptance is PENDING until the owner reviews and tests this
result in a separately authorized build. iOS build/device/provider validation
is NOT VERIFIED by Linux Flutter tests. Existing platform/deletion/recovery
acceptance gaps remain; this design batch does not close all project steps.

Next single owner action: review the rendered UI against approved Review 2.
Any later artifact build/deployment follows a separate authorized task and the
staging-first identical-commit workflow; do not merge an old docs snapshot over
newer records. Undo by reverting this isolated branch's UI commits; no runtime
or database rollback is needed because no deployment occurred.


## Owner render corrections — 9 October 2026

Owner authorized only the chip contrast, reader fixture diagnosis, enlarged-text
scroll checks and affected render regeneration; no deployment/upload/release build.
Changes remain on the isolated ui/figma-review-2 branch; newer production records
and unrelated edits were not replaced.

Confirmed chip issue: unselected detail/category Chips inherited a white label
on cream. Shared ChipTheme now explicitly uses brand brown #80501D on #F0E5D3
(5.47:1 contrast). Selected ChoiceChips retain their existing explicit white
foreground on brown. No layout or access behavior changed.

Confirmed reader artifact cause: test_support.dart's default glyph-coverage body
starts with `ټ ډ ړ ږ ښ ڼ ې ۍ`. Exact code points are U+067C, U+0020,
U+0689, U+0020, U+0693, U+0020, U+0696, U+0020, U+069A, U+0020,
U+06BC, U+0020, U+06D0, U+0020, U+06CD: seven ordinary spaces, no ZWJ/ZWNJ.
These are intentionally isolated glyphs, not joined prose. Compared with baseline
72cabf0, the existing PoetryText RTL/start alignment, saved Vazirmatn/default
16-point font and 2.2 line height are unchanged; neither specifies letterSpacing.
Joined words render normally in the regenerated light and enlarged dark renders.
No demonstrated font/shaping/layout regression required an app reader change.
Render tests now request the explicitly synthetic body
`لومړۍ کرښه\nدويمه کرښه\n\nنوی بند` via an optional fixture-only argument.
The original glyph fixture remains for other tests. Real book text, intentional
spaces, stanza breaks, typography and font choices are untouched.

Enlarged details: the initial screenshot was at the top of a scrollable page.
Focused checks at 320×568 and 390×844, scales 1.0/1.8, Pashto RTL and English
LTR reveal the complete title, each expanded publication/dedication/introduction
body and the complete final contents ListTile, asserting their bounds above the
persistent sample action and below the toolbar. All passed. Scaffold already
reserves the action area's height; existing 28-point bottom padding is sufficient.
No bottom padding/scroll code change. details-large.png now shows the scrolled
complete title; added metadata/contents snapshots document subsequent positions.

Verification: scripts/run_tests.sh --mobile with review-2-corrections scope:
20 focused Flutter layout/reader tests, 1 required staging-identity test and
1 required PrivacySupportTest smoke / 17 assertions passed. Evidence:
/home/shelf/tmp/shelf-figma-review-2/correction-checks.log. Regenerated selected
PNGs reviewed visually. No broad purchase/backend audit or release build.
Android phone acceptance and iOS validation remain pending. Checkout OFF,
pricing sync deferred, second-phone restore owner-deferred, joint Android/iOS
launch preserved. These remain synthetic test renders, not phone acceptance.
