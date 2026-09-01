# PC Simple Poetry Editor — SYSC-PC-POETRY-EDITOR-04

**Stage:** PC — Product Completion & Owner Acceptance

## One owner workflow

The poem editor is a deliberately minimal authenticated Filament rich editor.
Its only toolbar actions are Undo and Redo. Shift+Enter creates the next poetic
line inside the current paragraph; Enter creates a new bayt/paragraph. The live
preview shows those same RTL lines, paragraph gaps, true title state, and
date/place at the bottom.

The rich editor document exists only in the browser/form state. On save,
`<br>` becomes one newline and adjacent paragraph nodes are joined by two
newlines. `poems.body` remains plain Unicode `LONGTEXT`; HTML and Tiptap JSON are
never canonical poem storage. Loading performs the inverse conversion, so one
newline becomes a hard line break and two newlines become a paragraph boundary.

## Reader presentation

One newline is rendered as a normal line advance within the same text block.
Two newlines are consumed as a paragraph boundary and rendered as an explicit
0.5-em gap, never as an empty line using the Reader's 2.2 line height. Each
additional newline in the same boundary adds another 0.5 em conservatively.

Existing SOURCE, COUPLET, and FOUR_LINES values remain supported. An authored
paragraph boundary takes priority in every mode. For older bodies without any
paragraph boundary, COUPLET retains two-line grouping and FOUR_LINES retains
four-line grouping. New poems default to SOURCE; the legacy selector is shown
only when editing existing records.

## Rejected manual-spacing metadata

The prior line-by-line NONE/HALF/FULL workflow is rejected and removed from the
Admin, API, model, preview, and Flutter runtime. The already-applied nullable
`poems.presentation_spacing` column remains in the production schema solely to
avoid a destructive correction migration. It is deprecated and dormant: this
application does not read it, write it, expose it, or render from it. Production
had zero non-null values before this replacement. A later migration may remove
the column only after owner acceptance.

## Untitled marker

Untitled poems use a small custom monochrome ornament: one softly curved
manuscript sheet with restrained writing strokes and a diagonal quill. It has
no folded file corner or open-book shape, is non-interactive and theme-aware,
and retains an accessibility description without showing a fake title.
