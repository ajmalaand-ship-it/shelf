# P4 Share / Download Poem Cards

Ajmal Aand explicitly authorized P4 on August 29, 2026, after the P3 real-device gate passed. The technical build is implemented; owner real-device share-card acceptance remains required. P5 payments, P6 store submission, P7 iOS, accounts, social features, video, and AI voice have not started.

## Reader workflow and text integrity

The poem reader has a restrained secondary Share/Download action. The card screen offers two scopes: 1–4 contiguous non-empty lines, or all text currently accessible to the reader. Tapping outside the adjacent selection starts a new contiguous selection rather than creating disjoint excerpts. Selected text is copied exactly from the loaded poem with authored Unicode, punctuation, and line boundaries intact. Untitled poems receive no invented heading.

Locked poems never gain access to hidden text: their full-text option is explicitly labelled as the available excerpt and uses only `readableText`, which is the API-provided excerpt. Translation cards preserve both original-poet and Pashto-translation credits. Debug synthetic poems carry an explicit synthetic author label rather than being represented as Ajmal's authored poetry.

## Pagination and rendering

`ShareCardPaginator` is deterministic and independent from PNG encoding. It measures explicit RTL Noto Nastaliq Urdu at the fixed content width/height, keeps a short stanza together where it fits, otherwise splits at line boundaries, and splits an individually oversized line only at word boundaries. Every non-empty input segment appears in order exactly once. Multi-card results carry `1/n` numbering.

`PoemCardWidget` is a fixed 360×450 logical composition. `ShareCardRenderer` captures one `RepaintBoundary` page at a time with pixel ratio 3, producing 1080×1350 PNGs while disposing each decoded image before the next page. It never builds an enormous full-poem bitmap. Rendering and output failures are caught, partial output is removed, a Pashto error is shown, and the user can retry.

## Designs and branding

The three local, copyright-independent designs are warm parchment with a restrained inset border, deep charcoal with a quiet circular motif, and pale neutral with an abstract ink-wave line. All use Nastaliq poem text, explicit RTL, generous line height, safe margins, and the existing bundled font. Each card includes the `پېڅوَل` wordmark, `Pitswal`, truthful author/translation attribution, collection title when available, and a subtle rotated identity watermark. No stock imagery, collection-cover reuse, or user graphic editor is involved.

## Sharing, saving, and temporary files

`share_plus` opens Android's share sheet with one PNG or all numbered PNGs. Safe generated filenames contain only the app prefix, numeric poem ID, UTC timestamp, and page number. Share staging files stay in an app-controlled temporary directory so receiving applications can read them, then age out on the next operation after 24 hours. `gal` saves explicitly requested files to the gallery; save staging files are removed after insertion, while gallery copies are never deleted.

Modern Android uses scoped media saving. The only added permission is `WRITE_EXTERNAL_STORAGE` capped to API 28 and below for legacy gallery behavior. There is no `MANAGE_EXTERNAL_STORAGE`, read-media, contacts, camera, microphone, or location permission, and no analytics or tracking SDK.

## QA and phase boundary

Existing compile-time debug fixtures cover titled, untitled, long, free-verse, translation, locked excerpt, special Pashto letters, and diacritics without publishing production content. Automated tests cover selection integrity, pagination, stanza handling, long/single-line behavior, attribution, locked safety, all three designs, dimensions, filenames, temporary cleanup, error recovery, and release fixture isolation. Representative non-committed PNGs are generated under `mobile/build/p4-qa-artifacts/` for local inspection. Real-device verification of Android sharing, gallery insertion, and low-end rendering remains the P4 gate.
