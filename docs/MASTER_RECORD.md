# Shelf Master Record

The single governing document for building Shelf

Version 2.3 — Approved — 28 September 2026

Owner: Ajmal Aand   •   Prepared with Claude

**How to use this document**

This is the **only** document that governs Shelf. It replaces every earlier Shelf document, including the Constitution v1.0, its drafts, Amendment 1, and Master Record v2.0, v2.1 and v2.2. Nothing in those earlier files applies unless it is written here.

Only the **newest version** of this document counts. Every version has a number and a date on each page.

Before writing any instruction or prompt, Claude checks it against this document. If a change to this document is needed, Claude explains it plainly and Ajmal approves it first.

Codex (the coding assistant on the server) follows **AGENTS.md** in the Shelf code. AGENTS.md is the short summary of this document for coding agents and must always agree with it.

**Status of this version**

Master Record v2.0 was approved on 25 September 2026 and amended in v2.1 on 27 September 2026. This version 2.2 restored every v2.0 requirement and clarified R3 and staging. This version 2.3 (28 September 2026) records the new way of working approved by Ajmal (Codex runs server commands after Ajmal’s approval; Claude makes technical decisions; Ajmal is asked owner questions only) and closes **Step 2**. Current construction: **Step 4 — Accounts and purchases (test mode)**; the current status reconciliation in Part 10 supersedes the historical 28 September snapshot. Outstanding Step 3 acceptance and pre-release checks are kept separate.

**Contents**

Part 1 — About this document

Part 2 — What Shelf is

Part 3 — Where we start and where Shelf lives

Part 4 — Rules we never break

Part 5 — How we work together

Part 6 — What Shelf must do (product requirements)

Part 7 — The steps from today to public release

Part 8 — When Version 1 is done

Part 9 — Decisions (made and open)

Part 10 — Current status

Part 11 — Decision log

Appendix A — History of the existing poetry app

Appendix B — Plain-language glossary

# Part 1 — About this document

## 1.1 Purpose

This document keeps Ajmal and Claude on the same track. It says what Shelf is, where it lives, which rules always apply, how we work together, what must be built, the order of the work, what is still undecided, and where we are today.

## 1.2 One governing document

There is one governing document: this one. AGENTS.md in the code is its summary for Codex. Other files produced during the work (scripts, command outputs, screenshots, test results) are **working files**, not rules. When a working file shows something important, it is written into Part 10 or Part 11.

## 1.3 Versions

- **Status update** (for example 2.2): Part 10 (status) and Part 11 (log) change after approved work. No new rules. Issued at milestones, not after every small step.
- **Rule change** (amendment): any change to Parts 2 to 9. Claude proposes it in plain language; Ajmal approves it; it is recorded in the Decision log with the date, and AGENTS.md is updated to match.
- Each new version lists what changed at the top of Part 10.
- When a new version is issued, older versions no longer apply. Claude always names the one final file.

## 1.4 Approval

Ajmal is the only person who approves this document and its changes. Approval is given in writing in the chat and recorded in the Decision log.

## 1.5 Language

This document gives full detail so that future work can be guided by it. Claude’s chat messages are short, plain and useful: where we are, what to do next, and exactly where to do it.

# Part 2 — What Shelf is

## 2.1 The product

Shelf is a free-download Android app that works as a **Pashto and Farsi digital bookstore and personal library**. Readers discover books from many authors, read a free sample, buy an individual book, and then find it in **My Library**. Downloading the app is free; this does not mean every book is free.

| Item           | Decided                                                        |
|----------------|----------------------------------------------------------------|
| App name       | Shelf                                                          |
| Slogan         | کتاب مو ژوند بدلوي |
| Android app ID | services.shelf.app (permanent once published)                  |
| Domain         | shelf.services                                                 |

## 2.2 A managed catalogue for unlimited books

Ajmal and Shelf find, prepare and publish the books. Authors do not log in, upload or manage anything in Version 1. Ajmal controls what is published and at what price. The foundation must support **unlimited authors and books** added through the admin panel; the books that exist today are the first examples, not the goal.

## 2.3 Not in Version 1

The following are outside Version 1 unless Ajmal later approves a change:

- subscriptions or an all-books pass;
- author accounts, author uploads, author dashboards or an author marketplace;
- automatic payouts to authors (Version 1 keeps accounting records only);
- an iPhone (iOS) app — Version 1 is Android.

## 2.4 Shelf is its own product

Shelf is not part of the Ajmal Aand Professional Platform, System C, Career Command or the Private Assistant. Their documents, roadmaps and approvals do not govern Shelf. The old poetry app (Pitswal) is Shelf’s **technical starting point**, but its name, identity, old roadmap and old approvals do not carry over.

# Part 3 — Where we start and where Shelf lives

## 3.1 Shelf is built by changing existing work

Shelf is **not built from zero**. It is built from the existing poetry app (Laravel and Filament backend with an owner admin panel, and a Flutter Android reader), because the project’s name and purpose changed:

| The old poetry app (Pitswal)                 | Shelf                             |
|----------------------------------------------|-----------------------------------|
| One poet’s poetry collections                | Many authors and many books       |
| One purchase unlocks everything (unlock-all) | Each book is bought separately    |
| Poetry app name, com.hindara.pitswal         | Shelf, services.shelf.app         |
| Account ajmalaand, poetry.ajmalaand.com      | Own account shelf, shelf.services |

## 3.2 Where Shelf lives (amendment of 27 September 2026)

- Shelf has its **own cPanel account** on the server, named **shelf**, with its own files, database (shelf_app), database user, settings file, SSL certificate, backups and Codex login.
- Code: /home/shelf/apps/shelf. Website folder: /home/shelf/public_html, which points to the app’s public folder.
- Domain: **shelf.services**, with HTTPS.
- Code is kept in the private GitHub repository ajmalaand-ship-it/shelf.
- The app reads the server address from a setting (SHELF_API_BASE_URL), so a future address change does not change books, accounts or purchases.

## 3.3 The old poetry app

The old poetry app in the **ajmalaand** account (poetry.ajmalaand.com) is **not Shelf**. It stays untouched and will be retired. Its exact state on 25 September 2026 is kept in the git tag **pitswal-baseline-2026-09-25** and in the verified baseline backup (also copied to Ajmal’s PC). Codex never reads from, writes to or changes it.

## 3.4 What already exists (verified 25 September 2026)

Inspection confirmed a strong base: Laravel 12, Filament 5, protected owner preview, server-side access checks, signed media links, Pashto reader, fonts, audio and share cards. Missing for Shelf at that time: authors, languages, reader accounts, per-book purchases and purchase records. Details are in Appendix A.

## 3.5 The current catalogue

Six books, 342 works, all in Pashto:

| Book                                                                       | Credits                                                                                                                           |
|----------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------|
| څپو کې انځورونه                | Author: اجمل اند                                                                      |
| هېندارې او چینې                | Author: اجمل اند                                                                      |
| د زړه پر پاڼه مې انځور دی ګلاب | Author: اجمل اند                                                                      |
| دا ښار، هاغه غرونه             | Author: اجمل اند                                                                      |
| دلته ډېر لرې له غرونو          | Author: اجمل اند                                                                      |
| سيند په پرخه کې                | Author: پروین پژواک; translator: اجمل اند |

Each old collection is one Shelf book. پروین پژواک is the author of سيند په پرخه کې only. Inside دلته ډېر لرې له غرونو, the poem لمر ګلی is recorded as Ajmal’s translation of an unknown poet; it is left unchanged until Ajmal supplies the original author.

## 3.6 What still has to change

- Access protection verified with direct requests (task 6–7 check; see 6.10).
- Publication states instead of an on/off switch (Step 2, task 6).
- Owner-only admin access before reader accounts exist (task 7).
- An admin-chosen free sample for each book (task 8).
- The old unlock-all purchase disabled; later, one purchase per book (task 9, then Step 4).
- Reader accounts, My Library, sales and author-share records (Step 4).
- Reader wording, fonts and layout checked again for Shelf on a real phone (Step 3).

# Part 4 — Rules we never break

These rules apply in every step. They can only be changed by an approved amendment.

**R1 — Ajmal decides.** No change to the server, data, publication, prices, purchase setup or release happens without Ajmal’s clear OK for that task.

**R2 — Baseline kept.** The baseline of the old app (git tag pitswal-baseline-2026-09-25 and its backup) is never deleted or overwritten.

**R3 — Database changes.** **Migrations** are required for (a) any change to the database structure and (b) any bulk or scripted change to existing data (for example correcting credits for many books). They are Laravel migrations kept in git, with a working reverse step where possible, and are applied only after a fresh verified backup in the same run. Codex runs them after Ajmal approves the command in Codex (5.2). **Normal owner admin work** — adding or editing books, authors, credits, categories and content, and importing content through the admin — does not need migrations; it is protected by the nightly backup, the bin (restore), and the record of who changed what and when. The live database is never edited by hand outside these two routes.

**R4 — Everything in git.** All code changes are committed with a clear message and pushed to GitHub. No force-push, no history rewriting.

**R5 — Secrets stay secret.** Passwords, keys and tokens never go into code, the app package, this document, the chat, screenshots or logs. Ajmal types secrets himself. Old Pitswal tokens and store settings are never reused.

**R6 — Source text is protected.** Pashto and Farsi text is never silently repaired, guessed or rebuilt with OCR or AI. Uncertain text is marked and held. Original source files are never modified.

**R7 — The server decides access.** Paid text, audio and artwork go only to readers who own that specific book. A locked screen in the app is not protection by itself.

**R8 — Money records are never rewritten.** Sales, refunds and author-share records are kept as history. Corrections are new entries.

**R9 — Rights and credits before publishing.** No book, cover, image, font or audio is published without known rights and correct credit to author and translator. Credits are never guessed.

**R10 — No whole-catalogue unlock.** The old unlock-all purchase must never give access to Shelf books. Every Shelf purchase is for one book.

**R11 — Proof before “done”.** Nothing is finished without evidence. Reading and layout are accepted only after testing on a real Android phone.

**R12 — No silent decisions.** If something is not covered by this document, Claude and Codex ask Ajmal instead of choosing.

**R13 — Real people’s data.** Once real readers or purchases exist, they are not changed, moved or deleted without an approved plan.

**R14 — Honest reporting.** Failures, risks and unknowns are reported plainly. If something goes wrong, work stops and the problem is reported before any fix.

**R15 — Old app untouched.** Nothing in the ajmalaand account or poetry.ajmalaand.com is changed as part of Shelf work.

# Part 5 — How we work together

## 5.1 Roles

| Ajmal (owner)                                                                                                                                      | Claude (planner)                                                                                                    | Codex (builder on the server)                                                                                          |
|----------------------------------------------------------------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------------------------|
| Makes the owner decisions: books, rights and credits, prices and money, what readers see and can do, publishing and launch. Approves rule changes. | Plans each task, checks it against this document, writes Codex prompts and commands, reviews results.               | Writes and tests code in the shelf account, following AGENTS.md; commits and pushes to GitHub.                         |
| Approves Codex’s server commands when Codex asks; tests in the admin and on the phone.                                                             | Makes the technical decisions, following this document; explains in plain language; keeps this document up to date. | Runs server commands itself (backup, migrations, file moves, checks) after Ajmal approves; stops at the first failure. |
| Owns accounts: server, GitHub, Google Play, RevenueCat, domain.                                                                                    | Warns about risks and asks when something is unclear.                                                               | Stops and reports on any failure.                                                                                      |
| Checks books, text and layout as the Pashto/Farsi expert.                                                                                          |                                                                                                                     |                                                                                                                        |

