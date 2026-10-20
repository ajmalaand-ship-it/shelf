# Preparing and publishing a book

Use Shelf Admin → Library. Book and author records are managed by the owner;
authors do not need accounts. No app rebuild is needed for catalogue updates.

1. In **Authors**, add the person’s exact name and optional biography/image. Use
   the actual credited author; add a translator separately when needed.
2. In **Books → Create**, save a **Draft** with title, language, poetry/prose type,
   credits, description, categories, cover and approved USD reference price.
   Use only material with confirmed rights. Optional front matter stays exact.
3. Open its **Content** tab. Add individual poems/chapters, or **Import Word**.
   Review the import preview, warnings and omissions before confirming. Original
   source and uncertain text must never be guessed or repaired.
4. Mark reader-visible items and choose **Full item** or **First part only** for
   the free sample. Partial samples use the selected number of lines/paragraphs.
   Hidden items are excluded from the public sample.
5. **Preview book** shows full text and the exact **Free sample**, privately.
   It includes hidden-item labels, escaped source text and ten items per page.
   This is content verification; it does not certify layout on either phone.
6. Use **Ready for review** while preparing. In Books, **Publish** validates
   language, author, stored cover, visible content, sample, reference price and
   permanent product mapping. Confirm rights/credits before publishing.
7. **Published** makes the book visible through the shared catalogue API on
   Android and iPhone. **Withdraw** removes discovery/new sales while preserving
   prior-buyer access and all history. Stop new availability in both stores too.
   **Unpublish** returns to Draft; use Withdraw for previously sold books.

## Catalogue readiness and store purchases are separate

The permanent ID is generated after first save (shelf_book_ID), shown in Advanced,
and never follows title edits. Admin saving/publishing does not create or approve
products, change store prices or activate real checkout. Store checkout uses the
store’s localized price, not the Admin reference value. Google’s first-100-books
manual Console policy and deferred automatic sync remain in force.

For paid availability, configure that exact per-book product and entitlement in
Google Play/App Store Connect/RevenueCat and obtain the required store approval
and regional availability separately. A product ID or Published badge is **not**
approval. Real checkout remains OFF. The app requires a matching store product;
server consent, reader eligibility and independent purchase evidence still govern
access. No product/approval/availability was created or changed by this batch.

**Current essential iPhone limit:** installed shared purchase code accepts only
shelf_book_3 through shelf_book_8; the Apple server verifier uses the same six-book
allowlist. Future books can be independently added and prepared for both catalogues,
but cannot yet be sold on iPhone without a separately authorized purchase-code
update and build. This batch changes no mobile source or store configuration.

## Reused evidence

BookFoundationTest covers stable IDs, hundreds-scale pagination, filters, ordering,
bins, credits and content preservation. BookContentAdminTest covers per-book content
isolation and editing. WordImportTest covers preview/confirmation, exact source and
failure cleanup. PublicationAccessTest covers guards, status history, private drafts,
protected preview and paid/sample access. AuthorsAndLanguagesTest and BookstoreTest
cover author/language/catalogue discovery. AdminBookPreviewTest adds owner-only
preview, exact sample boundaries, escaping and pagination. Tests use isolated data;
no live draft or published book is added for verification.
