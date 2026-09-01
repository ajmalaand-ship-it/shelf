# PC Owner Administration — SYSC-PC-OWNER-ADMIN-01

**Stage:** PC — Product Completion & Owner Acceptance

**Status:** TECHNICAL BUILD COMPLETE — OWNER ADMIN/BROWSER REVIEW REQUIRED

## Later owner-decision addendum — 2026-09-01

This document preserves the historical scope of SYSC-PC-OWNER-ADMIN-01.
PC-D-001 and SYSC-PC-LAYOUT-SIMPLIFY-06 later removed layout modes from the
normal owner workflow: the layout selector, layout table column/filter, and
Presentation section are no longer current behavior. `Order in book` remains
in the main Poem section. Normal reading follows source text/newlines only.

## Existing foundation retained

The package improves the existing Filament 5 resources; it does not introduce
a second administration interface. Existing poem and collection CRUD, private
audio and cover uploads, publication/access controls, ordering, search,
filters, layout modes, front matter, attribution, authorization, and automatic
model-level `content_version` invalidation remain authoritative.

No database migration or production content write is part of this package.

## Owner workflow

The main Admin navigation group is `Poetry Library / شعري کتابتون`, with
`Collections / کتابونه` followed by `Poems / شعرونه`. Application settings
remain available in a less-prominent Advanced group.

Poem create/edit is organized into Poem, Source/Literary metadata,
Presentation, Access/Publication, Audio, and Translation sections. Title stays
optional and is never derived into stored data. The existing combined
`source_date_place` contract is presented as “Date / place as written” so its
exact value remains intact. Translation requires both original author and
Pashto translator. The public excerpt remains required because it is the safe
locked-content representation.

Collection create/edit is organized into Collection/Book, Front matter,
Order/Publication, and collapsed Advanced sections. The existing slug and store
product mapping remain available but do not dominate ordinary editing.

Each collection row and edit page provides `Manage poems` and `Add poem`.
Manage opens the existing Poems resource filtered to that collection; Add opens
the normal poem form with the collection preselected. Poem edit provides
`Edit collection / book` for the current association.

## Untitled truthfulness

The Poems table marks a null/blank title as having no original title and uses
the first non-empty body line only as a short navigation label. It never writes
that line to `poems.title` and does not expose the full body as a table column.

The mobile Reader replaces the generic file icon with a small custom-drawn
open-manuscript ornament and restrained flourish. It follows the active reader
palette, is visually secondary and non-interactive, has no visible fake title,
and retains accessibility semantics explaining that no original title exists.
The preview-only marker is `PC-OWNER-ADMIN-01 • BUILD 4`.

## Owner review routes

- `/admin/collections` — collection/book list and collection-to-poem actions.
- `/admin/collections/create` — create a collection/book.
- `/admin/collections/{record}/edit` — edit a collection and manage its poems.
- `/admin/poems` — searchable/filterable catalogue list.
- `/admin/poems/create` — create a poem, optionally preselected from a book.
- `/admin/poems/{record}/edit` — edit a poem and navigate to its book.

P6 remains not started. PC-B catalogue recovery and all production editorial
changes remain outside this package.
