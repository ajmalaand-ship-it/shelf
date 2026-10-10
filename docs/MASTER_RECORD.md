# Shelf Master Record

The single governing document for building Shelf

Version 2.3 — Approved — 28 September 2026

Binding owner amendment — 8 October 2026: Android and iOS joint launch

Owner: Ajmal Aand   •   Prepared with Claude

**How to use this document**

This is the **only** document that governs Shelf. It replaces every earlier Shelf document, including the Constitution v1.0, its drafts, Amendment 1, and Master Record v2.0, v2.1 and v2.2. Nothing in those earlier files applies unless it is written here.

Only the **newest version** of this document counts. Every version has a number and a date on each page.

Before writing any instruction or prompt, Claude checks it against this document. If a change to this document is needed, Claude explains it plainly and Ajmal approves it first.

Codex (the coding assistant on the server) follows **AGENTS.md** in the Shelf code. AGENTS.md is the short summary of this document for coding agents and must always agree with it.

**Status of this version**

Master Record v2.0 was approved on 25 September 2026 and amended in v2.1 on 27 September 2026. This version 2.2 restored every v2.0 requirement and clarified R3 and staging. This version 2.3 (28 September 2026) records the new way of working approved by Ajmal (Codex runs server commands after Ajmal’s approval; Claude makes technical decisions; Ajmal is asked owner questions only) and closes **Step 2**. **Current direction (owner-approved 8 October 2026):** four work areas and Android/iOS joint launch, following the five practical batches in Part 7. The single remaining-work list at the start of Part 10 governs priorities. Steps 0–2 remain completed; Steps 3–5 remain partly completed; Step 6 has not started. Current owner decision (10 October): current Shelf Review 3 work is accepted and further Android/UI changes are paused. Google Play approved 1.0.11 (20) for Closed testing – Alpha; the latest owner-reported screenshot showed “Changes ready to publish” with Managed publishing ON. Publishing completion and Play-installed build-20 phone acceptance are NOT CONFIRMED. Public Android/iOS release remains held for joint launch. Earlier status entries remain historical evidence.

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

Shelf has free-download Android and iOS apps that work as a **Pashto and Farsi digital bookstore and personal library**. Readers discover books from many authors, read a free sample, buy an individual book, and then find it in **My Library**. Downloading the app is free; this does not mean every book is free.

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
- automatic payouts to authors (Version 1 keeps accounting records only).

**Owner amendment — 8 October 2026:** iOS is included in Version 1. Android and
iOS must launch together. Neither may release publicly until both are complete,
accepted and ready, with final owner launch approval. This supersedes the former
Android-only Version 1 scope and all earlier “iOS later” release instructions.
No Apple account, build access, signing, purchase or store readiness is claimed.

## 2.4 Shelf is its own product

Shelf is not part of the Ajmal Aand Professional Platform, System C, Career Command or the Private Assistant. Their documents, roadmaps and approvals do not govern Shelf. The old poetry app (Pitswal) is Shelf’s **technical starting point**, but its name, identity, old roadmap and old approvals do not carry over.

## 2.5 Four work areas

1. Admin/backend.
2. Android app.
3. iOS app.
4. Shared app design and UX/UI.

Reuse shared Flutter code and passed evidence. Agree shared design and UX/UI
improvements before platform completion. Optional improvements remain outside
release scope. Maintain one remaining-work list under these four areas in Part 10.

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

**R11 — Proof before “done”.** Nothing is finished without evidence. Reading and layout are accepted only after testing on real Android and iOS phones for the respective apps. Reuse passed evidence without inferring platform-specific acceptance.

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

- Work in complete practical batches following Part 7, with clear dependencies, shared code and reused passed evidence.
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

- Both languages are first-class on Android and iOS: correct right-to-left layout, suitable fonts, readable spacing, correct Unicode.
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

The numbered steps retain completed milestones and project history. A step closes
only when its evidence exists **and** Ajmal accepts it. The owner-approved
8 October 2026 order supersedes earlier sequential/Android-only priorities:

1. **Inspect iOS readiness:** existing Flutter code, Apple account, Mac/build access,
   signing and purchases. Record verified readiness and missing dependencies.
2. **Agree and improve shared app design and UX/UI.** Agree concrete reader-facing
   changes and reuse shared code; optional improvements remain outside release scope.
3. **Complete iOS and remaining admin/backend work**, using readiness findings and
   agreed design while preserving completed functionality and passed evidence.
4. **Accept both apps together and fix demonstrated issues**, with targeted checks
   and existing evidence; preserve owner deferrals.
5. **Complete both store reviews and coordinate public launch**, with final owner
   approval only after both apps are complete, accepted and ready.

Work in complete practical batches using the one four-area list in Part 10.
Google Play preparation has progressed sufficiently: build 1.0.6 (15) is
owner-confirmed approved for closed testing. Pause further Play preparation until
needed for final release. This documentation task authorizes no implementation,
build, deployment, purchase, provider setting change or release.

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

**Goal:** A reader can browse, search and read samples properly in Pashto and Farsi on Android and iOS, using the agreed shared design.

**Work:**

- Retain completed Android SDK/build setup; inspect and establish iOS build/signing readiness through the first approved batch.
- Word import accepted with a real Pashto Word document read on the phone (6.12).
- Catalogue: authors, categories, search, book pages.
- Samples and the sample/full-book difference.
- Reader: right-to-left layout, fonts, text size, poetry and prose, mixed text.
- Optional audio and agreed offline behaviour.
- Owner preview and publishing checks in the admin.

**The step is finished when:**

- Ajmal accepts reading quality with several authors/books on real Android and iOS phones, reusing passed evidence and checking remaining platform-specific risks.

**Not allowed in this step:** real payments, public release.

## Step 4 — Accounts and purchases (test mode)

**Goal:** A reader creates an account, buys a single book in test mode, and finds it in My Library.

**Work:**

- Reader accounts (D7).
- Shelf’s own RevenueCat project and per-book store purchases: retain completed Google Play setup and inspect/complete App Store purchase integration for iOS.
- Server-side confirmation, My Library, restore, failed payments, refunds.
- Sales and author-share records.

**The step is finished when:**

- Required purchase cases work in sandbox on both platforms, including verified restore and refunds. Second-phone restore remains owner-deferred, not passed and not a launch blocker without a later owner decision.
- Access protection is tested with direct requests.
- Decisions D8–D12 made.

**Not allowed in this step:** real-money sales, public release.

## Step 5 — Ready for release

**Goal:** Everything for a safe public launch is in place.

**Work:**

- Google Play and App Store listing, signing and RevenueCat setup/reviews for joint launch. Preserve approved closed-test build 1.0.6 (15); further Play preparation pauses until needed for final release.
- Privacy policy, support, account deletion, retention (D14).
- Staging copy in place before the first real reader other than Ajmal or the first real payment (6.11).
- Off-server backup and a tested restore; simple operating instructions for Ajmal.
- Final launch catalogue (D16).
- Old poetry app retired.

**The step is finished when:**

- Ajmal accepts both apps together and the admin/backend end to end; both platforms are complete and ready for coordinated release.

**Not allowed in this step:** public release without Ajmal’s approval.

## Step 6 — Public release

**Goal:** Shelf launches publicly on Google Play and the App Store together.

**Work:**

- Release neither app publicly until both are complete, accepted and ready, both store reviews are complete, and Ajmal gives final joint-launch approval.
- Check a live purchase and support, as approved.
- Watch for failures in the first days; keep a recovery path ready.

**The step is finished when:**

- Both apps are publicly live together and approved real-purchase/restore checks work on both platforms.

**Not allowed in this step:** deleting the old app’s baseline.

# Part 8 — When Version 1 is done

- Both apps are fully Shelf: name, Android app ID services.shelf.app, domain shelf.services, own RevenueCat project and per-book store products. iOS identity/signing details are established through readiness work, not guessed.
- The multi-author catalogue works; each book for sale has a price and a working sample.
- Accounts, single-book purchases, My Library and restore work, including refunds and failed payments.
- Pashto and Farsi reading accepted on real Android and iOS phones; optional audio works where supplied.
- The admin is usable and owner-only; sales and author-share records are traceable.
- Access protection verified with direct requests; offline and deletion rules agreed and working.
- Backups on and off the server, with a tested restore.
- No critical defects or open release-blocking decisions.
- Ajmal accepts both apps together; both are complete and ready, both store reviews are complete, and final owner approval authorises coordinated public launch.

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
| D8  | Payment channels and territories | **Amended 8 Oct:** Google Play for Android and App Store for iOS using Shelf’s own RevenueCat project; both platforms launch together. Existing Android 174-region approval retained; iOS availability/readiness must be established, not assumed. |
| D9  | Prices and currencies                 | **Amended 7 Oct:** manual Play Console products/prices for first 100 books; automatic sync DEFERRED, not completed. USD 2.99 starting launch price approved for books 3–8; historical test approval preserved.                                                                                                                                                                                   |
| D10 | Author and rights-holder agreements   | Decided 1 October 2026: owner sets per-book shares in admin; all six current books 100% net received to اجمل اند. See the addendum for book 6 permission and immutable agreement versions. |
| D11 | Withdrawn books already bought        | Open — Step 4                                                                                                                                                                                   |
| D12 | Refund handling                       | Open — Step 4                                                                                                                                                                                   |
| D13 | Offline limits and storage clearing   | Open — Step 3/4. Current state: browsing the catalogue needs internet, so withdrawn or unpublished books are never shown from old saved data.                                                   |
| D14 | Account deletion and data retention   | Deletion/log/backup policy approved 7 October; financial policy approved 8 October: three years after relevant tax-return filing, scoped exceptions and minimum proof for continuing recovery rights. Financial expiry NOT IMPLEMENTED — Step 5                                                                                                                                                                                   |
| D15 | App interface languages and wording | **Decided 9 Oct Review 3:** پښتو، دری، English; Afghan Dari interface implemented in isolation; real-phone wording/layout acceptance pending. Existing account/purchase English policy retained. |
| D16 | Launch catalogue                      | All six books IDs 3–8 selected; owner-confirmed sale rights including covers/any included audio in approved 174 regions; USD 2.99 starting prices — Step 5                                                                                                                                                                                   |
| D17 | Samples                               | **Decided 27 Sep:** admin chooses each book’s free part; no fixed amount.                                                                                                                       |
| D18 | Content management and import         | **Decided 28 Sep:** each book manages its own content; Word import by Heading 1 and \*\*\* (6.12).                                                                                              |

# Part 10 — Current status

## Codemagic environment-group correction — 10 October 2026

Owner confirms Codemagic is connected to **ajmalaand-ship-it/shelf** and has
found root **codemagic.yaml** on **ios/foundation**. Connection/discovery is
OWNER-CONFIRMED, not an independently inspected cloud configuration or build.
Owner authorized this isolated YAML correction, records, commit and branch push.
Continued clean existing checkout **/home/shelf/tmp/shelf-ios-foundation** at
**49a1d53855b6cd05e5c67207aefc38fbafb14baf**; no branch recreation/main update.

Added only **shelf_ios_foundation** to
**workflows.ios-foundation.environment.groups**. Codemagic UI variables must
belong to a group explicitly imported by the YAML workflow; discovery alone
never imports them. Preserved every other parsed configuration value, including
Flutter 3.47.2, Xcode latest, CocoaPods default, mobile working directory,
manual-only unsigned compilation, API guard, artifacts and both false flags.
PyYAML parsing and exact before/after mapping comparison passed; git diff --check
passed. Only codemagic.yaml and this additive Master Record reconciliation
changed. No completed Flutter tests repeated and no native build performed.

**Readiness:** the source configuration now imports the intended variable group
for a first separately authorized unsigned cloud compilation. In the Shelf
application's Codemagic Environment variables, save **SHELF_API_BASE_URL** as
**https://shelf.services/api/** (trailing slash) in **shelf_ios_foundation**.
No other custom variable or Apple signing credential is required by this workflow.
Dashboard variable existence/value/group membership has NOT been inspected or
owner-confirmed. Refresh YAML discovery at the pushed revision and confirm these
settings before any later approved build. Compilation/CocoaPods resolution and
actual cloud toolchain compatibility remain unverified; unsigned .app is neither
an installable signed IPA nor iPhone/App Store acceptance.

**Next practical task:** confirm the dashboard variable/group and refreshed
workflow, then obtain separate owner authorization for the first unsigned cloud
compilation. This supersedes the earlier repository-connection next action only.
No cloud build, paid activation, signing, store upload, deployment, Android rebuild,
provider/secret/production edit or transaction. Checkout **OFF**, automatic pricing
sync **DEFERRED/disabled**, second-phone restore **owner-deferred/not passed**,
financial expiry unimplemented and joint Android/iOS public-launch hold preserved.
Other iOS login/purchase/device and backend work retains its recorded limitations.
Rollback is a normal revert on this isolated branch; no live rollback required.

## iOS foundation interruption reconciliation — 10 October 2026

**Foundation source verification complete; native iOS acceptance remains pending.**
Resumed the existing **/home/shelf/tmp/shelf-ios-foundation**, branch
**ios/foundation**, without recreating the branch or repeating implementation.
Initial HEAD and actual GitHub refs/heads/ios/foundation both matched exactly
**d7f0a4ca10a687ffff989f9e524c0ab62387901e**, containing implementation
**d8d42fbd2fe7652af769df065eb28761baac6070**. Initial isolated git status was
clean; git diff --check and scripts/check_ios_foundation.py passed again.
Remote main remains **72cabf07a953ff41b2191a3b67f7d18accdd6055**; no main update.
Host process names and accessible working directories showed no process using
this iOS checkout or template and no Flutter/Dart/Git foundation operation.
Other existing agent/server processes were left untouched.

Reused the completed scripts/run_tests.sh --mobile evidence above, independently
rechecking its exact SHA-256 **587101f9b3e7e01d9462cc60e6c052c8b0484ca9a0e6f9f4f3792ebb4001eb4e**.
No broad tests or successful shared checks repeated. Removed only
**/home/shelf/tmp/shelf-ios-template** after confirming a non-symlink disposable
Flutter scaffold: no Git repository, stock counter demo/native delegate, generated
project metadata at the documented Flutter revision and only template files.
Removal confirmed; retained the real checkout, source and passing test log.

This additive records-only follow-up leaves native implementation unchanged.
macOS/Xcode compilation, CocoaPods resolution/lock, signing, Apple/Google login,
App Store products/RevenueCat/server verification, sandbox restore/refund,
privacy declarations and real-iPhone reading/native/offline acceptance remain
unfinished as detailed below. No cloud connection/build, paid activation, store
upload, deployment, Android rebuild or production checkout edit performed.
Checkout **OFF**, pricing sync **DEFERRED/disabled**, second-phone restore
**owner-deferred/not passed**, financial expiry unimplemented and joint public-launch
hold preserved. Existing production working records/cPanel handler remain untouched.

**Next owner action:** Codemagic → Add application → connect GitHub with access
limited to **ajmalaand-ship-it/shelf** → select **ios/foundation** → discover the
root **codemagic.yaml**, workflow **ios-foundation**, Flutter directory **mobile**.
Stop after configuration discovery: no Start new build, billing activation,
automatic triggers/publishing or signing-key upload. Connection success is still
pending owner evidence. This completes the interrupted server foundation task,
not the iOS app or joint-launch readiness.

## iOS foundation implementation — 10 October 2026

Owner authorized isolated iOS foundation construction from accepted Shelf source,
with no Android/UI redesign, paid-service activation, cloud build, store upload,
production deployment or Android rebuild. Base **5f5c8f6087d5d908b96558863129b8a09b44fcf8**;
branch **ios/foundation**, checkout **/home/shelf/tmp/shelf-ios-foundation**.
Current working AGENTS/Master Record carried forward additively; production
checkout and its cPanel handler are untouched. Accepted mobile/lib, Android,
assets/fonts, pubspec and lockfile match the base without changes.

