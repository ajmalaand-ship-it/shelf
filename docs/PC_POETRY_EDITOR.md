# PC Poetry-Aware Editor — SYSC-PC-POETRY-EDITOR-02

**Stage:** PC — Product Completion & Owner Acceptance
**Status:** TECHNICAL BUILD COMPLETE — OWNER ADMIN/DEVICE REVIEW REQUIRED

## Canonical text and presentation

`poems.body` remains plain Unicode `LONGTEXT`. The Admin still saves only the
textarea value, and neither the preview nor the mobile renderer rewrites poem
wording, punctuation, line order, or source newlines. This package creates no
database migration, production content write, normalization, or layout-mode
assignment.

`SOURCE` continues to render the source, including blank lines, directly.
`FOUR_LINES` retains its existing grouping behavior. `COUPLET` now groups two
source lines per bayt and inserts a typography-relative gap equal to half the
computed line extent. This is presentation spacing, not a synthetic text line.

For existing Ghazals that already contain one blank source line between
complete two-line bayts, that blank is treated as the authored bayt separator
and produces the same single half-line presentation gap. It is not rendered as
an empty row in addition to that gap. A run of two or more blank lines remains
distinguishable: the first represents the normal half-line bayt gap and every
additional blank line adds one full typography-relative line of space. A blank
break beside an incomplete pair is not inferred to be a bayt separator and is
kept as full-line presentation space.

## Owner editing and preview

The existing poem create/edit form now labels the three choices as Source
spacing, Ghazal / 2-line bayts, and Four-line grouping while retaining the
stored `SOURCE`, `COUPLET`, and `FOUR_LINES` values. Concise help makes clear
that presentation never changes the stored poem text.

The authenticated Filament form contains a reactive **Presentation preview**.
It updates from the real title, poem textarea, selected presentation, and exact
date/place field. It shows a subtle no-title marker rather than a fake title,
renders date/place below the body, escapes entered text, and provides no HTML,
Markdown, style, or general rich-text controls. It is visible only within the
existing authorized Admin poem create/edit workflow:

- `/admin/poems/create`
- `/admin/poems/{record}/edit`

## Mobile and share cards

Only the Reader's `COUPLET` presentation path changes. Font choice, font size,
themes, SOURCE, FOUR_LINES, untitled semantics, title/body/date-place order,
audio, offline/cache, entitlement, and owner-preview isolation remain intact.
Share cards continue to paginate the selected canonical source text and do not
add reader-only gap rows; their existing design and pagination are unchanged.

The protected device-review artifact is
`mobile/build/SYSC-PC-POETRY-EDITOR-02-owner-preview-build5.apk`, version
1.0.4/code 5, with preview-only marker `PC-POETRY-EDITOR-02 • BUILD 5`. Its
token expires at 2026-09-08T03:21:20Z. The temporary build token file was
deleted after assembly, and the separately built release APK contains neither
the task marker nor owner-preview banner.