## 5.2 The work cycle (every task)

- **1. Check** — Claude checks the task against this Master Record. If an amendment is needed, it is discussed and approved first.
- **2. Plan** — Claude writes the task; technical choices are Claude’s, owner questions go to Ajmal in plain words.
- **3. Build** — Codex writes the code and runs the automated tests (temporary database only), then commits and pushes.
- **4. Apply** — Codex runs the live steps itself: backup, then migrate or move files, then a check. It asks for approval in its own window; Ajmal’s approval is the OK (R1). If anything fails, Codex stops, restores the site if it was put in maintenance, and reports.
- **5. Verify** — the result is checked on the server, in the admin panel, and on a real phone where relevant.
- **6. Record** — decisions go into AGENTS.md section 8 at once, and into this document at the next milestone.

## 5.3 How Claude gives instructions

- One step at a time.
- Every reply starts with one line showing where we are, for example: *Step 2 · Task 5 of 9 · Books*.
- When Ajmal must type anything himself, every block says exactly **where** to run it and includes every line from the start: PC PowerShell; server as root (ssh root@157.250.199.106); server as shelf (then su - shelf); or inside Codex.
- Commands and prompts are given in the chat, ready to copy, not as attached files.
- Plain, short language. Few files, and the final one is always named.
- Claude makes all **technical** decisions (with Codex), following this document, and records them. Ajmal is asked only **owner questions** — books, rights and credits, prices and money, what readers see and can do, publishing and launch — in plain words, without technical choices. Anything new of that kind is explained first.
- Tasks are written the way that gives the best result, even if they take longer.

## 5.4 Commands

- One block of commands at a time, never a long mixed list.
- Each block is labelled **READ-ONLY** (looks, changes nothing) or **CHANGES** (changes something), and says what it does.
- Any CHANGES block says which backup was made first and how to undo it.
- Ajmal sends back the output. Before sending, Ajmal removes anything private.

## 5.5 Codex report format

Codex ends every task with: **Done:** / **Tested:** / **Problems:** / **Next:** / **Decision needed:**.

## 5.6 Opening a work session

- Codex: PowerShell → ssh root@157.250.199.106 → su - shelf → cd ~/apps/shelf && codex
- Server commands without Codex: PowerShell → ssh root@157.250.199.106 → su - shelf → cd ~/apps/shelf
- New Claude chat: Ajmal writes “continue Shelf”; Claude reads the latest Master Record and states the current step and next task.

## 5.7 When something goes wrong

- Stop. Do not add more fixes on top of a failure.
- Report exactly what happened, in plain language.
- If needed, restore from the last backup, with Ajmal’s OK.
- Record the problem and its solution.

# Part 6 — What Shelf must do (product requirements)

## 6.1 Authors and books

- An **author** is a catalogue record, not a login: stable ID, display name, optional Latin name, biography, image, active or not.
- A **book** has a stable ID, title, credits, language, category, description, cover, ordered content, publication state, free sample and its own price. Optional audio belongs to the book or a section.
- **Credits**: a book can have several authors, translators and editors, in order. One author can have many books.
- **Language**: each book has a language. Allowed now: Pashto (ps) and Farsi (fa); the list is a setting so more can be added.
- Works inside a book keep their own original author and translator fields, because an anthology can mix authors.
- **Book type**: poetry or prose. Content of a poetry book is shown as *Poems*, of a prose book as *Chapters*.
- **Categories**: created and managed by the owner; a book can belong to several.
- Each book manages **its own content**. The admin has no page that lists content from all books at once.
- Deleted books and content go to a **bin** and can be restored. A book with content cannot be permanently deleted from the admin.
- Each book is linked to its own store product. Changing a title never changes who owns the book. Editing a published book keeps its identity and purchase history.
- Publication states: **Draft**, **Ready for review**, **Published**, **Withdrawn**.
- Withdrawing a book stops new sales and discovery. It never deletes sales records or silently removes the book from readers who bought it (policy D11).

## 6.2 Reader accounts

- Create account, sign in, sign out, recover account, and delete account.
- Browsing and free samples need no account. Buying and My Library need an account.
- The app shows which account receives a purchase. Switching accounts never shows another reader’s data or downloads.
- Reader accounts can never enter the owner admin panel.
- Sign-in methods are an open decision (D7). Old poetry-app accounts are never linked by guessing names or emails.

## 6.3 Free samples

- For each book, the admin chooses which part is free: whole poems/chapters, or only the first part of one (the owner sets how many paragraphs or lines). There is no fixed amount or percentage. Old free markings from the poetry project were cleared.
- A book cannot be published for sale without a sample.
- The reader sees clearly that it is a sample and how to buy the full book.
- Unpaid requests never receive paid text or audio, through the app, links, downloads or caches.

## 6.4 Buying a book

**Owner amendment — 7 October 2026 (supersedes prior admin-only D9):**
For Shelf’s first 100 books, the owner creates and manages Google Play products,
prices and availability manually in Play Console. Automatic admin-to-Play
product/price synchronization is **DEFERRED, NOT COMPLETED** and stays disabled.
Stop 403 investigations, calculations, retries and support follow-ups unless the
owner explicitly reopens automatic sync. Existing product mappings, credentials,
app-level access, verification, acknowledgements, entitlements, refunds,
accounting and historical records are preserved. Admin USD prices are approved
reference values; saving does not update Play. Checkout uses Google’s localized
store price. No Play product creation/change is authorized in this task.

- Each book has its own price and its own store product. No subscription in Version 1.
- Payments use Shelf’s own RevenueCat project and Shelf’s own Google Play products. Current provider requirements are checked before setup.
- A purchase counts only after the payment provider confirms it to the server. A success screen in the app alone never unlocks a book.
- Pending, cancelled and failed payments are explained clearly and never unlock the book early.
- Repeated notifications or retries never create a double purchase or double author earnings.
- **Restore**: after reinstalling or changing phone, a reader gets back the books they bought, after the store purchase and the correct Shelf account are checked. A purchase is never silently moved to another reader.
- Refunds and revoked purchases update access and accounting together. Payment history is kept.
- All payment testing starts in sandbox mode. Any real-money test needs Ajmal’s approval.

## 6.5 My Library and offline reading

- My Library lists the books the signed-in reader owns, with a clear way to open each.
- Verified purchases appear automatically, without manual work by Ajmal.
- Owned books, samples and downloaded books are clearly different.
- The server decides ownership. A download is only a local copy: deleting it or reinstalling the app never loses a purchased book.
- Before release, decide (D13): which content can be offline, how long paid books work offline without checking, what happens on sign-out or refund, and how readers clear storage. Files on a phone can never be fully protected from copying.

## 6.6 Pashto and Farsi reading

- Both languages are first-class: correct right-to-left layout, suitable fonts, readable spacing, correct Unicode.
- Mixed English text, numbers and punctuation stay readable.
- Font and text-size controls remember the reader’s choice.
- Poetry and prose are tested on real phones. Source layout is preserved; poetry is not automatically restructured.
- Search works by book, author, language and category, and never changes the original text.
- Interface language decided before release (D15).

## 6.7 Audio

- Optional per book or section.
- Paid audio is protected like paid text.
- Audio rights are checked before publishing.

## 6.8 Owner admin (Filament)

- Only the owner can enter the admin panel (owner flag). Login is rate-limited; an authenticator-app code (two-step login) is available and recommended.
- Workflow: create authors, add book, add credits and language, enter or import content, add cover and optional audio, choose sample, set price and store product, preview, publish.
- Protected owner preview of the full book and its sample before publishing.
- Publishing is blocked when required parts are missing or broken (at least one author, a language, a sample, media, a store product for paid books).
- Editing a published book keeps its identity and purchase history.
- Ajmal can correct and withdraw books, see a reader’s purchases and access, handle support cases, and see sales and author-share records.
- Important changes record who made them and when. Internal notes and money information never appear in public responses.

## 6.9 Sales and author-share records

- Each verified sale records: book, store transaction, time, currency, amount and status. The link to the reader is kept private.
- Refunds and adjustments are separate, traceable entries.
- Store fees, taxes and payout figures are marked **unknown** or **provisional** until real data exists.
- Each book has an internal agreement record with its author or rights holder: the percentage, whether it is of **gross or net** income, **which deductions** count, the **start date**, and **how several contributors share**. No standard percentage is invented.
- Changing a percentage never changes past earnings; historical sales keep the agreement and calculation basis that applied at the time.
- Three separate figures: estimated earnings, confirmed amounts owed, and payments actually made (with date and reference).
- Totals are kept separate per currency unless an exchange rate and its date are recorded.

## 6.10 Security and privacy

- Encrypted connections (HTTPS), safe password storage, request limits and strong login for the owner admin.
- Every protected request checks that this reader owns this specific book. Tests include **direct requests** and attempts to reach another reader’s data — not only the visible screens.
- Draft, private and unpublished content, and private files, are never reachable by public requests.
- Access protection is a foundation priority: its actual state is checked with evidence, never assumed.
- Collect only the personal data Shelf needs. Clear privacy, support and account-deletion information before launch.
- Decide how long sales, security and deleted-account records are kept (D14), and explain what deletion means for restoring purchases.

## 6.11 Testing, backups and recovery

- **Staging boundary** (approved 28 September 2026): until then, there is no staging or separate test copy and development happens directly in the shelf account. A staging copy **must exist before the first real reader other than Ajmal, or the first real payment, whichever comes first** — that is, inside Step 5, before any public or closed testing on Google Play. After that point, changes are made and tested on staging first.
- Automated tests stay. They always use an in-memory or temporary database, never the real one, and run through **scripts/run_tests.sh**.
- Android (Flutter) tests run only when the app code (mobile/) changed in that task.
- Shelf never writes to the shared server folder /tmp; all temporary files go to /home/shelf/tmp and are cleaned up.
- A verified backup runs every night at 03:00 in the shelf account and keeps the 14 newest; a backup is also made before every migration.
- Backups are access-controlled and **tested by restoring**; a backup job that ran is not proof that recovery works.
- Before launch, a regular copy off the server is added and a restore is tested.

## 6.12 Word import

- Inside a book’s Content tab, the owner can upload a Word file (.docx). It works for every kind of book.
- A paragraph in style “Heading 1” starts a new item (its text is the title); a line with only \*\*\* starts a new untitled item; text before the first split is an untitled first item.
- Text is kept exactly: same words and characters, paragraph and line breaks, right-to-left text untouched. No correction or normalization (R6).
- A **preview** shows every item before anything is saved. Anything that would be **left out** (images, tables, footnotes, comments, tracked changes) is listed clearly, and the owner must tick an acknowledgement before importing such a file.
- Import only **adds** items to the end of the book; it never replaces or deletes content. A failed import leaves **no partial content**, and a double click never creates duplicates.
- The original file is kept privately with its checksum, with a record of who imported it and when.
- The feature is fully accepted only after a real Pashto Word document is imported and read on a real phone.

