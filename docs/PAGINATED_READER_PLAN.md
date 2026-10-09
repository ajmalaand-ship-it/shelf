# Optional paginated reader — next approved implementation batch

Recorded 9 October 2026. Owner approved this as the next reader task, explicitly
not implementation in the pinned-preview batch. This is a concrete proposal,
not built functionality or permission for an app release/backend deployment.

## Inspected current foundation

- PoemReaderScreen loads one item ID through PoetryDataSource.loadPoem and shows
  it in a vertical SingleChildScrollView. It receives a collection title, not the
  book's ordered contents or book language. Contents returns to Book details.
- CollectionDetailScreen already has CollectionBundle.collection and ordered
  PoemSummary entries from the server, with stable item IDs and sortOrder. Its
  openItem closure is the place to pass the book context to the existing reader.
  Repository parsing preserves the supplied order; do not invent title ordering.
- PoetryCollection supplies book ID/slug and language; PoetryText presently uses
  RTL/start alignment and SelectableText with the exact PoemDetail.readableText.
  ReaderSettings supplies the existing three fonts, size and palettes.
- PoemDetail.readableText is empty when locked. Partial samples expose only the
  approved body/excerpt, with isFreeSample/hasMore/access metadata. Never infer
  paid text or grant access from a position or page count.
- OwnedBookRepository checks its captured account, then LibraryController.copy
  rechecks identity/generation, ownership and offline lease/clock before returning
  the account's copy. Keep this path; do not bypass it with a new text cache.
- No reader position store exists. Audio position is playback time, not reading
  position. ShareCardPaginator is for exported cards, not book reading: it
  normalizes CR/LF and trims/rejoins whitespace. Do not reuse that transformation
  for source-preserving pages.

## Proposed coordinated shared Flutter batch

1. Add an optional Scroll / Pages reading mode, retaining current scrolling as
   the default and all existing font/theme preferences. Extend the current
   reader with book ID, ordered item context, book language, content version and
   the same access-scoped repository. Contents remains direct navigation.
2. Use native Flutter TextPainter/shaped line metrics and a PageView with large
   labelled previous/next controls and swipes. Match actual reader font, text
   scale, line metrics and content viewport after reserving toolbar/actions.
   Measure title/credits/date/media blocks too; preserve audio, artwork, sharing,
   sample and locked/error controls. No separate iOS UI or third-party engine is
   proposed. The installed Flutter SDK exposes computeLineMetrics,
   getPositionForOffset and line-boundary primitives for this work.
3. Represent text pages as ranges of the exact original string, not rewritten
   strings. Keep original CR/LF characters, explicit poetry line breaks, blank
   lines and stanza spacing. Prefer intact stanzas when they fit; oversized
   stanzas can break at measured visual-line boundaries, snapped to valid
   shaped-text/grapheme boundaries. Rendering can wrap at narrow widths without
   editing source. No normalization, trimming, OCR or invented text. Handle
   oversized media/blocks explicitly so every accessible part remains reachable.
4. Move through the existing book order, loading the next item lazily through
   the same repository. A sample ends at the approved boundary; a locked,
   unavailable or failed item retains a clear access/error state and Contents.
   No page can imply ownership or extend an offline lease. Invalidate resident
   page buffers and in-flight results on account, generation, ownership or lease
   changes; recheck through existing access logic on resume and item transitions.
5. Persist a stable local anchor: environment + reader/account scope (separate
   guest scope) + canonical book ID + item ID + block kind + source UTF-16 offset
   and exact-item content fingerprint. Never persist just a page number or paid
   text. Use existing preferences/crypto facilities; position is not entitlement.
   Save the logical first visible text location on a settled page/lifecycle pause.
   Sign-out/account changes must never display another reader's saved location or
   protected buffers. Existing account/download cleanup remains authoritative.
6. Before reflow, retain the anchor. Recalculate when family, font size, text
   scale, viewport/orientation or content changes, then select the page containing
   that anchor. On an actual item text change, validate the fingerprint; avoid a
   silent stale offset. Proposed safe fallback is the same item's beginning with
   a short content-changed notice, rather than guessing relocated source text.
7. Use the book's direction independently of interface language: known ps/fa
   books RTL, English books LTR. Keep logical next/previous order stable and
   mirror controls/swipes accordingly. Unknown configured languages currently
   lack explicit direction metadata; establish their direction before claiming
   support, instead of treating interface language as the book's language.

## Scope choices and remaining decisions

No owner decision blocks pagination for the current Pashto books. Recommended
first-batch choices: scrolling remains default; book flow follows existing
poem/chapter contents; front matter remains available in Book details; changed
item text restores to that item start with a notice. The owner may expand scope
to include dedication/introduction/foreword as pages; that optional editorial
choice is not assumed or required for the core batch. Additional configured
book languages need an explicit direction decision if language metadata does
not identify it. No default reading-mode change, reading-progress percentage,
cloud position sync or new recommendation system is authorized by this proposal.

## Focused acceptance for that batch

Prove exact source reconstruction from page ranges; poetry/stanza/CRLF/bidi and
combining-character integrity; all original fonts and large-text/small-viewport
layout; item-to-item navigation, contents jumps and direction; anchor survival
across reflow/restart; changed-content fallback; approved sample end and locked
text denial; unavailable/error states; account/sign-out/revocation and offline
lease/clock isolation. Use synthetic fixtures and existing access tests without
repeating accepted purchase/backend tests. Android and iOS phone acceptance
remain separate evidence; any later build follows a separately authorized task.

## This batch's boundaries

Only this plan is recorded now. No pagination, position store, reading engine,
dependency, backend/schema/provider change, build or deployment was introduced
by the pinned-preview fix. Checkout OFF, pricing sync deferred, second-phone
restore owner-deferred and joint Android/iOS launch remain binding.
