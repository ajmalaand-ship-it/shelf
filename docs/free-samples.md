# Free samples and retirement of global access

Owner approved Tasks 8 + 9, 28 September 2026.

## Owner controls

In a book's Content tab, edit any Poem or Chapter and choose Free sample: No sample,
Full item, or First part only. For a first part, choose Lines or Paragraphs and a
positive count. Lines end at a source line break (CRLF counts once); paragraphs
are blocks separated by blank lines. The count must leave nonempty text for the
full book. Words, Unicode, spacing and internal line endings are never rewritten.
The source body and old excerpt are retained unchanged. Public excerpt text is
always derived from the approved sample, never the old excerpt.

The Content list has a sample column and filter; bulk actions approve full items
or remove samples. The book page summarizes visible full/partial samples and
hidden sample items. Publishing requires a visible, nonempty sample in addition
to the existing author/language/cover/visible-item checks. Nothing is published
or selected as a sample automatically.

## Public access

Full-item samples return their complete source body. Partial samples return the
exact approved prefix as body, with sample_mode=partial and has_more=true. An
item with no sample returns body=null and excerpt=null. The public summary never
contains a body; its excerpt is at most 240 characters from approved sample text.
Full-book/source footer notes are withheld unless the whole item is approved.
Hidden items and unpublished books remain 404 regardless of sample selection.

Only full-item samples may expose original audio/artwork, with signed URLs and a
fresh visibility/sample check on every request. Whole recordings and artwork for
partial samples remain private because they can reveal the unapproved remainder.
Previously valid paid URLs are denied. Owner-preview routes continue to return
full source and signed media under the existing owner-preview protection.

The old RevenueCat service is inert: no provider requests, cache reads, identity
headers or global entitlements grant access. BookAccessService is a fail-closed
placeholder for verified per-book purchases in Step 4. No purchase records or
money records are modified. The app has no purchase/restore screen or old product
IDs, and no RevenueCat SDK dependency. Its compatibility controller never contacts
a provider and cannot become entitled. It labels Sample and the remaining full
book, with a neutral Coming soon state. Cached audio needs fresh server permission;
public text continues to require server permission. Owner preview is separate.

## Migration, checks and undo

`2026_09_28_190000_reset_legacy_samples.php` saves every old free flag, including
hidden and binned items, in legacy_sample_flags; it adds the new sample fields and
sets all old flags false/new modes none. Body, excerpt, identity and order remain
unchanged. Down restores old flags for the recorded item IDs and removes the new
fields/table. Back up first; rollback also requires the previous code and would
restore the retired access behavior, so it is never automatic.

The approved live run is backup → down → optimize:clear → migrate --force → up →
`shelf:check-samples --after-reset`. If anything fails after down, up runs before
stopping. The check prints counts and sanitized HTTPS PASS/FAIL results; no source
text, secrets or signed URLs are printed. It tries forged legacy headers, old
unlock paths and valid old paid media signatures. All six current books are Draft,
so live checks cannot demonstrate the published sample-only branch; isolated
request tests cover that branch without publishing real books for testing.