# Part 7 — The steps from today to public release

Work happens one step at a time. A step closes only when its evidence exists **and** Ajmal accepts it.

## Step 0 — Approve the Master Record ✓ done 25 September 2026

**Goal:** Agree the rules and plan before any change.

**Work:**

- Ajmal approved Master Record v2.0.

**The step is finished when:**

- Approval recorded.

**Not allowed in this step:** changes to the app.

## Step 1 — Look at the current app and protect it ✓ done 25 September 2026

**Goal:** Know exactly what exists and protect it.

**Work:**

- Read-only inspection of the old app.
- Verified baseline backup, copied to Ajmal’s PC.
- Code pushed to private GitHub with the baseline tag.
- Old Pitswal rule documents replaced by Shelf’s AGENTS.md.

**The step is finished when:**

- Inspection, baseline and GitHub copy verified.

**Not allowed in this step:** feature work.

## Step 2 — Build the Shelf foundation ✓ done 28 September 2026

**Goal:** The app becomes Shelf, with a foundation for unlimited authors and books.

**Work:**

- 1\. Daily automatic backups.
- 2\. Move to the shelf account and shelf.services (code, data, domain, SSL, backups).
- 3\. Rename to Shelf; app ID services.shelf.app; server address as a setting; name and slogan.
- 4\. Authors, credits and languages.
- 5\. Book foundation: the admin can add and manage unlimited books easily; each book manages its own content.
- 5b. Word import (6.12).
- 6\. Publication states (Draft / Ready / Published / Withdrawn).
- 7\. Owner-only admin access.
- 8\. Admin-chosen free sample for each book.
- 9\. Old unlock-all payment disabled.

**The step is finished when:**

- All 9 tasks pass their automated tests and owner checks.
- The admin panel and API show correct books, credits and states.
- Every change is in git with a backup before each database change.

**Not allowed in this step:** real payments, public release.

## Step 3 — Catalogue and reading (in progress)

**Goal:** A reader can browse, search and read samples properly in Pashto and Farsi on a real phone.

**Work:**

- Android SDK set up so test builds can be installed on Ajmal’s phone.
- Word import accepted with a real Pashto Word document read on the phone (6.12).
- Catalogue: authors, categories, search, book pages.
- Samples and the sample/full-book difference.
- Reader: right-to-left layout, fonts, text size, poetry and prose, mixed text.
- Optional audio and agreed offline behaviour.
- Owner preview and publishing checks in the admin.

**The step is finished when:**

- Ajmal tests several authors and books on a real phone and accepts reading quality.

**Not allowed in this step:** real payments, public release.

## Step 4 — Accounts and purchases (test mode)

**Goal:** A reader creates an account, buys a single book in test mode, and finds it in My Library.

**Work:**

- Reader accounts (D7).
- Shelf’s own RevenueCat project and Google Play test products, one per book.
- Server-side confirmation, My Library, restore, failed payments, refunds.
- Sales and author-share records.

**The step is finished when:**

- All purchase cases work in sandbox, including restore on another phone and a refund.
- Access protection is tested with direct requests.
- Decisions D8–D12 made.

**Not allowed in this step:** real-money sales, public release.

## Step 5 — Ready for release

**Goal:** Everything for a safe public launch is in place.

**Work:**

- Production Google Play listing and RevenueCat setup.
- Privacy policy, support, account deletion, retention (D14).
- Staging copy in place before the first real reader other than Ajmal or the first real payment (6.11).
- Off-server backup and a tested restore; simple operating instructions for Ajmal.
- Final launch catalogue (D16).
- Old poetry app retired.

**The step is finished when:**

- Ajmal tests the real app and admin end to end and accepts them.

**Not allowed in this step:** public release without Ajmal’s approval.

## Step 6 — Public release

**Goal:** Shelf is live on Google Play.

**Work:**

- Release after Ajmal’s approval.
- Check a live purchase and support, as approved.
- Watch for failures in the first days; keep a recovery path ready.

**The step is finished when:**

- Shelf is live and real purchases and restores work.

**Not allowed in this step:** deleting the old app’s baseline.

# Part 8 — When Version 1 is done

- The app is fully Shelf: name, app ID services.shelf.app, domain shelf.services, own RevenueCat project and store products.
- The multi-author catalogue works; each book for sale has a price and a working sample.
- Accounts, single-book purchases, My Library and restore work, including refunds and failed payments.
- Pashto and Farsi reading accepted on a real phone; optional audio works where supplied.
- The admin is usable and owner-only; sales and author-share records are traceable.
- Access protection verified with direct requests; offline and deletion rules agreed and working.
- Backups on and off the server, with a tested restore.
- No critical defects or open release-blocking decisions.
- Ajmal accepts the real Android experience and authorises launch.

# Part 9 — Decisions (made and open)

| ID  | Decision                              | Status                                                                                                                                                                                          |
|-----|---------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| D1  | Permanent domain and hosting          | **Decided 27 Sep:** shelf.services, own cPanel account “shelf”.                                                                                                                                 |
| D2  | Separate test copy during development | **Decided 27 Sep, clarified 28 Sep:** no staging until the first real reader other than Ajmal or the first real payment (6.11); tests use a temporary database.                                 |
| D3  | Old poetry product                    | **Decided 27 Sep:** left untouched, then retired; baseline kept.                                                                                                                                |
| D4  | Old collections to books              | **Decided 27 Sep:** each collection is one book; priority is the foundation for unlimited books.                                                                                                |
| D5  | Translated book included              | **Decided 27 Sep:** credits recorded (author پروین پژواک, translator اجمل اند). Publication follows R9. |
| D6  | App name and Android ID               | **Decided 27 Sep:** Shelf, services.shelf.app; slogan کتاب مو ژوند بدلوي.                                                                           |
| D7  | Reader sign-in methods                | Open — Step 4                                                                                                                                                                                   |
| D8  | Payment channels and territories      | Open — Step 4                                                                                                                                                                                   |
| D9  | Prices and currencies                 | **Amended 7 Oct:** manual Play Console products/prices for first 100 books; automatic sync DEFERRED, not completed. USD 2.99 test approval retained for books 3–8.                                                                                                                                                                                   |
| D10 | Author and rights-holder agreements   | Decided 1 October 2026: owner sets per-book shares in admin; all six current books 100% net received to اجمل اند. See the addendum for book 6 permission and immutable agreement versions. |
| D11 | Withdrawn books already bought        | Open — Step 4                                                                                                                                                                                   |
| D12 | Refund handling                       | Open — Step 4                                                                                                                                                                                   |
| D13 | Offline limits and storage clearing   | Open — Step 3/4. Current state: browsing the catalogue needs internet, so withdrawn or unpublished books are never shown from old saved data.                                                   |
| D14 | Account deletion and data retention   | Policy approved 7 October; implementation/tested source below; financial expiry unresolved — Step 5                                                                                                                                                                                   |
| D15 | App interface languages and wording   | Open — Step 3                                                                                                                                                                                   |
| D16 | Launch catalogue                      | Open — Step 5                                                                                                                                                                                   |
| D17 | Samples                               | **Decided 27 Sep:** admin chooses each book’s free part; no fixed amount.                                                                                                                       |
| D18 | Content management and import         | **Decided 28 Sep:** each book manages its own content; Word import by Heading 1 and \*\*\* (6.12).                                                                                              |

# Part 10 — Current status

## D14 owner approval — 7 October 2026 (latest)

Approved: confirmed deletion removes profile/access, retaining necessary purchase,
refund and accounting evidence. Recovery is owner-only, support-assisted and
provider-verified, never email matching; refunded/revoked purchases stay locked.
Routine security logs expire after 90 days, with documented/reviewed incident
exceptions. Local backups retain 14 copies; OneDrive retains 90 days, preserving
and reporting the last verified recoverable set when expiry would remove it.
Minimal protected deletion records must survive older database restoration and
server loss; recovery stops before reopening if current records are unavailable.
RevenueCat metadata deletion is retryable while necessary refund/recovery proof
remains. Google's records are outside Shelf's deletion promise. Financial expiry
remains unresolved; no 90-day financial expiry or statutory deadline is approved.
This supersedes the recommendation-only D14 sheet below and historical append-only
OneDrive policy. Implementation is in `docs/D14_DATA_POLICY.md`: encrypted independent OneDrive
journal with verified publication; account deletion/outbox; owner-only recovery
with Google order/token status and original RevenueCat entitlement; immutable
recovery audit/claim; refunds revoke recovered access; scoped dry-run-first expiry
and incident holds; older recovery replays suppression and blocks reopening.
Focused staging checks passed on 45b5cc1: 88 PHP tests / 654 assertions and
23 backup checks (7 D14 plus existing backup regressions); no Flutter/phone suite.
The final metadata-read correction and its additional focused test are committed;
final staging/promotion and live activation evidence follow before completion.
**Provider configuration blocker:** read-only V2 customer-attribute verification
returned HTTP 403 on 7 October. Existing key lacks usable customer metadata read
access. No provider write, grant or credential change occurred. Retryable metadata
cleanup remains pending; owner approval of the existing V2 key's exact
`customer_information:customers:read` permission is needed. This is unrelated to
deferred Play product/price sync; no pricing 403 work was resumed.
No live reader was deleted to test. Financial records/agreements stay unchanged.
RevenueCat whole-customer deletion conflicts with required purchase/refund history;
only metadata is scrubbed/retried, with immutable metadata held for reviewed
resolution. Provider documentation is linked in the D14 runbook. Step 4 and
Step 5 remain open; real sales remain OFF and automatic Play sync DEFERRED.

## Webhook configuration complete; D14 proposals — 7 October 2026 (latest)