**Owner-confirmed Apple setup (not independently inspected):** Individual Developer
membership Active; Team **YSLWJQDH8B**; explicit Bundle ID **services.shelf.app**;
Sign In with Apple selected as primary App ID; App Store Connect record
**Shelf - شیلف**, **iOS 1.0 — Prepare for Submission**. This does not prove signing,
login, compilation, purchase or review readiness.

**Prepared:** missing mobile/ios using installed Flutter **3.47.2 / Dart 3.13.2**
template, Runner workspace/project/shared scheme and UIScene lifecycle; iOS 15.0
minimum; registered production identity/team in all Runner configurations;
Apple-sign-in entitlement and app-scoped Keychain group; CocoaPods integration
alongside Flutter-generated Swift Package integration. No certificate/profile/key
in git. Original supplied square Shelf mark resized to native icon dimensions
with opaque RGB output; supplied logo on cream launch screen; existing Flutter
font declarations/assets unchanged and reused. Background audio and photo-save
purpose configured, no insecure transport exception. Native avatar PHPicker uses
single selected image without broad library access, bounded thumbnail/JPEG without
source metadata; existing external channel opens HTTPS/mailto and returns failure
for Flutter's existing copyable fallback. No new shared screen or source text.

**Cloud configuration:** root codemagic.yaml, workflow **ios-foundation**, mac_mini_m2,
Flutter 3.47.2, manual-only, **no triggering or publishing**. Future separately
approved run resolves locked dependencies, generates native configuration, installs
pods and compiles **unsigned** release .app with checkout opt-in false. API URL is
required as a Codemagic variable, not hard-coded in new source. Unsigned output
cannot install on an iPhone and is not a store-ready IPA. Signing/distribution
requires a later approved workflow and privately managed credentials. Xcode latest
selection remains a future cloud-environment check; no build/provider connection
or credential upload was performed here.

**Server evidence:** scripts/run_tests.sh --mobile, review-3 scope with
PrivacySupportTest smoke: **1 PHP test / 17 assertions, 97 Flutter tests plus
1 staging-identity check** passed. Analysis no errors, **59 existing nonfatal
notices**. scripts/check_ios_foundation.py passes plist/XML, identities/entitlements,
icon dimensions/opacity, unsigned/manual CI and unchanged accepted source checks;
git diff --check passes. Log **/home/shelf/tmp/shelf-ios-foundation-checks.log**,
SHA-256 **587101f9b3e7e01d9462cc60e6c052c8b0484ca9a0e6f9f4f3792ebb4001eb4e**. These are source/shared behavior checks, NOT Swift/Xcode
compilation, pod resolution, signing or native/iPhone acceptance. Tests use isolated
temporary storage/database; test workspace cleanup passed. No Android build.

**Unfinished:** Apple login Flutter/native flow, backend Apple token/nonce/audience
verification and explicit account-linking policy; iOS Google OAuth client/reversed
URL scheme and native login validation (Android settings are not reused); App Store
per-book products/agreements/availability, separate appropriate RevenueCat Apple
configuration, platform-aware server receipt/event verification, restore/refund
and sandbox acceptance. Existing SDK requires goog_ and server PLAY_STORE:
App Store payments are NOT implemented, no Apple receipt can grant access through
this foundation. Email/password is shared source only, not yet iPhone-verified.
Native photo/save/share/audio, Keychain reinstall/sign-out/account isolation,
offline protection, RTL/fonts/layout/accessibility and device reading require
macOS/iPhone checks. Final plugin privacy manifests/required-reason APIs, actual
collected data, export-compliance declarations and App Store disclosures must be
reviewed after native dependencies compile; no invented declaration saved.
Provisioning/signing and TestFlight/App Store review remain unfinished.

**Repository connection:** existing origin is
**git@github.com:ajmalaand-ship-it/shelf.git** (private), browser repository
**https://github.com/ajmalaand-ship-it/shelf**, branch **ios/foundation**, Flutter
project directory **mobile**, root YAML **codemagic.yaml**. Remote main was inspected
at **72cabf0**, behind the accepted local base; main is not merged/pushed by this task.
The iOS branch carries the accepted source and newer governing records.
**Next single owner action:** in Codemagic Add application, connect GitHub with
access to only this private Shelf repository, select **ios/foundation**, and scan
for **codemagic.yaml**. Stop after connection/configuration discovery; do not Start
new build, activate paid billing, enable triggers/publishing or upload Apple keys.
Connection instructions follow the official Codemagic Flutter YAML guide linked
in IOS_FOUNDATION.md; cloud UI/connection success remains owner evidence pending.

Checkout **OFF**, automatic pricing sync **DEFERRED/disabled**, second-phone restore
**owner-deferred/not passed/not a new blocker**, financial expiry unimplemented and
joint Android/iOS public-launch hold preserved. No staging/backend deployment;
this native source-only task explicitly authorizes none. Before any later backend
change/promotion, follow existing staging/identical-commit/backup gates. Rollback
is discarding this isolated branch or reverting its source commits without touching
production, Android artifacts, database, provider settings or accepted content.


## Review 3 owner acceptance and Android/UI pause — 10 October 2026

**Owner accepts the current Shelf Review 3 work. Further Android/UI changes are paused until separately authorized.** This is acceptance of the current work, not evidence that the owner individually tested every control, not Play-installed build-20 phone acceptance and not completion of all platform or launch requirements.

**Google Play status — owner report:** version **1.0.11 (20)** is approved for **Closed testing – Alpha**. The latest screenshot showed **“Changes ready to publish”** with **Managed publishing ON**. **Publishing completion is NOT CONFIRMED; Play-installed build-20 phone acceptance is NOT CONFIRMED.** Approval/readiness does not establish publication, tester availability, installation or phone acceptance. No independent Console inspection or store action was performed in this documentation task.

**Preserved:** completed exact Review 3 backend promotion **5f5c8f6087d5d908b96558863129b8a09b44fcf8**, backups, nullable avatar migration and completed verification below; existing AAB and implementation unchanged. Checkout remains **OFF**. Automatic pricing sync remains **DEFERRED/disabled**, with no 403 retries. Second-phone restore remains **owner-deferred, NOT PASSED**, and not a new launch blocker without a later owner decision. The **joint Android/iOS public-launch hold** remains binding; closed-testing approval is not public-release authorization. Other unfinished backend/iOS/acceptance requirements retain their recorded status.

**Scope of this update:** Master Record only. No implementation, tests, rebuild, deployment, provider/store change, publication, commit or push. Earlier next-action and pending Review 3 acceptance statements are historical where superseded by this entry; they do not authorize resumed Android/UI work. Publishing completion and Play-installed build-20 acceptance must be recorded only when actual evidence arrives.

## Review 3 backend production promotion completed — 10 October 2026

**BACKEND READY FOR GOOGLE PLAY TEST-TRACK UPDATE.** Owner explicitly approved
executing the prepared 5f5c8f6 promotion, checkpoint/backups and nullable avatar
migration. Both production and staging release-state now identify exactly
**5f5c8f6087d5d908b96558863129b8a09b44fcf8**, including correction 5821c42.
Production git HEAD is that tested candidate; subsequent record-only changes do
not substitute a different backend release. Previous code HEAD/rollback target
**72cabf07a953ff41b2191a3b67f7d18accdd6055** (application release 34799b8).
The prepared approval-pending entry below is historical and now superseded.

