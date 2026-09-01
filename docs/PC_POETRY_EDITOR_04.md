# PC Simple Paragraph Spacing — SYSC-PC-PARAGRAPH-SPACING-04A

**Stage:** PC — Product Completion & Owner Acceptance

## One owner workflow

The poem editor is an ordinary authenticated Filament plain-text textarea.
Enter creates one source newline. Leaving one empty line (pressing Enter twice)
creates a paragraph/block boundary. The live preview shows the same RTL lines,
paragraph gaps, true title state, and date/place at the bottom. There is no
special key command, rich-text document, or line-by-line spacing control.

`poems.body` remains plain Unicode `LONGTEXT`. The form loads and saves the text
directly, preserving words, punctuation, Unicode, and source newlines exactly.

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
