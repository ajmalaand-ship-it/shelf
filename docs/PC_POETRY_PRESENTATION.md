# PC Manual Poetry Presentation — SYSC-PC-POETRY-PRESENTATION-03

**Stage:** PC — Product Completion & Owner Acceptance

## Why the previous gap looked too large

The Reader uses a 2.2-em line box. The prior implementation added half of that
entire line box (1.1 em) between separate text widgets. The widgets already
contributed the ordinary line advance, so consecutive bayts had roughly a
1.5-line baseline interval. The Admin preview repeated the same 1.1-em added
space. HALF and FULL now mean 0.5 em and 1 em of additional typography-relative
space respectively.

## Separate manual presentation data

Canonical `poems.body` remains plain Unicode `LONGTEXT`. A new nullable JSON
column, `poems.presentation_spacing`, contains only presentation instructions:

```json
{
  "version": 1,
  "line_count": 4,
  "gaps": [
    {"after_line": 2, "gap": "HALF"},
    {"after_line": 3, "gap": "FULL"}
  ]
}
```

`null` means the existing layout behavior remains automatic. When manual
spacing is enabled, boundaries omitted from `gaps` mean NONE. Line numbers are
the one-based ordinals of non-empty literary lines; source blank lines are not
deleted or rewritten. `line_count` prevents stale instructions from moving to
the wrong verse after a body change. Invalid or stale metadata fails safely to
the selected layout mode's automatic behavior.

The additive migration leaves every existing row `null`; it performs no
backfill and changes no poem. It has not been applied to production in this
package. A verified production backup is required before deployment applies
it.

## Owner workflow

On `/admin/poems/create` and `/admin/poems/{record}/edit`, the Presentation
section retains Source spacing, Ghazal / 2-line bayts, and Four-line grouping.
Ajmal can enable **Choose spacing manually** and then select No extra gap, Half
gap, or Full gap after each displayed literary line. The authenticated preview
updates from those choices immediately. Disabling manual spacing returns the
metadata to `null`; neither action inserts or removes characters in the poem.

SOURCE always renders the source text and blank lines directly. In COUPLET and
FOUR_LINES, valid manual instructions take priority and raw blank-line
heuristics are ignored. Without manual metadata, COUPLET retains its grouping
aid with the corrected 0.5-em subtle gap, and FOUR_LINES retains its established
automatic behavior.

Public locked responses return neither the body nor its spacing metadata.
Owner preview and entitled/free full-text responses include valid presentation
metadata. Model-level `content_version` invalidation remains the only cache
invalidation mechanism.

## Untitled marker

The prior symmetrical open-manuscript ornament could resemble a holy book. It
is replaced in both the mobile Reader and Admin preview by an asymmetrical
single folded-corner page with writing lines and a diagonal quill/pen. It is
custom vector drawing, theme-aware, non-interactive, secondary in contrast,
and retains the no-original-title accessibility description without visible
fallback text.

Share cards continue to use the canonical selected text and do not store or
inject presentation markers.

The protected device-review artifact is
`mobile/build/SYSC-PC-POETRY-PRESENTATION-03-owner-preview-build6.apk`, version
1.0.5/code 6, with preview-only marker
`PC-POETRY-PRESENTATION-03 • BUILD 6`. Its token expires at
2026-09-08T04:16:20Z. The temporary token file was deleted after assembly, and
the separately built release APK contains neither the marker nor preview
banner.