**Preservation and verified backups:** before document preparation, normal
production backup **/home/shelf/backups/shelf/20261010-050738/** verified SQL,
media ZIP integrity and SHA-256. Its **working-record-checkpoint/** contains exact
six-file copies/checksums for the five dirty governing/release documents plus
pagination plan and cPanel handler, git status/full diff, and private aggregate
catalogue/history fingerprints (no source/personal rows printed). Only the four
actually dirty tracked documents were restored to HEAD; the untracked pagination
plan was moved into that checkpoint. The documented list described these as five
tracked documents, but pagination was untracked and handled separately. No other
file cleanup/stash/reset. Promotion's second fresh verified production backup:
**/home/shelf/backups/shelf/20261010-050852/**, containing promotion-notes.txt and
previous revision. SQL/ZIP/SHA-256 passed before maintenance/migration.

The exact tested candidate was fast-forwarded through the recorded workflow;
same-lock staged dependencies copied, caches/autoload/assets refreshed; production
purchase/accounting/owner-share checks and HTTPS health passed. Maintenance lifted.
Only new migration **2026_10_09_120000_add_private_reader_avatar**, production
**batch 26**, adds nullable readers.avatar_path; no backfill or first-line schema
change. No pending migrations remain. No SQL/media restore, data repair or rollback
needed. Workflow history fingerprints proved all prior reader, purchase, refund,
ledger and agreement rows unchanged, with only null new avatar column excluded.

**Production evidence:** actual runtime confirms 3 avatar routes, column/migration/
first-line method present; staging same. Both real checkout=false,
play_sync.enabled=false/deferred=true. Direct production HTTPS catalogue verified
**6 books / 263 returned summaries / 98 bounded previews** against independent
first-nonempty source-line hashes (first returned page per book, not all 342 works).
No summary body or locked non-sample excerpt; six separately requested paid details
remain locked with body/excerpt null and no first_line. Paid sole-line withholding,
240-character bound and draft/hidden/source-preservation edge cases reuse exact
candidate's passing staging **7 tests / 77 assertions**; no broad/mobile rerun.
Production HTTPS avatar anonymous access 401, per-reader-path and public photo paths
404/403; admin login/privacy 200. No paid detail/media access relaxation.

Live production-kernel route probe uses the actual production configuration,
DB/storage with two disposable synthetic readers/tokens in a rolled-back transaction;
no existing account modified, no registration mail or provider transaction. Proved
own-account upload/read, JPEG/private storage, no-store, stable older profile fields,
no avatar_path in response, another reader cannot read/delete first reader's photo,
replacement deletes old file, removal clears pointer/file. All synthetic rows/tokens
rolled back and task-created photo directories removed. Normal allocation counters
can advance from rolled-back synthetic inserts; existing IDs/rows/content unchanged.
Authentication/lifecycle used the production HTTP kernel; anonymous/public-file
protection also checked over real HTTPS. This does not claim Android phone picker
or Play-signed sign-in acceptance.

After probes, independent aggregate fingerprints matched EVERY existing collection,
poem/source text, author, credit, reviewer grant, reader/token/account action,
purchase/event/entitlement/consent, sales/agreement/accounting row to the pre-promotion
checkpoint. Exact original cPanel handler SHA-256 unchanged:
**922984f743b00f460365a2f040db9591ab599911bccba902e952946195f8fa42**.
D14/Play records and pagination plan match checkpoint bytes; candidate AGENTS is
verified to retain every original instruction plus prior Review 2 evidence, and
newer Master Record preparation entry restored before this additive completion.
Old clients continue explicit profile/catalogue/sample/paid APIs; only optional
has_avatar/first_line fields added. Current AAB can now use its backend features.

Evidence scripts/logs: **/home/shelf/tmp/shelf-backend-promotion-20261010/**.
avatar-probe.log SHA-256
**c828d6c00c1483dd033c39749f0bc7e382d3c11f6ce27fb0a1b7614781fed255**;
verify-production.log SHA-256
**130412ca0f9bc1ac4dbc62f327baad20ff232903beb6840e072a1951122f9fcb**;
final-check.log SHA-256
**4ee28333542cd2f30d5f3b2f5a3b7464a8be5ad68ec7b19861cba9ed88c19f63**.
The staging evidence SHA-256 remains 903b061ea32ff1a0a1a7c2a3a57e1fabe33a217c489c6617ca357c1727ffd02e.
Preparatory helper initially named the credits table incorrectly, then used an
unavailable Python hashlib.file_digest helper; corrected only the task checker.
An attempted promotion before preparation completed was refused by the dirty gate
before backup/maintenance/deployment. Checkpoint completed, then exact promotion
passed. These were task-helper/gate stops, no live application/data fault or repair.

**Artifact and boundaries:** existing Shelf **1.0.11 (20)** AAB unchanged, checksum
rechecked **5afef9e0eb3228e8e7df8ea81164f4cb0445fbf49df7e8229759bae1475f60d6**;
exact private path in signed-AAB entry below. No rebuild, Test APK, Google Play
upload, provider setting, real payment, main push or public publication. Checkout
OFF, pricing sync DEFERRED/disabled, second-phone restore owner-deferred/not passed,
financial expiry unimplemented and joint Android/iOS public-launch hold preserved.
Phone/iOS acceptance and second/cover-photo purpose remain as previously recorded.
**Next:** separately authorized Google Play test-track update using the unchanged
verified AAB; backend dependency resolved. “Live” still means test track only.
No owner decision is needed to complete this promotion; store upload is separate.


## Review 3 backend promotion prepared — 10 October 2026

**READY FOR OWNER PROMOTION APPROVAL; PRODUCTION NOT DEPLOYED.** Owner authorized
backend preparation/staging verification for existing Shelf 1.0.11 (20), including
5821c42, and explicitly required stopping before production. No AAB rebuild,
Play upload, production migration/deployment, account/data or provider changes.

**Revisions:** initial staging 9a748ef2022ef274eddcf1941269e35bafa8fb14;
production release-state remains **34799b8efc93351767c05ecd516711a5badb6c0c**.
Production git HEAD remains **72cabf07a953ff41b2191a3b67f7d18accdd6055**;
the difference from release-state is AGENTS/Master Record documentation only.
Production application/backend files match 34799b8. Existing dirty AGENTS.md,
D14_DATA_POLICY.md, MASTER_RECORD.md, PLAY_RELEASE_REVIEW.md and cPanel
public/.htaccess, plus untracked PAGINATED_READER_PLAN.md, are preserved.
Latest isolated source continued d68f830 on ui/figma-review-2, never restarted.

**Exact staged/proposed promotion commit:**
**5f5c8f6087d5d908b96558863129b8a09b44fcf8**. This retains completed mobile source
without rebuilding/installing an app. Runtime backend delta is ONLY ten files:
AvatarController, Reader/ReaderAvatar/AccountActions, private filesystem disk,
authenticated routes, photo exception-log exclusion, reversible avatar migration,
Poem::catalogueFirstLine and PoemSummaryResource first_line. Composer lock and
unrelated production backend/purchase/accounting/privacy code unchanged.
Origins: avatar c2f402546ea019ef1a8bc695df6710b7f6f5f87f;
first-line 3a787ee; guard **5821c42ba4cc75eb96fd36a0172847dafe29c9aa**;
focused gate/history safeguard fb51755439de4325c90c79f9080d18aac6c54fdf;
5f5c8f6 preserves newer production financial-policy/Play-review records that
were missing from the development branch. Development AGENTS retains all current
production instructions plus its existing Review 2 evidence entry.

**Staging evidence:** intermediate fb51755 passed, then final exact 5f5c8f6
passed at **04:59:10 UTC**, review-3-backend-dependencies scope, mobile_tests=false.
Fresh verified SQL/ZIP/SHA-256 staging backup before final deployment:
**/home/shelf/backups/shelf-staging/20261010-045855/**; previous staging releases
retained. Existing avatar migration already applied on staging, no new migration
there. scripts/run_tests.sh: **7 tests / 77 assertions** (ReaderAvatarTest,
CatalogueFirstLineTest, PrivacySupportTest), temporary database only. Protected
paid detail, draft/hidden denial, exact source/title preservation, 240-character
bound, complete sole-line paid-body denial, private avatar lifecycle/cross-account/
invalid uploads/deletion and privacy checks passed. Direct staging isolation,
rolled-back synthetic accounts, private gate/noindex/banner, catalogue/cover,
locked paid detail, denied webhooks/private files and healthy production passed.
Check log /home/shelf/staging-runtime/checks-5f5c8f6087d5d908b96558863129b8a09b44fcf8.log
SHA-256 **903b061ea32ff1a0a1a7c2a3a57e1fabe33a217c489c6617ca357c1727ffd02e**.

Additional actual staging HTTPS: **6 books / 263 summaries / 98 non-null previews**
from the first returned page per book (not a claim of all 342 works), exact
first-nonempty-source-line hashes and bounds independently compared; no summary
body or locked non-sample excerpt. Runtime staging: 3 avatar routes, column and
migration present, first-line method present. Production: 0 avatar routes, column/
migration/method absent. Both: checkout=false, sync=false, deferred=true.
/home/shelf/tmp/shelf-backend-prep-20261010/verify.py and verify.log retained;
log SHA-256 **69a031a74f2e5cffe176122caf9d1be01c7e1a2f3ac41c43fa26020c3ac02f74**.
Verifier initially used nonexistent is_free instead of is_free_sample; corrected
and passed, no server defect or repair. Avatar backend files are byte-identical
to staging 9a748ef: reuse actual authenticated upload/read/no-store/cross-account/
replace/remove probe and cleanup evidence from 9 October, rather than repeat
real database/file mutations. Prior probe log SHA-256
ecc4fba60ef7a5a1def02ac600b7bfac2261bc7c73175c434f6604f2f75ec0c0.
Completed mobile/render/AAB, purchase, backup and provider evidence reused.

**Compatibility:** additive optional first_line and has_avatar fields; older app
versions ignore unknown JSON fields and continue account/sample/paid-access APIs.
Build 20 accepts absent first_line and defaults absent has_avatar=false, so an
old backend or code rollback remains parse-compatible but lacks new avatar and
locked-title features. New first_line is public catalogue-only metadata for blank
titles, max 240 characters; sole non-sample nonempty paid line withheld. No source
rewrite, ID/order/title changes or paid detail/media/ownership relaxation. Avatar
routes require own authenticated reader, private bounded/reencoded JPEG/no-store;
no public profile/path. Upload/deletion lifecycle requires migration first.

### Concrete production plan — only after separate owner approval

1. Recheck production HEAD/release-state, staging REVISION/check-log SHA-256,
   migrations and gates. Refuse drift from the revisions above; no refresh or
   catalogue/data copying. Confirm only avatar migration is pending. Recheck
   production current file fingerprints against preservation checkpoint.
2. Before any checkout cleanup, run the normal verified production backup
   scripts/shelf_daily_backup.py in production; record its actual dated path under
   /home/shelf/backups/shelf/. Verify SQL, media ZIP and manifest checksums, including
   private files. Independently save the five dirty governing/release documents,
   untracked pagination plan, full git status/diff and exact public/.htaccess bytes
   with checksums in a private preservation subfolder of that backup. Backup script
   covers DB/media, NOT a substitute for this working-file preservation checkpoint.
3. After checksum verification only, restore the five tracked document paths to
   production HEAD and move the untracked pagination plan into that checkpoint
   (candidate already contains the identical plan). Do not restore/edit .htaccess.
   No stash/reset of unrelated files. This removes the current documented dirty-gate
   blocker reversibly; any additional changed path stops the promotion for review.
4. From /home/shelf/tmp/shelf-figma-review-2 run the existing approved workflow:
   `python3 -B scripts/staging/workflow.py promote 5f5c8f6087d5d908b96558863129b8a09b44fcf8`
   It revalidates exact staged evidence/ancestry/cPanel handler, makes a SECOND fresh
   verified production backup in the same run, then down, fast-forward identical
   commit, copy tested same-lock dependencies, dump-autoload, optimize:clear,
   migrate --force, Filament assets, purchase/accounting/history checks; up always
   in finally; owner-share/HTTPS health and release-state record only after success.
   NO checkout/config/provider/scheduler changes, main push or store operation.
5. Only pending migration is
   **2026_10_09_120000_add_private_reader_avatar.php**: add nullable string
   readers.avatar_path; no backfill/data edits. Record actual production batch.
   No schema/migration for first_line. History guard ignores only a null new
   avatar_path in reader fingerprints; all preexisting reader fields, any non-null
   avatar and money/access/agreement rows remain checked. This avoids interpreting
   the additive null column as a reader-data rewrite without weakening other checks.
6. Reconcile preserved document checkpoint after promotion: D14/Play files already
   exact in candidate, pagination plan exact; retain candidate AGENTS superset and
   restore this newer Master Record additively. Verify every unrelated instruction/
   note and .htaccess checksum. Recheck 3 routes/column/migration, catalogue preview/
   locked paid-detail denial and checkout OFF/sync OFF, plus ordinary existing app
   profile/catalogue compatibility. Record production backup/batch/checks before
   declaring backend ready for a separately authorized Play test-track upload.

### Rollback and stop conditions

Previous production CODE checkout **72cabf07a953ff41b2191a3b67f7d18accdd6055**
(application release 34799b8), not an older historical backend. Preserve checkpoint
and both verified backups. If backup/checkpoint fails, do not enter maintenance or
clean files. If a live step fails, lift maintenance and STOP/report; no silent fix
or automatic SQL restore. After approval for rollback, checkpoint any newer docs,
return code to previous commit without rewriting history, clear caches/autoload
(same Composer lock), restore preserved docs/cPanel handler and check health/gates.
Prefer **code-only rollback with avatar column and private files retained**: old
code tolerates nullable extra column; new feature is temporarily unavailable.
Do not overwrite later reader/payment/activity with pre-promotion SQL/media.
Migration down explicitly drops avatar_path; it is reversible structurally but
would discard pointers after uploads. Use it only under separately approved
preservation review after proving no new avatars need retention; never a blanket
migrate:rollback of unrelated batches. Retained avatar files remain private;
account deletion during rollback must be reconciled before any later avatar
reactivation, so deleted-account photos are not resurrected. First_line rollback
needs code only. Staging previous release remains available; no refresh performed.

**Preserved artifact/boundaries:** AAB 1.0.11 (20), exact path/checksum in the next
entry, untouched; no rebuild/upload. Checkout OFF; pricing-sync DEFERRED/disabled;
second-phone restore owner-deferred/not passed/not a new blocker; financial expiry
not implemented, second/cover-photo purpose unresolved, Android/iOS device
acceptance pending and joint public-launch hold unchanged. “Live” means Play
test-track only. This entry is a records-only follow-up after the exact staged
candidate; do not substitute its later documentation commit for the tested
promotion commit. Owner decision needed: approve the concrete production backend
promotion and reversible document preservation above, not Play upload/public launch.


## Review 3 signed production AAB — 10 October 2026

**BUILD AND RELEASE CHECKS PASSED.** Owner-authorized corrections completed from
latest Review 3 development, with sample wording incorporated before building:
**بېلګه / نمونه / Sample**, selected interface language only. Blank wording issue
is **RESOLVED**. “Live” means a **Google Play test-track update**, not public release;
joint Android/iOS public-launch hold remains. No Play upload/Test APK/deployment.

**Immutable AAB source:** 94c0ca014d0d9074bb8bf08714777db8b4202886, including mobile
**a019b00d3429f8e8440056cfc2b510026594cba9**, all completed Review 2/3/pagination/
avatar work and latest records. Backend-only final protection commit
**5821c42ba4cc75eb96fd36a0172847dafe29c9aa** followed artifact preparation:
first-line catalogue preview is bounded to 240 source characters; a sole paid
nonempty line is withheld because it would expose the complete paid body (localized
Untitled fallback remains). Multiline first-line preview and source bytes unchanged.
No mobile tree difference from immutable build source, independently confirmed;
no rebuild needed for this backend-only correction, which is NOT deployed.

**Artifact:** production flavor **Shelf 1.0.11 (20)**, services.shelf.app,
https://shelf.services/api/. Next unused code 20 after retained shared artifact
codes 14–19; version set through build arguments, pubspec/lockfile unchanged.
Exact private path:
`/home/shelf/apps/shelf/storage/app/private/owner-aabs/shelf-review-3-production-1.0.11-20-20261010-040052.aab`
SHA-256 **5afef9e0eb3228e8e7df8ea81164f4cb0445fbf49df7e8229759bae1475f60d6**;
**63,122,418 bytes**, mode 0600, matching private JSON sidecar. Saved
10 October 2026 04:00:52 UTC / 00:00:52 local. Existing upload certificate retained:
SHA-1 DA:24:FF:12:6D:3A:D7:D2:83:E3:A3:36:B6:8A:A9:03:50:42:38:CB;
SHA-256 2065572ecb0f174a9e669602232ffcea31335303d3ff011caaeb7216fdb69e77.
No package/signing/OAuth/credential change; separate staging Test badge remains
in source, production launcher references only unbadged supplied Shelf assets.

**Packaged verification:** compiled manifest version/package/Shelf label/non-debuggable;
ZIP CRC; jarsigner verification; independent verified read of **all 528 payload
entries** using JarFile against the exact recorded upload certificate; production
endpoint in all native app libraries; no staging endpoint/Test label/banner/access
marker; no Test launcher resources in AAB; all **84 supplied logo/action PNGs** and
three original fonts exact. **20 packaged launcher PNGs** match alpha/visible pixels;
adaptive and legacy **circle/rounded-square/squircle** renders inspected from actual
packaged resources, no white surround. No internal-test checkout opt-in/build token.
Jarsigner warnings retained: self-signed/untrusted chain, missing timestamp, POSIX
attributes and streaming archive-order/manifest ordering. JarFile payload verification
passed independently; no repacking/signature replacement or Google approval inferred.
Verifier initially assumed source density paths without Android-added -v4; corrected
and passed on this same AAB, no rebuild. Task build workspace cleaned; recreated
task-only Gradle registry removed and absence confirmed; archived source/logs retained.

**Focused evidence:** 104 Flutter checks + identity check and final 31 reader/render
checks + identity; PHP final **7 tests / 77 assertions**, including full single-line
paid-body denial and bounded preview. Renderer-generated **1080×1350** light/sepia/
dark cards inspected (correct contrasting supplied logo, title when selected,
bold author, localized page digits). Reader/Contents/sharing three-language/narrow/
short renders inspected, with real bundled fonts/icons; no duplicate bilingual
Sample/Coming soon labels. Initial harness/fixture failures fixed; no live repair.
Final backend log /home/shelf/tmp/shelf-review3-backend-final-checks.log SHA-256
8d8d3e538787239f3cf9932ff053f1adf2fd06e43616445baa0d8e7f8ee54275.
AAB logs/scripts and packaged masks: /home/shelf/tmp/shelf-review3-aab-20261009/;
verify-production.log SHA-256 f744eed9e8694c3ccd7ad0222c9962275a6210204f8f48a655d14080c2b0ee6d;
packaged-launcher-masks.png SHA-256 af0b9bb8d720a48877f70ab8007ccb75078ad9fd1a015b0b947b722659f1df2a.
Earlier correction/render log hashes and preserved completed evidence remain below.

**Required before test-track rollout:** separately authorize staging verification
and identical tested-commit production promotion of existing private-avatar routes/
2026_10_09_120000 reversible migration plus new protected first_line metadata and
single-line guard, with verified backup. Production remains **34799b8**: actual
avatar routes=0, avatar column absent and first-line method absent. Staging remains
**9a748ef**, avatar column present; new first-line addition not staged. Current
production responses parse compatibly but cannot supply locked first-line titles
or support avatar add/remove; do not roll out as though those features are deployed.
Synthetic/packaged checks do not establish Android phone or iOS acceptance.
Real phone review remains pending; second/cover-photo purpose still unresolved.
No new wording decision needed. Checkout OFF; manual first-100-book pricing,
automatic sync DEFERRED/disabled, second-phone restore owner-deferred/not a new
blocker, financial expiry absent and joint public-launch hold preserved. No main
merge/push, provider edits, production setting/database/content changes or transaction.
Next: owner downloads the AAB to Windows Downloads\Shelf-Review-3. Backend promotion
and later Play test-track upload require separate authorized tasks; no upload now.

## Review 3 final corrections and production AAB authorization — 9 October 2026

Owner authorized continuing latest development 1e193b4, preserving all completed
Review 2/3/pagination/avatar work. Backend source **3a787ee** adds catalogue-only
`first_line` for null/blank titles. UI source **a019b00** corrects launcher legacy
resources, icon-only font control, sharing editor, counters, Contents and author
weight. All source/title/ID/order/access rules remain unchanged.

**Owner clarification is binding before build:** Pashto **بېلګه**, Dari **نمونه**,
English **Sample**, including related phrases and partial-sample notices, selected
interface language only. Previously blank requested sample wording is **RESOLVED**.
Exact **د لوست سیټینګ** retained. “Live” means **Google Play test-track update**,
not public release; joint Android/iOS public-launch hold remains binding. No new
launch confirmation needed for this clarification; final joint-launch approval
still required before any public rollout. Second/cover-photo purpose remains pending.

**Implemented:** supplied opaque square legacy launcher assets replace transparent
rounded/circle resources (xxhdpi derived from supplied square at required density);
supplied adaptive foreground/background/monochrome preserved. Staging adaptive
icon retains the same mark and red T badge; package/signing identities unchanged.
Six local launcher mask simulations (circle/rounded square/squircle, adaptive and
legacy) checked opaque brown rims/no white surround. Actual OS/phone acceptance
is pending. Top font control contains only supplied ب ب glyph, localized tooltip/
accessibility retained. Sharing uses smaller localized Create card heading, preview
at full available width in pinned scrollable viewport (52% height), expanded view
retained, independent initially unchecked title and supplied contrasting logo.
Exporter waits for logo decoding before raster capture. Previous reader share icon
Icons.ios_share_rounded restored; improved editor retained. Four-consecutive-line/
approved-readable-text export limits and attribution unchanged. Both sides of line/
page counters, font sizes, search/book counts and audio time/progress localize digits.
Untitled Contents uses exact first nonempty line as display fallback; reader retains
poetry glyph. Actual titles/stored text are not changed. Quiet 13px Sample/Contents/
locked notices, single-language Coming soon/credit labels and bold displayed authors.

**Focused checks:** scripts/run_tests.sh --mobile: 104 Flutter + 1 required identity
check; then 31 reader/render + 1 identity checks for final Contents bundled-font
correction. Temporary-database CatalogueFirstLineTest/ReaderAvatarTest/PrivacySupportTest:
6 tests / 69 assertions. Draft/hidden/paid-body denial, only necessary first-line
metadata, original stored bytes, title selection, small/short screens at 2x,
pagination/source/access regressions and actual 1080×1350 exported PNGs passed.
Full scoped analysis had no errors; 58 pre-existing notices plus one subsequently
removed interpolation notice. PHP syntax and git diff --check passed. Initial
synthetic-fixture, outdated-expectation and render-harness failures fixed before
passing runs; no live failure/data repair. Logs: /home/shelf/tmp/shelf-review3-final-checks.log
SHA-256 c9484165d331765e2ffb349ae1fc2c8e042eee0df144d04d29f9175d3547fda7;
/home/shelf/tmp/shelf-review3-final-render-checks.log
SHA-256 6c659a5d2777c3a79df4626bed22238ea83e650335fb70b5cc68831364c2037c.
Renders inspected: /home/shelf/tmp/shelf-review3-final-renders/ (three interface
languages, reader/Contents, narrow/short sharing, three exported palettes, masks).
These synthetic renders do not establish real-phone or iOS acceptance. Unrelated
completed purchase/backup/provider evidence reused.

**Production compatibility / rollout dependency:** release state read-only remains
production **34799b8**, staging **9a748ef**. Actual production has zero avatar routes,
no readers.avatar_path column and no catalogueFirstLine method. Existing catalogue
responses parse compatibly; missing first_line falls back to approved excerpt or
localized Untitled, and avatar features cannot function fully until promotion.
Before test-track rollout, separately authorize staging verification/promotion of
these backend additions plus existing reversible 2026_10_09_120000 avatar migration,
with verified production backup and identical tested commit. No production deploy,
migration or data change in this task, no staging deployment silently performed.
Production real checkout=false, play_sync.enabled=false, deferred=true.

**Authorized artifact preparation now:** production flavor, services.shelf.app,
existing Shelf upload certificate, https://shelf.services/api/, next unused shared
version **1.0.11 (20)** after retained codes 14–19; version via build arguments only.
No Test APK, Google Play upload, production deployment or checkout enablement.
Exact AAB path/hash and packaged verification will be added after successful build.
All existing pricing-sync/second-phone/financial-expiry deferrals and joint hold retained.

## Corrected Review 3 Shelf Test phone APK — 9 October 2026

Owner authorized build-only preparation from latest clean development source
**a66864e0f128bb0f137a65bdb72f021cca4553c2** (a66864e), including correction
**340142b43cd42047e7bd76c0718ce1904ca87768** (340142b). No newer work was
omitted. Immutable git-archive source and disposable build workspace; version
supplied through build arguments, no source/pubspec/lockfile changes.

**Artifact:** Shelf Test **1.0.10 (19)**, services.shelf.app.staging,
https://staging.shelf.services/api/. Next unused code 19 confirmed against retained
staging APK versions 12/13/16/17/18. Exact private path:
`/home/shelf/staging-runtime/storage/app/private/test-apks/shelf-review-3-corrected-test-1.0.10-19-340142b-20261009-234648.apk`
SHA-256 `576419c4f4b3758276675493a49a81f3d59eeaaa825d77a53fa1d749d1e1657f`; **62350628 bytes**, mode 0600,
matching private JSON evidence. Same verified signing certificate SHA-256
2065572ecb0f174a9e669602232ffcea31335303d3ff011caaeb7216fdb69e77
as Shelf Test build 18; same package and increasing code enable in-place update
without uninstalling or clearing data. Actual installed data retention remains
pending owner phone observation. Existing Play Shelf stays separate.

**Packaged checks passed:** release signature, manifest version/label/package,
non-debuggable mode, ZIP CRC; staging endpoint/Test banner in every native app
library; compiled exact **د لوست سیټینګ** with old phrase absent; localized
untitled-poem accessibility labels, poetry action asset and local-script digit
mapping; all 84 original logo/action PNGs byte-identical, original three fonts,
pagination/pinned previews and native avatar channel. Manifest launcher points
to supplied Shelf mark plus red Test badge; all **20 retained launcher density
PNGs** match original alpha/visible pixels and adaptive background/foreground/
monochrome references resolve. Android shrank unreferenced round-icon resources
and optimized invisible transparent RGB values; verification accounts for these
without rebuilding or changing the mark. Actual titled/untitled distinction and
display-only digit behavior use completed unchanged-source test evidence.

**Evidence reused:** 39 focused correction Flutter tests + 1 staging-identity
test, required PHP smoke 1 test / 17 assertions, scoped analysis no issues;
no unrelated suite repeated. Reused correction-log SHA-256
f6450997169f639afc6c38547256d135a5cf18942d71c97c4105669c133adfb1.
Build/verification scripts/logs: /home/shelf/tmp/shelf-review3-corrected-apk-20261009/.
Task build workspace cleaned, including any recreated task Gradle cache; secrets
stay private. Master Record updated additively, newer/unrelated records preserved.

**Pending/boundaries:** corrected Android phone acceptance PENDING, iOS validation
NOT VERIFIED, second/cover-photo purpose pending clarification. Staging backend
remains 9a748ef; no backend deployment/migration, production AAB, store upload,
production setting/data/source change or transaction. Checkout OFF; pricing sync
DEFERRED/disabled and second-phone restore owner-deferred. Next: download this APK
into Windows Downloads\Shelf-Review-3, update Shelf Test in place and review the
four corrections, retained settings/positions and existing previews/photo controls.
Then a separately authorized Google Play test-track update; public Android
release stays held for complete/accepted iOS and owner-approved joint launch.

## Review 3 focused owner-feedback correction — 9 October 2026

Owner authorized four source-only corrections, continuing latest development
208f544 and completed Review 3 c2f4025/c25e230 without discarding newer records.
**Source commit: 340142b43cd42047e7bd76c0718ce1904ca87768 (340142b), ui/figma-review-2.**

- Exact Pashto reading-settings title/entry/tooltips now **د لوست سیټینګ** through
  the shared localization string; no remaining old phrase in mobile/lib.
- Original shelf-icon-pack.zip Android launcher resources applied: density PNGs,
  adaptive/round/monochrome layers (27 resources byte-identical to the supplied
  pack). Shelf Test composes the same mark with a red T badge; existing Shelf Test
  label/banner, staging package suffix, production ID and signing remain unchanged.
- Genuinely untitled poems (null/blank stored title) show the supplied shelf-poetry
  glyph in Contents and reader; localized accessibility labels identify an untitled
  poem in Pashto/Dari/English. Actual titles remain source text, with no first line
  promoted to a title. Independent icon semantics prevent list-row label merging.
- Contents/list order numbers render **۰۱۲۳۴۵۶۷۸۹** in Pashto/Dari and ASCII
  **0123456789** in English. Display-only conversion; no ID, sort order, stored
  title/text, content mutation, migration or model serialization change.

**Focused evidence:** scripts/run_tests.sh --mobile, review-3-correction scope:
**39 Flutter tests + 1 staging-identity test**, required PHP PrivacySupportTest
**1 test / 17 assertions**. Exact pinned settings heading, three-language list
numbers/untitled semantics/real titled entry, native source package/signing/Test
identity checks, original reader source/locked behavior and pinned settings
regressions passed. Scoped Flutter analysis **no issues**; XML parsing, original
launcher-resource byte checks and git diff --check passed. Initial semantics
expectations failed; independent icon nodes and a semantics-enabled test frame
corrected the issue before final passing run. Log:
/home/shelf/tmp/shelf-review3-correction-checks.log. Static identity/resource checks
are not a newly built artifact or launcher acceptance on a phone.

**Preserved:** pagination engine and saved positions, sharing, avatars and access
controls unchanged. Prior installed/available Shelf Test 1.0.9 (18) from 9a748ef
has these corrections NOT PACKAGED; staging stays at 9a748ef, production at
34799b8. No new APK/AAB build, deployment, live database change or store upload.
Current governing records updated additively, preserving unrelated/newer edits.
Checkout OFF; pricing sync DEFERRED/disabled; second-phone restore owner-deferred;
second/cover-photo purpose pending clarification; iOS acceptance still unverified.

**Owner-approved next sequence:** corrected phone review (a separately authorized
fresh compatible Shelf Test build is needed), then Google Play **test-track**
update. No test-track upload authorized in this correction task. Public Android
release remains held until iOS is complete/accepted/ready and the owner approves
joint Android/iOS launch. Phone acceptance of the corrected wording, launcher,
untitled glyphs and numbering remains PENDING.

## Review 3 staging and Shelf Test phone-review APK — 9 October 2026

Owner authorized staging-only preparation and a compatible separate Shelf Test
update, retaining completed Review 3 c2f4025/c25e230 and all newer records.
Immutable deployed/build source: **9a748ef2022ef274eddcf1941269e35bafa8fb14** (9a748ef),
which adds only the focused staging gate to completed Review 3. Mobile source
remains **c25e23010a77fcf87618ee8bb0be68c6fa1b08b2**. Existing pagination/pinned
reading preview and unrelated edits retained; no main merge/push.

**Staging preflight and deployment:** actual connected database **shelf_staging**,
canonical storage **/home/shelf/staging-runtime/storage**, separate from production;
mail log, queue null, purchases/production checkout/pricing sync false. Only pending
migration was 2026_10_09_120000_add_private_reader_avatar.php. Established workflow
made and verified SQL/media/checksum backup
**/home/shelf/backups/shelf-staging/20261009-224852/** before migration/deployment.
Avatar column is now present on staging; production shelf_app has no avatar column.
Production deployed revision remains 34799b8 and source/settings/data untouched.
Reversible migration/code rollback requires reviewing intervening staging activity;
prior release and verified staging backup retained. No catalogue refresh.

**Staging evidence:** focused ReaderAvatarTest **4 tests / 35 assertions** passed
at the deployed commit; private storage/session/cache/database isolation, logged
mail/disabled queue, blocked sync, real HTTPS gate/noindex/TEST COPY/catalogue/
locked paid content/account/private-file/webhook checks passed. A separate direct
staged API probe used synthetic accounts/tokens in a rolled-back transaction and
real staging-only avatar storage: upload/read/no-store, cross-account denial,
replacement deleting old file and removal deleting current file passed. Synthetic
accounts/tokens rolled back and task-created photo directories removed. Initial
rapid probe failed its last request; subsequent probe respected the existing
five-per-minute account-path limit and passed, without clearing/changing limits.
Completed unchanged Flutter evidence (97 focused tests plus staging-identity test)
was reused; no unrelated suite repeated. State records passing staging checks at
2026-10-09 22:49:03 UTC, scope review-3-avatar-backend; staged-check log SHA-256
48f8e1472cccf6d8a77becd87fa7c91a37efc4128796ca9f6550efed929058b8.

**Phone artifact:** **Shelf Test 1.0.9 (18)**, package services.shelf.app.staging,
API https://staging.shelf.services/api/. Version 18 is next after retained staging
APK manifests 12/13/16/17. Build arguments supply version; source/pubspec/lockfile
unchanged. APK path:
`/home/shelf/staging-runtime/storage/app/private/test-apks/shelf-review-3-test-1.0.9-18-9a748ef-20261009-225653.apk`
SHA-256: `f25b21ca3d640e9c4bcb43e4606a3bccfb66d32153690e7a4dc8074d9f38370b`; **62134954 bytes**, private mode 0600,
matching private JSON evidence. APK signature verified; same upload certificate
SHA-256 2065572ecb0f174a9e669602232ffcea31335303d3ff011caaeb7216fdb69e77
(SHA-1 DA:24:FF:12:6D:3A:D7:D2:83:E3:A3:36:B6:8A:A9:03:50:42:38:CB)
as prior Shelf Test 1.0.8 (17). Same package/signature and increasing version permit
an in-place update: no uninstall or data clearing requested. Actual installed data
retention remains pending phone observation. Play-installed Shelf stays separate.

**Artifact verification:** compiled version/package/Shelf Test label/non-debuggable
manifest, release signature, ZIP CRC, staging endpoint in every libapp.so, visible
TEST COPY — Shelf Test banner, pagination/pinned reading and sharing-preview
markers/Dari choice, native Android avatar-picker channel and all three original
font families passed. All **84 supplied logo/action PNGs** match staging-source
bytes, including density/light/dark/inactive variants. Native picker compiled;
actual chooser/permission/photo lifecycle on phone remains unverified. Initial
banner verifier looked for ASCII; corrected to the actual UTF-16 string and passed
on the same artifact, no rebuild. Task build workspace cleaned; recreated Gradle
registry cache removed. Logs/scripts: /home/shelf/tmp/shelf-review3-apk-20261009/
(staging-deploy.log, preflight.json, staged-avatar.log, build-test.log, verify-test.py,
verify-test.log). No secrets printed/committed.

**Pending and boundaries:** owner install/update and Review 3 Android phone
acceptance PENDING. Review three interface languages/logo/icons, localized fonts
and size 16, enlarged text/small-screen controls, both pinned previews, page turns/
Contents/resume and private photo add/replace/remove/sign-out/relogin. iOS native
picker/build/device validation NOT VERIFIED. Proposed second/cover-photo purpose
still PENDING CLARIFICATION, no public profile. No production build/upload/deploy,
live production migration/account/data change or real-money transaction. Checkout
OFF, pricing sync DEFERRED/disabled, second-phone restore owner-deferred and joint
Android/iOS launch unchanged. Next action: download this private APK on Windows
into Downloads\Shelf-Review-3 and update Shelf Test in place for review.

## Shelf Review 3 — isolated implementation, 9 October 2026

**Owner-approved scope:** continue latest isolated Review 2 branch, including
pagination c211167, pinned reading preview 6c9ef45 and build-17 records 29dd095.
Use the supplied original bilingual logo/action assets and written requirements;
Figma board 15:8 was accessible and inspected as a visual reference. No placeholder
prices, book text/covers or logo were copied. ZIP integrity evidence was reused.

**Implemented:** proportion-preserving light/dark Shelf / شیلف Store header;
supplied action/inactive-navigation icons, directional RTL/LTR page controls and
local-script font glyph with localized label; persisted interface choices
پښتو، دری، English, with Afghan Dari bookstore/reader/settings/sharing translations.
Source text and book direction stay independent of interface language. Existing
owner-approved English/LTR account and purchase flows remain English/LTR.
Font names use only the interface language, including نوی شهرزاد; minimum reading
size is 16, preserving valid saved sizes; Pashto Settings is سیټینګ. Contents has
a subtly distinct reader-footer background without the repeated Book details
caption; real Book details remain reachable. Duplicate empty-search instruction
removed. Both Review 2 pagination/anchors and pinned reading preview retained.
Sharing preview remains above scrolling controls, updates font/size/colors/lines
immediately, offers expanded viewing and keeps attribution and existing limits.
Small-screen/enlarged-text export buttons use full width.

**Private account avatar:** authenticated own-account upload/replacement/removal,
private storage and no public URL/profile. Server validates JPEG/PNG/WebP up to
2 MB/4096 pixels, re-encodes bounded JPEG without source metadata, removes replaced
files and erases avatar files after confirmed account deletion. Resident photo
clears on sign-out; late requests cannot repopulate another account. Reversible
avatar-path migration is prepared in git, NOT applied live. Android picker uses
the existing native channel pattern without broad media permissions. Proposed
second/cover photo is PENDING PURPOSE CLARIFICATION, not implemented.

**Evidence:** focused scripts/run_tests.sh --mobile, review-3 scope and temporary
ReaderAvatarTest database: 97 Flutter checks plus 1 staging-identity check;
4 avatar API tests / 35 assertions. Covers all three UI languages, dark/inactive
assets, RTL/LTR arrows, 320-width text scales 1/2/3, short landscape, pinned live
preview updates, original-font reflow/pagination/positions/access regressions,
private cross-account denial, invalid uploads, replacement/removal and confirmed
deletion. Flutter analysis: no errors; 58 existing style/warning notices remain
(nonfatal policy). PHP syntax and git diff --check passed. Log:
/home/shelf/tmp/shelf-review3-checks.log; synthetic local renders:
/home/shelf/tmp/shelf-review3-renders/. These are not real-phone acceptance.

**Remaining/boundaries:** Android native picker/device and all new UI phone review
pending; iOS native avatar picker/build/device readiness NOT VERIFIED. Existing
installed Shelf Test 1.0.8 (17) does not contain Review 3. No production deployment,
live migration, release build, store upload, real transaction or main merge/push.
Checkout OFF, pricing sync DEFERRED/disabled, second-phone restore owner-deferred
and Android/iOS joint launch preserved. Newer records/unrelated edits retained.
Next phone-review step requires separately authorized private staging of the
avatar backend and a fresh separate Shelf Test APK; then review languages/logo,
font names/16-size, both pinned previews, pagination/Contents and photo lifecycle.

## Paginated-reader Shelf Test update — 9 October 2026

Owner authorized **only a fresh separate Shelf Test APK**, updating installed
Shelf Test 1.0.7 (16) without uninstalling/clearing data. Completed immutable
source **886220203a990b638af456913f65095d9fb596cc** (8862202 records), mobile implementation
**c2111672957cb14cca9dc3c51bf05f87de31cadd**, includes pinned preview 6c9ef45
and latest completed Review 2. Exported with git archive; mobile source,
pubspec/dependencies and production checkout were not edited. Version supplied
only through build arguments. No older branch/document replaced newer work.

**Built artifact:** release APK **Shelf Test 1.0.8 (17)**,
`services.shelf.app.staging`, API `https://staging.shelf.services/api/`.
Next unused version code 17 was checked against retained staging APK manifests
(12, 13, 16) and existing build records/artifacts through 16.
Path: `/home/shelf/apps/shelf/storage/app/private/owner-apks/shelf-review-2-test-1.0.8-17-c211167-20261009-065639.apk`
SHA-256: `b2d10c611a27c228ba794e698bdd9af645e4b405c6fb144a7ae8fd2ee3881bf0`
Size: 61206058 bytes; private mode 0600, matching private JSON evidence.
Build completed successfully with the established staging flavor/private access
defines, JDK 17/Flutter/SDK and existing Shelf upload signing properties. No
production AAB was built; all previous artifacts are retained.

**Update compatibility verified:** apksigner verified both prior build-16 and
new build-17 APKs. Same package and identical signer SHA-256
`2065572ecb0f174a9e669602232ffcea31335303d3ff011caaeb7216fdb69e77`
(SHA-1 DA:24:FF:12:6D:3A:D7:D2:83:E3:A3:36:B6:8A:A9:03:50:42:38:CB),
with version code 16 → 17. This is compatible with an in-place Shelf Test
update preserving its existing app data/account; no uninstall, data clearing or
migration is required. Actual phone installation/data retention not yet observed.
The separate Play-installed production Shelf remains untouched.

**Artifact verification:** release signature/certificate, compiled package,
version 1.0.8 (17), Shelf Test label, non-debuggable manifest, ZIP CRC, staging
endpoint in every libapp.so, pagination controls and pinned-preview markers,
and all three original font families passed. Build/verification evidence:
`/home/shelf/tmp/shelf-pagination-apk-20261009/build-test.log` and
`verify-test.py`, plus the private APK JSON. Disposable build workspace removed.
Reused completed unchanged-source evidence: 52 focused Flutter tests + 1 required
staging-identity test, PHP smoke 1 test / 17 assertions and scoped analysis with
no issues. No repeated suite or accepted purchase/backend tests.

**Pending:** owner installs this APK over Shelf Test and reviews pinned preview,
Pages mode, direction/turns, font/size/viewport reflow, Contents and resume with
real books. Android phone acceptance for these changes remains PENDING; iOS
validation remains NOT VERIFIED. Earlier acceptance stays limited to improved
design, font size and light/dark on build 16.

**Boundaries preserved:** no production AAB, store upload, backend deployment,
production settings/provider/database/account change or real-money transaction.
Checkout OFF; pricing sync DEFERRED/disabled; second-phone restore owner-deferred,
not passed or a newly imposed launch blocker; Android/iOS must launch together.
No main merge/push. Current Master Record updated additively; unrelated edits kept.
Next owner action: run the supplied Windows PowerShell download command on the
Windows PC, then open the APK on the Android phone and update **Shelf Test** in
place for review. Keep the existing apps and their data.

## Optional paginated book reader — implemented 9 October 2026

**Owner authorization and source:** owner authorized the planned shared Flutter
reader as one implementation batch. Source commit
`c2111672957cb14cca9dc3c51bf05f87de31cadd` on `ui/figma-review-2`, isolated at
`/home/shelf/tmp/shelf-figma-review-2`, continues 320231c and pinned preview
6c9ef45, retaining Review 2 correction c0e4561 and subsequent records. No older
branch replaced current source/docs; production mobile source remains unchanged.

**Implemented:** optional Scroll / Pages in existing reading preferences;
scrolling remains the default. The pinned live preview/close control and all
existing fonts, sizes and palettes remain. Book context supplies canonical ID,
language and server-ordered Contents. Large previous/next controls and swipes
follow Pashto/Dari RTL and English LTR, with continuous lazy navigation through
adjacent accessible sections. Contents returns to the real book list for direct
item jumps; those start the selected item, while Read resumes the book's saved
accessible section. Loading/error/access states retain Contents and settings.

Native TextPainter measures the exact original string with the selected font,
text scaler and available viewport. Pages are contiguous UTF-16 ranges snapped
to grapheme boundaries: no normalization, trimming, source rewriting or card
export pagination. Fitting stanza/explicit-line boundaries are preferred;
oversized stanzas split into exact ranges, and a line taller than the viewport
can scroll without shrinking or losing text. Metadata/title/credits/date,
artwork and audio remain reachable on each section's scrollable details page;
sharing remains available through the existing reader entry. Extreme short
viewports can scroll the navigation footer rather than clip its controls.

Local positions contain item ID, source offset, exact-text SHA-256 and a details
flag, never page number or book text. Keys isolate environment, account/guest
and book. Reflow for family/size/text scaler/viewport or reopening selects the
page containing that location; vertically panned oversized pages also update
and restore a text location. Scrolling mode uses the same text anchor. Changed
text safely restarts the same section with a notice; storage failure is visible.
Positions never grant access or extend a lease. No cross-device sync was added.

Navigation uses existing repositories, with no new protected-text cache or
prefetch of locked neighbors. Public partial samples retain only the approved
response; unexpected unlocked paid responses are rejected. Owned reading
continues through OwnedBookRepository/LibraryController.copy checks. Account
changes, revocation/download removal, lease expiry and failed clock checks deny
resident pages; stale in-flight responses are rejected. Pause discards resident
content and resume rechecks through the repository. Existing server protection,
offline/account isolation, audio/session behavior and checkout remain authoritative.

**Focused verification:** `SHELF_PHP_TEST_FILTER=PrivacySupportTest
SHELF_MOBILE_TEST_SCOPE=paginated-reader bash scripts/run_tests.sh --mobile`
passed: 52 focused Flutter tests, 1 required staging-identity test and the
required PHP smoke (1 test / 17 assertions). Scoped Flutter analysis found no
issues; git diff --check passed. Evidence:
`/home/shelf/tmp/shelf-pagination-checks.log`. Tests prove exact range
reconstruction/no missing or duplicated passages across all three bundled
fonts, CRLF/blank lines, combining/emoji graphemes, RTL/LTR and narrow/large-text
layouts; logical controls/swipes across items; reflow/reopen/changed-source
resume; actual Contents jump and Read resume; optional-mode and pinned-preview
interaction; approved sample/locked/contradictory paid-response denial;
account sign-out with an in-flight result; owned offline navigation, lease expiry,
clock rollback and revocation. Oversized pages preserve vertical text offsets;
320×360 at 3× and 568×240 at 3× remain usable in widget checks. Existing reader,
content-language, audio/share and pinned-preview regressions passed. Regression
tap was corrected to scroll to a font control now below the mode selector;
Contents integration test scrolls lazy list items into view. These are automated
code/layout checks, not Android phone acceptance or iOS validation.

**Remaining and deliberately omitted:** Android phone acceptance of pagination
and the pinned preview, iOS validation and broader outstanding Review 2 phone
checks remain pending. Installed Shelf Test 1.0.7 (16) is still c0e4561; owner
acceptance remains limited to better design, font size and light/dark controls.
Front matter stays in Book details; no progress percentage, cloud position sync,
new engine/dependency or direction support claim for unknown configured languages.
A later owner decision is needed to establish direction for additional languages.

**Constitution/boundaries:** authorized isolated shared UI, focused verification
and additive records only. No APK/AAB rebuild, store upload, backend deployment,
main merge/push, database/content/media/date or production-setting changes and
no transaction. Checkout OFF, pricing sync DEFERRED/disabled, second-phone
restore owner-deferred/not a new launch blocker, and joint Android/iOS launch
remain preserved. The production Master Record received this additive entry;
unrelated/newer edits were retained. Next single owner action: authorize a fresh
separate Shelf Test APK build for targeted pinned-preview/pagination phone review.

## Owner reading-settings feedback and pinned preview — 9 October 2026

**Owner phone evidence, limited acceptance:** owner confirms installing Shelf
Test 1.0.7 (16), reports the design looks better, and confirms font-size and
light/dark theme controls work. Record only these observations. No acceptance
of every screen, all fonts/sepia, purchases, offline/account isolation, privacy,
all reading/import checks or iOS is inferred. That installed artifact is from
c0e4561; the pinned-preview change below has not been packaged or phone-tested.

**Authorized and implemented:** keep live preview and close control pinned while
reading settings scroll. Shared Flutter source commit
`6c9ef455b5224321ebc7e5cfe06b517a928723d2`, continuing latest Review 2 branch
9b1937c/c0e4561 without discarding completed work or unrelated edits. Header and
preview are outside the controls' scroll view. Preview immediately observes
ReaderSettings family/size/palette notifications; existing preference keys, font
choices and reader/source behavior remain unchanged. Compact single-line preview
can pan horizontally; exceptionally tall text can pan vertically within a region
capped at 30% of available panel height. No reduction of selected font size or
accessibility scaling. Close remains in the pinned header; safe-area handling
and compact heading retain usable controls on narrow/short screens.

**Verification:** scripts/run_tests.sh --mobile, pinned-preview scope, passed
30 focused Flutter tests plus 1 required staging-identity test and the required
PrivacySupportTest smoke (1 test / 17 assertions). Scoped Flutter analysis of
the changed panel/new tests found no issues; git diff --check passed. Checks
load actual bundled fonts and exercise RTL/LTR at 320×568, 390×844 and 568×240,
text scales 1.0/2.0, maximum selected size 38, pinned offsets while settings
scroll, all font/palette choices, immediate preview updates, size buttons/slider,
persistence, settings reachability and closing from the bottom of the settings.
Existing reader sample/locked/source-format/artwork/font behavior regressions
passed. Initial test incorrectly required scrolling when every control fitted;
corrected to allow zero scroll extent while preserving the pinned-offset checks.
Evidence: `/home/shelf/tmp/shelf-figma-review-2/pinned-preview-checks.log`.

**Next approved reader task — recorded, not implemented:** optional book-style
paginated reading, page controls/swipes through the book, retained Contents,
reflow for font/size/viewport changes, exact poetry line/stanza preservation,
stable text-location position (not page number), sample/paid/offline/account
boundaries and book-direction navigation. Current reader was inspected; native
Flutter layout/range pagination, ordered item context and account-scoped anchors
are proposed in [the concrete next-batch plan](PAGINATED_READER_PLAN.md).
The card-export paginator rewrites spacing/newlines and must not be reused for
book text. No owner decision blocks the current-book core: retain scrolling by
default and front matter in Book details; owner inclusion of front matter as
pages is optional. Unknown book languages require explicit direction metadata.

**Remaining:** new pinned-preview Android phone acceptance and iOS validation
are pending. Owner's above feedback does not close all Review 2 phone checks.
No app rebuild, upload, backend deployment, real-money transaction, reading
engine or dependency in this batch. Checkout OFF, pricing sync DEFERRED,
second-phone restore owner-deferred/not a new blocker, and joint Android/iOS
launch preserved. No main merge/push or production app-source change.


## Review 2 Android phone-review artifacts — 9 October 2026 UTC / 9 October local

Owner authorized artifact preparation from completed shared Review 2, including
correction c0e4561, with no Play upload or backend deployment. Owner selected a
separate Shelf Test APK beside the existing Play-installed Shelf, using the
existing staging catalogue and separate test account. No uninstall/data migration.

Immutable build source: **c0e4561b41c87f6f56cb2ffb58eedce9a46af178**, branch ui/figma-review-2,
including Store, Book details, My Library, Search, Reader and reading preferences,
the readable brown chip correction and diagnosed/fixed render fixture. Real book
text and intentional poetry formatting were unchanged. Origin main was checked
read-only at 72cabf0; no newer mobile source was omitted. Current governing
amendments/unrelated edits are preserved; no old documentation snapshot replaced
the current record. Source was exported with git archive and built in disposable
workspaces. Version chosen only through build arguments: **1.0.7 (16)**;
no mobile source, dependency lockfile or pubspec version edit in this build task.

**Selected phone artifact:** release APK, label **Shelf Test**, package
`services.shelf.app.staging`, API `https://staging.shelf.services/api/`.
Path: `/home/shelf/apps/shelf/storage/app/private/owner-apks/shelf-review-2-test-1.0.7-16-c0e4561-20261009-044635.apk`
SHA-256: `6dfc7839ea9ffe13f99b527def3391ffb3ba34cab1b40e91dadddef7b90a6933`
Size: 61058570 bytes. Private mode 0600; matching private JSON evidence.
Built with established staging flavor/private access defines, JDK 17/Flutter/SDK
and existing Shelf upload signing properties. No owner-preview token generated.
Separate staging account/data; production reader account/purchases are not copied.
This verifies shared UI on a phone, not production-account or Play-signed behavior.

**Production artifact retained, not phone-installable:** release AAB,
package `services.shelf.app`, version **1.0.7 (16)**, production endpoint,
with internal-test checkout opt-in omitted.
Path: `/home/shelf/apps/shelf/storage/app/private/owner-aabs/shelf-review-2-production-1.0.7-16-c0e4561-20261009-044332.aab`
SHA-256: `e251545cb97fb5ea1f63d0ee38707accdca4602f9c16c1730a92a5933eff8789`
Size: 61899366 bytes. Private mode 0600; matching private JSON evidence.
No Play upload, rollout or launch. Preserved prior approved 1.0.6 (15).

**Installation compatibility:** actual APK signer matches the recorded Shelf
upload certificate: SHA-1 DA:24:FF:12:6D:3A:D7:D2:83:E3:A3:36:B6:8A:A9:03:50:42:38:CB;
SHA-256 20:65:57:2E:CB:0F:17:4A:9E:66:96:02:23:2F:FC:EA:31:33:53:03:D3:FF:01:1C:AA:EB:72:16:FD:B6:9E:77.
Existing Play Shelf uses owner-confirmed Play signing SHA-1
BE:61:2C:CD:0B:79:68:E3:AA:1E:2A:C7:E6:CF:B8:CB:61:8F:DE:24.
A locally upload-signed APK cannot update the Play-signed production package;
[Android requires compatible signing for updates](https://developer.android.com/studio/publish/app-signing).
The chosen separate package leaves Play Shelf and its local data in place.
Shelf Test also uses the same upload certificate as its earlier documented APK,
with a higher version code. Actual installation on the owner's phone is not claimed.
A production update would require a separately authorized Play-signed test update.

**Verification:** both release builds completed. APK signature/certificate,
compiled package/version/label/non-debuggable manifest, ZIP CRC, staging endpoint
in every libapp.so, Review 2 preferences marker and all three original bundled
reader font families passed. AAB ZIP CRC, compiled production manifest/version,
non-debuggable mode, JAR signature/upload certificate and production endpoint in
every libapp.so passed. Reused completed Review 2 focused 64-test evidence and
correction 20-test evidence, plus their identity/PHP smoke checks; no unrelated
suite or accepted purchase/backend test rerun. Build/verification scripts/logs:
`/home/shelf/tmp/shelf-review2-android-20261009`. AAB trap cleanup initially returned nonzero after verified artifact
save because a temporary Gradle directory was recreated; task-only leftover was
removed and its absence confirmed. No artifact rebuild was needed. An independent
APK verification initially lacked Java on PATH; setting the established JDK PATH
fixed the check. No app/source change was required.

Read-only runtime checks: production real checkout false, pricing sync false;
staging purchases false, production checkout false and pricing sync false.
Staging and production backend source remain deployed at 34799b8; no backend
change, migration, deployment, account/provider/configuration edit or transaction.
**Android phone installation/acceptance PENDING** until owner installs Shelf Test
and reviews the result. **iOS validation NOT VERIFIED**. Checkout OFF, pricing sync
DEFERRED, second-phone restore owner-deferred (not a new launch blocker), and
joint Android/iOS launch unchanged. This build does not approve public release.

**Single next owner action — Windows PC, PowerShell:** download the selected
Shelf Test APK into the Windows Downloads/Shelf-Review-2 folder. The subsequent
phone review uses Shelf Test beside Shelf and the separate existing test account.


## Figma Review 2 implementation — 9 October 2026

Owner approved [Figma Review 2](https://www.figma.com/design/NVSrbPa6eJJVUfIK3vdUeJ?node-id=7-5)
and authorized one shared Flutter design batch: Store, Book details, My Library,
Search, Reader and Reading preferences, focused verification and project records.
Actual Figma context/screenshots were inspected; no design guessed from blocked access.
Implementation is isolated on ui/figma-review-2 from current 72cabf0 source,
whose baseline mobile tree matches deployed reviewer release 34799b8.
Newer working governing amendments and unrelated production edits are preserved.

Implemented shared cream/brown styling, flexible book rows, outlined search,
clear navigation/filters/touch targets, original dynamic cover presentation,
persistent sample/owned/purchase-status actions, Library download/restore controls,
reader heading/Contents and visual persisted font/size/light/sepia/dark preferences.
Existing source text/credits, metadata/front matter/contents, loading/error/offline
states, account/language/privacy/support, purchases/restore, audio/sharing and
server-controlled access remain. No placeholders, fixed store price or mock progress
shipped. Progress/resume is absent: retain Read book and omit percentage/Continue
reading claim. No daily-feature selection or unsupported reader arrows/author-filter
API invented. See [implementation and evidence](FIGMA_REVIEW_2_IMPLEMENTATION.md).

Final focused checks passed: 64 Flutter tests,
1 staging-identity test, required PHP smoke 1 test / 17 assertions.
Flutter analysis: zero errors; three pre-existing warnings and 61 style notices
retained (strict default reports them; nonfatal-warning/info run succeeds).
Rendered RTL/LTR screens at 320×568/390×844, text scales 1.0/1.8, and Library
states were inspected; synthetic fixtures are not real-phone acceptance.
Android phone acceptance remains PENDING until owner review and separately
authorized build/device checks. iOS build/device/provider validation is not
established by shared Flutter checks. Android/iOS joint launch remains binding.
Checkout OFF; pricing sync DEFERRED; second-phone restore owner-deferred, not a
new launch blocker. No deploy/build/upload, transaction, settings/schema/data or
backend/admin changes. Next single owner action: review the rendered shared UI.

## Joint-launch direction and single remaining-work list — 8 October 2026

**Owner-approved amendment:** four work areas: Admin/backend, Android app, iOS app,
shared app design and UX/UI. Android and iOS must launch together. Neither may
release publicly until both are complete, accepted and ready, both store reviews
are complete, and final owner launch approval is given. This supersedes Android-only
Version 1, “iOS later”, one-platform release criteria and previous next-task priorities.
Completed work and historical evidence remain intact.

**Current work order:** inspect iOS readiness → agree/improve shared design and
UX/UI → complete iOS and remaining admin/backend → accept both apps together and
fix demonstrated issues → complete both store reviews and coordinate launch.
Use complete practical batches, shared Flutter code and passed evidence.

**Google Play — current owner report, 10 October:** **1.0.11 (20)** approved for
**Closed testing – Alpha**; latest screenshot showed **“Changes ready to publish”**
with **Managed publishing ON**. Publishing completion and Play-installed build-20
phone acceptance are **NOT CONFIRMED**. Earlier build-15 approval and review
snapshots remain historical. Preserve existing artifacts, assets, reviewer access,
products/mappings and completed evidence. Current Review 3 work is owner-accepted;
further Android/UI changes are paused pending separate authorization. No Console
read, store action, tester availability or public-release approval is claimed here.

**Preserved controls:** checkout OFF; manual Play pricing for the first 100 books;
automatic pricing sync DEFERRED/disabled, no 403 retries; second-phone restore
DEFERRED/not passed and not a launch blocker without a later owner decision.
Financial policy: three years after the relevant tax return is filed, affected
records held longer for applicable legal requirements, unresolved disputes or
unpaid author balances, and minimum proof while recovery rights continue.
Automatic financial expiry is NOT IMPLEMENTED. Seven-year/one-year alternatives
remain unapproved. Optional improvements stay outside release scope.

### One active remaining-work list under four areas

Use this list for current priorities. Earlier lists and next-action paragraphs
below and in supporting records are historical, not parallel active work queues.
Assign cross-cutting work once; other areas depend on that result.

| Area | Remaining work and type | Dependency / boundary |
| --- | --- | --- |
| **1. Admin/backend** | **Construction:** tax-return filing/mapping, scoped exception holds, minimum recovery-proof separation and owner-only financial-retention dry-run; reviewed preservation/disposal design before later expiry; align privacy/deletion wording with policy and actual behavior. | Batch 3 after readiness/design planning. Preserve built accounts, purchase verification, accounting, deletion/recovery and backups. Expiry OFF; deletion/schema/data changes need separately approved preservation, staging and backup plans. |
| **1. Admin/backend** | **Evidence/external dependencies:** RevenueCat onward integrations, hosting/mail and Microsoft terms, Apache/statistics retention, remaining diagnostic/identifier handling and applicable audience safeguards. Actual provider metadata erasure unverified, conditional on genuine approved deletion work; no eligible job to drain. | Reuse existing evidence; no blanket sharing assertion or manufactured reader deletion. Store-specific reconciliation when final review needs it; owner handles rating questionnaire, assistant poem review stays cancelled. |
| **1. Admin/backend** | **Operations/acceptance:** actual backup-alert delivery, full replacement-host recovery/cutover, owner admin/end-to-end acceptance and separately approved old-app retirement. | Reuse passed encrypted upload/download/isolated restore and October 7 unattended success. Preserve baseline/cPanel handler; no old-account access authorized here. |
| **2. Android app** | **Acceptance:** current Review 3 work owner-accepted; Play-installed build-20 phone acceptance NOT CONFIRMED. Retain unpassed Play-signed sign-in, reviewer access, reading/import, account isolation, sign-out, offline expiry/clock, withdrawal and platform acceptance requirements; earlier build-15 references are historical, not current-build acceptance. Further Android changes paused. | Batch 4 joint acceptance only when authorized. Reuse completed evidence; second-phone restore deferred. Current Android/UI pause remains binding. |
| **2. Android app** | **Final-release dependencies:** tester opt-in/install evidence when needed, final Play review/readiness and confirmation of removal of four temporary account-level grants, preserving app-level access; actual production event/acknowledgement/purchase/refund/restore evidence. | Batch 5 when release needs it. Closed-testing approval complete, not public approval. Real-money/gate checks require separate explicit owner authorization and coordinated launch readiness. |
| **3. iOS app** | **Foundation source prepared 10 Oct:** iOS project/identity/icons/native channels and manual unsigned Codemagic configuration. Apple membership/identity/App Store record owner-confirmed; macOS compilation, signing and device/provider checks not passed. | Batch 1, next practical task. Inspection first; later concrete tasks authorize setup/build/implementation. |
| **3. iOS app** | **Construction/acceptance:** complete platform project/configuration, signing, account/provider and per-book App Store purchase integration, reader/Library/offline/deletion/support flows as readiness establishes necessary; then real-iOS-phone acceptance and demonstrated fixes. | Batches 3–4 after inspection/agreed design. Reuse shared Flutter/backend. Exact gaps unverified until inspection; no Apple identity/availability/approval/purchase success claimed. |
| **3. iOS app** | **External/release dependencies:** necessary Apple account/build/signing access, App Store configuration/disclosures/review/readiness and approved transaction/restore/refund acceptance. | Batch 5 joint launch. Android evidence does not establish iOS provider/device results. Checkout OFF absent separate authorization. |
| **4. Shared app design and UX/UI** | **Agreement/construction:** current Review 3 work owner-accepted; further UI changes paused. Preserve delivered shared components and completed checks; no new design batch authorized. | Batch 2 after iOS readiness. Preserve source text, RTL book layout, interface/account-language decisions and access rules. Concrete reader-facing changes need agreement; optional improvements excluded. |
| **4. Shared app design and UX/UI** | **Acceptance:** accept both apps together against agreed design, clear actions/touch targets, loading/empty/error states, text/layout and account/purchase/Library flows; fix demonstrated issues and reuse passed evidence. | Batch 4 with platform-specific device/reading checks. One ready platform cannot release publicly alone. |

**Next practical task:** confirm the Codemagic dashboard API variable/group and refreshed ios/foundation workflow, then separately authorize the first unsigned cloud compilation. Repository connection/YAML discovery are owner-confirmed; follow the latest Codemagic correction entry. No build or paid activation in this task. Financial-retention dry-run
work remains in the backend list; it is no longer the immediate next-task priority.
This amendment authorizes documentation only: no features/settings, rebuild,
deployment, purchase, store submission or release; no commit/push in this task.

**Summary reconciliation still needed:** AGENTS.md §§1–3 retain Android-only/current
Step 4 scope, §4.11 has Android-only phone acceptance, §6 has small-step instructions,
and §8 contains “App Store later with iOS”. Those active instructions need alignment
with this amendment while preserving historical entries and unrelated edits.
This task changes only the requested Master Record. The explicit owner amendment
takes precedence over conflicting summary instructions.

### Historical status and recommendations below

Earlier entries, including old platform/review/next-task statements, remain dated
history and do not supersede this amendment or the single active list above.


## Financial retention approved — 8 October 2026

Owner-approved policy (owner chat, not a claim of a universal statutory period):
- Retain accounting and tax-supporting records for **three years after the relevant
  tax return is filed**. The clock is the actual filing date, not purchase date,
  account deletion, year-end, release date or the date this policy was approved.
- Retain affected records longer where an applicable legal requirement, unresolved
  dispute or unpaid author balance requires it. Exceptions apply to affected evidence,
  not automatically to every record; document the basis and release condition.
- Keep **only the minimum purchase proof needed while purchased-book recovery rights
  continue**. The three-year period must not break those rights, including approved
  support-assisted recovery after account deletion and refund/revocation checks.
- The **seven-year proposal and one-year alternative are NOT APPROVED**. No blanket
  permanent accounting archive or three-year post-rights period is approved here.

**Approved policy versus implemented behavior:** policy decided; financial expiry
NOT IMPLEMENTED. Existing accounting, tax-supporting and purchase/recovery records
remain retained without automatic financial expiry. Current 03:45 maintenance covers
approved log/backup retention and provider metadata retries, not financial records.
There is no tax-return filing-date registry or approved financial eligibility job.
Public policy still describes financial retention as under review; aligning deployed
wording is a later scoped change, not performed by this documentation task. Earlier
“financial expiry unresolved” entries remain historical; the policy is now approved,
while implementation remains outstanding. D14 is not declared fully complete.

Checkout **OFF**, pricing sync **DEFERRED / disabled**, reviewer access and completed
work preserved. **Second-phone restore remains owner-deferred, not passed, not
requested and not a launch blocker without a later owner decision.** No record
expiry/deletion, production setting change, migration, rebuild or deployment here.

Implementation plan: [D14_DATA_POLICY.md](D14_DATA_POLICY.md), scoped to filing-date
mapping, holds, recovery-proof separation and dry-run eligibility before any later
expiry authorization. No implementation or data deletion in this task.


## Closed testing Alpha submitted — 8 October 2026 (owner-confirmed)

Owner reports Shelf **1.0.6 (15)** uploaded to **Closed testing – Alpha**;
countries and tester email lists configured; release notes saved with **en-US**
language tags. Advertising ID declaration set to **No** after read-only inspection
of the final AAB's compiled merged manifest and actual SDK usage. AD_ID permission
is absent. Embedded dependencies include RevenueCat purchases 10.22.1,
purchases-hybrid-common 19.3.1 and play-services-ads-identifier 17.0.1; Advertising
ID capability is bundled, but Shelf invokes no identifier-collection/attribution
calls (their channel markers absent in all three compiled Flutter architectures).
Do not interpret the bundled library as an active Advertising ID collection flow.
Final artifact/hash/source evidence remains in the build-15 entry below.

Owner submitted the changes. Play Console shows **“Changes in review”**, with
quick checks running. This records owner chat confirmation, not an independent
Console read or Google approval. **Google approval, tester availability and build
15 phone acceptance remain PENDING.** No exact tester emails/country list or review
completion time invented. Submission does not certify every pending Data safety
provider fact or constitute public-launch/real-payment approval. Earlier statements
that no upload/submission occurred describe those earlier tasks and are superseded
for current release status only.

Checkout remains **OFF**; automatic Play pricing sync remains **DEFERRED / disabled**.
Preserve reviewer access, approved books/rights/prices/product mappings, completed
construction and test evidence, build 14 and the cPanel handler. This update changes
records only: no feature work, rebuild/upload/deploy, production settings or data
change. No broad tests or completed checks repeated.

### Unfinished work before public launch — implementation/evidence reconciliation

**1. Can progress while Play review is pending (separate authorization as needed):**
- D14 financial policy is now approved: prepare filing mapping/holds and dry-run
  implementation, then align disclosures in a later approved task. Automatic financial
  expiry is not built. Existing deletion/recovery/log/backup construction is retained.
- Close remaining provider/privacy evidence: RevenueCat dashboard onward integrations,
  hosting/mail and Microsoft service-provider terms, Apache/statistics retention,
  remaining provider diagnostic/identifier handling and applicable audience safeguards.
  Reconcile submitted declarations against those facts; no blanket sharing conclusion
  or guessed IARC answers. Owner handles the actual rating questionnaire; assistant
  poem review remains cancelled. Submitted listing/declarations are not reopened
  merely because older preparation documents said they were not submitted.
- Obtain outstanding actual backup-alert delivery and replacement-host recovery/cutover
  acceptance under a separately approved isolated drill/operating plan. Reuse already
  passed encrypted upload/download/restore and scheduled-backup evidence.
- Resolve recorded confirmation of removal of four temporary account-level Play grants,
  preserving app-level access; plan separately approved old-app retirement without
  accessing or changing the old account in this task.

**2. Requires the approved/available Play build:**
- Google approval and actual tester opt-in/install availability; then build 15 phone
  acceptance: Play-signed Google sign-in, reviewer Library/full-book access, native
  Privacy/Support links and copyable fallbacks, remaining account/reader UX acceptance.
- Finish only unpassed real-phone Pashto/Farsi poetry/prose/mixed-text/font/sample
  and real Word-import reading checks; remaining offline expiry/account isolation/
  sign-out/withdrawal checks. Second-phone restore remains owner-deferred, NOT PASSED;
  it is not a launch blocker without a later owner decision.
  Preserve the owner's passed refund/decline/repurchase results; no broad repeat.
- Actual production purchase/event delivery, SDK acknowledgement and production refund/
  restore acceptance require the Play build **and separate explicit owner real-money/
  controlled gate authorization**. Closed-test approval alone cannot enable checkout.
- Owner final end-to-end acceptance, explicit public-launch approval and approved
  launch rollout are still required. Review/tester availability is not public release.

Actual provider erasure remains unverified, conditional on genuine approved deletion
work; there is no eligible job to drain. Do not create/delete a real reader solely
for evidence or mark D14 fully complete. No new generic purchase/accounting/recovery
code defect established. Automatic price sync remains outside the launch work queue.

**Next scoped task proposed:** filing-date mapping, exception metadata and owner-only
financial-retention dry-run reporting, with expiry OFF, as planned in D14_DATA_POLICY.md.
Financial policy has been approved; no implementation or deletion authorized here.


## Updated closed-testing Android AAB — 8 October 2026

Owner authorized artifact preparation only, no upload/rollout/launch. Earlier
verified 1.0.5 (14) predates Privacy/Support Settings; its artifact and SHA-256
remain unchanged. No active build found; one signed production build completed
from clean source `4d7457e3125aad48c94a89059504d379d552ca0d`, including staged mobile
Privacy/Support release `4c3efd63b63242dfd5a6f7cdb924b74143dd6cbe`.
Next unused version selected via build arguments: **1.0.6 (15)**; no mobile
source or dependency lockfile change. Existing approved five launcher PNGs
match build 14 byte-for-byte; no icon recreation.

Private artifact (0600): `/home/shelf/apps/shelf/storage/app/private/owner-aabs/shelf-production-1.0.6-15-4d7457e-20261008-201550.aab`
SHA-256: `169c7c125bac279def533fd935fa865932f9c1e9ee898d3f0302cb7a9f08d316`.
Source/build/check evidence saved in matching private `.json`; build log:
`/home/shelf/tmp/shelf-closed-aab-20261008/build.log`. Package `services.shelf.app`,
compiled version/non-debuggable manifest, ZIP CRC, JAR signature and existing
upload certificate SHA-256 `20:65:57:2E:CB:0F:17:4A:9E:66:96:02:23:2F:FC:EA:31:33:53:03:D3:FF:01:1C:AA:EB:72:16:FD:B6:9E:77` passed.
Production endpoint `https://shelf.services/api/` and Privacy/Support Settings
markers verified in every compiled libapp.so. Existing self-signed certificate/
untrusted public chain and no-timestamp warnings retained; jar verified.

Reused prior focused release and staged Privacy/Support evidence (10 Flutter
tests plus staging identity); no broad suite or phone check repeated. Current
source mobile tree equals deployed reviewer release 34799b8. Fresh read-only
runtime confirms real purchases OFF, automatic sync disabled/DEFERRED; build
omits internal-test checkout opt-in. Reviewer access and cPanel handler retained;
no backend deployment, credentials/provider/data change. Build preparation has
no blocker; native links/Play Google sign-in and other recorded phone acceptance
remain unverified. No upload, closed-track rollout or public launch performed.


## Google Play reviewer access — 8 October 2026

Owner authorized a dedicated ordinary email/password reader with complimentary
reviewer access to six launch books. No existing reviewer account found.
Created play-reviewer@shelf.services (reader 12), pre-verified and purchase-blocked;
no users/admin identity or owner privileges. Six explicit reviewer_book_grants
for books 3–8 record purpose, authorizing owner and grant time, with revocation
field; independent of purchases, receipts and income. Other readers remain
account-isolated. Existing server ownership checks and Library now include only
that reader's unrevoked grants. Credentials stored privately, mode 0600, outside
Git; password never printed into task output/documentation.

Reversible migrations and source commit 34799b8 passed focused isolated/staged
checks (21 tests, 301 assertions; no Flutter/broad suite), then identical-commit
backed-up promotion, final verified backup /home/shelf/backups/shelf/20261008-075312/.
Normal staging AND production HTTPS reader API: email/password login, verified
profile, all six Library books/content lists, one non-sample full text per book,
six covers and book 6 artwork passed; anonymous Library denied, test session
revoked. Synthetic other-reader/no-grant/revocation/admin-route checks passed.
No fake purchases/events/ledger/consents created; no real-reader data changed.

First live staging checker hit existing 429 after excessive item requests;
session revoked, scope narrowed and rate-limit window respected, then passed.
First promotion history gate flagged the authorized new reader row and stopped
with maintenance lifted. Read-only current SQL versus verified 074913 backup
proved every original reader/token/purchase/ledger/agreement row identical;
only reviewer 12 added. Same-commit completion subsequently passed history gate.
A task-created Python cache was removed before completion; cPanel .htaccess
unchanged. No Android rebuild/provider changes/launch. Checkout OFF, pricing sync
DEFERRED; AAB 1.0.5 (14) preserved. Play Console access entry NOT saved/verified;
physical-phone access NOT verified.

Copy-ready app instructions: Choose English on first launch or from the Store
language menu. Tap the person/account icon in Store. Use email/password Sign in
with the supplied reviewer credentials (not Google sign-in or Create account).
Return to My Library and tap Read book on any of the six books; open its contents
and select a poem. All six books have complimentary full access; no registration,
verification email, OTP or payment required. If the Library is loading, wait or
pull down to refresh. Keep the password private and unchanged during review.

## Operator attribution correction; rating review stopped — 8 October 2026

Privacy meta description changed from “published by Hindara” to “operated by
Ajmal Aand”; visible text states Shelf is operated personally by Ajmal Aand.
Historical book/author/publisher credits unchanged. Commit c618814 passed isolated
and staged PrivacySupportTest (1 test, 17 assertions; no mobile/broad suite), then
identical-commit promotion with verified backup
/home/shelf/backups/shelf/20261008-063229/. Existing financial/deletion policy,
reader/purchase/ledger/agreement history and cPanel .htaccess preserved.

Owner stopped the poem-by-poem review and cancelled remaining questions.
[IARC_CONTENT_RATING_WORKSHEET.md](IARC_CONTENT_RATING_WORKSHEET.md) is INCOMPLETE;
no proposed content classifications approved/submitted or assistant interpretations
recorded as owner-confirmed facts. Owner will handle actual Play questionnaire
directly. No poetry edited, removed or restricted. Any-age intent preserved;
age selections unapproved. Checkout OFF, pricing sync DEFERRED; no build or launch.
AAB 1.0.5 (14) unchanged; prior mobile additions still need a later replacement build.

## Part 10 release review — owner identity/audience and technical evidence, 8 October 2026

OWNER-CONFIRMED: Shelf is operated personally by Ajmal Aand; no registered business
claimed (one may follow later). Intended readers: any age; no adult-only material.
Six launch books are general poetry, not specifically children's books; children's
books may follow. No adults-only restriction or Play age selections approved.
Recommendation only: 13–15, 16–17, 18+ for the present independent-reader design;
not an approved restriction, not an IARC rating, not a child-data exemption.
Rationale, younger-age evidence limits and regional Families/API/consent requirements
are in [PLAY_RELEASE_REVIEW.md](PLAY_RELEASE_REVIEW.md). Personal identity settled;
existing “Hindara” privacy meta requires a later identity alignment, no deployment now.

Reviewed and retained previous pending technical audit: archived search queries and
request metadata are non-ephemeral; hosting geoipfree country statistics establish
Approximate location/Analytics collection. Local Laravel logging and SMTPS transport
verified; complete RevenueCat webhook list has one Shelf return destination, but
other dashboard integrations are not exposed by that API. Sharing/provider evidence
and financial retention remain unresolved. Earlier next-action requests for owner
technical configuration and broad audience/identity are superseded.

Next concrete owner action: review six books/media against IARC content questions
and supply actual answers for the release draft; no Console submission. Codex still
needs scoped provider/dashboard evidence to finish sharing. Documentation-only
commit/push includes pending audit; no repeat audits/tests/build/deploy/provider edit.
Checkout OFF, sync DEFERRED, AAB 1.0.5 (14), approved books/rights/prices/174 regions
and existing .htaccess preserved. Recommendation approval is not launch approval.

## Privacy/support corrections — 8 October 2026

Owner authorized implementation, focused validation and staged/backed-up promotion.
Settings source now has bilingual Privacy policy/Support entries, visible selectable
copyable support email, native external opening and failed-browser/email fallback.
Public policy accurately discloses remote searches/request metadata, RevenueCat
reader-ID purchase processing/analytics, pending provider erasure and unresolved
financial retention. Deletion/recovery behavior unchanged. Data safety review uses
exact Google categories; sharing/provider log/geolocation/device-ID answers are
UNRESOLVED, not confirmed “Not shared”. See PLAY_RELEASE_REVIEW.md.
AAB 1.0.5 (14) preserved and does NOT contain these source additions; later replacement
release build/phone acceptance required, no rebuild authorized now. Checkout OFF;
automatic sync DEFERRED; no provider edit/Console submission/public launch/customer
deletion. Implemented release **4c3efd63b63242dfd5a6f7cdb924b74143dd6cbe** passed staged
focused checks at 05:19:55 UTC: 1 PHP test/14 assertions, 10 Flutter privacy-support/
language tests plus staging-identity check; direct staged policy/isolation passed.
Promoted same commit after verified SQL/ZIP/SHA-256 backup
`/home/shelf/backups/shelf/20261008-052036`. Existing reader/purchase/refund/ledger/
agreement snapshots and cPanel handler unchanged. Production privacy/deletion GETs
passed at 05:21:48 UTC; purchase-config confirms checkout OFF. Initial developer
check used the wrong app-config endpoint and was corrected, no server fault/fix.
No actual live account deletion or provider erasure test. Isolated initial PHP run
needed its bootstrap/cache directory; created locally and focused rerun passed.
No broad suite or app artifact rebuild. Native external intents require later real
Android phone acceptance with the replacement release build.
Next owner action: supply existing processor/integration and hosting data-handling
facts to resolve pending Data safety sharing/log/geolocation/device-ID cells;
financial retention, target audience and developer identity remain owner decisions.

## Play listing/Data safety preparation complete — 8 October 2026

Signing correction: owner saved Shelf Android Play/services.shelf.app in
shelf-510123 with **BE:61:2C:CD:0B:79:68:E3:AA:1E:2A:C7:E6:CF:B8:CB:61:8F:DE:24**,
from Play “App signing key — In use”; owner-provided Google Cloud screenshot
confirms exact fingerprint and “OAuth client saved”. Owner-confirmed configuration,
not successful Play-phone sign-in. DA:24:FF:12:6D:3A:D7:D2:83:E3:A3:36:B6:8A:A9:03:50:42:38:CB
is the upload certificate; earlier instruction/record using it for Play signing
was incorrect and is superseded. Original C9:B6 certificate role remains unknown.
Other clients unchanged; preserve verified AAB 1.0.5 (14), no rebuild now.

[PLAY_RELEASE_REVIEW.md](PLAY_RELEASE_REVIEW.md) provides copy-ready listing,
source/SDK-grounded Data safety table, official guidance and precise policy gaps.
Anonymous HTTPS GETs at 05:05:24 UTC: privacy and account/delete 200; candidate
support URL 404, not an existing support page. Existing support email retained.
Missing in-app privacy access/support contact, policy search/request-log disclosure,
RevenueCat purchase analytics and updated date identified, not fixed/deployed here.
Financial retention remains unresolved. Sharing selections require existing
processor/integration facts; target age/developer identity/copy approval remain
owner inputs. No completed catalogue/rights/price/region/product work reopened.

**Next concrete owner action:** approve the scoped privacy/support correction task
specified in the review (in-app links/contact and accurate public disclosures).
Codex implements only in that next approved task. Preparation completed, not final
Console submission or compliance/phone acceptance. Documentation-only commit/push;
no provider edits, upload, rebuild, deployment or broad tests. Checkout OFF;
automatic sync DEFERRED; existing cPanel .htaccess preserved and excluded.

## Android OAuth configuration owner-confirmed — 8 October 2026

Owner saved Google Cloud project shelf-510123 → Android OAuth client
**Shelf Android Play**, package **services.shelf.app**:
- Previous SHA-1: C9:B6:1C:FA:91:58:B1:B5:D8:12:DC:29:C4:B2:02:E0:D7:43:DF:C3.
- New SHA-1: DA:24:FF:12:6D:3A:D7:D2:83:E3:A3:36:B6:8A:A9:03:50:42:38:CB,
  reported by owner as copied from Play's app-signing certificate.
- Other OAuth clients unchanged, owner-confirmed.

**Configuration change OWNER-CONFIRMED; successful Play-installed phone Google
sign-in NOT VERIFIED.** Existing release evidence also records this new fingerprint
as the upload certificate; no independent Play certificate comparison performed.
Do not repeat the saved edit merely for status. No app/server source or packaged
configuration changed; preserve verified AAB **1.0.5 (14)**, its checksum and source
commit recorded below. No concrete rebuild requirement established.

**Single next action: prepare the Play listing/Data safety/privacy/support review**
(Part 7, Step 5; Part 10, Required launch decisions/configuration). Codex prepares
accurate draft entries and checks them against implemented account, purchase,
delete/retention and support behavior; owner reviews and saves Console entries.
This makes the store disclosures and reader-facing listing consistent with Shelf.
It does not authorize upload/publication; unresolved D14 financial expiry stays
explicit rather than receiving an invented deadline. Earlier signing/OAuth edit
next-action statements are superseded by this owner-confirmed change.

Documentation-only commit/push. Real checkout OFF; automatic sync DEFERRED.
No upload/public release, provider checks, rebuild, broad tests or repeated
completed checks. Existing public/.htaccess preserved and excluded.

## D16 rights clearance owner-confirmed — 8 October UTC / 7 October local

Owner confirms permission to sell all six selected books (IDs 3–8) through Shelf
in the approved existing 174 regions, including their covers and any included
audio. **D16 rights clearance: OWNER-CONFIRMED.** Evidence is this owner's chat
confirmation; no supporting documents invented or independently verified.
Catalogue selection, USD 2.99 starting prices and regions are already approved;
AF/IR were absent from the verified explicit list, not worldwide coverage.
Earlier requests for this same rights confirmation are superseded; do not repeat.

**Next unfinished release task: Play signing/OAuth setup (owner).** Add the Play
app-signing certificate SHA-1 to Shelf's existing Android OAuth client in Google
Cloud project shelf-510123 for services.shelf.app, as required by D7. This enables
Google sign-in for Play-signed installations. Owner performs the Console change;
Codex can prepare instructions and subsequently verify the configuration. The
existing upload-key certificate is not evidence that this step is complete.
No provider change or upload authorized/performed by this record update.

Documentation-only commit/push; public/.htaccess preserved and excluded. No repeat
product checks, rebuild or broad tests. Real checkout OFF, automatic sync DEFERRED;
rights confirmation is not public-launch approval.

## Manual-product decisions finalized — 8 October UTC / 7 October local

Owner confirms book 3's Play placeholder name/description corrected following
**څپو کې انځورونه** / **Full book in your Shelf Library.** Recorded as
OWNER-CONFIRMED; no independent provider reread. Owner approves retaining the
existing verified **174 available regions for all six launch books**, and
reaffirms **USD 2.99 per book**. This is not worldwide coverage: AF and IR were
absent from the verified explicit region list. Earlier region-decision and book 3
correction next-action statements below are superseded.

Pending manual-product verification documentation and these decisions are authorized
for documentation-only commit/push. Existing public/.htaccess change preserved and
excluded. No provider checks, credential/provider/data edits, rebuild, deployment
or broad tests. Real checkout remains OFF; automatic pricing sync DEFERRED;
no public release authorized.

**Single next unfinished release task: final launch rights clearance (owner).**
Confirm sale/distribution permission for the six selected books and included
covers/fonts/images/audio, retaining existing credits and written permissions.
This establishes that the selected catalogue may legally be sold; it does not
launch the app. Existing book 6 permission need not be recreated. Codex can organize
existing evidence, but rights-holder permission is an owner responsibility.
Completed production verification, AAB preparation, product creation/mappings,
prices, region selection and wording correction must not be reopened.

## Six manual Play products verified — 8 October 2026 UTC / 7 October local

Owner screenshot confirms shelf_book_3 through shelf_book_8 with one active
purchase option each. One read-only Google list at **03:21:47 UTC** returned
HTTP 200 with no additional page: all six have option **buy**, ACTIVE,
legacy-compatible, multi-quantity not enabled, **US USD 2.99 / AVAILABLE**.
All six share 174 explicit AVAILABLE regions and newRegionsConfig AVAILABLE;
AF/IR are absent from the explicit list. These product settings do not certify
app-track distribution or approve launch regions. Earlier missing-products and
create-book-4 next-action statements below are superseded; creation is complete.

Fresh RevenueCat catalogue/attachment GETs confirm each active non-consumable
product in Shelf's services.shelf.app app maps alone to its same-named entitlement.
Read-only Shelf inventory confirms book IDs 3–8 map to the same canonical IDs,
with USD 2.99 references. No missing/incorrect connection found. Exact provider
IDs and availability evidence are in [the manual checklist](MANUAL_PLAY_PRODUCTS.md)
and /home/shelf/tmp/shelf-product-check-20261008/evidence.json.

Book 3 listing remains “Shelf book 3” / “Test 1”. **Next concrete owner action:**
edit only its Play en-US listing to name **څپو کې انځورونه** and description
**Full book in your Shelf Library.** Preserve product/option IDs, price and mapping.
No provider write, credential change, database save, rebuild, deployment or broad
tests. Real checkout verified OFF; automatic sync disabled and DEFERRED.
Launch-region/rights decisions and existing acceptance gates remain separate;
no completed construction or product creation reopened. Documentation only;
no commit/push requested in this task.

## Initial launch catalogue and starting prices approved — 8 October UTC / 7 October local

Owner selects **all six existing books, IDs 3–8**, for the initial launch catalogue
(D16 catalogue selection decided). Owner approves **USD 2.99 per book as the
current starting launch price**, with individual prices changeable later (D9).
This supersedes the earlier test-only price limitation for these six books;
historical test transactions, price approvals and financial snapshots remain
unchanged. Regions and outstanding rights requirements remain separate decisions.
Selection does not certify rights or authorize public release/real checkout.

Read-only live Shelf inventory at 00:39:22 UTC confirms all six references already
USD 2.99, canonical shelf_book_3 through shelf_book_8 and existing Published state.
No supported admin price alignment was necessary; no book/admin/data save occurred.
Author credits, agreements and purchase history unchanged. Real checkout=false,
automatic sync enabled=false/deferred=true. No rebuild, deployment or broad tests.
Fresh read-only Play oneTimeProducts list returned HTTP 503; stopped without
retry, provider write or pricing diagnostic. Therefore product existence remains
**last verified 7 October 20:10:10 UTC**, not freshly confirmed: shelf_book_3 exists
with buy option buy, books 4–8 missing. The single current checklist is
`docs/MANUAL_PLAY_PRODUCTS.md`; missing products use existing canonical mappings,
with planned buy-option ID buy explicitly marked as not yet created.

Next concrete owner action: in Play Console → Shelf/services.shelf.app → One-time
products, check shelf_book_4 is still absent, then prepare its draft using the
checklist title, product ID shelf_book_4, buy option buy and approved USD 2.99.
Reuse existing products if present; no duplicate. Regions/activation remain pending
separate approval. Follow the same checklist for other missing launch products.
Preserve shelf_book_3 and existing RevenueCat entitlements. No launch/upload or
real payment authorized; financial retention and deferred acceptance remain open.

## Production Android AAB prepared — 8 October 2026 UTC (7 October local)

Owner-approved build only: **BUILD AND RELEASE CHECKS PASSED**, no upload,
installation, publication or gate enablement. Exact clean source commit:
`2c8d763d0b6fc8d284652df97b4340d6e5b1ba41`; includes mobile release support from
`340634b192022228698ab54d04426258382b451b`. Version **1.0.5 (14)** via build
arguments (source pubspec remains 1.0.4+13); production flavor, application ID
`services.shelf.app`, API `https://shelf.services/api/`.

Private artifact (0600; older artifacts preserved):
`/home/shelf/apps/shelf/storage/app/private/owner-aabs/shelf-production-1.0.5-14-2c8d763-20261008-002103.aab`
SHA-256: `bfa979e4e868d063d0ec83bb10d2005153800fb55604daa4c3c4815549737393`.
Size 62,036,611 bytes. Matching `.json` sidecar records source, build inputs,
checks, checksum, certificate and no-upload status. Build log retained privately
at `/home/shelf/tmp/shelf-production-aab-20261008/build.log`.

Reused existing release procedure, Flutter/JDK/SDK and Shelf upload signing
properties; task-local runner omitted SHELF_INTERNAL_TEST_PURCHASES opt-in and
set --build-name=1.0.5 --build-number=14 and the production API define. No source,
credential, provider or app/testing setup change. Dependency lockfile unchanged.
ZIP CRC, compiled AAB manifest/version/non-debuggable, signature verification,
endpoint in each native libapp.so and recorded upload certificate all passed.
Upload certificate SHA-256:
`20:65:57:2E:CB:0F:17:4A:9E:66:96:02:23:2F:FC:EA:31:33:53:03:D3:FF:01:1C:AA:EB:72:16:FD:B6:9E:77`.
Jarsigner verified; existing self-signed upload certificate/no timestamp warnings
are recorded, no credentials replaced. Build runner exit 1 occurred only after
successful artifact save/verification because its temporary Gradle directory
remained nonempty during cleanup. Scoped cleanup retry succeeded; zero daemons
needed stopping. Artifact/sidecar retained; no build rerun.

Default staging/internal-test flags are false; production checkout requires fresh
server gate and exact-reader/book/mode consent before SDK payment. Read-only
server check confirms production_checkout_enabled=false; Play sync deferred=true,
enabled=false. Restore and server-only unlocking unchanged. Existing 62 PHP /
474 assertions and 19 Flutter plus staging-identity check reused; no broad/phone
suite or backend deployment. Installed Android app remains unchanged.

Next: owner final launch catalogue/rights, launch prices/regions and missing manual
Play products, then separately approved release/targeted acceptance. Financial
retention unresolved; actual provider erasure, production-event/purchase acceptance,
second-phone restore, remaining reading/import/offline acceptance, alert delivery
and replacement-host cutover remain unverified/deferred as previously recorded.
Real sales OFF, automatic sync DEFERRED, Steps 4/5 open. This completes artifact
preparation, not launch approval or installed-app acceptance.

## D14 metadata read permission verified — 8 October 2026 UTC (7 October local)

Owner saved Customer information → Read only on the existing V2 key. One repeat
of the previously denied customer-attributes GET at 00:13:34 UTC returned HTTP
200 with the expected paginated-list shape. Existing credential reused; no
customer data/secrets displayed or provider write/customer deletion performed.
**Metadata read permission blocker RESOLVED.** Earlier 403 entries are historical.
Read-only database counts: zero unfinished provider-deletion jobs and zero jobs
eligible for deleted-reader cleanup. Nothing needs draining now. The deployed
approved worker can read metadata and uses the existing separate V1 credential
for POST `/v1/subscribers/{reader_id}/attributes` null-value removal, then V2
read-back. No additional permission is established as necessary; no write probe
was performed. Real provider erasure remains unverified until a genuine approved
deletion creates eligible work. Immutable metadata, if encountered, still requires
review; whole-customer deletion remains prohibited. Financial expiry unresolved;
D14 is not declared fully complete. Real checkout=false, Play sync deferred=true.
Prior 89 PHP / 656 assertions and 25 backup checks reused; no suite/deployment.

Prioritized remaining work (supersedes historical construction recommendations):
1. **Construction/release preparation:** no newly established generic purchase,
   accounting, deletion-journal or backup-code gap. Prepare/validate the implemented
   production-mode Android artifact in a separately approved task, with real
   checkout OFF and internal-test checkout opt-in absent. Installed app does not
   yet contain this release-mode source. Financial-expiry implementation waits for
   an owner policy; do not invent or apply a log/backup deadline to money records.
2. **Required launch decisions/configuration:** D16 final books/rights and final
   prices/regions (USD 2.99 is test-only); manually complete selected Play products
   (last verified shelf_book_3 exists, 4–8 absent), exact RevenueCat mappings;
   signing/OAuth, listing/Data safety/privacy/support review; confirm removal of
   only four temporary account-level Play grants, preserving app access; separately
   approved old-app retirement. Owner launch approval must precede gate enablement.
3. **Deferred targeted acceptance/operations:** release-app reading/real Word import,
   remaining offline/account isolation/withdrawal/UX checks, second-phone restore
   DEFERRED / NOT PASSED, actual backup alert delivery and full replacement-host
   cutover. Actual production-event delivery and real purchase/refund/acknowledgement
   acceptance await separately approved launch testing. Completed sandbox/refund,
   isolated recovery and October 7 automatic backup evidence must not be repeated
   merely for status. Automatic price sync stays DEFERRED, outside this queue.

Recommended next task: prepare and validate the production Android release
artifact behind the disabled real-sale gate, without uploading or launching.
Steps 4 and 5 remain open; no new app build or launch work performed here.

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
Final implementation e387b3619a023418ab9851bc1ab3e8dc39e4d344 passed identical-commit
staging: 89 PHP tests / 656 assertions and 25 backup checks (9 D14 plus 16 existing).
Passing log hash was reverified; no suite or phone tests repeated on resume.
Promoted successfully from 34a01db after fresh verified backup
`/home/shelf/backups/shelf/20261007-231416/`; previous reader, purchase, refund,
ledger and agreement snapshots and the cPanel handler remain unchanged.
Production and staging release-state both identify e387b36. Records-only follow-up
is separate from this tested application release and is pushed with its ancestors.
Resume found a clean isolated worktree at e387b36 and remote main at f024dbe;
unfinished operations were final promotion and push. The final staging command
succeeded. An archived development exit-1 test failed with an invalid isolated
Laravel compiled-view cache path; current isolated recovery creates that path and
all 25 backup checks passed. The exact last shell command mentioned in the
interruption is not preserved in release logs; no final staging/promotion failure
is evidenced. No implementation restart or live data repair was performed.
Activation completed 22:54:41 UTC after verified backup
`/home/shelf/backups/shelf/20261007-224743/`: encrypted OneDrive journal read-back,
14 local/7 remote backup identity inventories, zero absent identities to record,
dry-run zero expiry candidates/exceptions and 03:45 maintenance job installed.
No live reader was deleted. Existing jobs/credentials remain unchanged.
Read-only existing offsite evidence also confirms the 7 October scheduled run
finished 07:32:37 UTC with recovery_verified=true for the pinned
`shelf-production-20261007T073043Z-a319d3debfd0e839.tar.gpg`; no backup rerun was
performed to establish that finding. Actual alert delivery and full replacement-host
cutover remain unverified; second-phone restore remains deferred pre-release.
**Provider configuration blocker:** read-only V2 customer-attribute verification
returned HTTP 403 on 7 October. Resume read-only comparison returned HTTP 200
for existing project/webhook access and HTTP 403 for customer attributes; the
provider response explicitly named `customer_information:customers:read`.
Exact endpoint: GET `https://api.revenuecat.com/v2/projects/projbbce26da/customers/{numeric_reader_id}/attributes`.
The current encrypted independent journal was fetched and validated again (zero
records); journal directory 0700, baseline/retention configuration 0600.
Public checkout configuration still reports production_checkout_enabled=false.
Existing key lacks usable customer metadata read access. No provider write, grant or credential change occurred. Retryable metadata
cleanup remains pending. Owner: RevenueCat → Shelf (projbbce26da) → Project
Settings → API keys → existing V2 configuration key used by Shelf → Customer
information → Read only (`customer_information:customers:read`); preserve all
existing permissions. If this dashboard does not offer editing, stop and report
that limitation: official documentation establishes creation/revocation and the
required permission but does not establish in-place permission editing. No key
replacement is authorized. See D14 runbook for the private key-path identifier.
D14 is NOT fully complete: provider metadata cleanup is blocked, immutable
metadata exceptions require reviewed resolution, and financial expiry is undecided. This is unrelated to
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

- **10 October 2026 — iOS foundation authorized:** owner confirms active Individual Apple membership, Team YSLWJQDH8B, explicit services.shelf.app with primary Sign In with Apple, and Shelf - شیلف / iOS 1.0 Prepare for Submission. Isolated foundation and manual-only unsigned Codemagic configuration prepared from accepted source; server source/shared tests passed. No cloud build, signing success, Apple login/purchases, iPhone acceptance, paid activation, upload, deployment or Android rebuild claimed. Checkout OFF, deferrals and joint launch hold preserved.

- **10 October 2026 — owner acceptance and pause:** current Shelf Review 3 work accepted; further Android/UI changes paused. Owner reports Google Play **1.0.11 (20)** approved for **Closed testing – Alpha**, latest screenshot **“Changes ready to publish”**, **Managed publishing ON**. Publishing completion and Play-installed build-20 phone acceptance **NOT CONFIRMED**, not passed. Completed backend promotion/evidence preserved; checkout OFF, automatic pricing-sync and second-phone-restore deferrals and joint Android/iOS public-launch hold unchanged. Master Record only; no implementation, rebuild, deployment or store action.

- **9 October 2026 — Review 3 owner feedback:** exact د لوست سیټینګ wording;
  supplied launcher mark with distinct Shelf Test badge; supplied poetry icon for
  genuinely untitled poems and localized accessibility; Pashto/Dari list digits
  ۰۱۲۳۴۵۶۷۸۹ with English ASCII. Source 340142b, focused checks passed. Next:
  corrected phone review, then Google Play test-track update; public Android
  release held for joint iOS launch. No build/deploy/upload in this correction.

- **9 October 2026 — owner authorized Review 3 staging and phone-review build:**
  staged 9a748ef with verified staging backup and reversible avatar migration only
  on shelf_staging; produced compatible Shelf Test 1.0.9 (18), same package/signer,
  staging endpoint/Test identity. No production deployment/build/migration/upload.
  Focused staging/avatar and artifact evidence passed; owner phone acceptance
  pending. Cover-photo purpose, iOS readiness and all existing deferrals/joint
  launch remain unchanged. See Part 10 for exact private APK path/evidence.

- **9 October 2026 — owner-approved Shelf Review 3:** supplied original logo/icons,
  Afghan Dari interface alongside پښتو and English, pinned live sharing controls,
  Contents-only footer label, localized font names/minimum 16, سیټینګ, private
  account avatar replacement/removal and removal of repeated search instruction.
  This supersedes earlier two-language interface scope; account/purchase English
  policy is retained. Second photo/cover purpose remains undecided. Isolated
  implementation and focused checks/records only; no production deployment,
  live migration, release build/upload or real transaction. Joint launch and
  existing checkout/pricing/restore deferrals remain binding.

- **8 October 2026 owner approval; implementation continued 9 October:** Figma
  Review 2, file NVSrbPa6eJJVUfIK3vdUeJ, board 7:5, approved at the URL above.
  Authorized shared Flutter implementation of Store, Book details, My Library,
  Search, Reader and Reading preferences, focused checks and project records.
  Preserve working features, original content/credits, store localized prices,
  access rules and new governing records. No production changes/deployment,
  store upload, real transaction or checkout activation. Android phone acceptance
  pending; iOS validation separate; joint launch and existing deferrals preserved.

- **8 October 2026 — owner-approved joint-launch/work-order amendment:** four areas:
  Admin/backend, Android, iOS, shared design/UX/UI. Android and iOS launch together
  only after both are complete, accepted and ready, both store reviews complete,
  and final owner launch approval. Supersedes Android-only Version 1 / “iOS later”.
  Order: inspect iOS readiness (Flutter, Apple account, Mac/build access, signing,
  purchases); agree/improve shared design; complete iOS and remaining backend;
  accept both apps/fix demonstrated issues; complete both reviews/coordinated launch.
  Complete practical batches, shared code/passed evidence, one four-area remaining
  list; optional improvements outside release scope. Play 1.0.6 (15) owner-confirmed
  approved for closed testing; further Play preparation paused until final release
  needs it. Checkout OFF, three-year filing-based financial policy/continuing recovery
  proof retained, automatic expiry absent, pricing-sync/second-phone deferrals retained.
  Documentation only; no implementation/build/deployment/purchase/release or commit/push.


- **8 October 2026 — financial-retention owner approval:** accounting and tax-supporting
  records retained three years after the relevant tax return is filed; affected records
  held longer for applicable legal requirements, unresolved disputes or unpaid author
  balances. Minimum purchase proof retained while purchased-book recovery rights continue;
  three-year expiry must not break recovery. Seven-year proposal and one-year alternative
  NOT APPROVED. Documentation/implementation plan only; automatic financial expiry absent.
  Checkout OFF, sync DEFERRED, second-phone restore deferred and not a launch blocker
  without later owner decision. No deletion/production changes/rebuild/deployment.

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
| Flutter app        | The shared-code Android and iOS apps readers install.                                                                     |
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

These decisions remain binding except where superseded by later dated owner amendments. The 8 October 2026 joint-launch amendment in Parts 2, 7–10 and 11 supersedes “App Store later with an iOS version” and Android-only release scope. Original rows below remain historical evidence; other unsuperseded decisions continue to apply.

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
| D15 | App interface languages and wording | **Decided 9 Oct Review 3:** پښتو، دری، English; Afghan Dari interface implemented in isolation; real-phone wording/layout acceptance pending. Existing account/purchase English policy retained. |
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
