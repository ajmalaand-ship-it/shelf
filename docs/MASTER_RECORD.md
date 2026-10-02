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
| D9  | Prices and currencies                 | Open — Step 4                                                                                                                                                                                   |
| D10 | Author and rights-holder agreements   | Decided 1 October 2026: owner sets per-book shares in admin; all six current books 100% net received to اجمل اند. See the addendum for book 6 permission and immutable agreement versions. |
| D11 | Withdrawn books already bought        | Open — Step 4                                                                                                                                                                                   |
| D12 | Refund handling                       | Open — Step 4                                                                                                                                                                                   |
| D13 | Offline limits and storage clearing   | Open — Step 3/4. Current state: browsing the catalogue needs internet, so withdrawn or unpublished books are never shown from old saved data.                                                   |
| D14 | Account deletion and data retention   | Open — Step 5                                                                                                                                                                                   |
| D15 | App interface languages and wording   | Open — Step 3                                                                                                                                                                                   |
| D16 | Launch catalogue                      | Open — Step 5                                                                                                                                                                                   |
| D17 | Samples                               | **Decided 27 Sep:** admin chooses each book’s free part; no fixed amount.                                                                                                                       |
| D18 | Content management and import         | **Decided 28 Sep:** each book manages its own content; Word import by Heading 1 and \*\*\* (6.12).                                                                                              |

# Part 10 — Current status

## Current reconciliation — 2 October 2026 (accounting construction update)

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
| Unfinished building | Step 4: product/price sync integration blocked by last 403, disabled; live purchases remain sandbox-only pending separate real-payment approval. Owner accounting construction is recorded below. Step 5: regular off-server backup/recovery workflow and operating instructions; retention work after D14. See detailed separation below. |
| Pre-release checks | Second-phone restore DEFERRED, NOT PASSED, not requested now. Keep reading/import acceptance, final account/isolation/offline/withdrawal checks, signing/listing/privacy/rights and recovery acceptance on a targeted checklist; do not use them to stall construction. |
| Accounting construction (2 Oct) | Approved and built: Sales ledger historical book/rights-holder filters; Accounting journal for estimates, confirmations, linked adjustments/payment records/reversals; Author balances with separate currencies and unknown figures. Default scopes exclude Test. Necessary focused verification and required staging/full-PHP/rollback gates; exact promotion commit/backup recorded by release workflow. See ACCOUNTING.md. No payout or real-payment activation. |
| Next building priority | Regular off-server backup/recovery workflow (Step 5); keep the separate product-sync 403 visible for its approved integration work. Second-phone restore remains a pre-release check, not an implementation blocker. |
| Documentation and GitHub | This reconciliation is documentation-only, based on GitHub main a624ea7 because deployed code 2f0c2bf has unpushed implementation ancestors. Push only documentation; do not deploy or silently push those code ancestors in this task. |

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
| D9 | One price per book in US dollars, set and edited ONLY in the Shelf admin; Google Play shows it in the reader's local currency. Admin price changes automatically sync to Google Play: create missing one-time products, update their prices with Google's automatic local prices, and activate/deactivate with the book's publication state. The owner never creates products or edits prices in Play Console. Step 4 Task 2b test price approved: USD 2.99 on the six existing books, with backup first and actor/time recorded. Sync remains disabled until the owner provides server-only service-account credentials; one Play-enabled service account is used by both Shelf sync and RevenueCat. No real money. |
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