Owner saved Both Production and Sandbox on the existing webhook at approximately
21:17 UTC. Read-only verification at **21:19:03 UTC** confirms Shelf/projbbce26da,
Shelf sandbox book purchases/whintgr75a4892970, appf83cd58c5c/services.shelf.app,
existing URL and NON_RENEWING_PURCHASE/CANCELLATION/EXPIRATION filters. Explicit
API environment=null removes the environment filter, consistent with the owner's
Both selection and [RevenueCat's nullable API field](https://www.revenuecat.com/docs/api-v2/integration).
**CONFIGURATION COMPLETE / VERIFIED. Actual production-event delivery UNVERIFIED.**
Authorization is not exposed by the list API; unchanged per owner confirmation,
not independently compared or disclosed. HTTPS production_checkout_enabled=false,
test_mode=true, sandbox_checkout_enabled=true. Receiving notifications does not
enable sales. No purchase/test event, provider write, deployment or test-suite run.
Records-only commit/push; runtime release remains 340634b; handler preserved.

### D14 decision sheet — recommendations ONLY, not approved or implemented

Already approved/built: email-confirmed account deletion removes reader profile,
credentials/tokens and access; immutable transaction/accounting history remains
without reader name/email. A new account does not automatically inherit purchases.
Normal reinstall/second-device restore checks the same Shelf identity. Local daily
backups retain 14 copies; encrypted OneDrive retention is append-only with monthly
review and no automatic expiry. None of these is changed by proposals below.

| Unresolved owner choice | Recommended option (proposal) | Implication requiring approval |
| --- | --- | --- |
| Financial records: what remains and for how long? | Keep the minimal append-only sale/refund/accounting trail and required transaction/reader references; do not schedule financial deletion now. | Retention of references must be disclosed. Owner must establish applicable financial retention requirements before any expiry/anonymization policy; no statutory number is invented and R8 still applies. |
| Security logs: how long? | Propose 90 days for ordinary Shelf-controlled security/diagnostic logs; isolate only evidence needed for an open incident, reviewed at closure. | This is an operational proposal, not a legally required period. Owner chooses the window/incident exception; hosting/provider logs may need separate controls. |
| Deleted profiles and deletion receipts: what survives? | Keep current immediate confirmed profile/token removal; retain a minimal restricted deletion receipt until every older recoverable backup expires. No profile/password copy in the receipt. | Approve the minimal identifiers and receipt lifetime. A durable record and recovery reapplication procedure must be built; current wording alone is not implementation. |
| Books after account deletion: can purchases be recovered? | Offer support-assisted recovery after verifying original purchase and claimant ownership, with explicit approval and an audit trail; never transfer solely because emails match. | This changes the current no-automatic-transfer behavior only if separately approved/built. Choose recovery eligibility and minimal retained proof/link; deletion itself must not be blocked. Until then, existing deletion/access wording applies. |
| Backups: when does deleted data disappear? | Keep approved local 14-copy rotation; propose a 90-day maximum for routine offsite copies, with recovery-safe, owner-approved removal. Reapply deletions before recovered reader access resumes. | Proposed offsite expiry changes approved append-only retention and needs explicit approval, capacity/recovery review and separate safe implementation. Currently offsite copies can persist indefinitely; do not promise 14-copy expiry for all backups. |
| Provider-held data: what does a deletion request cover? | Include RevenueCat account metadata in the documented deletion process while preserving necessary purchase/refund evidence; explain separately what Google Play retains. | Owner chooses the scope/exceptions; inspect provider deletion effects before implementing. Do not promise Shelf erases store orders or break later refunds by deleting provider history. |

[Google Play's deletion guidance](https://support.google.com/googleplay/android-developer/answer/13327111?hl=en)
requires associated-data handling and clear disclosure of justified retained data;
it does not establish a universal financial retention period for Shelf. No D14
policy is approved by this decision sheet. Neither numeric proposal is a legal claim.

### Remaining work, without rebuilding completed features

- **Construction (D14-dependent):** implement approved retention rules for logs/
  expired records and provider metadata; durable deletion receipt plus recovery
  reapplication; chosen post-deletion purchase recovery, if approved; approved
  offsite expiry, if approved; align privacy/deletion/support wording with actual
  behavior. Read-only code search found no deletion journal/reapplication path.
  Existing deletion page promises restricted-backup expiry within 14-copy retention,
  while OneDrive is append-only: correct that wording in an approved policy task.
- **Completed construction to reuse:** accounts/deletion foundation, catalogue/
  samples/reader, verification in both environments, release-mode Android source,
  Library/offline/restore/refunds, append-only owner accounting, staging workflow,
  encrypted offsite backup/recovery and cron-path repair. Automatic Play sync is
  DEFERRED / NOT COMPLETED, excluded from the current construction queue; no 403 work.
- **Launch configuration:** D16 final book list; owner launch prices/regions (USD
  2.99 is TEST ONLY); manual products and exact entitlements for selected books
  (last verified Play book 3 exists, 4–8 absent); listing/Data safety/support/OAuth/
  signing; approved production Android artifact without internal-test checkout
  opt-in; temporary account-level grant removal confirmation; separately approved
  retirement of old app. Webhook environment configuration is now complete.
- **Pre-release/operational checks:** actual production-event delivery and approved
  purchase/refund/acknowledgement acceptance; second-phone restore DEFERRED / NOT
  PASSED; targeted reading/Word import, offline/account isolation/withdrawal and
  final UX acceptance, reusing covered cases; unattended backup-success evidence,
  actual alert delivery and replacement-host recovery/cutover. No new checks here.

Next construction is the approved D14 policy implementation after owner choices;
no newly established independent generic purchase-code gap remains in the current
record. Public launch/real payments still require explicit owner approval. Steps
4 and 5 remain open. This latest entry supersedes pending webhook instructions
below while preserving their historical evidence.

## Production webhook preparation — 7 October 2026 (latest)

Fresh read-only RevenueCat inspection confirms one existing Shelf webhook:
project Shelf/projbbce26da, app appf83cd58c5c/services.shelf.app,
Shelf sandbox book purchases/whintgr75a4892970 at
https://shelf.services/api/purchases/webhook; sandbox environment and exactly
NON_RENEWING_PURCHASE, CANCELLATION, EXPIRATION filters. Only the environment
filter needs an owner dashboard edit to Sandbox and Production / Both. Retain
app scope, URL, Authorization and filters; no duplicate webhook. **PENDING OWNER
ACTION / NOT VERIFIED**, not completed by this task. Short instructions and
source evidence: [production verification](PRODUCTION_PURCHASE_VERIFICATION.md).

Deployed 340634b already authenticates and handles both environments, rejects new
real sales with gate OFF, processes matched prior-real-purchase refunds/revocations
with gate OFF (including deleted-reader history), verifies reader/book/REST receipt,
prevents duplicates and preserves append-only accounting. No local implementation
gap found; existing focused staged evidence reviewed, not rerun. No deployment,
database/history changes, webhook/provider writes, credentials/permissions edit,
Android build/upload or Play product/price work. Real checkout remains disabled.
Documentation-only synchronization leaves authoritative runtime release 340634b;
cPanel handler preserved. Installed Android app unchanged.

**D14 = account deletion and data retention:** owner must choose retention periods
for sales, security and deleted-account records and the deletion/restore identity
policy. These block policy-specific retention/deletion implementation and final
privacy wording, not generic purchase/webhook construction. Existing deletion and
financial history remain unchanged pending decision.
**D16 = final launch catalogue:** owner chooses which books launch. It blocks final
book-specific launch preparation/acceptance, not generic payment construction.
Rights/credits/samples and exact product/entitlement availability must be checked
for those books; launch prices/regions need separate owner approval. USD 2.99 on
books 3–8 is test-only, not approved launch pricing. Last verified Play inventory:
book 3 present, books 4–8 absent; do not assume all six must launch.
Neither D14 nor D16 blocks this preparation; both remain pre-launch requirements.

Next: owner saves the existing webhook edit; bounded read-only confirmation can
then record configuration success. Actual production delivery/real purchase
acceptance needs separate launch approval. Release-capable source exists; Android
build/acceptance, manual launch catalogue, D14 and targeted pre-release checks
remain. Automatic sync DEFERRED / NOT COMPLETED; 403 work stopped. Steps 4/5 open;
second-phone restore DEFERRED / NOT PASSED. No new recovery/alert/grant-removal claim.

## Android release purchase construction — 7 October 2026 (latest)

Owner approved release-mode Android support behind disabled controls. Source now
uses separate server production/sandbox checkout gates and fresh authenticated
reader/book/product/mode consent before Google Play purchase. Default builds stop
before payment while real sales are OFF. Existing owner-test build scripts opt in
with SHELF_INTERNAL_TEST_PURCHASES; Google license-test payment methods remain
required. This flag never classifies receipts: provider evidence remains decisive.
Localized Google prices, server-only unlocking, restore independent of buying,
identity isolation, acknowledgements/refunds/offline/accounting are preserved.

Evidence: 62 focused PHP tests / 474 assertions; 19 focused Flutter tests plus
1 staging-identity check passed through scripts/run_tests.sh --mobile.
Source implemented; installed Android app unchanged. No APK/AAB built, upload,
provider/product/price/credential/permission change or phone request. Backend
availability/consent changes use isolated development, focused tests and exact
staging/verified-backup promotion; authoritative release-state.json records commit
and backup. No schema or historical data change; cPanel handler preserved.
Details: [production verification](PRODUCTION_PURCHASE_VERIFICATION.md).

Real sales remain disabled. Before activation: explicit owner launch approval;
approved release build without the internal-test flag and targeted acceptance;
app-scoped authenticated RevenueCat production webhook coverage (currently
SANDBOX-only); manual launch products/prices/regions and exact non-consumable
entitlement mapping. Last verified Play inventory: shelf_book_3 exists, books 4–8
missing. Next missing product remains book 4, هېندارې او چینې, shelf_book_4,
recorded USD 2.99 TEST price; launch price/regions require owner approval. Short
instructions remain in MANUAL_PLAY_PRODUCTS.md; no values guessed or changed.
Automatic sync DEFERRED / NOT COMPLETED; no 403 investigation/retries.

Next independent construction selection must follow owner direction; D14 retention/
privacy construction waits for owner policy. Release build and webhook/catalogue
configuration are launch preparation, distinct from deferred phone/recovery
acceptance. Second-phone restore DEFERRED / NOT PASSED. Steps 4 and 5 remain open.
Temporary account-level grant removal remains pending owner confirmation; preserve
existing app-level access and credentials. Roll back code through staging without
rewriting reader/money history. Earlier Android-not-built statements below are
superseded for source construction only, never for installed-app acceptance.

## Production verification construction — 7 October 2026 (latest)

Owner approved constructing production-mode purchase verification behind disabled
controls, with existing RevenueCat/Google Play integration. Real transactions
require authenticated provider-event environment and independent server REST
is_sandbox evidence to agree, with the exact reader, book, permanent entitlement
and unique purchase-time match. Unknown, malformed, ambiguous or failed evidence
never grants access or renews an offline lease. Test transactions remain excluded
from real income. Production acceptance defaults OFF and is forced OFF on staging;
no real payment or public release is authorized.

Implemented: environment-aware immutable purchases/events, real-sale ledger rows
using provider-reported currency/amount, historical agreement snapshots, unknown
fees/taxes/net earnings retained, duplicate/conflict checks, environment-aware
REST reconciliation/restore, and refunds including deleted-account history while
sales are disabled. Existing SDK acknowledgement and Google voided-purchase
polling preserved. No schema migration or scripted existing-data change.
Manual Play products/prices/availability remain owner-managed for first 100;
automatic sync DEFERRED / NOT COMPLETED, disabled; 403 work stopped.

Evidence: isolated focused BookPurchasesTest, VoidedPurchasesTest, AccountingTest
and PlayPriceSyncTest: **61 tests / 461 assertions**. Production-mode evidence is
synthetic, not an actual charged transaction. No mobile change, rebuild, phone
request, product/price change, permission/credential edit or provider write.
Staging/promoted exact commit and backup are recorded in authoritative
/home/shelf/staging-runtime/release-state.json after actual deployment; rollout
checks ensure real-sale gate remains OFF and historical records are unchanged.
Rollback: previous release code through staging, preserve money/reader history;
no database rollback needed. cPanel handler preserved.

**Next construction:** adapt Android purchase configuration/messages for release
mode behind disabled controls (current app requires test_mode=true), retaining
the owner-only test flow. This is a separate task, not implemented here.
D14 retention/deleted-account/privacy work still needs the owner policy.
Before real sales: owner launch approval, release-capable tested Android build,
manual launch products/local prices/regions and exact RevenueCat non-consumable
entitlement mapping, app-scoped authenticated provider webhook covering production,
refund/acknowledgement operational verification, signing/listing/legal/rights and
remaining targeted launch acceptance. Current credentials and permissions are
not changed in this construction task. A later approved rollout alone may enable
SHELF_REAL_PURCHASES_ENABLED; staging can never accept real sales.
Second-phone restore remains DEFERRED / NOT PASSED; reading/import and recovery
acceptance remain separate pre-release checks. Steps 4 and 5 are NOT complete.

The earlier statement below that production-mode server verification is not
built is superseded by this construction evidence, not by launch approval.


## Current owner amendment — 7 October 2026

For Shelf’s first 100 books, the owner creates and manages Google Play products,
prices and availability manually in Play Console. Automatic admin-to-Play
product/price synchronization is **DEFERRED, NOT COMPLETED** and stays disabled.
Stop 403 investigations, calculations, retries and support follow-ups unless the
owner explicitly reopens automatic sync. Existing product mappings, credentials,
app-level access, verification, acknowledgements, entitlements, refunds,
accounting and historical records are preserved. Admin USD prices are approved
reference values; saving does not update Play. Checkout uses Google’s localized
store price. No Play product creation/change is authorized in this task.

Read-only catalogue/Play list at 20:10:10 UTC: six Published paid books,
IDs 3–8, each recorded USD 2.99 and exact shelf_book_<id> mapping. Only
shelf_book_3 exists in Play: buy ACTIVE, US USD 2.99/AVAILABLE, legacy-compatible.
Books 4–8 missing in Play. Sync false, zero play-prices jobs. Checklist and short
owner instructions: [manual Play products](MANUAL_PLAY_PRODUCTS.md).
No source, publication, price, financial data, credentials or Play products changed.
Temporary four ACCOUNT-level grants saved around 5 October 00:38 UTC remain
**REMOVAL PENDING OWNER CONFIRMATION**; preserve existing Shelf app-level grants.
No permission write, credential removal or re-invitation performed.

Necessary implementation: admin reference-price/manual status wording, deferral
guard preventing saves/jobs/direct sync from invoking Play or rewriting sync
history. Refund polling remains independent. Focused staging verification uses
PlayPriceSyncTest, BookPurchasesTest, VoidedPurchasesTest and AccountingTest;
no mobile files changed, no broad suite/phone rerun. Rollback: previous release
through staging/promotion, retaining database history and handler.

**Next independent unfinished construction: Step 4 / §6.4 production-mode
purchase verification and sale recording behind disabled release controls.**
PurchaseService::receive and RevenueCatClient::confirms currently require sandbox;
production-mode verification is not built. A separate approved construction task
can implement/test it in isolation without enabling real payments and without
waiting for automatic price sync or D14. No such implementation in this task.
D14 retention/deleted-account/privacy work remains owner-decision dependent.
Second-phone restore remains DEFERRED / NOT PASSED, alongside targeted reading,
Word import, offline/isolation/withdrawal and final launch acceptance checks.
Actual backup alert delivery and replacement-host cutover remain unverified;
no new backup schedule/recovery claim. Steps 4 and 5 are NOT complete.

Historical status and investigation recommendations below are superseded by
this amendment; retained as evidence, not instructions to resume sync.


## Current reconciliation — 4 October 2026 (documentation-only update)

The historical table below records the 28 September milestone; this update is
current. No step is declared accepted without its evidence and owner acceptance.
Details and evidence: [status reconciliation](STATUS_RECONCILIATION_2026-10-02.md).

| Area | Current status |
| --- | --- |
| Construction | Step 2 closed; Step 3 catalogue/reader features built, outstanding acceptance retained. Step 4 accounts, sandbox purchases, Library/offline, refund detection and owner accounting tools built. Step 4 is not fully closed; deferred restore and product-sync integration remain. No real-money/public release authorization. |
| Phone evidence, owner-confirmed 2 October | Refund removed book from Library and app reported download removed; paid content locked, free sample worked. Declined test payment stayed locked. Repurchase restored Library and opened paid poem. |
| Focused server snapshot, 04:52:11 UTC | Reader 3, book 3: sandbox purchases 5 and 6; original sale ledger 13 USD +2.99, refund ledger 14 USD -2.99, repurchase ledger 15 USD +2.99. All labelled Test; zero real-income rows. Google refund event 18 matched order; repurchase event 19. Entitlement active after repurchase, checked 04:45:55 UTC. |
| Delivered fixes verified in code | Ownership published immediately after server confirmation; automatic startup/resume refresh and explicit errors; book action reads Open owned book. Admin Buying shows Allowed green / Blocked red. Deployed code at 2f0c2bf includes e4a286f, 69b4690 and d12a770. Phone evidence above supports purchase/refund behavior; it does not claim an owner visual check of admin badges. |
| Refund schedule | Latest audited scheduled check 04:45:01 UTC: completed HTTP 200, one duplicate handled, no additional refund, no conflict/unmatched. No new polling/purchase/refund triggered by this task. |
| Unfinished building | Step 4: product/price sync integration blocked by last 403, disabled; live purchases remain sandbox-only pending separate real-payment approval. Owner accounting construction is recorded below. Step 5: OneDrive encrypted upload/download/isolated restore and operating instructions built and verified 3 October; daily 03:30 AST schedule installed. Actual alert delivery remains unverified; first unattended run failed 4 October at 07:32:19 UTC after upload. Repair deployed at c887f91; controlled scheduled verification is recorded below, separately from future unattended success. Account-data retention work waits for D14. See detailed separation below. |
| Pre-release checks | Second-phone restore DEFERRED, NOT PASSED, not requested now. Keep reading/import acceptance, final account/isolation/offline/withdrawal checks, signing/listing/privacy/rights and recovery acceptance on a targeted checklist; do not use them to stall construction. |
| Accounting construction (2 Oct) | Approved and built: Sales ledger historical book/rights-holder filters; Accounting journal for estimates, confirmations, linked adjustments/payment records/reversals; Author balances with separate currencies and unknown figures. Default scopes exclude Test. Necessary focused verification and required staging/full-PHP/rollback gates; deployed at 5ff131f and retained in 5f75fa0, with historical agreements preserved; exact backup recorded by release workflow. See ACCOUNTING.md. No payout or real-payment activation. |
| Next building priority | Step 4 / D9 and 6.4: finish admin-to-Play product/price integration blocked by HTTP 403; the Step 5 / 6.11 repair is recorded below. D14 retention/privacy remains owner-decision dependent. Second-phone restore remains a pre-release check, not an implementation blocker. |
| Documentation and GitHub | At 4 October inspection, GitHub main and staging/production are 5f75fa0; accounting 5ff131f is included. The earlier unpushed-code warning is superseded. This task commits/pushes documentation only, without deployment. |

## Historical milestone — 28 September 2026

| Item                                              | Status                                                                                                                                                                                                                                                                                                 |
|---------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Date                                              | 28 September 2026 (evening)                                                                                                                                                                                                                                                                            |
| Changes in v2.3                                   | New way of working (5.1–5.3, R3): Codex runs server commands after Ajmal’s approval; Claude makes technical decisions; Ajmal gets owner questions only. Samples detail (6.3), owner login (6.8), D13 note. Step 2 closed; Step 3 started.                                                              |
| Changes in v2.2                                   | All v2.0 requirements restored where v2.1 had shortened them (6.2, 6.4, 6.5, 6.6, 6.8, 6.9, 6.10, 6.11, Steps 4–6, command labels in 5.4). R3 clarified. Staging boundary set (6.11). New: book type, categories, per-book content, bin (6.1); Word import (6.12); testing and /tmp rules (6.11); D18. |
| Current step                                      | Step 3 — Catalogue and reading (Step 2 done)                                                                                                                                                                                                                                                           |
| Task 1 — Daily backups                            | Done                                                                                                                                                                                                                                                                                                   |
| Task 2 — Move to shelf account and shelf.services | Done: code from GitHub, database and files imported and checked, HTTPS working, daily backup at 03:00. DNS fixed by giving kabul7.com its own nameserver records on the server.                                                                                                                        |
| Task 3 — Rename to Shelf                          | Done: services.shelf.app, server address as a setting, name Shelf and slogan in the database. Flutter installed in the shelf account; tests pass.                                                                                                                                                      |
| Task 4 — Authors, credits, languages              | Done on the server: author records, credits, languages, admin and API. Check PASS for all 6 books. Phone test comes in Step 3.                                                                                                                                                                         |
| Task 5 — Book foundation                          | Done: categories, poetry/prose type, Books/Content wording, automatic slugs, reliable order per book, bin and restore, filters, paginated API, record of who changed what. Each book manages its own content (no global list). Check PASS: 6 books, 342 items.                                         |
| Task 5b — Word import                             | Done with safeguards: left-out list and acknowledgement, all-or-nothing, no duplicates, add-only, private original with checksum. Final acceptance with a real Pashto document on the phone in Step 3.                                                                                                 |
| Access check (28 Sep)                             | 59 direct HTTPS checks: drafts, media signatures, owner preview, private files and secret files protected. Gaps found (admin for any user, public unpublished cover, global unlock) were closed by tasks 6–9.                                                                                          |
| Tasks 6 + 7 — States and owner-only admin         | Done and live: Draft / Ready / Published / Withdrawn with publish checks and history; all books Draft; owner-only admin; optional two-step login; covers of unpublished books private. 24 live checks passed.                                                                                          |
| Tasks 8 + 9 — Samples and unlock-all off          | Done and live: full or partial samples chosen per item; publish needs a sample; old markings cleared; global unlock removed from server and app (“Coming soon”); only samples and owner preview can read. 131 PHP, 95 Flutter tests and 22 live checks passed.                                         |
| Next                                              | Step 3, first task: an installable test version of the app for Ajmal’s phone (owner preview), then reading checks.                                                                                                                                                                                     |
| Owner to do                                       | Turn on the two-step login in the admin Profile. Choose samples for the books when ready.                                                                                                                                                                                                              |
| Latest code                                       | GitHub main, commit 1fb5951                                                                                                                                                                                                                                                                            |
| Server note                                       | On 28 September the shared /tmp space (3.9 GB) filled up and stopped the admin; Shelf leftovers were removed and Shelf no longer uses /tmp.                                                                                                                                                            |
| Open items                                        | SSH config file owned by the wrong user (server fix by root, not urgent). Repeated failed root login attempts (server security, later). Original poet of لمر ګلی unknown. Other projects also fill /tmp.                                                   |

# Part 11 — Decision log

- **7 October 2026, production construction:** Owner approved constructing production-mode purchase verification behind disabled controls, with existing RevenueCat/Google Play integration. Real transactions require authenticated provider-event environment and independent server REST is_sandbox evidence to agree, with the exact reader, book, permanent entitlement and unique purchase-time match. Unknown, malformed, ambiguous or failed evidence never grants access or renews an offline lease. Test transactions remain excluded from real income. Production acceptance defaults OFF and is forced OFF on staging; no real payment or public release is authorized. Preserves manual first-100-book pricing, acknowledgement, refunds and accounting. Owner authorized focused tests, staging/backed-up promotion and commit/push; no real-sale activation.

- **7 October 2026:** For Shelf’s first 100 books, the owner creates and manages Google Play products, prices and availability manually in Play Console. Automatic admin-to-Play product/price synchronization is **DEFERRED, NOT COMPLETED** and stays disabled. Stop 403 investigations, calculations, retries and support follow-ups unless the owner explicitly reopens automatic sync. Existing product mappings, credentials, app-level access, verification, acknowledgements, entitlements, refunds, accounting and historical records are preserved. Admin USD prices are approved reference values; saving does not update Play. Checkout uses Google’s localized store price. No Play product creation/change is authorized in this task.


- **2 October 2026:** Owner confirmed real Google Play phone refund/download
  removal, paid access locked with free sample retained, declined payment locked,
  and successful repurchase restoring Library/paid reading. Owner defers second-phone
  restore to the pre-release checklist, not passed/not requested now, and prioritizes
  construction with necessary automation rather than repetitive tests/phone checks.
  Approved one focused read-only record check and documentation-only commit/push;
  no implementation, deployment, rebuild or production data changes.

| Date       | Decision                                                                                                                                                                                                                                                                                                                                                                                      |
|------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| 2026-09-25 | Shelf defined as an independent Pashto and Farsi bookstore and library, managed multi-author catalogue, individual book purchases.                                                                                                                                                                                                                                                            |
| 2026-09-25 | Shelf is built by changing the existing poetry app work.                                                                                                                                                                                                                                                                                                                                      |
| 2026-09-25 | Master Record v2.0 approved as the single governing document; earlier documents replaced.                                                                                                                                                                                                                                                                                                     |
| 2026-09-25 | Old Pitswal rule documents deleted from the code; AGENTS.md created for Codex. Baseline tag and backup made; code pushed to private GitHub.                                                                                                                                                                                                                                                   |
| 2026-09-25 | Step 2 started.                                                                                                                                                                                                                                                                                                                                                                               |
| 2026-09-27 | Amendment v2.1 approved: own cPanel account “shelf” and domain shelf.services now; old app untouched then retired; no staging until live; each collection one book, foundation first; admin chooses samples.                                                                                                                                                                                  |
| 2026-09-27 | Android app ID services.shelf.app; app name Shelf; slogan کتاب مو ژوند بدلوي.                                                                                                                                                                                                                                                                     |
| 2026-09-27 | Five poetry books are by اجمل اند only; سيند په پرخه کې is by پروین پژواک, translated by اجمل اند; nothing else belongs to پروین پژواک. All six books are Pashto. |
| 2026-09-27 | Before any prompt, Claude checks it against this Master Record; any amendment is discussed and approved first.                                                                                                                                                                                                                                                                                |
| 2026-09-27 | Master Record v2.1 issued.                                                                                                                                                                                                                                                                                                                                                                    |
| 2026-09-27 | Word import is a separate task (5b) after task 5.                                                                                                                                                                                                                                                                                                                                             |
| 2026-09-28 | Each book manages its own content in the admin; no global content list.                                                                                                                                                                                                                                                                                                                       |
| 2026-09-28 | Automated tests stay; Android tests only when app code changed; Shelf never uses /tmp.                                                                                                                                                                                                                                                                                                        |
| 2026-09-28 | Word import works for every kind of book, split by Heading 1 and \*\*\*.                                                                                                                                                                                                                                                                                                                      |
| 2026-09-28 | Claude decides what follows from this document and always asks about anything new, explained first. Tasks are written for the best result, not the shortest time.                                                                                                                                                                                                                             |
| 2026-09-28 | Master Record v2.2 approved: v2.0 requirements restored; R3 clarified (migrations vs normal admin work); staging boundary before the first real reader other than Ajmal or first real payment; Word import safeguards.                                                                                                                                                                        |
| 2026-09-28 | Unpublished covers are private (follows 6.10). All existing books start as Draft. The existing single user is the owner.                                                                                                                                                                                                                                                                      |
| 2026-09-28 | Old free markings cleared; samples are chosen per item, fully or first part only.                                                                                                                                                                                                                                                                                                             |
| 2026-09-28 | Codex runs live server commands itself after Ajmal approves them in Codex; backup first; stop at first failure.                                                                                                                                                                                                                                                                               |
| 2026-09-28 | Claude makes technical decisions following this document; Ajmal is asked owner questions only, in plain words.                                                                                                                                                                                                                                                                                |
| 2026-09-28 | Step 2 closed. Master Record v2.3 issued.                                                                                                                                                                                                                                                                                                                                                     |

# Appendix A — History of the existing poetry app

Historical facts, kept for reference. The current state of Shelf is in Parts 3 and 10.

## A.1 Capability status before Shelf

| Area                         | Historical status                                                                        |
|------------------------------|------------------------------------------------------------------------------------------|
| Laravel and Filament backend | Foundation complete.                                                                     |
| Flutter reader               | Reader capability complete.                                                              |
| Audio                        | Capability complete; no production audio content.                                        |
| Share and save cards         | Passed real-device testing.                                                              |
| Payments and access          | RevenueCat and server-side entitlement foundation for one global unlock-all; never live. |
| Protected owner preview      | Implemented (commit 3a2f39c).                                                            |
| Android release preparation  | Not started.                                                                             |

## A.2 Protected owner preview

Separate /api/owner-preview path, signed temporary access and isolated caches, keeping draft content out of the public API. Kept for Shelf. Old tokens and settings are never reused.

## A.3 Old payment identifiers

**pitswal_unlock_all_v1** (product), **unlock_all** (entitlement), **default** (offering). They belong to the old unlock-all model and never grant access to Shelf books (R10).

## A.3a Catalogue snapshot of 30 August 2026

- 2 draft/private collections (81 and 67 works); 148 works; 0 published; 15 free, 133 locked; 1 cover; 0 audio.
- Three more original collections were then blocked for lack of reliable source text; a separate translated book was preserved but not yet catalogued. All were later imported, giving six books by September.

## A.4 Inspection of 25 September 2026

- 6 collections, 342 works, 79 translated works, 6 covers, 74 artwork images, no audio.
- No author model, language field, reader accounts or purchase records.
- Payments never live; no real readers or purchases found.
- No GitHub copy and last backup 22 days old at that time — both fixed the same day.

## A.5 Lessons from the poetry app

- **Real phones find real problems.** Real-device testing exposed typography and layout problems that automated checks missed.
- **Do not guess text.** Uncertain Pashto material was left unimported rather than guessed (R6).

# Appendix B — Plain-language glossary

| Word               | Meaning in this project                                                                              |
|--------------------|------------------------------------------------------------------------------------------------------|
| Backend            | The server part (Laravel). Stores books, accounts and purchases and decides who can read what.       |
| Filament           | The owner admin panel where Ajmal manages authors, books, prices and publishing.                     |
| Flutter app        | The Android app readers install.                                                                     |
| Codex              | The coding assistant that works inside the shelf account on the server.                              |
| AGENTS.md          | The short rule file in the code that Codex reads every time; the summary of this document.           |
| cPanel account     | A separate user on the server with its own files, database, domain and settings. Shelf’s is “shelf”. |
| DNS / nameservers  | The internet’s address book. shelf.services uses ns1 and ns2.kabul7.com, which point to the server.  |
| SSL / HTTPS        | The padlock: an encrypted connection to shelf.services.                                              |
| RevenueCat         | A service that connects the app to Google Play payments and tells the server who bought what.        |
| Sandbox            | Test mode for payments. No real money moves.                                                         |
| Git / commit / tag | The history of code changes. A commit is one saved change; a tag marks an exact version.             |
| Migration          | A controlled, recorded change to the database, kept in git, run by Ajmal after a backup.             |
| Credits            | Who is named on a book: authors, translators, editors, in order.                                     |
| Sample             | The free part of a book that anyone can read before buying.                                          |

---

# Addendum — owner decisions after v2.3 (29 September–2 October 2026)

These decisions are binding and will be folded into the next full version. Where they differ from the text above, this addendum wins.

| ID | Decision |
|---|---|
| Step 4 connection (30 Sep 2026) | Owner approved connecting the new Shelf Google Play/RevenueCat setup in sandbox only, securing private keys outside git, generating webhook authorization, per-book provider catalogue, admin-price sync attempt, bumped internal-test AAB, fresh seven-day owner APK, tests and commit. Supplied V2 configuration key and separate V1 subscriber verifier key are isolated. Six products/entitlements and app-scoped sandbox webhook configured; independent V1 authentication and sandbox server checks passed. Verified backup `/home/shelf/backups/shelf/20260930-211054/`; live simulation rolled back all synthetic rows. Google stopped at book 3 with initial 400/409 product/price error; read-only local-price conversion denied 403, consistent with propagation/permissions still unavailable. Scheduled sync remains disabled without retry loop; books 4–8 Pending. Tests: 173 PHP / 2,095 assertions and 134 Flutter. AAB 1.0.3 (12), owner APK expires 7 October at 21:09:17 UTC; owner upload and real-phone sandbox purchase/restore/refund checks pending. Paths, hashes, rollback and exact next steps are in docs/PURCHASES_TEST_SETUP.md. No real money, public release or source/price edits. |
| Legacy item visibility (30 Sep 2026, latest decision) | Supersedes the earlier same-day instruction to keep visibility unchanged. Ajmal confirms the hidden item state in books 3–8 is inherited from the old poetry app's private-draft period, not an editorial choice. Make all non-deleted items in these books visible through a backed-up reversible migration, recording owner and time. Continue the approved temporary samples (first two items in current book order, full items) and publish all six using existing publish logic and status history. Verify six books in the public catalogue, non-sample text locked and purchases disabled pending Play/RevenueCat setup. Do not change source text, access rules or the old poetry project; no rebuild. |
| Internal-test rollout evidence (30 Sep 2026) | Applied through reversible migration `2026_09_30_070000_publish_owner_internal_test_catalogue` after verified backup `/home/shelf/backups/shelf/20260930-070203/`. All six books Published, all 342 non-deleted items visible, exactly two full-item samples per book, owner/time/status history recorded and source/order checksums unchanged. Live HTTPS confirms six public books, paginated content, covers, locked non-sample text/media, disabled purchases and protected owner preview. The first verification hit the existing rate limit; the read-only rerun respected Retry-After and passed. PHP tests: 171 / 2,083 assertions; rollback and unrelated-draft protection tested in the isolated database. No app rebuild; real phone refresh/display check remains for Ajmal. |
| Internal-test catalogue (30 Sep 2026) | Publish all six books (IDs 3–8) for Google Play internal testing; Ajmal accepts that Published books are visible through the public catalogue. For any book without a sample, use a TEMPORARY test sample consisting of its first two visible items in full; Ajmal can change samples later in the admin. Check author credit, language, cover, visible content, sample, USD 2.99 price and canonical product ID; fix only missing approved test values and report other blockers. Backup first, use a reversible migration and existing publish logic with status history. Keep non-sample text locked, draft/owner-preview protection unchanged and purchases disabled until Play/RevenueCat setup is finished. Verify six books in the live public catalogue and no body for a non-sample item. No app rebuild is authorized by this decision. |
| Visible-item clarification (30 Sep 2026) | Keep item visibility unchanged; Ajmal will choose visible items in the admin. Publication remains pending this choice: read-only preflight found no visible items in books 3, 4, 6, 7 or 8, and only item 152 in book 5. No publication or temporary samples were applied. |
| D7 | Reader sign-in: Google sign-in + email/password. Registration is open to any email (owner decision); the staging boundary (6.11) still applies before real readers other than the owner. Google Cloud project shelf-510123; the Play Store signing key SHA-1 must be added to the Android OAuth client before release. |
| D8 | Payments through Google Play (App Store later with an iOS version), using Shelf's own RevenueCat project. |
| D9 | **Amended 7 October 2026:** For Shelf’s first 100 books, the owner creates and manages Google Play products, prices and availability manually in Play Console. Automatic admin-to-Play product/price synchronization is **DEFERRED, NOT COMPLETED** and stays disabled. Stop 403 investigations, calculations, retries and support follow-ups unless the owner explicitly reopens automatic sync. Existing product mappings, credentials, app-level access, verification, acknowledgements, entitlements, refunds, accounting and historical records are preserved. Admin USD prices are approved reference values; saving does not update Play. Checkout uses Google’s localized store price. No Play product creation/change is authorized in this task. Existing six books retain approved USD 2.99 test reference prices. |
| D11 | Readers who bought a book keep it forever, even if the book is later withdrawn. New readers cannot buy a withdrawn book. |
| D12 | All sales are final; Shelf gives no refunds except accidental duplicate purchases or a book that does not work (owner decides in the admin). Google Play's own 48-hour refund window cannot be disabled: if Google refunds, access is removed. Before buying, the reader ticks: "Read the free sample first. All sales are final. I agree." The admin shows refunds per reader and can block a reader from buying. |
| D13 | Bought books can be downloaded and read offline. The app checks ownership with the server whenever it is online. An offline copy stays readable for at most 30 days without a successful check; after that the reader must go online once. A refunded or revoked book, and its downloaded copy, is removed at the next check. |
| D15 | App interface languages: Pashto and English (Farsi later). Default Pashto, or English if the phone is set to English. Visible compact language dropdown in the Store header, a one-time choice on first launch, and the same option in Settings. English changes only the app's own words; books (reader, text, titles, author names, descriptions, contents, share cards) always stay right-to-left and unchanged. All account and purchase screens are always English and left-to-right. |
| UX | Every screen must be friendly and clear for non-technical readers: obvious primary action, large touch targets, clear hierarchy, short plain text, helpful empty/error/loading states, no mixed-direction punctuation or clipped text, consistent with the Shelf look. |
| Dev | Failing tests or build errors in Codex's own work-in-progress are normal development and are fixed without stopping. Codex stops and reports only when something fails on the live server or database, data could be lost or changed unexpectedly, a backup or rollback fails, or a fix would go beyond the approved task. |
| Test app | Owner test builds are delivered the proven way: Codex builds an owner-preview APK with a fresh 7-day token and gives one scp download command. |
| D10 (1 Oct 2026) | The owner sets author-share percentages per book in the admin. All six current books: 100% of the net amount received to اجمل اند, effective 1 October 2026. For سيند په پرخه کې, author پروین پژواک permits the owner to keep all income; written permission is retained by the owner. اجمل اند receives 100% as translator/publisher. New versions record owner/time; past agreements and financial records remain unchanged. |
| Staging (1 Oct 2026) | Private staging.shelf.services in the Shelf cPanel account, with separate folder/database/user/APP_KEY/secrets, catalogue-only refresh and only the owner admin copied. No reader or financial/personal data is copied. Play price sync permanently disabled, emails logged, queues/scheduler disabled, test payments only with staging-prefixed RevenueCat identities and separate credentials; REST verification, no staging webhooks. Shelf Test Android app services.shelf.app.staging installs alongside Shelf. Every change goes to staging first; production promotion requires the identical staged commit with passing checks, backup first, migration, health verification and rollback instructions. Production daily backup and webhook stay unchanged. |
| First purchase fixes (2 Oct 2026) | Owner approved excluding test/sandbox purchases from all real-income totals, author amounts, dashboards and exports while retaining clearly labelled Test history. Immediate server-confirmed ownership update; startup/resume Library refresh and visible failures; Allowed/Blocked admin badges; Library covers, Read book, downloaded/remove state, one account icon, pull-to-refresh and small refresh icon. Isolated development, staging first, promotion of identical commit through release script with fresh backup, required PHP/Flutter tests, commit, bumped Google Play AAB and Shelf Test APK. Existing reader, purchase, ledger, agreement and source records remain unchanged. |
| Google refund detection (2 Oct 2026) | Owner approved the simpler Google Voided Purchases API route using existing Shelf Play credentials, without new RevenueCat/Pub/Sub notification setup. Read-only API probe first; production check every 15 minutes, exact purchase matching, immutable refund entries, Test labels retained, D12/D13 access removal, idempotency, safe errors and logs. Develop in isolation, stage first, promote identical commit with backup, tests and commit. |
| Construction priority and phone evidence (2 Oct 2026) | Owner phone results and focused server evidence are recorded in Part 10 and STATUS_RECONCILIATION_2026-10-02.md. Second-phone restore moves to the pre-release checklist: deferred, not passed, not requested now. Prioritize finishing construction; automate necessary checks and avoid repetitive tests/phone requests unless they resolve a concrete essential risk. No broad suite rerun for status reconciliation. This task is documentation-only commit/push, no deployment/rebuild/data changes or new implementation. |

| Step 4 accounting construction (2 Oct 2026) | Owner approved and Codex implemented the missing 6.9/D10 recordkeeping tools, using historical agreements and append-only source-linked entries, unknown receipts retained, Test exclusion, separate currencies, recording owner/date/reference and payments/reversals with no money transfer. Necessary automated verification and backed-up staging-first identical-commit promotion required. See ACCOUNTING.md and the authoritative release state for exact deployment evidence. No new phone tests, no change to purchase/access behavior; second-phone restore deferred/not passed; product sync 403 and off-server recovery unfinished. |
| Step 5 off-server preparation (2 Oct 2026) | Owner chose their existing OneDrive. Authorized isolated preparation and staging-first integration: dedicated Shelf-Backups folder, encrypt database/private and uploaded content/recovery configuration before copy-only upload, secrets outside Git/logs/chat, independently retained recovery key, quota check before retention. One private authorization step. Completion requires an uploaded copy downloaded and successfully restored in isolation, scheduled backups and failure reporting. Preparation details and remaining gates are in OFFSERVER_BACKUP.md. No broad PHP/phone reruns; product-sync 403 remains unfinished and second-phone restore deferred. |

| Step 5 continuation (3 Oct 2026) | Owner confirms recovery key downloaded to D:\shelf-recovery-key.secret on a separate unencrypted USB (SCP 100%, 65 bytes); preserve existing key/OAuth. Complete staged/promotion, encrypted upload/download and isolated DB/file recovery, schedule and failure handling, record actual evidence and commit/push. No owner test notification; mocked failure transport permitted. Product-sync 403 unfinished; second-phone restore deferred. |

| Step 5 OneDrive evidence (3 Oct 2026) | Tested implementation 44ce420 staged and promoted identically after backup /home/shelf/backups/shelf/20261003-200920; 14 focused tests and HTTPS/history checks passed. Production ciphertext uploaded to Shelf-Backups and downloaded/decrypted/restored 20:31:01 UTC: 34 tables with identical schema/rows, 774 media/source files and code/configuration hashes; socket-only disposable DB, event execution off, restored app never started, workspace removed. Sidecar download matched. Daily 03:30 America/Lower_Princes (UTC−04:00; 07:30 UTC) schedule installed once, previous jobs preserved; pre-schedule backup /home/shelf/backups/shelf/20261003-203119. Append-only offsite retention with monthly review/100 MiB reserve; local policy 14 unchanged. Owner external key on unencrypted USB confirmed, existing key retained. Mocked failure path passed with no owner test notification; actual email delivery and first unattended cron execution unverified. Final staged/promotion commit is recorded by release-state.json; full replacement-host cutover not tested. See OFFSERVER_BACKUP.md and private offsite evidence. Product-sync 403 unfinished; second-phone restore deferred/not passed. |

Manual scheduled-entry evidence (3 October 2026): fresh package
`shelf-production-20261003T203339Z-db49e08f6b471fde.tar.gpg` uploaded,
downloaded and restored successfully at 20:35:10 UTC (34 tables, 774
media/source files, schema/rows and configuration/code checksums identical).
`/home/shelf/backups/shelf/offsite/last-success.json` records successful
completion with recovery_verified=true. This verifies the installed entry point,
not an unattended cron launch.

## Historical read-only reconciliation — 4 October 2026 (before repair)

GitHub main and both staging/production release-state commits were
`5f75fa0eea475431cbad9d7e0587a384e3f933c0` at inspection. Accounting
`5ff131f` is an ancestor: Accounting journal, Author balances and Sales ledger
are deployed; historical agreement snapshots remain preserved and Test
transactions excluded from real income. OneDrive implementation/evidence is
deployed at `5f75fa0`; latest promotion backup is
`/home/shelf/backups/shelf/20261003-204156/`. This reconciliation changes
only documentation in an isolated checkout; no deployment or tests are run.
The unrelated cPanel-generated PHP handler remains untouched.

**First unattended run: FAILED, not passed.** Existing scheduled log
`storage/logs/offsite-backup.log` reports `FileNotFoundError` (details withheld).
`last-upload.json` records package
`shelf-production-20261004T073043Z-983d1910c66c57ed.tar.gpg`
uploaded at **2026-10-04 07:31:41 UTC**, following the 03:30 local / 07:30 UTC
launch. `failure.json` records failure at **07:32:19 UTC**. There is no
recovery evidence for that package. `last-success.json` remains **3 October
20:35:11 UTC**, the earlier manual scheduled-entry run, not unattended success.
The log may show an error from failure handling; it does not establish the
original failing operation or actual alert delivery. No repair, rerun, restore
or notification was attempted. Direct `crontab -l` was denied by PAM in this
session; schedule installation evidence and existing execution records were used.

The two successful 3 October remote drills remain valid: encrypted copies in
Shelf-Backups were downloaded/decrypted, with identical database schema/rows
(**34 tables**) and checksums for **774 media/source files**, code and recovery
configuration. Daily schedule remains **03:30 America/Lower_Princes / 07:30 UTC**.
Offsite retention has no automatic deletion and requires monthly capacity review;
local nightly retention remains **14**. Owner-confirmed recovery key on a
separate unencrypted USB is preserved; its contents were not read or printed.
Actual alert delivery and full replacement-host recovery/cutover remain
unverified. Product-sync 403 remains unresolved and its runner disabled.
Second-phone restore remains explicitly deferred to pre-release, NOT PASSED.
Neither Step 4 nor Step 5 is declared complete.

**Next unfinished building task: Step 5 — Ready for release, off-server backup
and tested restore; requirement 6.11 (Testing, backups and recovery).** Diagnose
and repair the unattended scheduled backup/failure-handling path, then establish
successful recovery through the scheduled environment. This comes next because
the first unattended attempt demonstrably failed despite the manual pass.
Repair requires a separate approved task; it is not started here. Step 4
admin-to-Play product/price integration (D9) and Step 5 retention/privacy (D14,
6.10) remain separate unfinished work.

## Unattended backup repair — 4 October 2026

Owner authorized the repair after reconciliation d8e5499. Exact cause confirmed
by replaying the failed run's existing uploaded package under the cron PATH:
`drill → restore_database → subprocess.Popen` could not find **mariadbd**.
The installed daemon is **/usr/sbin/mariadbd**; manual login PATH includes
/usr/sbin, while cron uses **/usr/local/bin:/usr/bin:/bin**. Download, decryption
and file checks reached SQL restoration before this failure. This was the
original recovery error, independently reproduced without invoking alerts.

Repair **c887f91fe52e0166cf936056b27e247ec8134946** passed staging's
16 focused backup regressions and HTTPS isolation checks, then identical-commit
production promotion with verified backup
`/home/shelf/backups/shelf/20261004-233613/`. No broad PHP/phone tests.
Recovery launches the daemon by its absolute installed path. Scheduled failure
records now identify package/upload/recovery/success-record stages, error type
and allowlisted executable names when available, withholding arguments/output.
Alert exceptions are recorded separately in alert-failure.json and cannot mask
the original error. Mocked transport tests send no notification.
The focused staging gate includes the already approved accounting/status
reconciliation documents; application changes still require their normal gate.

**Controlled scheduled-environment verification PASSED**, as user shelf with
HOME=/home/shelf, USER/LOGNAME=shelf, SHELL=/bin/sh, working directory
/home/shelf, stdin noninteractive, clean environment and the exact cron PATH.
The promoted `scheduled` entry point created and uploaded a fresh encrypted
package, downloaded/decrypted it and verified isolated recovery:
`shelf-production-20261004T233755Z-2d2f4eee28debdf2.tar.gpg`.
Ciphertext SHA-256:
`e572e1959c76ce513bb3a9b5d31c00cbc1fcb5dcd21eb476c4ec47ee782369a4`.
Recovery finished **2026-10-04 23:39:27 UTC**: **34 tables**, identical schema/rows;
**774 media/source files**, configuration and code inventories matched.
Socket-only disposable MariaDB, no restored app startup; workspace cleaned up.
Private recovery evidence and last-success.json record this success.
The failed 07:30 package and original failure record remain preserved.

This controlled run does **not** establish future unattended cron success.
Next automatic run: **5 October 2026, 03:30 America/Lower_Princes / 07:30 UTC**.
Cron was inspected read-only; all existing jobs and its PATH remain unchanged.
Review the next run's new last-success and recovery timestamps before claiming
unattended success. Historical failure.json can remain after a newer success;
compare timestamps rather than treating its presence alone as a fresh failure.

Alert configuration: one marked owner with a valid address and executable
/usr/sbin/sendmail. Existing cron/mail delivery logs are not readable by shelf;
no actual delivery evidence was established. **No test notification sent; actual
alert delivery remains unverified.** Keys/OAuth, owner-confirmed separate
unencrypted USB key, existing backups, append-only offsite retention/monthly
review/100 MiB reserve and local retention 14 are preserved. Runtime cPanel
handler is untouched. Full replacement-host recovery/cutover remains unverified.
Second-phone restore remains deferred to pre-release, NOT PASSED. Product-sync
HTTP 403 remains unresolved/disabled; D14 retention remains an owner decision.
Neither Step 4 nor Step 5 is declared complete.

Next unfinished building task: **Step 4 — Accounts and purchases, D9 / 6.4,
admin-to-Play product and price integration**. Resolve the recorded 403 and prove
admin-managed product/pricing sync before enabling its runner; this is the
remaining purchase integration construction after accounting and backup repair.
D14 privacy/retention work (Step 5 / 6.10) waits for its owner decision. No product
sync or retention work started in this task. For rollback, retain backups and
financial history, return code to the previous revision through the approved
release workflow; no database schema change or cron/key/OAuth rollback is needed.

## Step 4 / D9 and 6.4 — current investigation, 4 October 2026

Reused the preserved pricing investigation and owner-confirmed permissions and
merchant setup. Current configured identity is
shelf-play@shelf-510123.iam.gserviceaccount.com, project shelf-510123,
package services.shelf.app, documented androidpublisher OAuth scope.
Bounded GET of shelf_book_3 passed (HTTP 200, ACTIVE, buy); documented,
non-mutating USD 2.99 price conversion again returned HTTP 403 PERMISSION_DENIED
with no reason/details. No new request defect established; exact ineffective
Console grant/account prerequisite remains unknown. Prior PATCH denial is
preserved evidence, not a new write. Current official Google docs checked.

Effective sync remains disabled, price queue empty. Books 3–8 retain approved
USD 2.99 prices, Published states and canonical product IDs. Book 3 local sync
Error; others Pending, with no new sync-status/data change. No product write,
availability change, credential recreation, setup repetition or broad admin
request. Deployed code remains c887f91; prior pushed records 93f73df preserved.

One necessary read-only owner action: Play Console → Users and permissions →
search shelf-play@shelf-510123.iam.gserviceaccount.com → Export user list;
provide only that identity's CSV row to establish active access, expiry and
app/account permissions for services.shelf.app. Manage store presence maps to
CAN_MANAGE_PUBLIC_LISTING. This resolves the exact-grant uncertainty before
another setup change or Google escalation. Detailed evidence, official sources
and enablement gates: [price-sync investigation](PLAY_PRICE_SYNC_VERIFICATION.md).

No application changes/deployment, broad tests, backup recovery, phone checks
or notifications. Next automatic offsite run remains 5 October 03:30
America/Lower_Princes / 07:30 UTC; last success is still the controlled 4 October
23:39:27 UTC verification, not unattended evidence. Actual alert delivery and
replacement-host recovery unverified; D14 awaits owner decision; second-phone
restore deferred to pre-release, NOT PASSED. Steps 4 and 5 remain open.
Next work: resolve this authorization block, then finish D9/6.4 integration.

## Pricing permission confirmation and continued diagnosis — 4 October 2026

Owner confirmed exact service account shelf-play@shelf-510123.iam.gserviceaccount.com
and app services.shelf.app: app-level View app information, View financial data,
Manage orders and subscriptions, Manage store presence checked; app quality and
policy declarations greyed checked; App Admin and all account permissions unchecked.
No permission changed. This supersedes the permission-export request above.
Manage store presence is granted; no broader permissions are requested.

Independent native cURL at 23:55:00 UTC, existing service credentials and documented
androidpublisher scope: product GET 200; documented USD 2.99 conversion POST 403
PERMISSION_DENIED without reason/details. Same denial independently of Laravel
request serialization; no evidenced code fix. Official Google docs support app
permissions for pricing/products and no longer require Cloud-project linking.
The exact Google backend restriction/account prerequisite remains unknown.

Next essential owner action: Play Console → Help → Contact us, submit the
prepared [pricing authorization support report](PLAY_PRICE_SYNC_VERIFICATION.md)
asking for the specific restriction and least-privilege correction. No support
message sent by the agent, no secrets in the report. No permission toggles,
credential recreation, product write, setup retry, sync enablement or deployment.
Deployed code remains c887f91; documentation committed separately. Preserved
synchronized records and cPanel handler; no broad/phone/backup recovery tests.

Future unattended backup: 5 October 03:30 America/Lower_Princes / 07:30 UTC;
controlled verification is still the last observed success. Alert delivery and
replacement-host recovery unverified; D14 undecided; second-phone restore
deferred to pre-release, NOT PASSED. Steps 4 and 5 remain open. D9/6.4 resumes
when Google's restriction/correction is established, without speculative Admin.

## Owner Explorer success and server reproduction — 5 October 2026 UTC

Owner reports successful APIs Explorer calculation for services.shelf.app with
USD units "2", nanos 990000000: convertedRegionPrices, convertedOtherRegionsPrice,
regionVersion {"version":"2026/01"}. Exact same endpoint/body reproduced once
on server at 00:17:11 UTC with existing service credential: HTTP 403,
PERMISSION_DENIED, "The caller does not have permission", no reason/details.
Caller shelf-play@shelf-510123.iam.gserviceaccount.com, androidpublisher scope,
credential project shelf-510123. No explicit quota_project_id, quota environment
override, API key or x-goog-user-project; browser consumer/project/scope unknown.

Browser success changed identity and client environment together. It establishes
successful calculation for the owner, not a specific missing permission or
quota configuration. App-level Manage store presence is still confirmed granted.
No products/prices/permissions/credentials changed; sync remains disabled.
No browser token requested/copied, no code deploy or broad suite.
Deployed code remains c887f91; prior pushed documentation ee8d0b2.

Historical next action (superseded by the Shelf-side audit below): paired-results
support report prepared in [price-sync evidence](PLAY_PRICE_SYNC_VERIFICATION.md);
no report sent.
Existing manual/controlled backup evidence preserved; next automatic run
5 October 03:30 America/Lower_Princes / 07:30 UTC, unattended success not yet
verified. Actual alert delivery/replacement-host recovery unverified; D14 open;
second-phone restore deferred, NOT PASSED. Steps 4 and 5 remain open.


## Step 4 / D9 and §6.4 — Shelf-side audit, 5 October 2026 UTC

Implementation traced from transactional admin save through Collection model
hooks, durable revisioned PlayPriceSync intent, dedicated cron/queue and
GooglePlayClient. GET/conversion/PATCH share token loader, scope, bearer/client,
package and URL base; documented endpoint casing/body correction already present.
Fresh production bootstrap in ordinary CLI and clean shelf cron environment
(PATH /usr/local/bin:/usr/bin:/bin, initial cwd /home/shelf) resolves identical
credential file, owner-inspected service identity, project shelf-510123 and
services.shelf.app, sync disabled, staging false, no config cache or relevant
inherited env override. No HTTP requests made. Web shares source/document root,
but actual web runtime env was not directly measurable (no Shelf PHP process
present); not claimed verified. No concrete local defect established or code fix.

Conversion is an implementation choice for D9's Google-generated regional prices;
modern product PATCH requires explicit regional settings and regionsVersion,
without legacy autoConvertMissingPrices. Legacy API previously required migration;
no unapproved alternative pricing or availability workflow adopted. Prior PATCH
403 also remains. Official docs confirm androidpublisher scope, service-account
Play setup and Manage store presence pricing/product rights; no evidenced mandate
for Admin or account-wide grants. Owner's granted permission remains authoritative.

Next single discriminating check: request-local x-goog-user-project shelf-510123
on one otherwise unchanged service-account calculation. Not executed this task.
Success implicates consumer routing; structured consumer/serviceusage error
identifies the explicit variant's Cloud prerequisite; unchanged generic 403
reduces that hypothesis but proves no particular missing Play grant. No speculative
IAM/permission/credential edits; support draft held. Detailed functions, evidence,
limits and official references: [PLAY_PRICE_SYNC_VERIFICATION.md](PLAY_PRICE_SYNC_VERIFICATION.md).

Documentation-only audit; deployed code c887f91, preceding pushed docs f6eff7c.
Sync/products/prices unchanged, cPanel handler and synchronized records preserved.
No broad suite, backup recovery, phone test or notifications. Next unattended
backup due 5 October 03:30 America/Lower_Princes / 07:30 UTC remains unverified;
actual alert delivery and replacement-host recovery unverified. D14 undecided;
second-phone restore deferred to pre-release, NOT PASSED. Steps 4/5 remain open.
