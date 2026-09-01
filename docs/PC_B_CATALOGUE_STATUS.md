# PC-B — Catalogue Completion & Editorial Reconciliation Status

PC-B remains broader Product Completion work. This record closes only
**SYSC-CATALOGUE-DATEPLACE-01 — Reconcile Missing Date/Place Metadata in
څپو کې انځورونه**.

## Date/place reconciliation — 2026-09-01

- Authoritative manifest:
  `storage/app/source/collections/tsapo-ke-anzorona/import-manifest.json`.
- Authoritative source checksum matched the private source PDF recorded by the
  manifest.
- Matching used the original deterministic collection plus continuous manifest
  sequence / production `sort_order` identity. Exact title and body hashes were
  refusal guards, never fuzzy matching inputs.
- Source works: 81.
- Source records containing combined date/place metadata: 47.
- Safely restored: 47.
- Already present before reconciliation: 0.
- Conflicts, ambiguities, invalid source values, or missing production records:
  0.
- Verified backup:
  `/home/ajmalaand/backups/poetry/20260901-194656`.
- Production poem count before and after: 81.
- Content version advanced from 3 to 50 through the existing poem-save
  invalidation behavior.
- Post-write dry-run: 47 already present and matching; 0 safe missing values.
- No poem body, title, excerpt, collection, order, publication/access state,
  layout, audio, translation attribution, or other collection changed.

The established schema stores date and place together in one
`source_date_place` field, so the 47 updates are reported as combined
date/place values rather than inferred separate date and place counts.

## Remaining PC-B catalogue work

- select the intended Version 1 catalogue;
- address remaining source-blocked original collections only from trustworthy
  sources or approved transcription;
- decide the treatment of `سيند په پرخه کې`;
- confirm or revise the current 15 free-sample decisions;
- complete missing covers, front matter, and remaining metadata;
- preserve and verify truthful translation attribution; and
- verify the off-server source archive.

This reconciliation does not mark PC or PC-B complete. P6 remains not started.
