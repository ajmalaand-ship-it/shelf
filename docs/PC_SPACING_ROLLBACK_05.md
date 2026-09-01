# PC Spacing Rollback — SYSC-PC-SPACING-ROLLBACK-05

**Stage:** PC — Product Completion & Owner Acceptance

**Current governing decision:** **PC-D-001 — Source-Text Poetry Presentation
for V1**, owner approved September 1, 2026. Source presentation is the accepted
V1 baseline; spacing refinement is postponed and is not a PC blocker.

Bayt and paragraph spacing refinement is **postponed and not currently being
pursued**. Multiple implementation attempts added complexity without producing
an owner-acceptable visual result.

The product has returned to the stable `SYSC-PC-OWNER-ADMIN-01` behavior:

- the Admin poem body is an ordinary plain Unicode textarea;
- no spacing preview, manual spacing controls, rich editor, special key
  commands, or paragraph parser are present;
- source text and existing blank lines are preserved without reinterpretation.

SYSC-PC-LAYOUT-SIMPLIFY-06 subsequently removed the owner-facing `SOURCE`,
`COUPLET`, and `FOUR_LINES` controls and their automatic two-line/four-line
rendering. The legacy `layout_mode` schema/API value remains unchanged but is
ignored by normal reading presentation.

The already-applied nullable `poems.presentation_spacing` column remains as
dormant legacy schema. Normal application code does not read, write, or expose
it. Its applied migration remains unchanged and production contained zero
non-null values at rollback.

The later approved untitled marker is intentionally retained: one subtle,
secular manuscript page with a quill, no visible fake title, and accessibility
semantics.

The current deployed checkpoint is
`0c011b10da011f8cbab050338f2c11d8731a4088`. All 148 production poems use the
simple source presentation, and no poetry records were rewritten. PC remains
active; PC-B — Catalogue Completion & Editorial Reconciliation is next; P6
remains not started.
