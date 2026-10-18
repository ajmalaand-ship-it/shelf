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

**Standing owner instruction — 12 October 2026:** automate everything possible
through Codex within the approved scope. Request manual action only where owner
interaction or access is genuinely necessary. Reuse known verified identifiers
and completed evidence; do not ask for repeated setup or manual work Codex can do.

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

## Apple review sandbox offers — 18 October 2026

Owner authorizes one batch: ordinary verified reviewer with no ownership, separate
Apple sandbox offers while real checkout stays OFF, private App Store Connect login
update, focused staging-first backend promotion and reuse of Build 8. Existing blocked
reviewer/grants and Google Play must remain unchanged. Separate default-false
SHELF_APPLE_CUSTOMER_SANDBOX_ENABLED opens the existing Build 8 customer protocol
(the legacy production_checkout_enabled field means offer availability). Explicit
real_checkout_enabled=false and sandbox_only=true distinguish runtime acceptance.
New production transactions remain rejected before ledger/access changes; sandbox
requires existing independent matching provider proof and remains Test income.
No client change, migration, complimentary grant or special reader role. Rollback:
close the two customer flags and use prior c8c4d74 code through release workflow;
retain all account/purchase history. Deployed backend **fa2bec0** to staging then identical production commit. Staging
backup `/home/shelf/backups/shelf-staging/20261018-150541/`; production promotion
backup `/home/shelf/backups/shelf/20261018-150638/`. Focused wrapper passed
**78 PHP tests / 843 assertions**; no mobile source change or Flutter rerun.
Staging/production health/isolation/history checks passed. Separate pre-account/
configuration backup `/home/shelf/backups/shelf/20261018-150908/` includes verified
encrypted configuration rollback checkpoint. Existing customer/ownership rows compared
unchanged; original reviewer 12 remains blocked with all six grants.

New ordinary reviewer **19** registered through AuthController; normal verification
mail dispatched and generated one-time token consumed through EmailActionController.
Verified, unblocked, no grants/entitlements/purchases; no administrator or special
ownership role. No suitable existing test login with known credentials was available;
other zero-ownership accounts were not repurposed. Credentials remain only in protected
`/home/shelf/secrets/apple-review-purchasing.json` and App Store Connect.
Only SHELF_APPLE_CUSTOMERS_ENABLED and SHELF_APPLE_CUSTOMER_SANDBOX_ENABLED enabled;
all unrelated configuration compared unchanged, real checkout remains OFF.

Live HTTPS login/me/Library and **six successful per-book consent responses** verified
for canonical products 3–8 on stable reader identity; Library remains empty, session
revoked. Google Play public configuration compared unchanged. These prove server offer
access, not native StoreKit price-sheet rendering or a purchase. App Store Connect
reviewer login/accurate sandbox notes PATCH **200**, exact private GET readback **200**;
existing review contact unchanged. Build **8** association independently reconfirmed;
no rebuild/upload/submission/public release. Actual Apple sandbox purchase/restore/
refund evidence remains **NOT VERIFIED**. One remaining action for this task:
exercise an actual Apple sandbox purchase using the saved reviewer login on Build 8.


## Screenshot Back-navigation correction — 18 October UTC 2026

Owner reports screenshot run #2 reached Book3 and failed at capture line78:
`tester.pageBack()` found two Back buttons. Replaced all three capture Back calls
(sample, book details and optional signed-in account) with navigator pops resolved
from the known active screen route, asserting that route is current. No global
Back-button finder remains. Existing screenshot capture/upload logic unchanged.
Focused wrapper analysis: **no issues**. **3 checks passed**: duplicate visible
Back buttons with nested sample/details routes, plus six-book iPhone and iPad
navigation. Mobile source, purchases, dependencies, signing and Apple settings
unchanged; Build8 preserved and checkout OFF. Corrected cloud screenshots/upload
remain pending; retry **ios-submission-screenshots** on corrected `ios/foundation`.
No new App Store build or submission. Sandbox transactions remain DEFERRED, not passed.

## Screenshot capture navigation fix — 18 October UTC 2026

Owner supplied failed Codemagic run **6ad430b17550aed8108fbaee**, workflow
**ios-submission-screenshots**, source **93ba607**. The line60 wait timed out on
Search-result title/key matching. Store readiness and its capture had already
returned; optional reviewer login had not been reached. The excerpt does not
establish why the Search result was absent. Exact six-title public API queries
returned the correct books; a focused keyboard-open portrait reproduction also
found the old predicate. No keyboard/backend diagnosis is claimed.

Capture automation now opens actual visible Store titles by canonical book IDs
3–8, using live model slugs and checking the resulting detail route. Removed the
unnecessary Search dependency; timeout errors identify the capture stage/device.
Focused wrapper checks: Dart analysis **no issues**, **2 tests passed**, exercising
all six books on iPhone 430x932 and iPad 1032x1376 layouts, scrolling/returning and
zero Search requests. Test fixtures are regression inputs only, never store assets.
Mobile source tree remains **1aecaddba930910a4110a660612365bf4abf8d84**; Build8,
Apple metadata/settings, checkout OFF and release workflows unchanged.

**Corrected Mac capture/upload remains pending**, not passed: no authenticated
Codemagic execution access is available here. One owner start of
**ios-submission-screenshots** on the corrected `ios/foundation` commit will run
the existing genuine screenshot capture/upload/processing checks. No release build
or submission. Apple sandbox transaction verification remains **DEFERRED, NOT PASSED**.

## Apple age/review saved; simulator asset workflow ready — 18 October UTC 2026

Owner authorized simulator-only captures on existing Codemagic Macs, genuine listing
and IAP asset upload, factual age assessment, private contact lookup and DSA read-only.
Read all 342 published works across books3–8 (81/67/57/74/22/41), 74 existing Book6
artwork pages and six covers; no source/content changed. Verified recurrent drug/
alcohol, weapons, war/realistic violence and mature themes; occasional mild language,
fear and romantic intimacy. No gambling/contests, public UGC/chat/social feed,
advertising, unrestricted embedded browser, age assurance/parental controls or
medical/wellness advice functionality. Owner specifically confirmed prolonged
graphic/sadistic realistic violence **NONE** after poem51 was identified. Other
explicit/graphic sexual and fantasy-violence descriptors NONE. Questionnaire PATCH
**200**, related GET **200**; Apple normalizes frequency enums to legacy equivalents.
Calculated API appStoreAgeRating **SEVENTEEN_PLUS**, Brazil **EIGHTEEN**; overrides
unchanged. The initial incomplete PATCH 409 saved nothing; completed questionnaire
supersedes earlier missing-age entries. No unsupported all-None classification.

Private search of 110 authorized store JSON records and relevant docs found no
verified phone. Owner then supplied/confirmed contact phone; saved only to Apple,
not Git. Existing verified reviewer credentials reused from protected file in memory.
Review-detail POST **201**, GET **200**, exact contact/login/notes readback verified
without printing private values. Notes honestly disclose complimentary six-book
reader access, samples, email-confirmed deletion and checkout currently disabled.
No remaining contact or content input requested.

New manual workflow **ios-submission-screenshots** / **Shelf — genuine iPhone and
iPad screenshots (simulator only)** uses existing Mac/integration/groups. Real
production app/catalogue navigation and native iOS screenshots; debug capture
entrypoint selects existing public data source, no fixtures or mocked app UI.
Build8 mobile tree **1aecaddba930910a4110a660612365bf4abf8d84** asserted unchanged;
release source/workflows untouched. Capture-only SDK instrumentation is worker-local.
Existing reviewer variables are reused if already available; public catalogue and
book-detail captures do not require login. No new credential setup/transfer needed.
Screenshot-only uploader reuses the proven CLI credential-resolution route and its
official ephemeral JWT cache, not the prior unsupported raw-key environment assumption.
Uploads listing captures and six phone IAP images via official asset APIs, compares
checksums, polls COMPLETE and preserves existing completed assets; no build upload,
signing, submission or publishing. Native iPhone/iPad dimensions validated, no resize
or invented screens. Image-generation/widget-render fixtures are never store assets.

Focused capture Dart analysis **no issues**; wrapper **4 synthetic upload checks
passed**, covering all images/six IAPs, existing-asset preservation, processing failure
and existing CLI auth-cache resolution. Work-in-progress selector/import/fixture
issues corrected. Existing workflows and mobile source compared unchanged. No
repeat app suites. **Mac run/captures/Apple image processing NOT YET PERFORMED**:
agent still lacks Codemagic API token/connector. Owner starts exactly this workflow
once on the latest `ios/foundation`; it automatically captures/uploads/verifies assets.
No new release build. DSA availability readback still **TRADER_STATUS_NOT_PROVIDED
in 26 selected EU territories**; underlying account verification is not exposed by
available API, no declaration/country change. Build8 association, compliance, prices,
territories, privacy and MANUAL release preserved. Checkout OFF; Apple transaction
verification remains **DEFERRED, NOT PASSED**. No submission/publication.

## Remaining Apple fields automated — 18 October UTC 2026

Saved and independently read back app availability and products 4–8 availability:
exact **174-territory existing Apple Book 3 set**, France excluded, new territories
opt-in false. Book 3's existing set preserved and compared with prior captured set;
all six match. App creation first returned 409 requiring explicit excluded-territory
rows; corrected request includes all Apple territories with only approved 174 true,
POST 201. Five IAP POSTs 201; all readbacks match. No price/rights changes.
Build 8 PATCH 200 / GET confirms **usesNonExemptEncryption=false**, VALID and
APP_STORE_ELIGIBLE. Reused approved standard outside-OS AES-256-GCM/non-France
no-document basis; dependency/native/encryption implementation unchanged. Version
1.0 remains PREPARE_FOR_SUBMISSION / MANUAL with Build 8 attached.

Actual app availability statuses include **TRADER_STATUS_NOT_PROVIDED in the 26
selected EU territories** (France excluded), and generic **CANNOT_SELL in all 174**;
API gives no further cause for generic status, no guessed tax/agreement diagnosis.
Owner must complete/resolve the DSA trader declaration in App Store Connect;
legal declaration cannot be invented or saved through available public API.

Outstanding inputs remain: real contact phone with international country code
(known verified name/email/reviewer credentials reusable; Apple requires phone to
save review details); catalogue-wide content frequencies for age rating (profanity,
horror, alcohol/drugs/tobacco, mature/suggestive, sexual/nudity, graphic sexual,
cartoon/fantasy violence, realistic violence, prolonged graphic/sadistic violence,
weapons, medical/treatment, gambling references/simulation and contests; wellness
recommendations yes/no). Known app feature answers retained, not guessed catalogue
answers; prior API refusal to save incomplete questionnaire reused, not repeated.

Source targets **iPhone and iPad (1,2)**. Linux has no xcrun/xcodebuild/simulator,
connected iOS capture or authenticated remote Mac route; no suitable genuine local
screenshots. Need original PNG/JPEG listing captures at iPhone 1320x2868 or1290x2796
and iPad 2064x2752 or2048x2732, plus actual book-detail/purchase-area review capture
for each of books3–8. No invented images, resizing, gate activation, rebuild or
upload of unsuitable assets. No listing/IAP assets uploaded. Manual inputs can be
supplied together; existing Apple reviewer/account evidence reused, no broad audit.
No submission/publication, checkout change, tests or app edits. Sandbox transaction
verification remains **DEFERRED, NOT PASSED**. Prior uncommitted record preserved.

## Build 8 attached to public-review draft — 18 October UTC 2026

Owner reports successful Codemagic run **6ad41bb47550aed8108fb7e5**, workflow
`ios-app-store-release`, source **47f0e8e**. Apple API independently confirms
**1.0.0 (8)** upload **COMPLETE** (no errors/warnings), processing **VALID**, audience
**APP_STORE_ELIGIBLE**; build ID **5e4b0e7d-2e6f-44cf-94fa-c9d9ee40d000**.
Authorized draft build association PATCH returned **204**; GET verified that exact
build on version **1.0**, ID **47de1d04-6b51-4338-be3a-1e26343a057a**. Version
remains **PREPARE_FOR_SUBMISSION**, release **MANUAL**. Prior association was empty.

Current API-visible submission gaps: en-US listing has **zero screenshot sets**;
age declaration frequency/feature answers null and no calculated age rating;
App Review details null (contact/login not saved; prior genuine phone missing);
all six `shelf_book_3`–`shelf_book_8` non-consumables **MISSING_METADATA**, with no
review screenshots; products 4–8 availability absent (404), Book 3 present;
app availability absent (404). Build 8 **usesNonExemptEncryption=null**: export
compliance answer remains unresolved. No speculative additional blockers: published
privacy, agreements/tax/banking and UI-only legal questionnaires were not independently
revalidated through unavailable APIs. Existing approved rights/prices/France exclusion
unchanged. Next action: obtain genuine iPhone/iPad listing captures and purchase-offer
review captures from Build 8 to fill the confirmed missing assets.

Only build association and this record changed. No rebuild/tests/checkout activation,
review submission or publication. Apple sandbox transaction verification remains
**DEFERRED, NOT PASSED**; build eligibility is not purchase acceptance.

## Single App Store-eligible build authorization — 17 October local / 18 October UTC 2026

Owner authorized ONE release build from **47f0e8e1ecf4ec5825f8c05d20231e229f7e04cf**,
branch `ios/foundation`. Clean source verified. Existing `ios-app-store-release`
already exports testFlightInternalTestingOnly=false with explicit verification;
no source/workflow changes or repeat tests needed. Existing signing and passed
six-book tests/backend reused. Both review submission switches remain false.

Apple API readback **18 October 2026 00:59:28 UTC**: build and build-upload lists
HTTP 200; uploads 1–7 consumed, builds 2–7 VALID / INTERNAL_ONLY; no build/upload 8.
**Next unused number: 8 (not reserved).** No new build started or uploaded and
APP_STORE_ELIGIBLE / VALID cannot yet be claimed. No Codemagic token environment,
stored Codemagic credential or authenticated connector is available to this agent;
existing dashboard signing authorization is not API access.

Unavoidable owner action: in https://codemagic.io/ open existing Shelf app, set
existing `shelf_ios_testflight` variable `SHELF_IOS_BUILD_NUMBER=8`, then start
exactly ONE build on `ios/foundation` at **47f0e8e** using workflow
`ios-app-store-release` (Shelf iOS — App Store eligible release (manual)). Retain
existing integration/signing/environment groups. Return its run link for Apple
receipt/audience/processing verification. No alternate/internal build. Checkout
remains OFF; no submission/publication. Apple sandbox transaction verification
remains **DEFERRED, NOT PASSED**. Only this record changed; build source stays pinned.

## Normal iOS customer purchases and restore — 17 October local / 18 October UTC 2026

Owner authorized six-book customer purchase/restore implementation, focused checks,
staging/production rollout with real checkout OFF, final eligible-build preparation
only, and commit/push. Prior uncommitted Apple submission record preserved in its
own commit **f27808c**. Implementation **d685f6a**, mixed-store correction and final
application **c8c4d74383292069f5f58583610eaef88da9b423**.

Ordinary verified readers use authenticated consent version 2 and their stable
numeric Shelf/RevenueCat identity for all six canonical Apple non-consumables.
Existing independent V2 store/app/product/customer/time/environment/entitlement
proof, shared access and append-only sale/refund/reversal ledger extended; each
book retains its purchase-time author agreement. Actual sandbox evidence stays
Test, unknown production net remains unknown. Restore uses native restore followed
by server proof for all six, including delayed webhook confirmation. No receipt or
SDK CustomerInfo grants access by itself. Checkout closure preserves legitimate
customer purchase history/access and refund reconciliation. Existing Play ownership
and complimentary grants prevent another checkout; legitimate in-flight proof is
retained. Demonstrated same-customer/same-time mixed-store case fixed by excluding
Apple rows from Play proof. Owner-only session experiment retained disabled, not
reopened. No schema change or historical-data rewrite.

RevenueCat Apple products mapped to existing same-book entitlements, retaining
prior Play links: book 3 prod0d084e901f unchanged; book 4 prod9312fbe695,
5 prod37f03f5fd7, 6 prodee1160bb99, 7 prod80d1364794, 8 prod0a2413becd.
Existing Apple webhook **whintgr769cb03ff2** changed only environment sandbox→null
(Both); GET/POST/GET 200, other fields/integrations compared unchanged. No test
notification sent; actual production delivery remains unverified. Evidence:
`/home/shelf/tmp/shelf-ios-customers-20261017/` (private sanitized mapping/webhook
records); existing credentials unchanged.

Focused wrapper checks passed locally and on final staging commit: **77 PHP tests /
806 assertions, 36 Flutter purchase tests plus 1 staging-identity test**; existing
build-route wrapper **8 tests** passed. Test development failures corrected; no
broad audit, Android build or Apple transaction. Staging backup
`/home/shelf/backups/shelf-staging/20261018-002655/`; final staging checks passed.
Production backup `/home/shelf/backups/shelf/20261018-003336/` SQL/ZIP/SHA-256 verified;
identical **c8c4d74** promoted. Strict prior reader/token/purchase/refund/ledger/
entitlement/agreement fingerprints unchanged, cPanel handler preserved, maintenance
lifted and production/staging HTTPS/isolation/access checks passed. Existing bounded
refund-runner lock reused; no cron changes or historical preservation allowance.
Rollback code: prior production **4aa2f613778d58a3ced8034dd95921cfea092bd6** through
existing release procedure; retain all later financial/reader history, no bulk restore.

**Runtime gates remain OFF:** real checkout, new default-false
SHELF_APPLE_CUSTOMERS_ENABLED, both legacy Apple sandbox gates, pricing sync and
Apple sign-in/relay. Staging forces Apple off. No checkout activation, build,
submission, price/territory/rights change or public release. France exclusion and
joint Android/iOS hold preserved. Existing Apple products still need their recorded
review assets/availability and other store metadata; those are not transaction proof.

**One final build prepared, NOT STARTED:** manual Codemagic workflow
`ios-app-store-release`, export testFlightInternalTestingOnly=false and matching
assertion, existing signing/identity/explicit unused-number checks and both review
submission switches false. No new dependencies/native code. Recheck unused number
when separately authorized (8 was the existing candidate), then require Apple's
APP_STORE_ELIGIBLE readback. Source implementation is complete; signed-build/device
and live purchase/restore/refund acceptance are not claimed. Apple sandbox transaction,
entitlement/webhook/accounting acceptance remains **DEFERRED, NOT PASSED**.
Next action: authorize the single final App Store-eligible build from completed
`ios/foundation` source; do not start another intermediate build.

## Apple public-review preparation — 17 October 2026

Batch 1 authorized metadata/assets and release-access preparation only. Apple API
before/after evidence: `/home/shelf/tmp/shelf-asc-submission-20261017/` (private
0600 files). Parts 7/10 govern completion; no separate Completion Plan file found.

**Saved/read back:** app 6821471140 / services.shelf.app identity unchanged;
USES_THIRD_PARTY_CONTENT; Books category; subtitle “Pashto and Farsi books”;
English description/keywords, support URL to existing privacy/contact page and
privacy-choices URL to account/delete; copyright “2026 Ajmal Aand”; approved free
app price USD 0. Version 1.0 stays PREPARE_FOR_SUBMISSION, release control now
MANUAL instead of AFTER_APPROVAL, preserving joint launch. Book 3 title correction
retained: څپو کې انځورونه / شعري ټولګه, ar-SA.

**Six approved paid books need six non-consumables.** Existing shelf_book_3:
6822080809. Created draft shelf_book_4 **6824083153**, shelf_book_5 **6824083087**,
shelf_book_6 **6824083088**, shelf_book_7 **6824083193**, shelf_book_8 **6824083291**.
Exact catalogue titles/subtitles (Book 6: existing poetry classification), ar-SA
matching Book 3, approved USD 2.99 USA base prices verified. All MISSING_METADATA;
review captures absent. New drafts have no assigned availability (404). Book 3's
174 territories/FRA exclusion unchanged. App-level availability also returns 404;
no regions selected, no assumed worldwide rights, no RevenueCat mapping change.

**Access:** reviewer login/me/Library 200, all six Library books; normal logout 204.
Six public contents endpoints 200, two sample items each. Privacy/deletion page
200; prior deletion evidence reused, no deletion or email sent. Apple rejected
review-details creation (409 required/invalid contactPhone), so reviewer credentials
were NOT saved. Known name/email/instructions available; genuine contact phone missing.
Partial factual age answers also rejected (409 remaining required attributes);
readback confirms none saved. Literary-content frequency answers require owner facts.

**Privacy/legal limits:** official Apple OpenAPI 4.5.1 exposes no App Privacy label,
DSA, agreement/tax/banking or Digital Goods questionnaire endpoints. Published eight
privacy types/active financial agreements remain owner-confirmed, not independently
reread; earlier DSA In Review unconfirmed today. Source/prior hosting evidence
requires label review for name, email, private photos, user IDs, purchases, searches,
request interactions, IP-derived country statistics and service diagnostics;
actual published types/purposes/linkage and onward integrations cannot be certified
through this API. No speculative label edits. Digital Goods is UI-only/conditional
if displayed; EU choices lock 12 months, no irreversible choice made.

**Assets missing:** no App Store screenshot sets, TestFlight feedback screenshots
or genuine local iOS captures; widget renders/artwork and an unavailable-state phone
photo are unsuitable. Linux has no xcrun/simulator/connected capture route. Need
original device or accurately running simulator captures: iPhone e.g.1320×2868 or
1290×2796; iPad 13-inch 2064×2752 or 2048×2732 (device family 1,2), opaque PNG/JPEG;
plus genuine Book 3 purchase-offer review capture. Nothing uploaded or processed.
Do not enable checkout/restart deferred tests to manufacture a capture.
[Apple specifications](https://developer.apple.com/help/app-store-connect/reference/app-information/screenshot-specifications/).

**Demonstrated purchase blocker:** mobile forces iOS sandbox; LibraryController
returns production_checkout_enabled=false/only shelf_book_3; ApplePurchaseProof
and AppleSandboxPurchases require owner-only Book-3 SANDBOX evidence. Normal
customer/reviewer six-book Apple purchase/restore and deleted-account Apple recovery
are not supplied by this source. No development started. Apple transaction,
entitlement/webhook/accounting verification **DEFERRED, NOT PASSED**; authentication/
relay deferrals preserved. Public config confirms production/sandbox checkout OFF,
Apple sign-in OFF, email/Google account config available. No transaction attempted.

**Minimal eligible-build change, not executed:** all received builds 2–7 VALID /
INTERNAL_ONLY, uploads through 7, no 8. HEAD 26864fa mobile matches accepted Build 7
eafea353 (empty diff). Change codemagic.yaml testFlightInternalTestingOnly true→false
(line 101) and matching assertion (102); retain app-store signing, identity checks,
manageAppVersionAndBuildNumber=false and submission switches false. Freshly verify
unused number (8 candidate), upload once only when authorized and require Apple
buildAudienceType=APP_STORE_ELIGIBLE. Packaging alone does not resolve purchase,
capture, age, review-phone, availability or UI-only declaration/access gaps above.
No source/workflow edits, build, deployment, submission or launch; no existing book
price/territory changes. Joint Android/iOS hold and France exclusion preserved.

## D14 reporting/disclosures deployed — 16 October local / 17 October UTC 2026

Completed application commit **4aa2f613778d58a3ced8034dd95921cfea092bd6**
is identical on staging and production, as recorded in release-state.json.
Owner-only Admin: https://shelf.services/admin/financial-retention
Public owner-review pages: https://shelf.services/privacy and
https://shelf.services/account/delete . No store declaration/provider setting saved.

Evidence: isolated wrapper **41 tests / 241 assertions**, including staging workflow
gate; identical staged application **40 focused tests / 240 assertions** covering
retention, public wording, accounting and existing D14 recovery/deletion behavior
with synthetic/mocked providers. No mobile files changed in this task, no mobile
suite/build and no Apple purchase test. Actual deployed Admin HTTP-kernel route
rendered on both copies with an in-memory owner session only; real page, report,
evidence history and actions present, no persistent login or live metadata write.
Public privacy/deletion HTTPS returned matching approved wording on both copies.
This is server-render verification, not a claimed interactive browser/owner review.

Verified backup before staging migration:
`/home/shelf/backups/shelf-staging/20261017-003109/`.
Verified production backup before maintenance/migration/promotion:
`/home/shelf/backups/shelf/20261017-003141/` (SQL, ZIP and SHA-256 passed).
New metadata migration is production **batch 30**; **zero metadata rows** were
seeded or entered. Report: **2 purchase groups, 0 eligible, 2 blocked/retained**.
Strict existing reader/token/purchase/refund/ledger/entitlement/agreement/journal/
share fingerprints matched across promotion. Production healthy and maintenance
lifted; cPanel handler preserved as the sole pre-existing production working edit.
Required runtime flags (real checkout, Apple sandbox and owned-book exception,
price sync) verified OFF. No deletion, provider cleanup or expiry executed.
A sandbox-denied optional migration-batch read was repeated with approved server
access and passed; no database/service failure occurred.

Append-only filing/mapping/hold/report foundation and disclosure construction are
complete for this batch. Current approved policy is unchanged. No tax filing date,
jurisdiction or mapped financial evidence invented; owner can enter real evidence
when available. Financial minimisation/expiry remains **unimplemented/OFF** and
requires the separately reviewed preservation/disposal plan before any later
execution. Actual provider metadata erasure remains unverified, conditional on
genuine approved work; external privacy evidence, operational/device acceptance
and store/final joint-release requirements remain as recorded. D14 is not declared
fully complete. Apple purchase testing remains closed/deferred, no builds, France
exclusion and every existing release hold preserved.

Rollback: previous production code **1d3dfa5abc1bb35097ada6f2a69fb003c2e57b19**;
retain additive metadata/evidence if reverting code. Empty-table migration rollback
is tested; populated rollback refuses data loss. Never restore over later reader
or financial activity. Existing source/recovery data and journals unchanged.

**One next release milestone:** joint Android/iOS release-readiness acceptance and
store-review readiness, preserving owner-deferred tests and requiring separate
explicit launch authorization. No next milestone work started by this batch.


## Authorized D14 Admin reporting and disclosure batch — 16 October 2026

Owner authorized completion of the remaining release-required filing/mapping,
scoped holds, owner-only financial-retention reporting and public privacy/deletion
disclosures. Implemented in isolated ios/foundation; the two pre-existing
uncommitted 16 October status entries below are preserved without alteration.
Additive append-only metadata only; no existing financial/customer/ownership data
is mapped, changed or deleted. Actual financial expiry/minimisation remains OFF
and unimplemented; reporting eligibility is preservation review only. Approved
three-years-after-filing policy and scoped exceptions unchanged. See
[D14_DATA_POLICY.md](D14_DATA_POLICY.md) for behavior, recovery dependencies and
future disposal boundary. Deployment/evidence is recorded after passing rollout.
All release holds, France exclusion, owner deferrals and checkout/Apple gates OFF
remain binding. No Apple purchase testing, mobile build or unrelated security work.


## Purchase UI accepted; testing closed; return to joint release plan — 16 October 2026

Owner confirms installed TestFlight **1.0.0 (7)** demonstrated the Apple purchase
screen/button and accepts that UI for now. This is **UI acceptance**, not purchase
acceptance. Owner directs closing testing and moving toward public release; no purchase
development, builds, safeguards or repeated tests authorized.

Immediately returned both existing production Apple sandbox flags to **false**,
preserving unrelated .env values, then config:clear. Runtime and actual HTTPS iOS
config verify both gates OFF, real checkout OFF, enabled/sandbox/production false
and SDK key null. One brief read-only count found **zero APP_STORE / SANDBOX /
collection_id=3 purchases**. **Actual transaction/entitlement/webhook/accounting
verification is DEFERRED, NOT PASSED.** No transaction initiated, history changed,
provider setting edited or test repeated. Earlier open-window instructions are closed.

Reviewed the governing Part 7 completion batches and Part 10 four-area remaining-work
list. No separately named Completion Plan file was found in this repository; no
unseen plan is claimed read. Admin financial-retention construction/disclosure alignment
remains unfinished; Android public-release/current Play-installed acceptance remains
unconfirmed, iOS is internal TestFlight with accepted purchase UI and deferred transaction/
authentication evidence, and shared Review 3 UX remains accepted with further changes
paused. Store reviews/final joint acceptance and explicit launch approval remain open.

**Proposed one next milestone:** finish the remaining Admin financial-retention foundation
and accurate privacy/deletion disclosures before final joint-release acceptance, using
the approved three-years-after-filing policy. **One next action for owner agreement:**
authorize the existing scoped filing/mapping, exception-hold and owner-only retention
dry-run/disclosure task from D14_DATA_POLICY.md, with financial expiry/deletion and
all purchase gates OFF. No new development starts until agreed. Preserve France exclusion,
all existing deferrals and the joint Android/iOS public-launch hold.

## Build 7 available in internal TestFlight — 16 October 2026

Owner-reported Codemagic **#12 / 6ad29daf7550aed8108f6b83** succeeded from
**eafea353d45f4dac82c3454e9f37d702cb621004**. Independently verified Apple app
**6821471140 / services.shelf.app**, **IOS / 1.0.0 (7)**, build/upload ID
**ab8f1ac9-3c85-4351-b466-fe9c2d4db5b7**: upload **COMPLETE**, processing **VALID**,
**INTERNAL_ONLY**, not expired. Compared source against accepted Build 6 b16a8d8:
only Library controller/button and test changes; dependencies, native iOS, Keychain
and encryption implementation unchanged. Reused accepted non-France declaration.
Build-7-only **PATCH usesNonExemptEncryption=false / HTTP 200** cleared missing
compliance; independent readback **READY_FOR_BETA_TESTING** before assignment.

Additive **POST /betaGroups/1511a217-4f89-4dc7-8b8d-4679ce8a6226/relationships/builds**
containing only Build 7 returned **204**. Final readback **2026-10-16 22:16:46 UTC**:
**IN_BETA_TESTING**, VALID / INTERNAL_ONLY, external NOT_APPLICABLE; group-side
builds and relationship preserve every older ID plus 7, group settings and sole
owner tester ID/email unchanged. Only compliance field changed on build attributes.
Private 0600 snapshots: **/home/shelf/tmp/shelf-build7-before-20261016.json** and
**/home/shelf/tmp/shelf-build7-after-20261016.json**. All Apple GETs returned 200.

**Build 7 is available to install:** TestFlight → Shelf → Update/Install **1.0.0 (7)**
(or Previous Builds → 1.0.0 → 7). Installation, button-on-phone and purchase acceptance
remain unverified. Production runtime independently confirms both sandbox gates and
real checkout **OFF**. No build, source change, tests, infrastructure, product/settings
change or public release. France exclusion, existing deferrals, owner automatic-closure
waiver and joint public-launch hold preserved. Only this existing Master Record updated.

## Missing Apple test action fixed in source; gates CLOSED — 16 October 2026

On the owner's missing-button report, **first closed both production Apple sandbox
flags**, preserving every unrelated .env value, and cleared config. Runtime and HTTPS
readback verify sandbox/owned-test OFF, proof disabled, real checkout OFF, SDK key null.
This supersedes the earlier OPEN window below. No Apple sessions or purchases existed
at inspection; no successful transaction is claimed. Prior uncommitted records preserved.

Actual database/catalogue **Book ID 3** is published **څپو کې انځورونه**, slug
**څپو-کې-انځورونه**, product **shelf_book_3**. Existing owner reader is verified,
unblocked and has active Play access. Those IDs are technical identifiers, not the
reader-facing title. Previous instructions incorrectly used “Book 3” as its displayed
name and assumed the test entry was on a Library card.

**Demonstrated Build 6 client defect:** canTestOwnedAppleBook requires checkoutAvailable
from LibraryController.configuration. Login clears that map; Library refresh fetched
ownership and server owner-test permission but never checkout config. My Library did
not load prices/products. Its Read book path uses ownedMode=true, suppressing the
BookPriceLabel that otherwise calls product/configureStore. Thus the owned-book route
cannot discover checkout readiness and hides its test button. The Library card itself
also had no test action. Store details could incidentally warm config via price loading,
but this was not a reliable entry path; configureStore did not notify listeners. Existing
widget tests concealed the cold-state defect by calling product() in shared setup.
This code-path finding explains the reported route; the phone's authenticated state
was not remotely inspected. No new provider-configuration investigation was performed.

**Scoped source fix:** iOS Library refresh now fetches checkout visibility config without
SDK identification/product loading or a transaction; validates APP_STORE, production OFF
and session protocol 1, and publishes it with refreshed server authorization. Failed
config clears stale purchase controls while independent Play Library access still refreshes.
The existing owner/Book3/ownership/authorization/not-confirmed predicate is unchanged.
Added the same guarded **Test Apple purchase — no charge** entry directly beneath
Read book on the title's My Library card, opening the existing explicit-consent test
purchase screen. Existing detail-screen action also benefits from refreshed flags.
No backend, product, ownership, financial, source-book text or permission change.

Focused `scripts/run_tests.sh --mobile`, PHP filter AppleSandboxPurchasesTest and mobile
scope ios-purchases: **27 PHP / 301 assertions, 18 Flutter purchase tests + 1 staging
identity test passed**. New regression begins with empty checkout config, verifies
visibility after Library refresh and hiding on closed/unavailable config with Play
ownership retained; widget test opens existing consent screen directly from Library.
Analysis has no errors/warnings, existing 21 style infos. Inspected font-loaded synthetic
390×844 render **/home/shelf/tmp/shelf-apple-button-library.png**: exact copied catalogue
title and complete LTR button label fit the RTL Library card without overflow. This is
widget-render evidence, not acceptance of a new installed iPhone build.

**New iOS build required: YES** for this Dart UI/controller fix. No build/upload or
backend deployment performed. The action remains intentionally hidden with gates OFF;
phone accessibility of the corrected build remains pending. Do not reactivate or transact
until that entry is confirmed accessible under the owner's later explicit instructions.

**One next action:** authorize one updated internal TestFlight build from this scoped
fix, with all purchase gates OFF. France exclusion, all deferrals, automatic-closure
waiver and joint public-launch hold preserved; no watchdog introduced.

## Owner-authorized single Book 3 sandbox window OPEN — 16 October 2026

Owner explicitly withdrew the automatic 30-minute gate-closure prerequisite and
prohibited implementing it. That decision supersedes the prior automatic-closure
blocker below. Owner authorizes **one genuine shelf_book_3 Apple sandbox purchase**
using accepted installed TestFlight **1.0.0 (6)**; no public checkout or second buy.

Activated **2026-10-16 21:10:23 UTC** after verified SQL/media/hash backup
**/home/shelf/backups/shelf/20261016-210933/** and verified encrypted exact .env
checkpoint plus private pre-test history fingerprints and preserved uncommitted
records in **apple-book3-window/** within that backup. Production remains **1d3dfa5**.
Initial format check stopped before changes: existing sandbox flag was quoted false,
owned-book3 flag absent/default false. Atomic update preserved unrelated settings,
set sandbox true and added the existing owned-book3 control explicitly true; no code,
infrastructure, provider, product or ownership change. Ran config:clear.

Runtime verifies both gates ON, exact verified owner/Book3 eligible with existing
Play sale/access intact, nonowner/blocked owner/other-book denied, real checkout,
sign-in/relay and sync OFF; staging both gates/real checkout OFF. Actual HTTPS iOS
config verifies enabled/sandbox true, production false, APP_STORE, allowlist exactly
[shelf_book_3] and session protocol 1. Existing provider/security evidence reused.

**Awaiting owner iPhone action/result; no successful purchase claimed.** Owner was
instructed to refresh Library, open Book 3, use **Test Apple purchase — no charge**,
accept agreement and confirm exactly one genuine Apple sandbox transaction; stop
on unclear sheet/error/pending, never retry a buy. After owner result, verify actual
session-bound Apple/RevenueCat proof, permanent entitlement, webhook outcome and
Test-only accounting; immediately close both gates whether success or failure,
verify OFF and preserve Play ownership/all financial history. No watchdog installed.
France exclusion, all deferrals and joint public-launch hold remain unchanged.

## Build 6 launch accepted; sandbox activation blocked by automatic closure — 16 October 2026

Owner confirms **TestFlight iOS 1.0.0 (6)** installed and launches correctly on the
owner's iPhone. This is **installation/launch acceptance only**, not product retrieval,
purchase, restore/refund/reversal, notification delivery or Apple sign-in acceptance.
Reused Build 6 Apple receipt/compliance/group readback and completed backend/security/
provider evidence; no new build, broad tests or repeated provider-settings audit.

Focused read-only production check completed by **2026-10-16 20:52 UTC**, starting
from clean **4cdd177**. Production HEAD remains approved purchase-session source
**1d3dfa5abc1bb35097ada6f2a69fb003c2e57b19**; inspected deployed session/proof/owner
exception/purchase service/controller/config bytes match that commit. **Zero pending
migrations**, session table present and empty, no APP_STORE purchases. Both Apple
flags, real checkout, Apple sign-in/relay and Play sync remain **OFF**. Public HTTPS
iOS config returns enabled/sandbox/production false and SDK key null.

Private queries confirm the same sole owner admin has one exact-email verified,
unblocked reader. Closed exception denies access. A separate CLI process changed
flags **in memory only**, verifying existing provider prerequisites and owner Book 3
eligibility, including existing active Play access and unrefunded/unrevoked Play sale.
Nonowner, blocked owner and other book denied. No model save, consent creation,
provider proof request, persistent flag/config change or purchase occurred.

Existing RevenueCat webhook **whintgr769cb03ff2** GET **200** confirms Apple app
**app8ef018b95b**, sandbox environment, existing Shelf Apple webhook URL and exactly
non_renewing_purchase/cancellation/expiration/refund_reversed events. Protected local
webhook authorization matches runtime; configured app/product IDs match. Reused owner-
verified notification tracking OFF, Keep with original App User ID, sandbox override
OFF and existing entitlement protection. No settings changed; actual delivery remains
unverified. No private identity, secret or purchase record printed in these checks.

**NOT READY TO ACTIVATE:** required durable **30-minute automatic closure of both
Apple gates is still unimplemented/uninstalled**. Source/config/scheduler inspection
and current crontab show no Apple window closure mechanism. The existing 15-minute
purchase-session expiry limits individual consent; it does not expire gates or prevent
issuing later consent. Manual closure alone does not meet the recorded safety rule.
Stop before activation; no executable activation/test sequence is cleared.

Existing backup/encrypted checkpoint, atomic two-flag changes, config-clear and manual
close/readback plan remains applicable, but must be supplemented by verified automatic
closure armed before opening gates, surviving an interrupted interactive session,
refusing unexpected/concurrent configuration changes and reporting failure. Close early
on completion/mismatch; never automatically reopen. Closing gates preserves all Play
ownership and Apple/Test history and is not a refund. No backup or recovery rerun now.

**One next action:** authorize scoped implementation and verification of that automatic
closure safeguard with both gates kept OFF; activation/transaction remains a separate
explicit authorization. No source implementation or deployment is authorized by this
readiness inspection. France exclusion, all existing deferrals and joint Android/iOS
public-launch hold remain unchanged. Only the two requested records updated.

## Build 6 internal TestFlight readiness completed — 16 October 2026

Owner reports Codemagic run **#10 / 6ad249367550aed8108f53d9** succeeded from
**b16a8d88a2b2778fbb3ebc2b89edc94a7331ba4b**, producing **Shelf.ipa**, intended
**1.0.0 (6)**. Cloud success/artifact remains owner-reported; Apple receipt and
readiness below were independently verified using the existing protected ASC key.
This supersedes the earlier preparation-only/next-unused-6 status; no new build,
upload, diagnostic or credential setup is needed for this received build.

Authenticated Apple GETs confirmed app **6821471140 / services.shelf.app**, build
and upload **c77637e1-7e7e-4f7c-81dc-94cada28c9a2**, preReleaseVersion
**IOS / 1.0.0**, build **6**, upload **COMPLETE** with empty errors/warnings/infos,
processing **VALID**, audience **INTERNAL_ONLY**, expired false. Upload date was
**2026-10-16T09:05:24-07:00** (16:05:24 UTC); build uploadedDate
**2026-10-16T09:06:08-07:00**. Initial internal state was
**MISSING_EXPORT_COMPLIANCE**, usesNonExemptEncryption null, declaration GET null.

### Approved compliance reused after focused source comparison

Compared accepted Build 5 **8283bad** with Build 6 **b16a8d8**. Mobile production
changes are only purchase-session identity/consent checks in
book_purchase_provider.dart and library_controller.dart. No encryption primitive,
key handling or general-purpose encryption feature was introduced. Dependencies,
lockfile, native iOS tree, account/Keychain, repository, audio cache and encrypted
download store are byte-identical. Existing **cryptography 2.9.0 / AES-256-GCM**,
per-reader generated key, Keychain storage, HTTPS/SDK transport and SHA-256 cache
naming assessment remains applicable. This is a source comparison, not an IPA audit.
Rechecked [Apple's encryption documentation table](https://developer.apple.com/help/app-store-connect/reference/app-information/export-compliance-documentation-for-encryption/)
and [build update attributes](https://developer.apple.com/documentation/appstoreconnectapi/buildupdaterequest/data-data.dictionary/attributes-data.dictionary).
Approved basis retained: encryption Yes, standard outside OS Yes, proprietary No,
France No; **usesNonExemptEncryption=false** is the accepted non-France no-document
route, not a claim of no encryption or OS-only encryption.

Complete current Book 3 availability GET verified **174 territories / FRA absent**.
No availability changed; app-wide availability remains previously unconfigured,
and future app distribution must exclude France. ANSSI cancellation preserved.

Only compliance mutation: **PATCH /v1/builds/c77637e1-7e7e-4f7c-81dc-94cada28c9a2**,
attributes **usesNonExemptEncryption=false**, HTTP **200**. Independent readback
confirmed **READY_FOR_BETA_TESTING**, false flag and every other build attribute
unchanged before assignment. Declaration GET remains null, consistent with the
accepted no-document route.

### Existing group preserved; additive assignment verified

Verified existing **Shelf Owner / 1511a217-4f89-4dc7-8b8d-4679ce8a6226** belongs
to the exact Shelf app and is internal. All group attributes match the accepted
Build 5 snapshot. Complete tester list contains the same sole owner ID/email,
compared privately; identity withheld. Initial complete builds were **5, 4, 3, 2**.

Only assignment mutation: **POST /v1/betaGroups/1511a217-4f89-4dc7-8b8d-4679ce8a6226/relationships/builds**,
one builds resource ID **c77637e1-7e7e-4f7c-81dc-94cada28c9a2**, HTTP **204**.
No replacement/removal, invitation, membership edit or setting change.

Final readback **2026-10-16 20:44:19 UTC** independently verified group builds and
builds relationship both contain exactly prior IDs plus Build 6 (**6, 5, 4, 3, 2**),
**internalBuildState=IN_BETA_TESTING**, external **NOT_APPLICABLE**, same sole tester,
unchanged group attributes and autoNotifyEnabled true. Build remains
**VALID / INTERNAL_ONLY** with all attributes unchanged except cleared compliance.
Every provider GET returned **200**; bounded complete lists reached terminal pages.
Private **0600**, outside-Git before/after evidence:
**/home/shelf/tmp/shelf-build6-before-20261016.json** and
**/home/shelf/tmp/shelf-build6-after-20261016.json**.

**Owner can now install Build 6 through TestFlight.** Open **TestFlight → Shelf →
Update/Install**; if needed use **Previous Builds → 1.0.0 → build 6**. Preserve the
existing installation/account/data. Installation/launch is not yet owner-confirmed;
no notification delivery, purchase acceptance or public-review approval is claimed.

Only these two documentation files changed; scoped diff/whitespace/source checks,
no repeated source tests, build or broad suite. No deployment, upload, new testers,
product/territory change, transaction, purchase activation or public release.
All purchase/owned-test/real-checkout/sign-in/relay gates remain OFF. Backend stays
**1d3dfa5**; France exclusion, pricing-sync and second-phone-restore deferrals,
Android/UI pause, Apple authentication/relay and small-improvement deferrals and
joint Android/iOS public-launch hold remain binding.

**Next:** owner installs and checks Build 6 launch/version and existing account/
Book 3 Library/paid access with checkout closed; no purchase or Apple sign-in test.

## Purchase-session TestFlight preparation — 15 October local / 16 October 2026 UTC

**Preparation only; no build or upload started.** Clean starting checkpoint and exact
candidate application/workflow source:
**1bde4f0dbe2d278723d749d3c714fd851c1314cf**, branch **ios/foundation**.
Select that exact revision for a later authorized run; this preparation commit changes
only records. `mobile/` is byte-identical to validated/deployed **1d3dfa5**. The client
requires session protocol version 1, validates authenticated consent's reader/product/
mode and unexpired `shelf_apple_` random identity, identifies RevenueCat with that identity
before buying, checks account-generation changes and confirms through the server.
It does not grant access from the SDK response. Existing consent and server proof remain.
Reuse **64 PHP tests / 604 assertions, 16 Flutter tests + 1 staging identity**, **45 operator
tests**, successful backend batch **29**, zero pending migrations and strict preservation/
Play Library evidence recorded above. No broad suite, native compile or dependency install
was repeated.

### Build number independently checked

Existing protected ASC inspection key used for **GET only**, at
**2026-10-16 02:37:12 UTC**. App **6821471140 / services.shelf.app** verified.
Complete paginated builds and app buildUploads lists reached terminal pages, HTTP **200**:
received **2, 3, 4, 5**, all **VALID / INTERNAL_ONLY**; uploads **1 FAILED**, **2–5 COMPLETE**,
all **IOS / 1.0.0**. No processing build or pending upload and no 6-or-higher record.
**Next unused number is 6**, not reserved. Private bounded evidence:
**/home/shelf/tmp/shelf-testflight-preparation-20261016.json**. Before a later run, repeat
only this receipt/pending check and confirm no competing signed Codemagic/GitHub run;
stop on changed evidence rather than assuming 6 remains available.

### Exact workflow and credential evidence limit

Use existing Shelf application, **ios-testflight-internal**, candidate SHA above,
**1.0.0 (6)**. Required later Codemagic variable setting is
**SHELF_IOS_BUILD_NUMBER=6** in existing **shelf_ios_testflight**; not changed here.
Keep **shelf_ios_foundation**, approved HTTPS API, **shelf_app_store_connect** integration,
**shelf_ios_distribution** certificate and **shelf_ios_app_store** profile unchanged.
Team **YSLWJQDH8B**, bundle **services.shelf.app**; no new key/profile/account.

Workflow and `scripts/ios/check_build_number.py` are byte-identical to owner-reported
successful signed build-5 source **8283badf674f94c8cc7e18a5371e35cdc67d5d1c**, run #9
**6acfddac546ce6236ceea2d5**. The mandatory official CLI guard remains before compilation,
delegating credential resolution to Codemagic's CLI, with explicit positive number,
latest-number comparison, bounded timeout and fail-closed authentication/read errors.
No unsupported raw-credential prerequisite or fallback was reintroduced.

**Evidence gap:** repository records still label the standalone **ios-asc-access-check**
diagnostic NOT cloud-verified; no diagnostic success log/run ID or exact working private
variable configuration was found. Build 5's reported successful run with the same mandatory
guard is indirect success evidence, not an independently inspected diagnostic log.
No Codemagic connector/token access is available to inspect worker logs, current variables,
certificate expiry, dashboard signing state or competing queued/running jobs. Do not claim
those checked or ask to rotate/recreate credentials. Inspect the existing successful run's
CLI output and retained credential configuration before clearing this readiness gap;
no diagnostic rerun is requested merely to reproduce an existing success.

Static YAML parse, each signed script's shell syntax, native team/bundle/entitlements,
photo-library strings, source comparisons and upload/build-number checks passed.
Workflow SHA-256 **3270bff39673c47cdf88b62b29464aab97b85559bc99e04bc2546956eab8a149**.
Export retains **testFlightInternalTestingOnly=true**, automatic version management false,
checkout/test Dart flags false, publishing auth integration, **submit_to_testflight=false**
and **submit_to_app_store=false**. A later separately authorized signed run uploads via
publisher without tester assignment or public submission. Mandatory IPA checks cover bundle/
version/build number, archive integrity, strict signature and team entitlements. Explicit
artifact collection requires one nonempty IPA, matching SHA-256, source commit, export
options, entitlements, Podfile.lock and privacy-manifest paths; dSYMs are collected too.
These are verified workflow requirements, not an inspection of a nonexistent new IPA.

### Planned acceptance, no activation now

After a separately authorized build/upload, independently verify Apple receipt, processing,
INTERNAL_ONLY audience and compliance. Any compliance save or assignment to existing Shelf
Owner requires its own authorization; no tester change here. Phone acceptance then checks
version/build, launch, existing account/Book 3 Library/paid access, refresh and closed checkout.
Apple sign-in/relay acceptance remains deferred. Do not purchase during these checks.

A later explicitly authorized single owner/Book 3 sandbox test additionally requires the
existing documented automatic bounded gate-closure mechanism, still unimplemented, before
activation. Verify owner, preserved Play access, consent/session identity, one sandbox
transaction and independent Apple proof; reject wrong account/book, expired/replayed/
cross-session confirmations. Close both gates on completion/mismatch/deadline, verify
Play access/Library survives and no real income, duplicate royalties or unintended permanent
Apple ownership. Do not reset ownership or retry purchase to resolve ambiguity. This is a
plan only, not permission to enable gates or transact.

All purchase/sign-in gates remain OFF. No deployment, provider/product/territory/tester
change, build, upload or transaction occurred. France exclusion, authentication/relay,
Android/UI pause, pricing-sync and second-phone-restore deferrals, and joint Android/iOS
public-launch hold remain binding.

**Exactly one next owner action:** inspect/share the existing successful Codemagic run's
CLI-guard result and confirm no competing signed run, to close the cloud-readiness evidence
gap before authorizing a single build. Do not start a run in this preparation task.

## Apple purchase-session safety — production promotion completed, 15 October local / 16 October 2026 UTC

Owner authorized resuming the already-staged application rollout using the isolated
promotion operator at **71c56cf7ff1dc479ebdb4cdd5433aa4d36f92534**. Started from that
exact clean `ios/foundation` checkout. **Production and staging now both run application
source 1d3dfa5abc1bb35097ada6f2a69fb003c2e57b19**. This entry supersedes the earlier
stopped-promotion and separate-promotion-authorization status; historical evidence below
is retained. The operator correction remains in the trusted isolated checkout; production
was advanced only to the intended application source, not to the later operator/docs commits.

### Preflight, backup and bounded safeguards

Verified production initially at **c8c266221d972d409ffa947ca87397293c69c004**, healthy and
out of maintenance; the only local change was the recognized cPanel PHP handler.
Staging remained at exact **1d3dfa5**, migration **batch 12**, zero pending migrations.
Reused the passing staged log SHA-256
**ec4faca3ecd88b1c0a498fc8b5732a8341a045ba180bc8bc86088cdfa0082723**.
Finished the previously unreached staging purchase/accounting/owner-share health commands;
Apple schema/closed-gate and real HTTPS/private-boundary checks all passed. No broad
unit or mobile suite rerun: prior **64 PHP / 604 assertions, 16 Flutter + 1 staging
identity** and **45 focused operator tests** remain the source-validation evidence.
Backup storage readiness and the pinned historical checkpoint/configuration passed.
Both existing provider webhook configurations matched their original verified fingerprint
by read-only RevenueCat GET (HTTP 200), before and after promotion.

Fresh production backup **/home/shelf/backups/shelf/20261016-021356/** passed SQL,
ZIP `unzip -t` and SHA-256 verification before maintenance/source/migration changes.
It contains `promotion-notes.txt`, the rollback source and
`entitlement-refresh-verification.json`. The original encrypted private-configuration
checkpoint remains **/home/shelf/backups/shelf/20261016-004143/apple-session-rollout-checkpoint/**;
its pinned provenance/hash and all protected private-file fingerprints passed again.
The new backup is the current release-state backup; prior rollback source is **c8c2662**.

The operator acquired the existing refund lock non-blockingly before backup and held it
through the promotion checks, under the documented 10-minute window and bounded child
commands/maintenance cleanup. Cron was never edited or disabled. Historical acceptance
was limited to entitlement **id 5**'s `checked_at`, `created_at`, `updated_at`, independently
linked to completed duplicate-only refund audit **1342**. UTC epoch normalization verified
the backup/live SYSTEM/AST offset without changing stored timestamps. All identity,
active-status, ownership, reader, purchase/refund, ledger, consent, agreement and accounting
comparisons remained strict. The fresh actual-promotion before/after comparison included
all three timestamps and passed exactly; no table-wide exemption or rebaseline was used.

### Production results and resumed scheduling

Fast-forward promotion succeeded. Apple purchase-session migration
**2026_10_15_010000_create_apple_purchase_sessions** applied in production **batch 29**;
zero pending migrations, zero sessions, no Apple purchase history. Production purchase,
accounting and all six owner-share checks passed. Maintenance was lifted and HTTPS
health, locked paid content/Library for unauthenticated requests, webhook guards and
staging isolation passed. Release state records source **1d3dfa5** on both environments.

The existing verified owner's Book 3 Play ownership and paid access passed. The actual
Library controller reconciliation performed independent read-only provider proof and
returned Book 3, its lease and all Apple denials correctly. This verification ran inside
a database transaction that was rolled back, under the same refund lock. Strict protected
history snapshots before/after matched exactly: no persistent ownership, timestamp,
purchase, refund, consent, ledger or accounting change was made by the probe. No duplicate
sale or Test income in ordinary real-income queries was found.

The lock was released and the original crontab fingerprint was unchanged. Natural
scheduled refund audit **1343** completed at **2026-10-16 02:15:02 UTC**, HTTP **200**,
**pages=1, seen=duplicate=1, refunded=unmatched=conflict=0**. No manual refund run was
started. A subsequent coherent comparison against the original backup again verified
strict ownership/financial history and accepted only the same three metadata columns,
now independently linked to audit 1343. Private final evidence is in the fresh backup's
`post-promotion-verification.json`; no reader identity or provider secret is published.

Production `.env`, staging private `.env`, Apple webhook authorization/metadata, provider
key and cPanel handler fingerprints are unchanged. Existing Apple and Google RevenueCat
webhooks are unchanged. Production's sole local change remains its preserved cPanel
handler. **Both Apple sandbox gates OFF; real checkout OFF; Apple sign-in/relay gates
OFF; pricing sync OFF. Production healthy and out of maintenance.** No rollback was
needed. If a later rollback is authorized, follow `docs/STAGING.md` and this backup's
notes: inspect migration batch/evidence first; never restore over subsequent reader or
money activity. No database restore or migration rollback was performed.

No TestFlight build/upload, purchase, provider-setting edit, gate activation or public
release occurred. France exclusion/ANSSI cancellation, authentication/relay deferrals,
Android/UI pause, pricing-sync and second-phone-restore deferrals, and the joint
Android/iOS public-launch hold remain unchanged. A **new internal TestFlight build is
required** because installed build 5 lacks the purchase-session identity protocol;
backend promotion does not update that app. Apple activation and automatic test closure
remain separately gated; this rollout authorizes neither.

**Exactly one next action:** authorize a new internal TestFlight build containing the
validated purchase-session protocol, keeping all purchasing gates OFF.

## Promotion validator and operator safety — implemented in isolation, 15 October local / 16 October 2026 UTC

Owner authorized implementation/testing of the scoped correction documented at
**e523c7b047558dbe6ee2b4f99d687fec29894d62**, with no deployment or production changes.
Started from that exact clean checkpoint. **Implemented and synthetically tested only.**
This supersedes the investigation's request to authorize validator implementation;
production promotion still requires a separate task. No application, migration, mobile,
purchase/provider configuration or scheduled-job source changed.

### Narrow historical acceptance; strict actual promotion

New isolated operator module **scripts/staging/promotion_safety.py** is called only by
the protected Apple-safety promotion path. It requires explicit
**--preservation-checkpoint**, exactly the reviewed checkpoint at
**/home/shelf/backups/shelf/20261016-004143/apple-session-rollout-checkpoint/**,
selected application source **1d3dfa5abc1bb35097ada6f2a69fb003c2e57b19** and prior
production source **c8c266221d972d409ffa947ca87397293c69c004**. There is no general
entitlement-table exemption or automatic acceptance of another rollout.

The original verification.json is pinned to SHA-256
**a9ff66431e3a031a1c7e960594a46e8b2358855b90b757328c044a5dc0ae425d**,
preventing silent rebaselining. Loading verifies private ownership/modes/canonical paths,
original source/provenance, SQL SHA-256 against the Shelf manifest and all original
configuration/private-webhook-file/cPanel fingerprints. SQL is parsed as data only;
no backup SQL is executed or restored. The only production-side artifact read during
this implementation was that existing checkpoint's fingerprint; no live DB query or
promotion command was run.

The future gate captures ownership/audit evidence and the complete existing protected
history fingerprints in one database read transaction, always rolled back. Dump
TIMESTAMP(0) values are parsed as UTC; live entitlement/audit TIMESTAMP values use
**UNIX_TIMESTAMP**, avoiding AST/UTC string mismatches. Precision/schema are verified,
and the historical SYSTEM/AST offset must still be the measured **−14400 seconds**;
other session semantics fail for explicit review. DATETIME fields are not shifted.
Original and current entitlement raw fingerprints must reproduce their corresponding
canonical row data, with correct numeric/null values and column ordering.

Only the reviewed entitlement **id 5**, with unchanged row count/id/reader/book/**active**
and all non-timestamp fields, can have metadata accepted. All three new timestamps must
be equal and nondecreasing and match exactly one newly recorded completed HTTP-200 run:
**seen=duplicate=1, pages=1, refunded=unmatched=conflict=0, error=null**. The run must
uniquely link to the one preserved refunded Play purchase/Google refund event for that
association and its separate valid Play sale. Existing refund-run audit rows must match
the backup exactly. Missing/ambiguous audit, partial/backward/null/unexplained timestamps,
wrong row/book/account/status/schema or any other protected-table fingerprint difference
stops. Purchase/refund/sales/accounting/consent/agreement/reader/token comparisons stay
exact. The module never updates entitlement timestamps or ownership.

After accepted historical evidence, the actual migration/promotion still compares a
fresh before/after snapshot of **every protected field, including all three entitlement
timestamps**. The old strict fingerprint logic is shared, not weakened. A protected
private report in the new promotion backup records accepted historical columns, safe
entitlement/run ids and UTC epochs while retaining the original checkpoint reference.
No secret values or reader identifiers are logged.

### Bounded refund-polling exclusion without changing schedules

The protected promotion acquires the existing **storage/framework/play-refunds.lock**
non-blockingly before any fresh backup or maintenance. Busy/missing/unsafe/symlink locks
refuse work; no job is killed to obtain a lock. It preserves the lock inode and checks
ownership/mode. Cron is **never edited, disabled, reinstalled or rescheduled**, so there
is no scheduling file/state to restore. Polling resumes through the unchanged cron once
the kernel lock is released. Existing production maintenance or open/deferred controls
are refused before backup; unrelated maintenance is not lifted.

Future ordering is: verify unchanged staged commit/evidence → refund lock/closed-state
preflight → fresh verified production backup → arm cleanup before maintenance down →
coherent historical preservation gate → fresh strict baseline → existing source/migration/
health workflow → exact history/configuration comparison → maintenance up → runtime/HTTPS
verification → release-state success, then lock release. Failed preservation cannot merge
source or run migrations. Successful runtime checks remain required before release-state
success is recorded. The module is an isolated operator correction; it does not change
the already-validated/staged application source.

The operator window is bounded to **10 minutes**. External commands run in isolated
process groups with an independent **/usr/bin/timeout**, at most 120 seconds plus a
5-second kill grace. Signals **INT/TERM/HUP** and deadline expiry abort, stop/reap the
active child group, attempt maintenance-up with a separate bounded 30-second cleanup
budget and release the lock in finally. Repeated signals cannot skip that cleanup;
prior signal handlers are restored. Cleanup failure reports the original failure type
and stops without success evidence. Lock ownership is inherited by independently timed
children: after an uncatchable operator **SIGKILL**, an orphan command retains exclusion
only until its own bounded timeout, then the kernel releases it. No permanent watchdog,
cron edit or scheduled cleanup job is created.

**Limit:** SIGKILL/power loss cannot execute Python maintenance cleanup. Scheduling-lock
release remains kernel/child-timeout controlled, but after such a hard interruption the
operator must inspect the retained backup and maintenance/source state before resuming;
do not claim maintenance-up completed or blindly retry. No synthetic test establishes
live server recovery from such an interruption.

### Focused evidence and next authorized operation

`SHELF_TOOL_TEST_SCOPE=apple-rollout scripts/run_tests.sh` passed **45 focused tests**:
existing 12 operator safeguards plus 33 new validator/window/integration cases. Tests
use fixtures, mock all promotion/DB/backup/migration/provider operations and use real
processes/locks/signals only inside the runner's disposable **/home/shelf/tmp** directory.
Coverage includes allowed/unchanged metadata; ownership/financial/row-set rejection;
null/backward/partial timestamps; timezone/precision/schema errors; dump extended/multiline
formats; checksum/original-checkpoint tampering; wrong/missing/ambiguous audit/link;
busy/unsafe locks; deadline/child timeout; INT/TERM/HUP; cleanup failure and second signal;
SIGKILL with and without an inherited-lock child; strict final timestamp drift; backup,
preservation, staging-evidence and migration failures; and existing-maintenance refusal.
Generated coherent-evidence PHP passed syntax checking without execution. Diff checks
passed. Existing unrelated Python cPanel-handler escape deprecation notices remain.
No PHP app/Flutter suite repeated: validated **64 PHP / 604 assertions, 16 Flutter +
1 staging identity** and existing exact-source staging evidence remain reusable.

No deployment, promotion, migration, live data/ownership write, scheduling control,
provider setting, transaction, build/upload or public release occurred. Last verified
runtime state remains production **c8c2662**, staging **1d3dfa5** (batch 12), Apple sandbox/
owned-book3/real checkout OFF. These are retained evidence, not fresh live checks in this
task. France exclusion/ANSSI cancellation, Apple auth/relay and stopped relay work,
Android/UI pause, pricing-sync, second-phone restore/small-improvement deferrals and
joint Android/iOS public-launch hold remain unchanged. New TestFlight build is still
required later; activation/automatic Apple-test closure remain separately gated.

After separate approval, run the trusted isolated operator (not the old deployed helper):
`python3 -B scripts/staging/workflow.py promote 1d3dfa5abc1bb35097ada6f2a69fb003c2e57b19 --preservation-checkpoint /home/shelf/backups/shelf/20261016-004143/apple-session-rollout-checkpoint`.
Recheck current state and finish any unreached required staging health checks first.
No redeployment or new test transaction is needed merely to repeat completed evidence.

**Exactly one next action:** authorize resuming the backed-up production promotion of
already-staged **1d3dfa5** using this verified isolated operator, all purchasing gates OFF.

## Entitlement timestamp investigation — read-only findings, 15 October local / 16 October 2026 UTC

Resumed clean **b1523eca563ca028e161c2436920380d71d9e6b3** under the owner's
read-only investigation authorization. **No deployment, migration, data repair, provider
change or validator/enforcement change.** Only these two documentation files are changed.
Production remains **c8c266221d972d409ffa947ca87397293c69c004**; staging remains
**1d3dfa5abc1bb35097ada6f2a69fb003c2e57b19**, session migration batch **12**, with
its existing passed rollout evidence. This entry resolves the preceding timestamp-cause
uncertainty; it does not authorize production promotion.

### Findings: real routine refresh, plus a timezone display difference

Verified the SHA-256 of **/home/shelf/backups/shelf/20261016-004143/database.sql**
against its manifest. Parsed the original SQL in memory without restoring/executing it.
There is exactly **one** entitlement row, safe internal entitlement **id 5**. Its id,
reader/book association and **active=true** match the backup. No reader identifier or
personal information was printed. Only **checked_at, created_at, updated_at** changed.

The backup's `mysqldump --single-transaction` writes TIMESTAMP values under
**TIME_ZONE='+00:00'**. Current Laravel query-builder reads use database session/global
**SYSTEM**, system **AST (UTC−04:00)**, while application timezone is **UTC**.
All three columns are **TIMESTAMP(0)**: second precision, no fractional rounding.
The backup prints **2026-10-16 04:30:27 UTC**, equivalent to the original application/
session representation **2026-10-16 00:30:27**. The stopped comparison subsequently saw
**00:45:01** in that same session representation (database UTC **04:45:01**).
After normalization the stored time really advanced **874 seconds (14m34s)**.
Thus timezone display alone does not explain the changed checkpoint fingerprint.
Do not change database/application timezones or rewrite historical timestamps in this task.

`production_history_snapshot()` uses query-builder rows, ordered by primary id, then
PHP `json_encode` and SHA-256; it does not apply Eloquent date casting. It compares the
same session representation before/after. Reconstructing the backup entitlement with
correct numeric/null types, original column order and explicit UTC→AST conversion
reproduces the **exact original preflight entitlement hash**. With one row, row ordering
cannot explain the mismatch. There is no demonstrated precision, serialization or
ordering defect causing this particular failure.

### Demonstrated writer path and operational evidence

The existing ***/15 * * * *** cron entry runs **scripts/run_play_refunds.sh**. Its
existing lock is **storage/framework/play-refunds.lock**. The runner calls
`shelf:check-play-refunds`; no scheduler, job or transaction was manually invoked here.

At **2026-10-16 00:45:01 UTC**, application log and persisted **play_refund_runs id 1337**
record **completed, seen=1, duplicate=1, refunded=0, unmatched=0, conflict=0**.
The run starts/finishes in the same logged second as the changed application timestamps.
Source chain on deployed c8c2662 is:

`VoidedPurchaseService::apply` → `PurchaseService::recordGoogleRefund` → `refreshAccess`.

`recordGoogleRefund()` recognizes the existing refund event/ledger entry, but still calls
`refreshAccess()` for that reader/book before returning the duplicate outcome. The
refresh uses `updateOrInsert` with **active, checked_at, created_at, updated_at** in its
update values. It therefore rewrites even `created_at` on an existing row. That field
currently is not a stable original-creation marker. Changing that behavior is outside
this documentation-only task; do not silently repair its historical values.

Read-only queries confirm exactly one already-refunded Play purchase and its existing
Google refund event for the same entitlement association, plus a separate valid Play
sale. Recalculation keeps the entitlement active. Purchase/refund/ledger rows do not
change when this duplicate is processed. The scheduled poll can therefore change only
these entitlement timestamps without changing the reader's actual ownership or income.

While this investigation remained read-only, the next natural scheduled run,
**id 1338 at 01:00:02 UTC**, again recorded completed/seen=1/duplicate=1/refunded=0/
unmatched=0/conflict=0; all three entitlement timestamps advanced together to **01:00:02**.
This repeated observation, exact cron timing, persisted run audits and deployed code
establish the routine duplicate-refund refresh as the supported cause. No per-statement
binary-log trace was retrieved; attribution is based on those correlated records/code.
No entitlement trigger exists; table engine is InnoDB.

The prior Library probe file was created at **00:45:34 UTC**, after the observed
**00:45:01** change, so that probe cannot explain this stopped checkpoint mismatch.
There is no evidence here of failed transaction rollback. Available production access-log
slice showed earlier successful Library requests at **00:30:20/00:30:27 UTC**, consistent
with the backup's earlier refresh time; that slice is not a complete SQL/writer audit.

### Ownership and financial integrity verified

Direct full-row comparison against the verified SQL backup, after only TIMESTAMP display
normalization and numeric/null type preservation, matches exactly:

| Table | Backup/current rows | Full rows equal |
| --- | --- | --- |
| purchases | 2 / 2 | Yes |
| purchase_events | 3 / 3 | Yes |
| sales_ledger | 3 / 3 | Yes |
| purchase_consents | 3 / 3 | Yes |
| author_share_agreements | 6 / 6 | Yes |
| accounting_entries | 0 / 0 | Yes |
| accounting_shares | 0 / 0 | Yes |

The saved preflight fingerprints for readers, account actions and access tokens also
still match. Entitlement association/status is unchanged; one valid Play sale remains,
one historical refunded purchase remains refunded, and no APP_STORE purchase exists.
There are **zero duplicate sale groups, zero ordinary real-income rows and zero ordinary
accounting entries**. No reset, refund reversal, regrant or ownership modification was
performed. Financial and ownership integrity remain verified within these comparisons.

### Required future promotion gate — proposal only, enforcement unchanged

Safe promotion is **supportable after a scoped validator/operator correction**, not
cleared under the still-strict historical timestamp comparison. Do not merely omit
`book_entitlements`, forgive arbitrary metadata drift or replace the failed checkpoint.
A separately approved correction should implement and focus-test this narrow rule:

1. Compare the complete entitlement row set by stable id and reader/book association;
   require exact row counts, ids, every non-timestamp field and **active**, including
   null semantics. Financial/consent/purchase/refund/agreement/accounting rows remain
   exact comparisons. Normalize TIMESTAMP displays explicitly at their actual precision;
   do not shift DATETIME fields or reinterpret application wall times as database UTC.
2. For this rollout, permit only metadata changes on existing entitlement **id 5**, and
   only when all three new timestamps are equal, nondecreasing and match a persisted
   completed duplicate-only refund run (seen=duplicate, refunded=unmatched=conflict=0,
   no error), uniquely attributable to its already-recorded same-reader/book refund.
   Retain the original checkpoint plus exact field differences and run evidence.
   Missing/ambiguous audit evidence, changed ownership or any financial difference stops
   promotion. No tolerance window for arbitrary timestamps or allowance for other rows.
3. After this historical preflight is accepted, obtain a **fresh verified backup** and
   a fresh coherent baseline. For the bounded promotion/verification window acquire the
   existing refund-runner lock non-blockingly, fail safely if busy, use maintenance to
   stop new reader requests and release the lock in finally. Do not edit cron or kill
   jobs. Capture the baseline after writers are quiescent. Preserve the existing strict
   before/after comparison of **all fields, including all entitlement timestamps** for
   the actual migration/promotion window. Release maintenance on every failure.
4. Reuse existing exact staging/unit evidence, finish any unreached required staging
   health checks, verify the selected source and configuration, then promote only under
   separate authorization. A validator correction must be tested in isolation and must
   not substitute a different unverified application/migration source.

No validator, lock behavior, schedule or deployment enforcement was changed in this
investigation. No unit/phone suite repeated and no Library reconciliation/probe executed.
Production is out of maintenance; **Apple sandbox OFF, owned-book3 test OFF, real checkout
OFF** remain verified. France exclusion/ANSSI cancellation, Apple auth/relay and stopped
relay work, Android/UI pause, pricing-sync, second-phone restore/small-improvement deferrals
and joint Android/iOS public-launch hold remain intact. A new TestFlight build remains
required later; no build/upload/activation/purchase/settings change occurred.

**Exactly one next action:** authorize the isolated, focused-tested validator/operator
correction above; keep production promotion and all purchase activation OFF meanwhile.

## Session safety rollout — staging passed; production STOPPED — 15 October local / 16 October 2026 UTC

Owner authorized the documented staging-first backed-up rollout of validated source
**1d3dfa5abc1bb35097ada6f2a69fb003c2e57b19**, all purchasing gates OFF.
**Staging deployed that exact source; production was NOT promoted.** Production remains
**c8c266221d972d409ffa947ca87397293c69c004**. This entry supersedes the preceding
implementation entry's next-rollout instruction. Do not blindly rerun deployment or
repeat completed checks; inspect the retained checkpoint and resolve the blocker first.

### Backups and protected state

- Fresh verified production preflight backup:
  **/home/shelf/backups/shelf/20261016-004143/**.
- Private preservation/evidence checkpoint:
  **/home/shelf/backups/shelf/20261016-004143/apple-session-rollout-checkpoint/**.
  AES256 configuration archive was decrypted in memory and compared exactly. It protects
  production/staging .env and existing private provider authorization/metadata/key files;
  cPanel handler, release state and aggregate history fingerprints were retained privately.
  No secrets or reader identifiers were printed or committed.
- Verified staging backup:
  **/home/shelf/backups/shelf-staging/20261016-004455/**.
- Both backups passed SQL completion, ZIP integrity and SHA-256 verification. No restore
  drill repeated, production promotion backup taken or production maintenance entered.

Only production's existing **public/.htaccess** cPanel handler was locally modified at
preflight; it is preserved byte-for-byte. Isolated source started clean at 1d3dfa5.
No source/migration/application change beyond the authorized validated source was deployed.

### Exact staging source, migration and checks

Staging **1d3dfa5** passed at **2026-10-16 00:45:13 UTC**. Migration
**2026_10_15_010000_create_apple_purchase_sessions** applied in staging batch **12**;
the new table is empty and no staging migrations are pending. Production has no session
table or new migration applied; no migrations are pending in its retained c8c2662 source.

The isolated operator helper needed a scoped preparation correction before deployment:
recognize already-committed Codemagic/build-number tooling and reuse only exact validated
**1d3dfa5** source/test bytes and its documented **64 PHP / 604 assertions, 16 Flutter
purchase tests + 1 staging-identity** evidence. These unit suites were not rerun.
The helper correction passed **12 mocked automation tests** via
`SHELF_TOOL_TEST_SCOPE=apple-rollout scripts/run_tests.sh`; it rejects source/evidence
mismatches and closed-runtime failures. Existing unrelated Python escape deprecation
notice remains. The helper correction ran from the isolated checkout and is recorded
with this rollout documentation; it was **not a different staged application revision**.

Staging checks passed: independent database/storage/session/cache; forced-disabled Play
sync; logged mail/null queue; synthetic account/isolation probes rolled back and temporary
file cleaned; absent production Apple credentials; applied empty session schema; runtime
Apple/owned-test/real-checkout/sign-in/relay/sync OFF; HTTPS private access/noindex/banner,
catalogue/cover, locked paid text, account/Library protection and denied webhook/private
files. No new provider transaction, session or ownership created. Check evidence:
**/home/shelf/staging-runtime/checks-1d3dfa5abc1bb35097ada6f2a69fb003c2e57b19.log**,
SHA-256 **ec4faca3ecd88b1c0a498fc8b5732a8341a045ba180bc8bc86088cdfa0082723**.
Additional standalone staging accounting/owner-share commands were **not reached**:
the production preservation precondition stopped their wrapper first. Accounting source
and passed regression evidence were reused, not a claim that those extra commands ran.

### Stop condition: production entitlement timestamp mismatch

An actual production Library controller/reconciliation probe confirmed existing verified
owner Book3 **PLAY_STORE** sale/access and a successful Library/lease response using
independent provider proof, with Apple controls closed, no duplicate sales and no Test
income in real totals. It was enclosed in an outer database transaction with rollback
in finally; no token, sale or sandbox purchase was created. **Do not claim complete
preservation from its initial success message:** the subsequent independent whole-history
checkpoint comparison failed before promotion.

Read-only comparison isolates the mismatch to the existing **book_entitlements** row:
row count is still **1**, reader/book/active ownership unchanged and active remains true.
Only **checked_at, created_at, updated_at** differ from the verified SQL backup. All other
protected table counts/fingerprints, including readers/tokens, purchases/events, consent,
sales/refunds, agreements and accounting, still match the checkpoint. The table engine is
InnoDB. The cause/attribution of timestamp drift is **not established**; do not assume
concurrent activity or blame the verification probe without evidence. Rollback code ran,
but full checkpoint preservation was not demonstrated. No repeat reconciliation probe,
manual SQL repair, timestamp restoration, baseline relaxation or production promotion
was attempted after the failure. Retained aggregate evidence includes
`promotion-stopped.json` and `entitlement-column-comparison.json` in the checkpoint.

Owner's explicit STOP rule was followed. Staging stays on the passed revision; production
stays on the previous source. There is no failed production migration or maintenance to
recover from. Do not discard staging or overwrite any existing records to resume.

### Final read-only production state

Production is **healthy and out of maintenance**; HTTPS app-config and iOS purchase-config
both return **200**. iOS enabled/sandbox/production checkout fields are false and SDK key
is null. Runtime **Apple sandbox OFF, owned-book3 test OFF, real checkout OFF, Apple
sign-in/relay OFF and Play sync OFF** on both environments. Production/staging .env,
private webhook authorization/metadata/key files and cPanel handler match the checkpoint.
Bounded RevenueCat GETs returned **200** before/after: the complete saved webhook metadata
matches, including Apple **whintgr769cb03ff2** (app-scoped sandbox, distinct Apple URL and
four events) and Google **whintgr75a4892970** (Both). No provider settings changed.

**New TestFlight build still required** for the session protocol before any future Apple
purchase test. None started or uploaded; installed build 5 unchanged. Durable automatic
closure remains unimplemented; activation/phone acceptance remain separately gated.
France exclusion/ANSSI cancellation, Apple authentication/relay and stopped relay work,
Android/UI pause, pricing-sync, second-phone restore and small-improvement deferrals,
and joint Android/iOS public-launch hold remain unchanged. No activation, transaction,
ownership repair or public release performed.

**Exactly one next action:** investigate the unexpected entitlement timestamp discrepancy
under a separately authorized focused recovery task before resuming production promotion.

## Apple session binding and store-aware gate closure — 15 October local / 16 October 2026 UTC

Owner authorized only the two corrections assessed at **212cd1cc5f6c83d5b827326c200a192af268ac0d**,
focused validation, deployment/rollback preparation, records and push to **ios/foundation**.
Started from that exact clean checkpoint. **Implemented in isolation only; NOT deployed,
NOT activated.** This entry supersedes the assessment's request to authorize implementation;
it does not clear activation or claim live purchase acceptance.

### Correction 1 — authenticated purchase-session binding

Apple consent now requires protocol **purchase_session_version=1**. Inside the existing
reader/book transaction and locks, verified owner/Book3/store/mode/publication/price and
normal duplicate or explicit owned-test consent checks remain mandatory. The server
creates a 256-bit random **shelf_apple_** identity bound to that reader, Book3 and the
specific consent, with a **15-minute** lifetime. A fresh consent invalidates earlier
unconsumed identities. A consumed session or any existing Apple purchase prevents issuing
a second session. The identity is a private capability: do not log, publish or copy it;
authenticated consent responses use the existing private/no-store middleware.

The client validates protocol, reader, book, mode, store and expiry before identifying
the Apple SDK with that session and buying. Numeric Play identity and all existing Play
history remain unchanged. Numeric identification is still used to initialize/list Apple
products, but native Apple buy refuses numeric IDs. Direct identified-to-identified SDK
login is supported without merging customers by [RevenueCat identity guidance](https://www.revenuecat.com/docs/customers/identifying-customers).
No SDK/dependency or provider setting change; no invented StoreKit account-token API.

Confirmation and webhook independently require matching RevenueCat **current AND original
session identity**, exact transaction and millisecond purchase time, app/product mapping,
APP_STORE/SANDBOX/purchased/quantity-one and permanent active entitlement. Session ownership,
consent and expiry are checked before recording and again under lock. The first verified
transaction atomically consumes the session, retaining its exact transaction/time for
historical proof/refund handling. Wrong reader/book/session, revoked/replaced/expired
unconsumed sessions, numeric impersonation and second transactions fail closed. Exact
historical duplicate events/confirmations remain idempotent; they cannot add a sale or
renew a lease without the existing independent reconciliation. Existing pre-session
numeric Apple history can only be independently reconciled/corrected, never used to
create a new unbound sale. No such live history was claimed or created here.

Server confirmation discovers only the authenticated reader's sessions. The consumed
identity is returned privately to that same reader for native restore while configured;
it is never in public config or persisted in the download manifest. Account changes
clear it. Strict original/current identity validation remains authoritative if the
provider aliases or transfers anything. Expired/unbound late purchases are not silently
accepted: preserve their provider evidence for explicit review, never repeat a pending buy.

New reversible migration **2026_10_15_010000_create_apple_purchase_sessions** creates only
an empty table. It records consent/reader/book, private identity, validity/revocation and
unique consumed transaction/time; no old reader, consent, purchase or money row is rewritten.
No cascade deletes evidence. Down migration refuses any retained session evidence and
requires an approved preservation plan; empty-table reversal is tested. **No live migration run.**

### Correction 2 — Play refresh survives Apple closure

Disabled Apple verification returns to the normal independent Play reconciliation rather
than throwing 503 solely because Apple history exists. Library uses the same book-access
filter as direct paid-text/content/cover/audio/artwork requests. With Apple unavailable,
Apple-only historical grants are excluded from Library/download renewal and direct access;
valid Play ownership or an existing reviewer grant still works. Shared entitlement rows
and immutable Apple history are not deleted or rewritten just to close gates. Failed Play
proof cannot fall back to disabled Apple history. Reconciliation with an enabled but
unavailable provider continues to fail closed; no fresh lease is fabricated.

Apple sandbox and owned-book test flags retain existing default **false**. Real checkout
remains OFF. Apple sales/corrections remain forced SANDBOX/Test, excluded from real income
and confirmed author earnings; duplicate/replayed transactions cannot multiply sales or
royalties. These fixes do not erase provider sandbox ownership or historical Test records.

### Focused validation (synthetic only)

- `SHELF_PHP_TEST_FILTER='AppleSandboxPurchasesTest|BookPurchasesTest|AccountingTest' scripts/run_tests.sh`:
  **64 PHP tests / 604 assertions passed**, temporary/in-memory data only.
- `SHELF_PHP_TEST_FILTER='AppleSandboxPurchasesTest|BookPurchasesTest|AccountingTest' SHELF_MOBILE_TEST_SCOPE=ios-purchases scripts/run_tests.sh --mobile`:
  **16 Flutter purchase tests + 1 staging-identity test passed**; PHP at that point
  **63 / 594** (the subsequently added failed-Play-proof case accounts for the final 64 / 604).
  Flutter analysis: no errors/warnings, **21 style infos**. Completed Flutter checks reused
  after the final backend-only case; no broad suite or Android artifact build.
- `SHELF_TOOL_TEST_SCOPE=apple-rollout scripts/run_tests.sh`: **12 mocked automation tests passed**.
  Existing Python cPanel-handler byte-literal escape deprecation notice remains unrelated;
  no runtime handler changed. Tests exercise scoped staging selection, source-byte mismatch,
  test/guard failure refusal and passing evidence only after focused tests/disabled guards.
- Covers nonowner/wrong book, missing/incorrect consent version, numeric/wrong-account/
  cross-session proof, expiry/replacement/replay, exact duplicate idempotency, independent
  proof rejection, Test-only ledger/refund/reversal, closed-gate overlapping Play+Apple and
  refunded Apple history, Play failure, Apple-only Library and direct-content denial,
  account change while consent waits and reversible/evidence-preserving schema.

### Prepared rollout and rollback — instructions only, not executed

1. Obtain separate authorization for **disabled staging-first backend rollout**. Record
   the selected verified commit and previous deployed release from the existing release
   state, preserve unrelated runtime changes, inspect current holds and verify both Apple
   flags, real checkout, sign-in/relay and sync remain false. Use `docs/STAGING.md`.
2. From this trusted isolated checkout, under that later authorization run
   `python3 -B scripts/staging/workflow.py deploy VERIFIED_COMMIT`. Existing workflow backs
   up staging before migration. The prepared **apple-session-safety-disabled** path compares
   deployed source bytes to that commit, runs only the focused PHP purchase/accounting tests,
   validates the applied empty session schema, disabled runtime/HTTPS guards and staging
   isolation. No production Apple credentials are copied to staging. Reuse this commit's
   passed Flutter evidence; do not activate sandbox on staging or manufacture a transaction.
3. Only after passing staging evidence and explicit production authorization, run
   `python3 -B scripts/staging/workflow.py promote VERIFIED_COMMIT`. It requires the same
   verified commit/log, makes a fresh verified production backup under
   `/home/shelf/backups/shelf/`, preserves reader/payment/agreement history, applies the
   committed additive migration and checks disabled runtime/HTTPS guards. Stop on first
   live failure; maintenance is lifted by the workflow's finally block. Do not run the
   old provider-configuration helper or recreate the existing webhook.
4. Before any Apple sessions/history, rollback may return to the **recorded prior deployed
   revision**, keeping all gates OFF and preserving runtime configuration/handler. The
   additive empty table can safely remain. An explicitly approved migration rollback must
   be limited to this empty table after backup; never blanket-roll back a mixed batch.
   After any sessions or Apple history, preserve the schema and these access/session guards;
   do not restore old code that reintroduces stale Apple-only access, drop evidence or
   restore SQL over later activity. Use a separately reviewed forward correction instead.

**A new TestFlight build IS required before any future Apple test.** Installed 1.0.0 (5)
lacks session protocol/SDK identity handling and is intentionally rejected at consent by
the corrected backend. No build number reserved, build/upload or phone test performed.
Actual RevenueCat/StoreKit identity/proof shapes, product retrieval, purchase/restore/
refund/reversal and delivery acceptance still require separately authorized phone work.
The durable 30-minute automatic-closure mechanism remains **unimplemented** and must be
prepared and verified before a separately authorized activation window. These fixes do
not authorize or perform that activation or closure scheduling.

Owner-verified RevenueCat settings stay recorded unchanged: notification tracking OFF;
Keep with original App User ID; different sandbox behavior OFF; Anybody entitlement access.
France exclusion/ANSSI cancellation, Apple authentication/relay and stopped relay task,
Android/UI pause, pricing-sync, second-phone restore and small-improvement deferrals,
and the joint Android/iOS public-launch hold are preserved. No deployment, live migration,
provider edit, purchase, checkout activation, build/upload or public release in this task.

**Exactly one next action:** authorize the disabled staging-first backend rollout of
this verified correction commit, with purchasing kept OFF.

## Confirmed RevenueCat settings and complete sandbox path review — 14 October local / 15 October 2026 UTC

Resumed clean **cb6cb498efd4266802ecf8ad40246ce38e965d70**. Owner supplies
screenshot-verified existing dashboard observations, **no settings changed**:

- Shelf Apple app **Track new purchases from server-to-server notifications OFF**.
- Project **Transferring purchases seen on multiple App User IDs: Keep with
  original App User ID**; **Use a different behavior for sandbox OFF**. Sandbox
  therefore inherits Keep with original App User ID.
- Separate **Allow testing entitlements and in-app currency for: Anybody**.

The two previously unverified dashboard values are now **OWNER-VERIFIED**, not
API-verified; no repeat screenshot/access request needed. Build 5 basic iPhone
installation/launch acceptance remains recorded; purchase acceptance pending.

### Actual meaning of Anybody

Current official [RevenueCat Sandbox Testing Access](https://www.revenuecat.com/docs/projects/sandbox-access)
says Anybody allows non-production purchases to grant configured RevenueCat
entitlements/currency to every App User ID. It covers platform sandboxes as well
as Test Store. This is **not dashboard staff permission**, and does not authorize
Shelf API access. Changing to an allowlist still records sandbox purchases; it
can remove existing nonqualifying sandbox entitlements and leaves already-granted
currency. Consequently do not change this project-wide setting casually or treat
an owner-only allowlist as authentication. No currency feature is implemented by
this Shelf test, and its platform app key is not a RevenueCat Test Store key.

RevenueCat may grant a sandbox entitlement **independently of Shelf consent**.
That is a real provider-level grant/identity-pollution risk. Shelf's normal client
does not use CustomerInfo to unlock books, so this is **not evidence of
unauthorized Shelf paid-text access**. Restriction is in authenticated server
consent, store proof and book access, not in the public SDK key or SDK UI.

### Every relevant path evaluated

Reused cb6cb49 runtime/private-owner/Play-sale/provider/disabled-config findings,
56 PHP tests/511 assertions, 15 Flutter tests and same-source rollout evidence.
Narrow read-only comparison at **2026-10-15 00:38:02 UTC** confirms deployed
purchase/proof/exception/consent/webhook/access/ledger/accounting/API route bytes
match **c8c266221d972d409ffa947ca87397293c69c004**; both Apple gates and real
checkout remain false, no Apple purchase exists. No broad audit/test or synthetic
transaction rerun. Findings below are source/path analysis, not a live exploit.

| Path | Actual safeguard and limitation |
| --- | --- |
| Normal SDK buy | Authenticated consent, explicit owned-test server echo, both gates and fresh owner/book3 eligibility precede SDK buy; CustomerInfo alone never unlocks. |
| Direct SDK calls | Public app key and client-selected App User ID can bypass Shelf's UI/consent to obtain a **RevenueCat** sandbox entitlement under Anybody. Shelf does not control StoreKit or RevenueCat recording through these calls. A caller using their own nonowner ID is rejected by Shelf owner/proof checks. Numeric owner-ID impersonation has the attribution limitation below. |
| Restore | SDK restore may restore RevenueCat state; it grants no Shelf access by itself. Authenticated library/restore uses server reconciliation, exact current/original reader and app/product/SANDBOX proof; Keep-original discourages transfer but does not authenticate SDK IDs. Apple recovery cannot transfer history to another Shelf reader. |
| Webhook | Private distinct Apple authorization, configured gate, app/store/environment/product, numeric identity, exact transaction/time and independent V2 proof; locked owner/fresh test consent rechecked for a new overlapping purchase. Nonowner events rejected; duplicate events/transactions idempotent. Provider webhook authentication proves delivery origin, not that the SDK user ID was chosen by the authenticated owner. |
| Server confirm/Library | Reader-authenticated routes, independent permanent entitlement and transaction proof; no reliance on client receipt or cached SDK entitlement. Ordinary duplicates remain blocked; overlapping test requires existing Play access and no prior Apple purchase. Gate-closure defect below prevents successful Library refresh after Apple history. |
| Account switching/offline | Client clears configuration/test permission/confirmation, serializes SDK sign-out, uses reader/generation checks and per-reader encrypted downloads. Late responses do not cross Shelf accounts. Server token determines paid-text identity; provider aliases/entitlements do not override it. |
| Paid text/audio/media | Authenticated reader middleware and BookAccessService check active server entitlement/reviewer grant. No direct RevenueCat entitlement grants a new server entitlement; existing server entitlement is not itself erased by closing Apple gates. Direct content paths do not run the Library reconciliation guard. |
| Play/accounting | Existing Play sale/access preserved and union-of-valid-store access remains during open-window reconciliation. Apple records forced SANDBOX/Test, excluded by real-income scope; accounting rejects Test owed/payment/real reversal entries. Unique sale/event keys and second-overlap rejection prevent duplicated real royalties. |

### Two remaining safety problems — activation NOT CLEARED

**1. SDK identity is not authenticated purchase intent.**
[RevenueCat's identity guidance](https://www.revenuecat.com/docs/customers/identifying-customers)
allows client-supplied App User IDs; Shelf uses the numeric reader ID and exposes
the SDK key in public purchase config when enabled. Matching current/original
RevenueCat ID and a genuine transaction proves provider attribution, **not**
that a Shelf-authenticated owner initiated that transaction. A modified client
could choose the owner's numeric ID. If a genuine sandbox purchase is attributed
to that ID inside the owner's fresh consent window, current server checks do
not bind the specific transaction to a secret owner test session. This is an
inferred attribution/race risk, not a reproduced exploit or demonstrated paid-text
disclosure: the caller still lacks the owner's Shelf token. It can contaminate
owner provider/history or consume the single test slot. Anybody does not fix it;
allowlisting the same numeric owner ID alone does not fix impersonation either.

**Smallest scoped security correction proposal:** bind the single Apple test to
an unpredictable server-issued owner/Book3 purchase-session identity or supported
StoreKit account token, delivered only after authenticated consent; verify that
binding in confirmation **and webhook** before recording. Do not trust a
client-selected numeric ID as purchase-session authentication. Preserve existing
numeric Play identity/history, strict Apple original/current identity and permanent
proof, and prohibit unbound new Apple sales. Exact SDK/provider support and any
reversible schema/client change need a separately approved implementation task;
no invented API or migration is proposed as already available. Do not silently
switch existing identities or rely on secret public API keys.

**2. Closing Apple gates blocks valid Play Library refresh.** In deployed
AppleSandboxPurchases::reconcile, any recorded APP_STORE purchase with the gate
OFF causes **503**, before PurchaseService performs Play reconciliation and before
Library returns books/lease. This also affects refunded recorded Apple history.
The existing test explicitly expects 503 for recorded Apple history with the gate
OFF. For this dual-store owner, after one accepted Apple transaction, the planned
closure would leave the Play sale/entitlement intact but fail Library/confirm/
restore and download refresh. Earlier cb6cb49's statement that valid Play access
remains was accurate about records/direct entitlement, **insufficient about normal
Library operation**. No such live failure exists now because no Apple purchase
has been made; no data changed to demonstrate it.

**Smallest scoped closure correction proposal:** make disabled-Apple handling
store-aware: independently verify/return valid Play-backed books and leases while
refusing new Apple processing, stale Apple-only grants or Apple-only lease renewal.
Apply the same distinction to direct content authorization; do not expose stale
Apple-only text merely by skipping reconciliation. Preserve all immutable Apple
history and valid Play ownership. Focused regressions must cover overlapping
Play+Apple, Apple-only, refunded Apple history, closure and direct paid-content
requests. No blanket entitlement removal, history deletion, SQL restoration or
provider refund is a valid shortcut.

**Activation is not safe to approve yet.** Confirmed dashboard settings resolve
that earlier blocker; they do not resolve these two code-path issues. No genuine
revenue or duplicate real royalties found, and no ordinary nonowner Shelf unlock
found, but provider entitlement retention and attribution and correct post-test
closure are not fully controlled. A sandbox non-consumable/provider entitlement
and immutable Test purchase can persist after disabling Shelf gates. Closing
checkout is **not refund/revocation** or removal of provider ownership.

### Future bounded activation/cleanup — blocked plan only

Retain the prior exact two-flag backup/encrypted-checkpoint, atomic .env and
runtime verification procedure, but **do not execute it** until the scoped fixes
are approved, tested, staged/promoted and compatible with the installed client
(or separately accepted replacement where needed). Then a separately authorized
window should have a recorded hard deadline **30 minutes after activation**,
with Codex arranging verified automatic closure of both flags before opening them.
That closure mechanism is **not implemented or installed in this task**; no cron,
queue or scheduled task created. It must survive an interrupted interactive
session, refuse concurrent/unexpected config changes and report closure failure.
Close earlier on the first mismatch or completed single test; no automatic reopen.

Only the verified owner and Book3, one cancellation then at most one genuine
TestFlight sandbox transaction under explicit approval; no repeats on pending
confirmation. Verify server-bound transaction, Test-only records, real totals and
Play history. At deadline/end set both gates false, clear config, verify disabled
config/proof/webhook guard **and successful independent Play Library/download
refresh and denial of stale Apple-only direct content**. Remove only window-created
closure scheduling after verified closure; retain evidence/checkpoint and all
purchase/refund/consent history. Leave RevenueCat and Play configuration untouched.
Refund/reversal and removal of provider sandbox ownership require separate explicit
scope; do not promise that timer cleanup erases Apple/RevenueCat non-consumables.

Only these two records changed; no activation, purchase/refund, provider edit,
deployment/migration, build/upload, tester invitation, Play/accounting modification
or public release. France exclusion/ANSSI cancellation, Apple sign-in/relay,
Android/UI pause, pricing-sync, second-phone restore, small-improvement deferrals
and joint Android/iOS launch hold preserved.

**Exactly one next action:** authorize a focused isolated correction of Apple
purchase-session binding and store-aware gate closure before any activation.

## Build 5 installed; final owner Apple purchase preflight — 14 October local / 15 October 2026 UTC

Resumed clean **bdd550af3fc256e756b8ba5a0afe9828b02425ce**. Owner confirms
**TestFlight 1.0.0 (5) installed on their iPhone and opens correctly**. Record
basic installation/launch acceptance only; no product retrieval, purchase,
restore/refund/reversal or notification acceptance follows from this evidence.

### Current read-only evidence

At **2026-10-15 00:23:29 UTC**, production HEAD and staging/production release
state match **c8c266221d972d409ffa947ca87397293c69c004**. Deployed exception, proof,
Apple event handler, consent controller, purchase config and accounting service
bytes match the validated source. Reused **56 PHP tests / 511 assertions, 15
Flutter tests**, exact staging/promotion and backup/history-preservation evidence.
No broad tests, full provider audit, build or deployment repeated.

Private production queries verified one owner admin and one exact-email reader;
reader verified, buying allowed, owner predicate true. Book **3 / shelf_book_3**
is Published with positive price. Existing unrefunded/unrevoked **PLAY_STORE sale**
and active book access remain intact; no APP_STORE purchase exists. No reader
ID/email, purchase history or entitlement changed or printed.

Runtime **Apple sandbox OFF, owned-book3 test OFF, real checkout OFF, Apple
sign-in/relay OFF, Play sync OFF**. Exception is unavailable while OFF. A separate
CLI process set both Apple flags **in memory only** and confirmed configured
provider prerequisites and exact owner eligibility; nonowner, blocked owner and
another book denied. No .env/cache writes, saved models, consent calls or provider
proof calls occurred in that process. Staging separately confirms active staging,
both Apple gates OFF, proof disabled and real checkout OFF. Public production
**GET /api/purchases/config?platform=ios** returned **200**, all checkout fields
false and SDK key null at **00:24:55 UTC**. Disabled webhook guard evidence from
rollout reused; no test event or POST sent in this preflight.

Existing credentials: RevenueCat bounded GETs **200** confirm Apple app
**app8ef018b95b / services.shelf.app**, non-consumable product
**prod0d084e901f / shelf_book_3**, and **whintgr769cb03ff2**, app-scoped sandbox,
URL **https://shelf.services/api/purchases/webhook?source=apple**, exact
non_renewing_purchase/cancellation/expiration/refund_reversed events. Local
protected webhook authorization matches runtime. Apple product GET **200** at
**00:25:13 UTC** confirms **6822080809 / shelf_book_3 / NON_CONSUMABLE /
MISSING_METADATA**. Saved metadata state is not actual StoreKit retrieval;
existing title discrepancy/genuine review image remain separate pending work.
France exclusion evidence and notification V2 configuration reused unchanged.

Ordinary owned-book consent remains **409** for Apple without the explicit test
exception and for Play. Client normal purchase still refuses owned books. The
exception requires both gates, production checkout OFF, non-staging, exact
verified/unblocked owner, active Play sale/access, book3 and no prior Apple book3
purchase. Consent rechecks locked reader/book, uses explicit test wording and
requires the server echo before SDK buy. Existing fresh-consent/time/transaction,
original numeric reader and independent APP_STORE/SANDBOX permanent entitlement
proof remain mandatory; SDK success or Play access alone cannot confirm Apple.
Apple sale/event keys prevent duplicate journal entries and a second overlapping
Apple purchase. Apple records use **SANDBOX / Test**; ordinary ledger scope excludes
them and accounting rejects real earnings/owed/payment entries for Test history.
Thus this path cannot create real revenue or duplicate real author royalties.
Play records and valid union-of-store access remain untouched, including on
Apple cancellation/refund. These safeguards reuse the passed implementation tests.

### Remaining blocker: two saved RevenueCat values are not API-exposed

Used existing V2 credentials before requesting manual inspection. Actual Apple
app response exposes only app metadata and app_store fields **bundle_id,
app_store_connect_api_key_configured, app_store_connect_vendor_number,
custom_url_scheme, subscription_key_configured**. Shelf project list GET **200**
exposes **object, id, name, created_at, icon_url, icon_url_large**. Neither contains
Apple **Track new purchases from server-to-server notifications**, restore
behavior, or sandbox-override fields. Current documented
[app](https://www.revenuecat.com/docs/api-v2/app) and
[project](https://www.revenuecat.com/docs/api-v2/project) resources do not provide
these settings. Unsupported project-detail GETs with/without trailing slash
returned **404**; documented project-list GET succeeded. No settings-write probe,
private dashboard endpoint, token search or provider change was attempted.

**Both actual values remain unverified:** Apple new-purchase notification tracking
must be **OFF**; effective sandbox restore behavior must be **Keep with original
App User ID**. Do not infer defaults from missing fields or subscription-key
configuration. RevenueCat's
[notification guidance](https://www.revenuecat.com/docs/platform-resources/server-notifications/apple-server-notifications)
and [restore guidance](https://www.revenuecat.com/docs/projects/restore-behavior)
explain the numeric-ID/anonymous-ingestion risk and optional sandbox override.
Technical owner isolation is ready, but **not cleared to activate yet** while
these required values remain unknown. No activation or transaction authorized.

Minimum dashboard inspection, **read-only**, existing Shelf project:

1. **Apps & providers → existing Shelf iOS (App Store) → Apple Server to Server
   notification settings**: read **Track new purchases from server-to-server
   notifications**. Required OFF; do not toggle or Save.
2. **Project settings → General → Restore behavior**: read the main selection and
   **Use a different restore behavior for sandbox**. If override is enabled, read
   the sandbox selection; otherwise main selection applies. Required effective
   sandbox value **Keep with original App User ID**. Do not change any setting.

Report those saved values in text; no screenshot or credential request. An
unexpected value needs a separately approved correction, not silent adjustment.

### Safe activation procedure — plan only, NOT EXECUTED

After that blocker is resolved and explicit one-owner/one-book sandbox-window
approval is given, Codex performs the existing fresh verified production backup
and encrypted .env checkpoint under **/home/shelf/backups/shelf/**, records private
pre-test reader/Play entitlement/purchase/event/ledger/accounting fingerprints,
and rechecks deployed source, owner eligibility, provider mapping and OFF gates.
Stop before activation if backup/checkpoint or any prerequisite fails.

As shelf, atomically change **only** these existing production .env flags:
**SHELF_APPLE_SANDBOX_ENABLED=false → true** and
**SHELF_APPLE_OWNED_BOOK3_TEST_ENABLED=false → true**. Require regular file/no
symlink, shelf ownership/0600, exactly one occurrence per field, expected before
values and unchanged content since reading; preserve every other setting. Run
**php artisan config:clear** in **/home/shelf/apps/shelf**. No config helper prepare,
migration, deployment, new key or provider setting change is needed.

Verify actual runtime proof configured and both flags true; exact owner eligible,
nonowner/blocked/other-book denied, real checkout/sign-in/relay/sync false and
staging OFF. Private iOS config GET must advertise sandbox true, production false,
allowlist exactly **[shelf_book_3]**, correct app/store/SDK mapping. Public config
readiness is global, not reader-specific; server consent/proof enforce the
restriction. Close both gates immediately on mismatch; no purchase follows.

### Exact iPhone procedure — later approved window only

Use the installed **TestFlight 1.0.0 (5)** and existing verified **Shelf reader
email/password**, not admin/Apple/relay/anonymous/new account. Keep existing
Apple/TestFlight account, app and downloads. No new sandbox tester or sign-out of
Media & Purchases is needed for this basic TestFlight case. TestFlight uses
sandbox with production Apple Accounts
([RevenueCat](https://www.revenuecat.com/docs/test-and-launch/sandbox/apple-app-store));
no charge is incurred by a genuine sandbox transaction.

After Codex verifies activation, open Shelf, sign in if needed and refresh
My Library. Open **book3**, read its sample and use the separate
**Test Apple purchase — no charge** action. Accept the explicit agreement on the
Apple sandbox test screen. Inspect Apple's purchase sheet for the intended book
and test/no-charge context; authenticate only through the genuine Apple sheet
if requested. If identity/product/environment is unclear, or product unavailable,
stop without confirming; do not change storefronts/providers or repeat buying.
Within explicit test authorization, cancel once to verify existing Play access
stays intact, then complete **at most one** sandbox purchase. This planned
cancellation is not a second transaction. Wait for the app's **Apple sandbox
purchase confirmed** message; do not treat existing Read book access as proof.
If confirmation fails/pends, report it and do not buy again.

Codex then independently checks exact Apple transaction/time, original reader,
RevenueCat SANDBOX proof and permanent entitlement, Shelf Test-only immutable
purchase/event/journal, unchanged Play history/access and real accounting totals.
Record actual webhook delivery separately from REST reconciliation; one does not
prove the other. Restore/refund/reversal/full phone acceptance remain separate;
no second-phone restore or Apple sign-in/relay test is requested.

### Disable and verify afterward — plan only, NOT EXECUTED

Codex atomically returns **both** production flags to false with the same
permissions/concurrency checks, runs **php artisan config:clear**, and verifies
runtime proof/exception OFF; iOS config enabled/sandbox/production false and SDK
key null; staging OFF and real/sign-in/relay/sync OFF. Under that later window's
verification scope, incomplete unauthenticated Apple webhook guard must stop
**503 production / 403 staging** before processing. Keep provider keys/webhooks,
all Play/Apple consent/purchase/refund/journal history, schema and downloads.
Never restore SQL or run migration down merely to close the window. Gate closure
is not a refund. Disabled Apple proof cannot renew an Apple-only offline lease;
existing valid Play access remains. Any unconfirmed Apple reconciliation needs a
later authorized window; do not erase transaction history or initiate another buy.

Only requested records changed. All gates stayed OFF; no transactions, provider
settings, build/upload/tester changes, ownership deletion or public release.
France exclusion, pricing-sync, second-phone restore, Apple authentication/relay,
Android/UI and small-improvement deferrals and joint-launch hold preserved.

**Exactly one next action:** inspect the two RevenueCat settings in the existing
dashboard using the read-only paths above and report their saved values.

## Build 5 assigned to existing Shelf Owner — 14 October local / 15 October 2026 UTC

Resumed clean **b662fbd33107471963b59fd5b2b7c7e4e5ca34c9** under explicit owner
authorization to add **only 1.0.0 (5)** to the existing internal **Shelf Owner**
group. Existing protected ASC key reused; no credentials or tester details printed.

Preflight **2026-10-15 00:12:07 UTC**, authenticated GETs **200**: exact build
**76e0e894-e322-49bc-9874-fbfc770b8a80**, app **6821471140 / services.shelf.app**,
preReleaseVersion **IOS / 1.0.0**, build version **5**, **VALID / INTERNAL_ONLY**,
expired false, usesNonExemptEncryption false and **READY_FOR_BETA_TESTING**.
Compliance decision/readback from the previous task reused without change/audit.

Existing group **1511a217-4f89-4dc7-8b8d-4679ce8a6226 / Shelf Owner** is internal,
linked to that exact app, with all settings equal to the accepted Build 4 private
snapshot. Complete tester GET contains **one** tester; both ID and email exactly
match the recorded owner in
**/home/shelf/tmp/shelf-build4-group-before-20261013.json**. Identity withheld.
Complete group-build list initially contained **4, 3, 2**; Build 5 was absent.
Private pre-write snapshot saved **0600**, outside Git, at
**/home/shelf/tmp/shelf-build5-group-before-20261015.json**.

Only provider mutation:
**POST /v1/betaGroups/1511a217-4f89-4dc7-8b8d-4679ce8a6226/relationships/builds**,
body data containing one resource, type **builds**, ID
**76e0e894-e322-49bc-9874-fbfc770b8a80**; HTTP **204**. This is additive; no
replacement/removal, tester invitation, membership edit or group setting sent.
Already-present handling skips this POST; no repeated write or retry occurred.

**Readback completed 2026-10-15 00:12:14 UTC:**

- Group-side builds and builds relationship GETs both independently confirm
  **5, 4, 3, 2**, exactly the previous build IDs plus Build 5.
- **internalBuildState=IN_BETA_TESTING**, external **NOT_APPLICABLE**;
  existing autoNotifyEnabled true unchanged.
- Build 5 remains **VALID / INTERNAL_ONLY**, all build attributes exactly unchanged,
  including the cleared compliance field.
- Group settings exactly unchanged; the same sole tester ID/email retained.

Private sanitized readback retained **0600** at
**/home/shelf/tmp/shelf-build5-group-after-20261015.json**. All provider readbacks
returned **200**. Assignment and internal testing availability are verified;
installation/launch and Apple purchase/notification acceptance remain pending.
Only the two requested documentation files changed, with scoped diff/whitespace
checks; no source/test change, broad suite or completed-audit rerun.

No build/upload, compliance change, tester invitation, purchase activation,
ownership change, metadata/territory edit, deployment, email or public release.
Real checkout, Apple sandbox/owned-book3 test and Apple sign-in/relay remain OFF.
France exclusion/ANSSI cancellation, pricing-sync and second-phone-restore
deferrals, deferred Apple authentication/relay checks, Android/UI pause,
small-improvement deferrals and joint Android/iOS public-launch hold preserved.
Backend remains c8c2662. The existing auto-notification setting was preserved,
not deliberately changed or used to claim delivery.

**Exactly one next action:** owner opens **TestFlight → Shelf → Update/Install**
for **1.0.0 (5)**, using **Previous Builds → 1.0.0 → build 5** if needed.
Keep the existing installation/account/data; no uninstall or data clearing.
Confirm Build 5 installs and launches; this does not authorize a purchase or
Apple sign-in test. No dashboard assignment or new build is needed.

## Build 5 export compliance cleared — 14 October local / 15 October 2026 UTC

Resumed clean **ada8bc557855f9af37b681b30015ac26278ce6c1** under explicit owner
authorization for **Build 5 only**, reusing the accepted Build 4 non-France
standard-encryption decision. Build 5 source remains owner-reported Codemagic
**8283badf674f94c8cc7e18a5371e35cdc67d5d1c**; no new artifact or binary audit.

### Focused encryption comparison

Compared documented Build 4 source **2702fbb** with Build 5 **8283bad**. All
mobile production changes are confined to owner purchase-test permission,
confirmation and UI in library_controller.dart, purchase_screen.dart and
collection_detail_screen.dart; reviewed diffs introduce no encryption primitive,
key handling or general-purpose encryption feature. Test changes are separate.
Dependency declarations/lockfile, native iOS tree, account/Keychain implementation,
audio cache, repository and **purchases/download_store.dart** are byte-identical.
Thus the documented **cryptography 2.9.0 / AES-256-GCM**, per-reader generated
key, Keychain storage, HTTPS/SDK transport and SHA-256 cache naming assessment
remains applicable. Full prior audit and source test suites were not repeated.
This comparison does not claim independent inspection of Codemagic's IPA.

Rechecked [Apple's current documentation table](https://developer.apple.com/help/app-store-connect/reference/app-information/export-compliance-documentation-for-encryption)
and [BuildUpdateRequest attributes](https://developer.apple.com/documentation/appstoreconnectapi/buildupdaterequest/data-data.dictionary/attributes-data.dictionary).
The accepted standard-algorithm/non-France no-document answer remains supported:
**usesNonExemptEncryption=false**. This does not assert no encryption or OS-only
encryption. The recorded accurate basis remains encryption Yes, standard outside
OS Yes, proprietary No, France No. No ANSSI research, filing or email resumed.

### Actual provider write and independent readback

Existing private ASC key reused. Preflight GETs confirmed app **6821471140 /
services.shelf.app**, **IOS / 1.0.0 (5)**, build
**76e0e894-e322-49bc-9874-fbfc770b8a80**, **VALID / INTERNAL_ONLY**, flag null,
encryption declaration null and **MISSING_EXPORT_COMPLIANCE**. Availability
readback at **2026-10-15 00:08:03 UTC** confirms **174 territories / FRA absent**
for shelf_book_3. App-wide availability remains previously verified unconfigured;
no list was created or changed. Future app distribution must exclude France.
An initial availability GET used v1 and returned **404 PATH_ERROR**; corrected
existing v2 route and complete territory read returned **200**, no provider change.

Private before-state saved **0600**, outside Git:
**/home/shelf/tmp/shelf-build5-compliance-before-20261015.json**.
Immediately rechecked unchanged build attributes and missing-compliance status.
Only mutation: **PATCH /v1/builds/76e0e894-e322-49bc-9874-fbfc770b8a80**,
data type builds, attributes **usesNonExemptEncryption=false**, HTTP **200**.
No relationships or other attributes sent; no retry or other provider write.

**Readback verified by 2026-10-15 00:08:39 UTC:**

- **usesNonExemptEncryption=false**; all other build attributes exactly unchanged.
- **processingState=VALID**, **buildAudienceType=INTERNAL_ONLY**, expired false.
- **internalBuildState=READY_FOR_BETA_TESTING**: MISSING_EXPORT_COMPLIANCE cleared.
- **externalBuildState=NOT_APPLICABLE**, existing autoNotifyEnabled true unchanged.
- Encryption declaration GET **200 / null**, consistent with the no-document route.
- Existing **Shelf Owner / 1511a217-4f89-4dc7-8b8d-4679ce8a6226** remains internal;
  complete group-build GET contains **4, 3, 2**, not 5. Sole tester count **one**;
  tester identity withheld and membership untouched.

Private sanitized after-state retained **0600** at
**/home/shelf/tmp/shelf-build5-compliance-after-20261015.json**.
Compliance is cleared; **Build 5 is still unassigned and installation is pending**.
No government certification, public-review acceptance, phone purchase acceptance
or notification delivery is claimed. Only requested documentation changes,
scoped comparison and diff checks; no broad test or build rerun.

All purchase/owned-test/real-checkout/sign-in/relay gates remain OFF. No deployment,
build/upload, tester assignment/invitation, group creation, metadata/territory
change, transaction, email or public release. France exclusion, pricing-sync and
second-phone-restore deferrals, deferred Apple authentication/relay checks,
Android/UI pause, small-improvement deferrals and joint Android/iOS launch hold
remain binding. Backend remains c8c2662.

**Exactly one next action:** authorize assigning existing **1.0.0 (5)** to existing
**Shelf Owner** internal group only, preserving its one tester and all gates OFF.

## Build 5 Apple receipt and internal readiness — verified 14 October local / 15 October 2026 UTC

Owner reports Codemagic signed internal TestFlight run **#9**, ID
**6acfddac546ce6236ceea2d5**, succeeded on 14 October from source
**8283badf674f94c8cc7e18a5371e35cdc67d5d1c**, producing **Shelf.ipa (29.55 MB)**,
intended **1.0.0 (5)**. That cloud/artifact evidence is owner-reported; Codex did
not independently inspect the IPA, signing output or Codemagic worker logs.
Local clean branch HEAD matched that exact source before this documentation edit.
Apple receipt and readiness below are independently verified, not inferred from
Codemagic success. Earlier candidate/no-upload/diagnostic-next-action entries are
historical and superseded by this receipt; no diagnostic rerun or upload retry is
requested.

Authenticated **GET-only** inspection using the existing protected ASC key at
**2026-10-15 00:01:23–00:02:01 UTC** (14 October local):

| Evidence | Actual Apple value |
| --- | --- |
| App | **6821471140 / Shelf - شیلف / services.shelf.app** |
| Build/upload ID | **76e0e894-e322-49bc-9874-fbfc770b8a80** |
| Version/platform | **1.0.0 (5) / IOS**, independently confirmed through preReleaseVersion |
| Upload | **COMPLETE**, errors/warnings/infos all empty; uploadedDate **2026-10-14T13:02:02-07:00** (20:02:02 UTC) |
| Build processing | **VALID**, uploadedDate **2026-10-14T13:02:54-07:00** (20:02:54 UTC), expired **false** |
| Audience | **INTERNAL_ONLY** |
| Internal eligibility | **internalBuildState=MISSING_EXPORT_COMPLIANCE** |
| External state | **NOT_APPLICABLE** |
| Encryption field | **usesNonExemptEncryption=null**, not false |
| Encryption declaration | GET **200 / data=null** |
| Shelf Owner group | **1511a217-4f89-4dc7-8b8d-4679ce8a6226**, isInternalGroup **true**, hasAccessToAllBuilds **false** |
| Group assignment | Complete group-side build list contains **4, 3, 2**, **not 5** |
| Existing testers | Complete group tester relationship list contains **one**; identity withheld, membership untouched |

All nine Apple GET routes returned **200**; bounded app uploads/builds/groups
and group builds/tester lists reached terminal pages. Uploads are **1 FAILED /
2 COMPLETE / 3 COMPLETE / 4 COMPLETE / 5 COMPLETE**; received builds **2–5 VALID**.
The initial sandboxed connection failed DNS resolution; the approved network
inspection succeeded. No Apple error, provider mutation or credential change.

**Build 5 has finished processing. It is not ready for internal installation:**
export compliance is missing, and it has not been assigned to Shelf Owner.
[Apple's beta-compliance guidance](https://developer.apple.com/help/app-store-connect/test-a-beta-version/provide-export-compliance-information-for-beta-builds/)
requires the build's compliance information before testing. Build 4's previously
cleared declaration does not establish Build 5 clearance. Existing approved
non-France encryption inventory/declaration evidence is retained for a later
specifically authorized compliance action; no encryption answer is saved here.
No public App Review acceptance, installed-build acceptance, purchase acceptance
or notification delivery is claimed.

Only these two requested records changed; scoped diff/whitespace checks replace
an unnecessary source/test/build rerun. No compliance PATCH, tester/build
assignment, invitation, group creation, deployment, gate change, new build,
upload, notification test or transaction occurred. Checkout, Apple sandbox,
owned-book3 test and Apple sign-in/relay remain OFF. France exclusion/ANSSI
cancellation, Android/UI pause, pricing-sync, second-phone restore, Apple auth/
relay and small-improvement deferrals and joint Android/iOS public-launch hold
remain binding. Backend deployment remains c8c2662.

**Exactly one next action:** authorize clearing **only Build 5 export compliance**
using the existing approved non-France declaration, with readback verification
and all checkout gates OFF. Group assignment remains a later separate action.

## Run #8 credential failure — documented CLI path prepared, NOT cloud-verified

14 October 2026. Started clean at **1116317bedde0402a6516c175d66bbf6073e2df2**.
Owner confirms run #8 stopped before compilation because the custom guard could
not access its expected ASC credential environment variables; owner subsequently
saved **SHELF_IOS_BUILD_NUMBER=5**. Run #7's duplicate-4 rejection remains recorded.
Existing integration supports signing/publishing; that fact does not establish
private-variable availability to Python scripts or CLI access in this workflow.
No run, upload, deployment, activation or provider change performed here.

### Actual dependency and official findings

Inspected signed **ios-testflight-internal**: integration name exactly
**shelf_app_store_connect**; variable groups exactly **shelf_ios_foundation** and
**shelf_ios_testflight**. Previously, Python required raw
APP_STORE_CONNECT_KEY_IDENTIFIER / APP_STORE_CONNECT_ISSUER_ID /
APP_STORE_CONNECT_PRIVATE_KEY before making its own JWT/API requests. Both hooks
ran before a signed build; the first stopped #8. This direct credential-exposure
assumption is unsupported by the observed account behavior and is removed.

[Codemagic build versioning](https://docs.codemagic.io/knowledge-codemagic/build-versioning/)
documents existing publishing-integration reuse and the official CLI.
[Publishing](https://docs.codemagic.io/yaml-publishing/app-store-connect/) distinguishes
integration authentication from explicitly supplied credential variables.
[CLI action](https://github.com/codemagic-ci-cd/cli-tools/blob/master/docs/app-store-connect/get-latest-testflight-build-number.md)
supports Apple app ID **6821471140**, **--platform=IOS** and
**--pre-release-version=1.0.0**. CLI authentication still needs authorized ASC
access; its documented resolution uses configured key/issuer/private-key options,
environment or supported private-key files. There is no documented per-command
integration-name flag to invent. Server cannot prove how this Codemagic worker
exposes its existing integration; neither successful publishing nor server API
success proves the custom CLI call works. That limitation remains explicit.

Codemagic **BUILD_NUMBER / PROJECT_BUILD_NUMBER** count workflow/project runs;
run indexes **7/8** are not Apple's CFBundleVersion. Shelf retains explicit
owner-selected **SHELF_IOS_BUILD_NUMBER** with no run-index/numeric fallback.
The CLI's latest TestFlight number is not proof about pending/rejected upload
records; no claim that it reserves numbers or covers all in-progress uploads.

### Refreshed Apple read-only evidence

Existing protected server key, bounded app-scoped builds/uploads GETs **200** at
**2026-10-14 19:23:55 UTC**: received **2,3,4 VALID**; uploads **1 FAILED /
2 COMPLETE / 3 COMPLETE / 4 COMPLETE**, **IOS / 1.0.0**, no pending upload or 5.
**Candidate remains 1.0.0 (5)**. Before any later full run, repeat those two GETs,
require no processing build/pending upload, selected number greater than every
received/uploaded number, and no competing Codemagic signed run. This separates
complete upload evidence from CLI latest-TestFlight evidence. No public endpoint,
credential relay or speculative extra automatic guard was introduced.

### Minimal source correction and focused verification

Replaced only unsupported custom credential/JWT/REST behavior with a small
wrapper invoking:

```bash
app-store-connect get-latest-testflight-build-number 6821471140 --platform=IOS --pre-release-version=1.0.0 --disable-jwt-cache --silent
```

CLI output must be one positive integer; explicit chosen number must exceed it.
Missing CLI, timeout, auth/read failure or malformed result stops; no silent
fallback or raw credential precheck. Logs show only number/safe result; captured
provider output stays withheld. Removed the redundant second pre-compile custom
hook. The single check stays before dependency/configuration/compilation steps.
Signing integration, certificate/profile, internal-only export, publisher,
post-export version check, application/purchase/entitlement source are unchanged.

Prepared manual **ios-asc-access-check** with the exact same integration/groups,
working directory **mobile**, mac_mini_m2 and **5-minute** maximum. Its sole script
runs the same wrapper. **No ios_signing, Flutter/dependency commands, artifacts,
publishing, triggers or tester actions**. This is a prepared diagnostic workflow,
not a started build/run. It does not consume an Apple build number.

**8 synthetic tests passed** through
`SHELF_TOOL_TEST_SCOPE=ios-build-number scripts/run_tests.sh`: explicit number/no
run-index fallback, exact CLI scope/no secret arguments, delegated credential
resolution, duplicate/stale choice denial, withheld auth errors, malformed result,
missing CLI/timeout and identical nonpublishing integration/group path. YAML
parse/signed and diagnostic shell syntax, scoped diff checks passed. No purchase,
backend, Flutter suite or macOS compilation repeated. Synthetic runner responses
are not proof of live Codemagic CLI access.

### Non-publishing validation procedure and remaining limitation

Under separate diagnostic-run authorization, select existing Shelf app,
**ios/foundation at this new source**, workflow **ios-asc-access-check** only.
Keep saved **SHELF_IOS_BUILD_NUMBER=5** and existing integration/groups unchanged.
Expected safe result: CLI TestFlight **IOS/1.0.0 latest 4**, chosen **5**, exit 0.
Confirm the exact source and no publishing steps. Stop on unexpected number/auth/
CLI error; do not try a full signed run to discover whether this path works.
Server-side read-only inspection and synthetic tests cannot verify the worker's
integration injection, installed CLI version or permissions; only this bounded
nonpublishing diagnostic establishes that account-specific path.

If it reports unavailable authentication, the smallest documented configuration
is **reuse the same existing authorized ASC key**, privately from the retained
original, adding only **APP_STORE_CONNECT_KEY_IDENTIFIER**, **APP_STORE_CONNECT_ISSUER_ID**
and **APP_STORE_CONNECT_PRIVATE_KEY** as protected Secret variables to existing
**shelf_ios_testflight**, as described in the official publishing/environment
variable guides. Do not generate/rotate keys, change integration/signing, print
values, copy secrets to Git, assume saved dashboard secrets can be retrieved, or
ask for a new Apple account. This is a conditional supported plan, **not executed
or claimed necessary yet**: first test the documented existing-integration CLI.
After any approved credential exposure correction, repeat only the diagnostic.
A full signed build requires successful diagnostic evidence, fresh upload/queue
preflight and separate owner authorization. **Not READY for another full build.**

**Exactly one safe next owner action:** authorize one **ios-asc-access-check**
nonpublishing diagnostic run from the new ios/foundation source, not a signed
build or upload. No repeated build-number setup request; saved 5 is owner-confirmed.

Checkout/Apple sandbox/owned-test/real/sign-in gates OFF; France exclusion,
internal-only testing, Apple/Google entitlements, Android/UI pause, Apple auth/relay,
pricing-sync, second-phone restore, small improvements and joint launch hold
preserved. Deployed backend remains c8c2662. No cloud access success inferred.

## Codemagic run #7 duplicate build incident — guarded source fix, 14 October 2026

Owner reports run **#7**, source **9c41c3281480465ef79ec27cdfc5649894ec3ed7**,
produced a signed **Shelf.ipa** but publishing failed **409
ENTITY_ERROR.ATTRIBUTE.INVALID.DUPLICATE**: cfBundleVersion must exceed
**previousBundleVersion=4**. Intended number was **5**. No successful new Apple
receipt/installation/purchase is inferred from signed artifact creation.

### Confirmed findings and uncertainty

The exact signed YAML has **no numeric build-number fallback**. Its required
`SHELF_IOS_BUILD_NUMBER` is passed directly to Flutter `--build-number`; any
positive integer passed the old guard, including already-used **4**. Mandatory
export inspection compared CFBundleVersion with that same effective environment
variable; export **manageAppVersionAndBuildNumber=false** remained intact.
Thus the workflow did not enforce the previously verified unused number and
allowed a stale dashboard/run environment value through. The reported upload
error is consistent with effective build number **4**; the run's IPA/logs and
Codemagic saved/run-level variable values are not independently available here.
Do not claim which saved group, app override or run override supplied it, or that
Codemagic itself rewrote the number. The unsafe behavior was acceptance of any
positive number without current Apple collision evidence, not an invented YAML
fallback. Unsigned foundation's fixed number 1 is outside the signed workflow and
unchanged; no automatic increment/fallback was introduced.

### Current Apple evidence and correct candidate

Existing protected-key read-only GETs **2026-10-14 18:43:13 UTC**, scoped to
**6821471140 / services.shelf.app**, both **200**: received builds **2, 3, 4 VALID**;
upload records **1 FAILED / 2 COMPLETE / 3 COMPLETE / 4 COMPLETE**, all
**IOS / 1.0.0**. Complete bounded lists contain no pending upload or build **5**.
**Correct next candidate remains 1.0.0 (5)**, not assumed from previous inspection.
No number reservation, dashboard change, upload retry or provider write occurred.
Collision/processing checks run again in the later authorized cloud run.

### Implemented fail-closed safeguard

New `scripts/ios/check_build_number.py` runs before build preparation and again
immediately before signed IPA compilation. It requires the explicit positive
`SHELF_IOS_BUILD_NUMBER`, existing Codemagic App Store Connect integration access,
exact Apple app/bundle identity and complete bounded GET evidence from both
`/v1/apps/6821471140/builds` and `/v1/apps/6821471140/buildUploads`. Chosen number
must exceed every used/uploaded number, including failed upload numbers. Pending
upload, processing build, unknown/malformed state/number, incomplete pagination,
foreign/repeated page, identity mismatch, missing credentials, auth/transport/
parsing failure all stop clearly. No automatic fallback, number selection or API
write. Only chosen/highest number and a safe status are logged, never credentials.

Reuses documented integration-provided **APP_STORE_CONNECT_KEY_IDENTIFIER**,
**APP_STORE_CONNECT_ISSUER_ID**, **APP_STORE_CONNECT_PRIVATE_KEY**; signing
integration/certificate/profile/publisher are unchanged. Private JWT signing uses
a short-lived restricted temporary file, removed before API checks finish; no
secret is written into artifacts or repository. If the integration does not
expose its existing API credentials to custom scripts, the guard stops with a
clear unavailable-credentials message; do not bypass it or create/rotate a key.
[Codemagic's existing-integration guidance](https://docs.codemagic.io/knowledge-codemagic/build-versioning/)
documents reuse without adding separate environment secrets when configured.
Actual custom-script credential exposure in this cloud account remains a later
run-time check, not claimed proved by the server's separate authenticated access.

The two checks reduce stale-number and concurrent-upload risk but do not reserve
a number. Maintain one signed build/upload at a time; no concurrent pipeline or
retry. Apple remains authoritative at upload. Existing post-export number,
internal-only signing and artifact verification are retained.

### Focused verification and exact minimal dashboard action

**13 synthetic tests passed**, through
`SHELF_TOOL_TEST_SCOPE=ios-build-number scripts/run_tests.sh`. They cover explicit
number parsing, stale/lower numbers, failed-upload collision, successful next
number, processing/pending/unknown states, missing credentials, HTTP denial with
private details withheld, exact read-only app paths/identity, trusted bounded
pagination (including actual terminal links with matching meta.paging.total)
and both workflow placements. A bounded live response-shape check confirmed
Apple omits links.next on terminal pages; the guard requires final item count to
match the reported total, and rejects changed/missing totals or truncated lists.
YAML/signed shell syntax passed;
`git diff --check` passed. No PHP/Flutter suite repeated; no build/upload/provider
call made by the synthetic tests. Prior app, entitlement, accounting, backup and
deployment evidence reused. Changes are limited to the guard, its tests, wrapper
scope, two YAML hooks and these records. Reverting those source changes is possible
in isolation but must not be used to bypass a later failed collision check.

**Exactly one next owner action:** in **Codemagic → Team settings → Environment
variables → shelf_ios_testflight**, edit existing **SHELF_IOS_BUILD_NUMBER** to
**5** and save. Ensure the same name is not overridden to 4 in the Shelf app/run
settings; the effective next run must be 5. Keep API/groups/signing and every other
value unchanged. **Do not start/retry run #7 or upload its existing IPA.** A later
separately authorized run must select the new guarded ios/foundation source;
missing API access or any collision stops before compilation.

Backend remains deployed at **c8c2662**; this task performs no deployment or
activation. Apple sandbox/owned-test/real-checkout/sign-in gates remain OFF.
France excluded/ANSSI cancelled, Android/UI pause, Apple auth/relay deferrals,
pricing-sync, second-phone restore, small improvements and joint Android/iOS
public-launch hold preserved. New internal build/processing/install and actual
owner sandbox purchase/notification acceptance remain pending.

## Next signed internal TestFlight build — prepared 14 October 2026, NOT STARTED

**Exact reviewed candidate source: 14c392c17ab13a3a59bde3e159cc625c0f0a6456**,
branch **ios/foundation**. Checkout was clean at that SHA. All intended owner
Apple Book 3 Flutter changes from **c8c2662** are included byte-for-byte;
`git diff c8c2662 14c392c -- mobile codemagic.yaml` is empty. This documentation
follow-up changes only the two records and retains the reviewed application/YAML.
The reviewed immutable SHA remains a valid build candidate; if Codemagic selects
the later documentation-only branch tip, record its exact clone SHA and verify
application/YAML equality to this candidate before accepting the artifact.

### Actual Apple build-number evidence

Authenticated read-only GET **2026-10-14 18:03:10 UTC**: received builds **2, 3, 4**
are **VALID**, **INTERNAL_ONLY**, **IOS / 1.0.0**. App-scoped uploads GET **200**
at **18:04:03 UTC** returned **1 FAILED / 2 COMPLETE / 3 COMPLETE / 4 COMPLETE**,
all IOS/1.0.0, no pending upload and no 5 in either bounded complete list.
**Next unused candidate: 1.0.0 (5)**. No number was reserved or variable changed.
Recheck builds/uploads immediately before the separately authorized start; stop
on collision or competing upload. Existing protected ASC key reused; no key,
identifier credential or JWT printed/copied. An initial collection-level uploads
GET returned 403; the documented app-scoped
[Apple uploads route](https://developer.apple.com/documentation/appstoreconnectapi/get-v1-apps-_id_-builduploads)
returned 200. The inspection assertion initially treated Apple's nested state
object as a scalar; complete readback establishes the states above. Neither
inspection issue changed provider state or requires new permissions.

### Signed workflow readiness

Workflow **ios-testflight-internal**, Codemagic existing Shelf application,
**mac_mini_m2**, Flutter **3.47.2**, working directory **mobile**, release IPA,
bundle **services.shelf.app**, Team **YSLWJQDH8B**, version **1.0.0** explicitly
supplied by the build command. Shared `pubspec.yaml` remains **1.0.4+13**; workflow
build-name/build-number overrides determine this iOS artifact. No Android version
change needed. Existing integration **shelf_app_store_connect**, certificate
**shelf_ios_distribution**, profile **shelf_ios_app_store**, groups
**shelf_ios_foundation / shelf_ios_testflight** retained; no automatic trigger.

YAML parse, every signed-workflow shell script's `bash -n`, inline Python syntax,
plist purpose strings, native bundle/team/IAP capability and explicit signing/
artifact guards passed without executing build steps. Internal-only export
**testFlightInternalTestingOnly=true**, **manageAppVersionAndBuildNumber=false**,
strict codesign/team/bundle/version checks, `ditto` preservation and explicit
`$CM_BUILD_DIR` artifact/evidence paths intact. Publisher uses existing integration,
**submit_to_testflight=false / submit_to_app_store=false**: the signed manual
workflow uploads the IPA but does not automatically assign testers or submit
public review. Existing successful build-4 signing/export evidence reused;
no new macOS compile, signing credential introspection or binary validation claimed.

RevenueCat dependency remains locked **purchases_flutter 10.13.2**, enforced
lockfile resolution; native iOS selects only **appl_** server-supplied SDK config,
original numeric Shelf reader identity and Book 3 allowlist. No purchase key goes
in a build variable. **SHELF_TEST_MODE=false** and
**SHELF_INTERNAL_TEST_PURCHASES=false** remain correct: native iOS chooses sandbox
while server configuration/consent independently controls availability. Owner
already-owned Book 3 test action, explicit owned-book test consent, server echo,
independent Apple confirmation and normal duplicate protection are present.
Server **SHELF_APPLE_OWNED_BOOK3_TEST_ENABLED=false** and
**SHELF_APPLE_SANDBOX_ENABLED=false** remain OFF; no client override opens them.

Minimal backend recheck passed: release-state still records staging/production
**c8c266221d972d409ffa947ca87397293c69c004**, maintenance absent, runtime Apple
sandbox/owned-test/real-checkout/sign-in/relay false, Apple proof disabled,
production HTTPS app-config healthy and iOS purchase config false with SDK key
null. Reused completed 56 PHP tests/511 assertions, 15 Flutter tests, staging
isolation, ownership/accounting/history, webhook and verified backup evidence.
No broad test, deployment, migration, backup drill or provider audit repeated.

### Exact single-run settings and owner steps — later authorization only

1. Before the authorized run, Codex rechecks the two bounded Apple build/upload
   lists and exact branch application/YAML. In **Codemagic → existing Shelf app**,
   confirm no other queued/running signed build or upload. Existing access does
   not independently expose Codemagic queue/saved variable values here; no token
   search, credential creation or duplicate pipeline is required.
2. **Team settings → Environment variables → existing shelf_ios_testflight**:
   set only **SHELF_IOS_BUILD_NUMBER=5**. Retain existing
   **SHELF_API_BASE_URL=https://shelf.services/api/** in **shelf_ios_foundation**,
   existing Google iOS configuration, groups/integration/signing and all other
   variables. This task has not changed any dashboard value.
3. **Shelf app → Start new build**: select **ios/foundation**, workflow
   **ios-testflight-internal**, reviewed commit above where commit selection is
   available; otherwise use only its verified documentation-only descendant tip.
   Start **once**, under the next explicit build/upload authorization. Do not
   choose unsigned/diagnostic workflows, main/default branch or a PR target.
4. After that later upload, Codex inspects exact clone SHA/IPA/signing/version **5**
   and processing/export-compliance status before any acceptance claim. Assignment
   remains only to existing **Shelf Owner** under the applicable authorization;
   no tester/group/membership changes or external/public distribution. Do not
   restart merely because Apple processing is pending or the test button is absent.

### Pending phone procedure

Once the new build is received, cleared and assigned, install/update via existing
TestFlight without uninstalling or clearing account/download data; confirm **1.0.0
(5)** and basic launch. With gates OFF, Book 3 remains readable through existing
Play ownership and the Apple test action remains hidden. This establishes only
installation/launch, not purchase acceptance. Do not request an Apple sign-in or
relay test, or make a purchase, at this stage.

Later separate activation/transaction authorization must verify tracking OFF /
original App User ID and open both Apple gates with the existing backup/checkpoint
procedure. Use existing owner reader email/password, refresh Library, open Book 3,
read sample and agree through **Test Apple purchase — no charge**. Under that
window's exact authorization, cancellation must preserve Play access; at most one
new verified sandbox purchase must show independent Apple server confirmation,
Test-only immutable history and unchanged Play ownership. Product unavailable or
missing confirmation: stop and report, do not repeat buying/change providers.
Actual notification delivery versus REST, restore/refund/reversal and iPhone
rendering remain pending separate evidence; second-phone restore stays deferred.

No preparation blocker found. Cloud queue/variable checks and final build-number
recheck are start-time prerequisites, not claims of completion. Product title
metadata/genuine review image/public readiness stay open. France excluded/ANSSI
cancelled, Android/UI pause, pricing-sync, second-phone restore, Apple auth/relay,
small improvements and joint Android/iOS public-launch hold all preserved.

**Exactly one next action:** authorize one manual **ios-testflight-internal**
build/upload **1.0.0 (5)** from the verified candidate/identical documentation-only
branch tip, after the immediate collision/queue recheck, all purchase gates OFF.

## Owner Apple Book 3 exception — disabled backend rollout passed, 14 October 2026

Owner authorized the smallest staging-first, backed-up backend rollout, keeping
all purchase activation gates OFF. **Staging and production now run exact source
c8c266221d972d409ffa947ca87397293c69c004.** Release-state confirms both copies;
prior deployed/rollback source is **560ef50ac35aa2db78492b1c7bfb92ee72a69f76**.
No build, upload, transaction, refund, provider setting or public release occurred.

### Backups and preservation

- Fresh production preflight backup:
  `/home/shelf/backups/shelf/20261014-175255/`.
- Private preservation checkpoint:
  `/home/shelf/backups/shelf/20261014-175255/owner-apple-rollout-checkpoint/`.
  Retains the existing cPanel handler and restricted verification fingerprints;
  no secret values or reader data printed or committed.
- Staging backup before deploying the exact source:
  `/home/shelf/backups/shelf-staging/20261014-175413/`.
- Fresh production promotion backup:
  `/home/shelf/backups/shelf/20261014-175454/`.
  Includes `promotion-notes.txt` with prior code and preservation-aware rollback.

All three backups passed SQL completion, ZIP integrity and SHA-256 verification.
This is backup-artifact verification, not a new restore drill. Production's sole
unrelated modification was the recognized cPanel `public/.htaccess` handler;
workflow preservation and final checkpoint comparison passed. Both .env files and
existing private Apple webhook authorization/metadata files are byte-unchanged.
Google configuration remains unchanged with the complete production .env;
no provider writes or repeated provider setup checks were made.

### Staging and production verification

Reused **56 passed PHP tests / 511 assertions and 15 passed Flutter tests** from
c8c2662; no suite or Flutter build repeated. Necessary rollout-validator source
**c9f6728** updates the historical test-reuse gate to this exact validated source,
checks deployed purchase-path bytes, no pending migrations and both Apple gates
OFF, and directly exercises closed consent paths using an unsaved synthetic
reader. Python and embedded PHP syntax plus diff checks passed. This operator
fix runs from the isolated checkout; it is **not a different deployed revision**.

Staging passed at **2026-10-14 17:54:28 UTC**: all compared purchase source/test
paths match c8c2662; independent shelf_staging database/storage, private access
key, noindex/TEST COPY, logged mail/null queue, absent production Apple purchase
credentials, locked paid text, private files denied, accounts/Library protected,
HTTPS health and disabled webhook guards passed. Check evidence is
`/home/shelf/staging-runtime/checks-c8c266221d972d409ffa947ca87397293c69c004.log`,
SHA-256 **0352122603f19e878e3700f1ed2576eb0442ce31195b25d17900c4baae34eefc**.
Promotion required the same staged commit and unchanged evidence hash.

Production promotion passed the existing workflow: backup before maintenance,
fast-forward to the identical source, dependencies/autoload/cache/asset handling,
purchase/accounting/owner-share health checks, history comparison and maintenance
lifted. No new schema migration or data migration; both copies have no pending
migrations and retain the already-applied Apple store-scope schema. Final direct
HTTPS production catalogue/locked-poem checks confirm non-owner paid body remains
null. iOS purchase config returns enabled/sandbox/production false and no SDK key;
Apple webhook is guarded **503 production / 403 staging** before processing.
The new owned-book3 exception and existing Apple sandbox gates are runtime false;
real checkout, Apple sign-in/relay and Play sync remain OFF. Direct consent probes
deny a non-owner, another book and ordinary Apple consent (503 while disabled),
and reject the exception flag on a Google path (422). Owner-only restrictions
with gates configured are covered by the reused passed scoped tests; no gate was
opened even temporarily on either server.

Final private before/after fingerprints match all **11** reader/account/token,
purchase/event/ledger/entitlement/consent/agreement/accounting tables. Existing
owner Play book-3 purchase and valid access independently confirmed, exception
permission false, no Apple purchase history. Test income remains excluded from
ordinary revenue/author totals; all six existing D10 agreements pass. Public
handler, env files, webhook files and packaged APK/AAB/IPA inventory unchanged.
Tracked Flutter source follows the repository revision, but no compiled mobile
artifact was built, uploaded or added to production by this backend rollout.

### Rollback and remaining work

Prior staging release remains at
`/home/shelf/apps/shelf-staging-releases/560ef50ac35aa2db78492b1c7bfb92ee72a69f76/`.
Any separately authorized code rollback uses the prior production revision with
both Apple gates closed, preserving the checkpointed cPanel handler, private
configuration, schema and all later reader/payment history. No blanket SQL
restore or migration-down; rollback notes require considering later activity.
No rollback was needed or executed.

**A new internal TestFlight build is still required.** Installed **1.0.0 (4)**
lacks the owner test action/client confirmation changes. Build preparation must
verify exact branch application/workflow source and the next unused Apple build
number; none reserved or assumed here. Actual iPhone rendering, product retrieval,
Apple sandbox purchase/notification delivery, restore/refund/reversal acceptance
remain pending. Tracking OFF / original App User ID confirmation remains required
before any later separately authorized activation/transaction window. Both Apple
switches need that later explicit authorization; this rollout does not grant it.

France remains excluded and ANSSI work cancelled. Product title discrepancy,
MISSING_METADATA/genuine review image and public-release requirements remain open.
Apple authentication/relay checks, Android/UI pause, pricing sync, second-phone
restore, small improvements and joint Android/iOS public-launch hold preserved.

**Exactly one next action:** prepare one new internal TestFlight build from the
validated application source, verifying the next unused build number and existing
signing/workflow settings, with all purchase gates OFF and without starting it.

## Recovered owner test for an already-owned Book 3 — completed 14 October 2026

Resumed interrupted work at **29d9f32** in `/home/shelf/tmp/shelf-ios-foundation`,
branch **ios/foundation**. Actual recovered HEAD and remote tip were
**29d9f3250fdcd3030907c5ae7d65643e6915b92e**; no later local commits existed.
Inspected the complete pending diff and preserved all **11 modified files plus
one untracked service**; no reset, clean, discarded work or recreated implementation. This authorization covers isolated source, focused
verification, records and branch-only commit/push. **NOT DEPLOYED OR ACTIVATED.**
The historical ownership blocker remains present in deployed 560ef50 and installed
TestFlight **1.0.0 (4)** until separately authorized rollout and a new artifact.

### Completed safeguards

- New **SHELF_APPLE_OWNED_BOOK3_TEST_ENABLED** defaults false. It additionally
  requires configured Apple sandbox (existing SHELF_APPLE_SANDBOX_ENABLED),
  staging inactive and SHELF_REAL_PURCHASES_ENABLED false. Both Apple controls
  remain OFF; no .env/provider setting changed in this task.
- Eligibility requires the verified, buying-allowed exact-owner-email reader,
  Published **3 / shelf_book_3**, positive price, current active entitlement and
  an unrefunded/unrevoked **PLAY_STORE sale** for that same reader/book. An
  account without that sale or with any recorded Apple book-3 purchase cannot
  use the exception. No alias/relay/new account or complimentary grant substitutes.
- Library returns authenticated `apple_owned_book3_test_allowed` and a separate
  `apple_sandbox_book3_confirmed` only after existing reconciliation succeeds.
  Book details adds a 52-pixel LTR **Test Apple purchase — no charge** action only
  for native iOS with current server permission, owned book3 and sandbox checkout.
  Normal Read book/sample and duplicate-purchase paths remain intact. Permission
  is not persisted offline and clears on identity change; failed refresh blocks it.
- The test screen explains existing Play ownership, preserves source title RTL,
  requires the existing sample/all-sales-final agreement, and identifies sandbox/
  no charge. Client sends explicit `owned_book_sandbox_test=true` with APP_STORE/
  sandbox consent and requires the server to echo authorization before SDK buy.
  The server locks reader/book, rechecks eligibility, appends distinct test consent
  wording, and rejects that flag on the Play consent path. Normal owned-book
  consent still returns 409; other books/nonowners/real checkout are rejected.
- Before recording a new overlapping Apple purchase, server requires that same
  test consent **at or before Apple purchase time and no more than 15 minutes
  before it** (existing consent timestamps have second precision). Older Apple
  proof preceding consent and stale consent fail closed. Existing independent V2
  app/product/original-reader/transaction/millisecond-time/permanent-entitlement/
  APP_STORE/SANDBOX checks are retained. SDK success or existing Play access can
  never establish Apple confirmation. A failed confirmation refresh cannot reuse
  a cached confirmation flag. Confirmation retries never initiate another buy.
- Only a proven Apple transaction appends Apple purchase/event/Test journal rows;
  it does not rewrite Play purchase/ledger history. Existing union-of-valid-store
  access remains: cancelling/pending/failing Apple testing leaves Play ownership
  and download intact; Apple refund/reversal never removes valid Play access.
  Duplicate event delivery remains idempotent; a second new overlapping Apple
  transaction is rejected. Sandbox sale/refund/reversal rows remain excluded from
  real revenue and author totals, without new royalty calculation or payout.

### Verification and remaining limits

Focused wrapper invocation:
`SHELF_PHP_TEST_FILTER='AppleSandboxPurchasesTest|BookPurchasesTest|AccountingTest' SHELF_MOBILE_TEST_SCOPE=ios-purchases scripts/run_tests.sh --mobile`.
All tests use synthetic identities/provider responses and disposable SQLite/
Flutter storage under `/home/shelf/tmp`; no live request, purchase or data change.
PHP checks cover both gates, staging, exact owner/verified/unblocked account,
book/store/mode scope, unchanged normal duplicate guard, active Play-sale eligibility,
fresh consent/proof, a second Apple transaction, immutable Play rows, Test-only
income and independent Library Apple confirmation/refund behavior. Flutter checks
cover controller guards, server consent echo, separate proof versus Play access,
account reset, cancellation/pending/failure/download preservation and the actual
Book details → test screen route, agreement, price, confirmation and disabled
repeat button. Final wrapper **passed: 56 PHP tests / 511 assertions, 14 Flutter
iOS purchase tests and 1 staging identity test**. Focused analysis passed with
21 style infos, no errors or warnings; `git diff --check` passed.
The first recovery run exposed widget platform-override cleanup and a stale Play
test expecting 422 for an Apple event; the existing default-OFF Apple guard
returns 503. Restored the override before widget invariants and corrected only
that assertion, then reran the complete focused scope successfully. No live
failure occurred. No dependency
upgrade, broad suite or Android artifact. Source UX checked by widget routing/
layout with 52-pixel actions; real iPhone rendering/StoreKit acceptance is pending.

A **new internal TestFlight build is required**: book-detail routing, controller
and purchase screen are bundled Flutter changes absent from installed build 4.
This task starts/reserves no build, upload or build number. Recheck actual unused
number and exact branch source under later build authorization. No native signing,
SDK/dependency, key, Apple/RevenueCat product or workflow setting change needed.
Real StoreKit retrieval, purchase proof shapes, notification delivery and phone
acceptance remain unverified; tracking OFF / original App User ID dashboard
confirmation is still required before later activation. Product title discrepancy,
MISSING_METADATA/genuine review image and public-release requirements remain open.

### Exact recovered files and completion

Recovered existing changes in:

- `app/Http/Controllers/LibraryController.php`
- `app/Services/Purchases/AppleSandboxPurchases.php`
- `app/Services/Purchases/OwnerAppleSandboxTest.php` (untracked at recovery)
- `config/purchases.php`
- `mobile/lib/purchases/library_controller.dart`
- `mobile/lib/purchases/purchase_screen.dart`
- `mobile/lib/screens/collection_detail_screen.dart`
- `mobile/test/ios_purchases_test.dart`
- `tests/Feature/AppleSandboxPurchasesTest.php`
- `scripts/run_tests.sh`
- `docs/MASTER_RECORD.md`
- `docs/IOS_PURCHASES.md`

The interrupted source implementation was present; final passing verification and
accurate recovery/test records were unfinished. Completed those, corrected the
widget fixture and added the narrowly scoped existing-guard assertion correction
in `tests/Feature/BookPurchasesTest.php`. All 13 final files belong to this scope.
No schema, deployment, provider setting, gate, build, upload or purchase changed.
The validated branch-only commit is titled **Allow owner Book 3 Apple sandbox test
without changing Play ownership**; deployment remains 560ef50, not this source.

### Rollout and rollback — plans only, no deployment in this task

1. Under separate rollout authorization, stage this exact verified commit with
   both Apple gates and real checkout OFF; staging must force Apple off. Reuse the
   existing already-applied store-scope schema: **no new migration**. Verify fresh
   backups and promote the same tested commit through docs/STAGING.md, preserving
   private config, Google integration, ownership and all history. Record exact
   promotion evidence; do not assume current deployment contains these changes.
2. Separately build/install the updated internal iOS artifact with all gates OFF.
   Later explicit activation/transaction approval must include **both** Apple
   sandbox and owned-book3-test switches, fresh verified backup/encrypted .env
   checkpoint and exact runtime owner/mapping checks. The older one-switch plan
   is insufficient for this exception. Keep real/sign-in/relay/sync OFF. Refresh
   Library to obtain current owner permission before opening only book3 test UI.
3. To close an approved window, atomically set **SHELF_APPLE_OWNED_BOOK3_TEST_ENABLED=false**
   and **SHELF_APPLE_SANDBOX_ENABLED=false**, retaining permissions and unrelated
   settings, then clear config cache and verify disabled config/guards. Preserve
   all Play/Apple records, keys, webhooks, schema and downloads; no SQL restoration
   or migration down. Closing gates is not a refund. As already documented,
   disabled Apple verification cannot issue fresh offline leases for recorded
   Apple history; any pending reconciliation needs a separately approved window.
   The Play purchase remains valid and is not revoked by this exception.
   A later code rollback uses the recorded previous deployed revision with gates
   closed and schema/history retained, never blanket restoration over reader data.

**Exactly one next action:** authorize staging-first, backed-up promotion of this
verified backend source with both Apple controls and real checkout OFF.
France excluded/ANSSI cancelled, deferred Apple authentication/relay checks,
Android/UI pause, pricing-sync and second-phone-restore deferrals, small-improvement
pause and joint Android/iOS public-launch hold all remain unchanged.

## Build 4 installed; owner-only Apple test preflight blocked by existing ownership — 13 October 2026

Continued **8de0fc9**. Owner reports **TestFlight 1.0.0 (4) installed on own iPhone
and launches correctly**. Record **basic installation/launch acceptance only**;
not product retrieval, purchase, restoration, notification delivery or sign-in
acceptance. Build/compliance/group evidence reused without repeat checks.

Read-only preflight **2026-10-13, product check 08:10:41 UTC**: production source
**560ef50ac35aa2db78492b1c7bfb92ee72a69f76**; staging release-state records the
same source and deployed LibraryController/ApplePurchaseProof/AppleSandboxPurchases/
purchases config are byte-identical. Staging is a deployed release, not a git
checkout: initial git HEAD command there failed before mutation; used its existing
release-state and file comparisons, no repair or deployment. Production/staging
runtime: Apple gate/proof OFF, real checkout OFF, Apple sign-in/relay OFF, sync OFF.
Staging database isolated, private Apple key/auth configuration absent. Both HTTPS
iOS config GETs advertise enabled/sandbox/production false and SDK key null.

Production private mapping/key files present and correctly restricted; Apple app,
internal product/project match recorded IDs; separate webhook auth matches private
file. Existing RevenueCat GETs 200 verify Apple app **app8ef018b95b / services.shelf.app**,
non-consumable **prod0d084e901f / shelf_book_3**, entitlement **entlb3d491e78e**
attached to exactly existing Apple product and Google **prod03ffc5153f**.
Apple webhook **whintgr769cb03ff2** matches app, sandbox and separate URL;
Google **whintgr75a4892970 / appf83cd58c5c / Both(null)** remains configured.
Provider API does not expose auth header; server/file match verified privately,
not provider auth readback. No webhook test, delivery claim or settings write.
Apple product GET 200 remains **NON_CONSUMABLE / MISSING_METADATA**; availability
GET verifies **174, FRA absent**. Existing title discrepancy/review screenshot
remain pending, no metadata/price/territory edits. Apple purchase key's prior
owner-reported Valid credentials reused, not a new receipt-validation claim.

**Owner identity and blocker:** exactly one owner admin and one exact-email reader;
reader verified, buying allowed and deployed owner predicate true. Existing owner
already **owns book 3**, and existing **PLAY_STORE book-3 history** confirmed
privately. No IDs/emails/history rewritten. Book3 Published, canonical product and
positive price present; no Apple purchases recorded. Client purchase method exits
on owns(book.id); deployed consent endpoint returns **409** on existing book
access. Therefore the requested fresh Apple purchase **cannot run unchanged for
this existing reader**, even with gate opened. Do not remove/refund/expire the Play
entitlement, use another/anonymous/relay reader, bypass consent or make a transaction.

Read-only PHP process evaluated readiness with Apple gate true **in CLI memory
only**, never .env/cache/provider/database: configured=true, real remains false,
non-owner rejected, buying-blocked owner rejected, existing owner accepted. No
reader saved or consent/purchase invoked. The normal app calls server consent
before StoreKit; only owner and book3 can receive accepted consent, server proof
requires APP_STORE/SANDBOX numeric original owner, mapped permanent entitlement
and exact transaction/time. Non-owner purchase/access grants remain denied; staging
cannot enable Apple purchasing. **Caveat:** iOS config readiness is global/public,
not reader-specific UI visibility; safety is enforced at consent/proof, not by
hiding the SDK key. No claim that nonowners would never see an enabled control.
No changes to Google flow/other entitlements needed or made.

Current official [Apple TestFlight purchase guidance](https://developer.apple.com/help/app-store-connect/test-a-beta-version/testing-subscriptions-and-in-app-purchases-in-testflight/)
and [TN3186](https://developer.apple.com/documentation/technotes/tn3186-troubleshooting-in-app-purchases-availability-in-the-sandbox)
confirm TestFlight uses sandbox and IAP review submission is not needed for sandbox.
[RevenueCat TestFlight guidance](https://www.revenuecat.com/docs/test-and-launch/sandbox/apple-app-store)
confirms production Apple Accounts with sandbox purchases; no new sandbox account
or device sign-out required for this minimal TestFlight case. Actual device
storefront/product fetch remains phone evidence, not API-provable. Restore behavior/
server-notification purchase tracking aren't exposed by the public app API; prior
tracking OFF / Keep original App User ID requirement remains unverified and must
be resolved before a later activation window. Don't request it now while ownership
already blocks the intended purchase. Do not erase RC/customer/Apple history.

**Exact deferred activation/verification/rollback** and smallest one-purchase scope
recorded at start of IOS_PURCHASES.md. Activation sequence is blocked until the
existing-owner ownership conflict is resolved by a separately authorized scoped
implementation, preserving Play ownership; a later signed artifact may be needed
if that implementation changes the client. No new build authorized now. Fresh
backup and private .env checkpoint before later one-field gate opening, normal
server owner/other-book denials, one cancelled sheet then at most one authorized
book3 sandbox purchase, independent proof/access/Test accounting verification,
and one-field closure without SQL/history rollback. No second-phone/restore/refund
matrix requested in this first window.

**Exactly one next action:** authorize focused owner-only Apple sandbox test support
for **already-owned book3**, preserving the existing Play entitlement and all gates
OFF during preparation. Do not activate the current gate as a workaround.
No code changes necessary merely to discover/document this blocker; no complete
test-suite rerun. Documentation-only commit/push to ios/foundation. Real checkout,
Apple sandbox/sign-in/relay remain OFF; France exclusion/ANSSI cancellation,
Android/UI pause, Apple authentication/relay, pricing-sync/second-phone restore
deferrals and joint Android/iOS public-launch hold preserved.


## Build 4 assigned only to existing Shelf Owner internal group — 13 October 2026

Continued **41b1388** under explicit owner authorization. Reused compliance/
purchase evidence; no repeated audit/test/build. Authenticated build GET **200**
on 13 October UTC confirms **1.0.0 (4) / VALID / INTERNAL_ONLY**, encryption flag
false; buildBetaDetail initially **READY_FOR_BETA_TESTING / NOT_APPLICABLE**.

Build-side betaGroups GET returned **403 FORBIDDEN_ERROR**, a route permission
limitation, not proof no assignment exists. Top-level betaGroups scoped to Shelf
app **6821471140** GET **200** returned exactly one group: **Shelf Owner**,
**1511a217-4f89-4dc7-8b8d-4679ce8a6226**, isInternalGroup=true,
hasAccessToAllBuilds=false; no external group returned. Existing group and tester
GETs **200**, exactly **one tester**, name matches recorded Ajmal Aand, state
**INSTALLED**, inviteType EMAIL. This is the existing owner setup that already
received builds 2/3, not a new invite. Its Apple email differs from the Shelf admin/
reader email, privately compared; identity preserved, no linking or replacement
assumed. No email/PII printed or committed. Group builds GET **200** initially
returned only **2 and 3**; build 4 genuinely absent on that readable relationship.

Private before-state group/tester/build snapshot 0600 outside git:
/home/shelf/tmp/shelf-build4-group-before-20261013.json.
[Apple's supported group-build endpoint](https://developer.apple.com/documentation/appstoreconnectapi/post-v1-betagroups-_id_-relationships-builds)
used: **POST /v1/betaGroups/1511a217-4f89-4dc7-8b8d-4679ce8a6226/relationships/builds**,
payload contains only build **60002f22-847e-4d06-9b7c-623eee9aea70**; **204**.
No new group/user/invitation, group configuration or external-test relationship.

**Readback 2026-10-13 07:58:42 UTC:** group builds GET **200** now includes exactly
**4, 3, 2**; tester list and group settings exactly equal before/after.
BuildBetaDetail GET **200** now **IN_BETA_TESTING** (not inferred from POST alone).
Thus build 4 is available through the existing owner-only internal group; actual
installation remains a phone step, not claimed. The earlier build-side 403 did
not prevent inspection/assignment via authorized group-side API endpoints. No
credential request or manual App Store Connect assignment is necessary.

**One owner action:** on the iPhone, open **TestFlight → Shelf → Update** (or
Install), selecting **1.0.0 (4)**; if a newer build is not selected automatically,
open **Previous Builds → 1.0.0 → build 4** and install it. Confirm the installed
version/build; no purchase or Apple sign-in test is requested. Existing app/account
and content retained; do not uninstall/clear data. Gate activation needs later
separate authorization. See updated IOS_PURCHASES.md installation section.

No additional build/upload, product/territory write, transaction, external testing,
public release, migration, deployment or .env/gate change. Real checkout and Apple
sandbox/sign-in/relay gates remain OFF. France exclusion/ANSSI cancellation,
Android/UI pause, pricing-sync/second-phone-restore and Apple authentication/relay
deferrals, joint Android/iOS release hold retained. Documentation-only scoped
commit/push on ios/foundation, unrelated work preserved.


## Build 4 export compliance cleared by Apple API — 13 October 2026

Continued **55a64da** under explicit owner authorization for the minimum build-4
compliance write. Existing encryption inventory/provider evidence reused; no repeat
audit/tests. France excluded; ANSSI enquiry, filing and email remain **CANCELLED**.

Pre-write authenticated GETs: build **60002f22-847e-4d06-9b7c-623eee9aea70**
confirmed **1.0.0 (4), IOS, VALID, INTERNAL_ONLY**, usesNonExemptEncryption null,
no appEncryptionDeclaration; internalBuildState MISSING_EXPORT_COMPLIANCE.
Full shelf_book_3 availability GET confirms **174 territories, FRA absent**.
AppAvailabilityV2 still **404 NOT_FOUND / unconfigured**; no list created, activated
or claimed to have had France removed. Future App Store availability must exclude
France. That unconfigured state did not prevent Apple's supported build flag update.

Current official [Apple documentation table](https://developer.apple.com/help/app-store-connect/reference/app-information/export-compliance-documentation-for-encryption)
and [encryption property guidance](https://developer.apple.com/documentation/security/complying-with-encryption-export-regulations)
checked again. Shelf has standard **AES-256-GCM via Dart cryptography**, Keychain
and HTTPS, not no encryption or OS-only encryption. For standard non-OS algorithms,
French documentation is conditional on France distribution; the proprietary CCATS
case does not match the verified inventory. With France excluded, supported
no-document/exempt-use declaration is **usesNonExemptEncryption=false**. It does
not assert absent encryption. Earlier builds' flags were not used as justification.
Current official BuildUpdateRequest schema supports this single field.

Private before-state saved outside git 0600:
/home/shelf/tmp/shelf-build4-compliance-before-20261013.json. Only request:
**PATCH /v1/builds/60002f22-847e-4d06-9b7c-623eee9aea70**, data type builds,
attributes **usesNonExemptEncryption=false**; HTTP **200**. No relationship,
expiry, tester, notification or app-wide declaration/configuration field sent.
No credential/token/key printed or committed, existing access reused.

**Readback verified 2026-10-13 07:52:31 UTC:** usesNonExemptEncryption **false**;
processing **VALID**, audience **INTERNAL_ONLY**; all other build attributes
exactly equal before/after. appEncryptionDeclaration GET **200 / null**, consistent
with the no-document route. buildBetaDetail GET **200** returns
**internalBuildState=READY_FOR_BETA_TESTING**, externalBuildState=NOT_APPLICABLE,
autoNotifyEnabled=true (existing value, not changed). Thus **MISSING_EXPORT_COMPLIANCE
actually cleared**, not merely PATCH success. No associated official document or
regulatory certification claimed. No build, upload or installation inferred from
that status; prior owner-reported Codemagic evidence stays distinct.

**One next action:** authorize assignment of this existing internal-only build to
the existing **Shelf Owner** group for installation; no new tester/group or purchase
activation follows automatically. Assignment and signed-phone purchase acceptance
remain pending; prior group-read 403 limitation retained, not re-audited here.
Backend/webhook evidence unchanged. Public app availability, review metadata/title
correction/genuine screenshot and joint release readiness remain separate work.

No rebuild/upload, emails/ANSSI, transactions, release, tester assignment, .env,
migration, deployment or gate change. Real checkout, Apple sandbox and Apple
sign-in/relay gates remain OFF. Deferred Apple authentication/relay checks,
Android/UI pause, pricing-sync and second-phone restore deferrals, joint Android/
iOS public-release hold preserved. Scoped documentation-only commit/push on
ios/foundation; unrelated work untouched. No source/tests changed or broad rerun.


## France removed from saved Apple IAP availability — 13 October 2026

Owner explicitly supersedes include-France: **exclude France from Shelf App Store
distribution** and relevant Apple IAP availability. ANSSI enquiry/filing/email is
**CANCELLED**, no owner address/phone request or French paperwork work to resume.
This decision authorizes the availability change, not a compliance submission.

Authenticated existing private ASC API inspection found app
**6821471140 / services.shelf.app** appAvailabilityV2 **404 NOT_FOUND**, both before
and after the operation. App availability is unconfigured; **no app-wide list was
created or modified**, no other-country list invented, no claim France was removed
from that unconfigured list. Future app availability setup must exclude FRA and
preserve the owner's eventual explicitly selected remaining countries.

Bounded app IAP list GET 200 returned only **shelf_book_3 / 6822080809**.
Its availability GET and full territory list GET 200 initially returned **175**,
including FRA, availableInNewTerritories **true**. Private before-state/rollback
payload and product/price evidence saved 0600 outside git at
/home/shelf/tmp/shelf-france-availability-before-20261013.json. Concurrent-state
recheck passed before write. Using current official
[Apple IAP availability schema](https://developer.apple.com/documentation/appstoreconnectapi/post-v1-inapppurchaseavailabilities),
POST /v1/inAppPurchaseAvailabilities **201**, preserving the exact existing set
minus **FRA**, original future-territory value and product relationship.

**Readback verified 2026-10-13 07:11:55 UTC:** exactly **174** territories, FRA
absent, all other territory IDs unchanged, availableInNewTerritories still true.
Product attributes, price-schedule resource and every manual/automatic price
record unchanged by exact comparison. France's price record was retained, not
sold/changed/deleted; price availability and purchase availability are separate.
No Google availability/webhook or other provider settings touched. Rollback would
restore saved availability payload only under later owner authorization; do not
restore France automatically against the superseding instruction.

**Build-4 compliance reassessment:** reuse verified standard Dart AES-256-GCM
book/manifests protection plus platform Keychain/HTTPS; not OS-only and not no
cryptography. Current [Apple documentation table](https://developer.apple.com/help/app-store-connect/reference/app-information/export-compliance-documentation-for-encryption)
requires the French declaration for standard non-OS algorithms only if distributed
on the App Store in France; its CCATS row concerns proprietary/nonstandard
algorithms, which are not present in the verified inventory. With France excluded,
that French-document requirement no longer applies. Earlier build flags alone are
not justification. Future accurate questionnaire: **uses encryption Yes; standard
algorithms beyond OS Yes; proprietary/nonstandard No; France No**. Where Apple
asks the exempt-use question, no export-compliance document is required for this
recorded non-France standard-algorithm case; Apple's exempt/no-document use maps
to **usesNonExemptEncryption=false**, rather than asserting no encryption.

**Exact next step, not executed:** under separate compliance-write authorization,
set only build **60002f22-847e-4d06-9b7c-623eee9aea70 / 1.0.0 (4)**
usesNonExemptEncryption=false using Apple's build-modification API (or the truthful
questionnaire above if required). Read back build and buildBetaDetail until actual
internal state is confirmed; do not copy builds 2/3 or claim state changed now.
No French declaration/file, ANSSI contact, rebuild or repeat upload needed for that
step. App-wide future distribution must continue to exclude France.

Final GETs 200: build 4 **VALID / INTERNAL_ONLY / usesNonExemptEncryption=null**;
buildBetaDetail **MISSING_EXPORT_COMPLIANCE / NOT_APPLICABLE**. No declaration or
build PATCH in this task. No tester assignment, release, transaction, gate/.env
change, build/upload, migration or deployment. Checkout and Apple sandbox/sign-in/
relay gates OFF; pricing-sync/second-phone restore deferrals, deferred Apple
authentication/relay checks, Android/UI pause and joint public-launch hold retained.
Documentation-only commit/push to ios/foundation; unrelated work preserved.


## Owner removes France and stops French paperwork — 13 October 2026

Owner supersedes the preceding include-France instruction: **exclude France from
Shelf's planned Apple App Store availability**, rather than pursue France-specific
encryption paperwork. Stop ANSSI enquiries, declarations, exemption research and
requests for owner postal address/phone for that work. Existing drafts are retained
as cancelled historical preparation, not active next actions. Do not restart this
work without a new owner instruction. Owner preference: avoid country-specific
paperwork work when the owner instead elects to exclude that country.

The prior authenticated appAvailabilityV2 GET returned 404 NOT_FOUND; no saved
app-wide Apple territory list was established. This task records the approved
exclusion for the eventual availability setup, **not a claim that a saved provider
list was changed**. Preserve all other planned countries and existing IAP prices/
territories; no new bulk availability configuration or product change made.
Build 4's last verified compliance status is unchanged; no exemption or cleared
status inferred solely from the owner decision. No more French paperwork next step.
All purchasing/real checkout and Apple sign-in/relay gates OFF; pricing-sync,
second-phone restore and Apple authentication/relay deferrals, Android/UI pause
and joint public-launch hold preserved. Documentation-only record on ios/foundation.


## Encryption paperwork prepared; content-protection exemption investigated — 13 October 2026

Continued **7d8d5eb**. Current official Apple/ANSSI material read; completed
cryptographic inventory reused, no re-audit/build/provider query required.
Prepared [procedure](IOS_ENCRYPTION_COMPLIANCE.md), French technical/commercial
annex, current-notice form-field draft, unsigned accuracy attestation and French
ANSSI enquiry in docs/ios-compliance/. **All drafts, not filed or official receipts.**

Important qualification of the preceding entry: decree 2007-663 Annex I category
6 exempts limited content/software protection with user-inaccessible cryptographic
capability. Shelf's book/manifests layer has a credible factual rationale, but whole
app scope includes HTTPS/Keychain/SDK functions. No agency exemption or Apple
acceptance is inferred. Formal French filing is therefore **conditional**, not
necessarily required solely because France is included. Current ANSSI control
page points to this decree; official government/regulator copies read, with
2014 annex-copy date disclosed and direct consolidated Legifrance fetch blocked.

Current ANSSI portal notice **260331_Notice_V3.pdf** read using temporary PDF
reader (pdftotext unavailable; no product dependency change). Procedure available
online since 31 March 2026; signed applicant accuracy statement differs from the
portal deposit acknowledgement and later ANSSI accepted declaration attestation.
Mandatory technical/commercial materials drafted; official portal signature-model
access requires login, so field draft is not misrepresented as that downloaded form.
Apple table still asks for French declaration in standard non-OS/France case;
public Apple sources do not specify exact substitute for an exempt product or
that a mere draft/deposit receipt suffices. ANSSI scope response first, Apple
confirmation for exemption evidence if applicable; otherwise actual accepted
ANSSI declaration/form packet, followed by Apple's separate document review.

One practical next action: review prepared enquiry for a later authorized request
to **controle@ssi.gouv.fr**, ANSSI's official help contact on
https://demarche.numerique.gouv.fr/commencer/declaration-relative-a-un-moyen-de-cryptologie.
No email, declaration, enquiry or portal draft submitted/created this task.
Owner confirmed existing name/email and country privately; address/phone reply
contained placeholders, so actual postal address/phone remain missing. A single
consolidated request made; no personal contact values committed. No new owner
choice about France or cryptography requested. Existing Individual membership
reused, no invented company/Kbis, signature, filing number or approval.

Build 4 last verified **VALID / INTERNAL_ONLY / MISSING_EXPORT_COMPLIANCE** at
06:22:29 UTC remains the governing Apple evidence; no status change claimed.
No compliance answer/availability/encryption/source/gate change, rebuild/upload,
tester assignment, provider write, transaction, deployment or migration. Purchase,
real-checkout and Apple sign-in/relay gates OFF; pricing-sync/second-phone restore
and Apple authentication/relay deferrals, Android/UI pause and joint-launch hold
preserved. Documentation-only scoped commit/push on ios/foundation.


## Build 4 encryption assessment; French declaration required — 13 October 2026

Continued **dd05b12**, inspection/documentation only. Owner authorized an accurate
build-4 declaration if established, and now explicitly confirms **include France**
for Shelf's App Store availability. This records the owner decision; it does not
change any saved availability, price, territory, product or provider setting.

**Actual build-source inventory (2702fbb):**
mobile/lib/purchases/download_store.dart uses **AesGcm.with256bits()**, from locked
**cryptography 2.9.0**, to authenticated-encrypt purchased-book JSON and library
manifests. Per-reader random 256-bit keys are stored via FlutterSecureStorage
(iOS Keychain); each box has a 12-byte nonce and 16-byte authentication tag.
LibraryController writes books/manifests through this store. Media is stored in
private app files without this AES layer. HTTPS protects API/SDK transport;
account tokens use secure storage and audio cache filenames use SHA-256 hashing.
The app is a bookstore/reader, not a general encryption utility. No proprietary
or unpublished encryption algorithm found in Shelf source. Its Dart AES-GCM is
standard cryptography outside Apple's OS, so **OS-only/None of the above** is
inaccurate even though Keychain and transport also use platform security.
No source/Info.plist or dependency modification, rebuild or repeated purchase test.

**Earlier accepted-build evidence, independently read:** builds **2 and 3** have
usesNonExemptEncryption **false**, but both appEncryptionDeclaration relationships
are null. GET /v1/appEncryptionDeclarations?filter[app]=6821471140 returns **200 /
zero declarations**. Thus there is no approved saved declaration/document to
reuse and no recorded questionnaire basis establishing that those flags were
correct. Their successful processing/installation is not proof of classification.
An initial /v1/apps/{id}/appEncryptionDeclarations route returned 404 PATH_ERROR;
correct top-level filtered collection succeeded. AppAvailabilityV2 returns 404
NOT_FOUND, so saved app-wide France availability was not established by API;
the owner's explicit France instruction resolves planned distribution.

**Current official requirements and accurate questionnaire answers:**
- Uses encryption: **Yes** (including third-party library encryption).
- Encryption kind: **standard encryption algorithms instead of, or in addition
  to, Apple's OS encryption**. AES-256-GCM is implemented via the Dart library.
- Proprietary/non-standard encryption algorithms: **No**.
- Available/distributed on the App Store in France: **Yes**, owner-confirmed.
- Description: Shelf is a digital bookstore and reader. Standard AES-256-GCM
  encrypts downloaded purchased-book text and library manifests per reader;
  iOS Keychain stores keys/tokens and HTTPS protects network connections.

[Apple's current encryption-documentation table](https://developer.apple.com/help/app-store-connect/reference/app-information/export-compliance-documentation-for-encryption)
calls for a French encryption declaration for standard non-OS encryption when
App Store distribution includes France; the proprietary-algorithm CCATS row does
not match this inventory. [Apple's declaration procedure](https://developer.apple.com/help/app-store-connect/manage-app-information/determine-and-upload-app-encryption-documentation/)
requires uploading required documentation and Apple approval before attachment.
[BIS DRM guidance](https://www.bis.gov/learn-support/encryption-controls/cryptography-for-data-confidentiality)
is relevant to book-access protection, but is not evidence of a French filing,
Apple approval, or a blanket exemption for every linked library. No classification,
French filing or exemption is invented from it. Formal French declaration evidence
is absent; no accurate document can be reused or fabricated from earlier flags.

**Dashboard path when the required French declaration is available:** App Store
Connect → Apps → Shelf → TestFlight → iOS → **1.0.0 (4)** → **Manage** beside
Missing Compliance. Give the exact answers above. Upload the required French
cryptology declaration via **App Information → App Encryption Documentation → +**
if prompted; retain the accurate description and wait for Apple's document approval.
Then return to **build 4 only** and attach that approved declaration. Do not change
builds 2/3, select OS-only/no encryption, exclude France, assign testers or rebuild.
Required dashboard role: Account Holder, Admin or App Manager. Codex should automate
submission/attachment with existing access when accurate documentation is supplied
and authorization covers any necessary app-level document creation/upload; no new
credential or credential permission request is needed now.

**Final API verification, 2026-10-13 06:22:29 UTC:** build
60002f22-847e-4d06-9b7c-623eee9aea70 remains **VALID / INTERNAL_ONLY**,
usesNonExemptEncryption **null**; buildBetaDetail remains
**MISSING_EXPORT_COMPLIANCE / NOT_APPLICABLE**; declaration remains null (all
three GETs 200). No provider PATCH/POST attempted because required supporting
French documentation is absent, not because API write permission was proven
unavailable. Existing key's write permission remains untested. Blocker unresolved
honestly; no fabricated declaration and no legal exemption guessed.

**Next:** obtain the actual French encryption declaration required for the confirmed
France distribution, then submit/attach it to build 4 and verify internal readiness.
No remaining owner choice about France; formal supporting documentation is the
missing input. All purchasing/checkout/sign-in/relay gates OFF, existing pricing-sync
and second-phone-restore deferrals, deferred Apple authentication/relay checks,
Android/UI pause and joint public-launch hold preserved. No tester assignment,
notification/email, transaction, build/upload, deployment, migration, .env or
credential change. Documentation-only commit/push on ios/foundation.


## Internal purchase build 4 received; compliance pending — 13 October 2026

**Owner-supplied Codemagic evidence:** run **6acda60d546ce6236cee037c** finished
in **7m35s** from **2702fbbe2083ca8adf2a20ab977fe2e479af3c8b**, with Shelf.ipa
and evidence artifacts available. This is owner-reported build/source/artifact
success, not an independently authenticated Codemagic run or downloaded-artifact
verification. Existing preparation and completed tests reused unchanged.

**API-verified Apple results, 2026-10-13 06:08:14 UTC:** existing private App
Store Connect credentials used for authenticated GETs only, scoped to Shelf app
**6821471140 / services.shelf.app**. Build **60002f22-847e-4d06-9b7c-623eee9aea70**
is **1.0.0 (4), IOS**, independently confirmed by its preReleaseVersion relationship.
Upload record **COMPLETE**, errors/warnings/infos all empty; upload-record receipt
**2026-10-13 05:02:21 UTC**, build uploadedDate **05:03:20 UTC**. Processing
**VALID**, expired **false**, audience **INTERNAL_ONLY**. Thus upload receipt and
successful Apple processing are confirmed separately from owner-reported build
success; no public-release, installation or purchase acceptance is implied.

BuildBetaDetail GET 200 returns internalBuildState **MISSING_EXPORT_COMPLIANCE**,
externalBuildState **NOT_APPLICABLE**, autoNotifyEnabled **true**.
usesNonExemptEncryption is **null** and appEncryptionDeclaration GET 200 returns
**null**. Export compliance is a confirmed internal-testing blocker despite VALID
processing. No encryption exemption or declaration is assumed. Shelf's existing
inventory includes HTTPS and Dart cryptography for offline encryption; do not
answer that the app has no encryption or guess exemption from HTTPS alone.
BetaAppReviewSubmission GET 200 returns null, consistent with no review submission
record; it does not establish internal readiness. BetaGroups GET **403 /
FORBIDDEN_ERROR** means assignments cannot be independently inspected with this
key, rather than proving no assignments. No tester assignment or notification was
made by Codex; returned autoNotifyEnabled is a saved Apple value, not a task write.
[Apple beta-build export compliance guidance](https://developer.apple.com/help/app-store-connect/test-a-beta-version/provide-export-compliance-information-for-beta-builds/).

**Next single action:** authorize a focused build-4 export-compliance completion
batch: reuse the actual encryption inventory, determine truthful declaration
answers and use existing authorized API access where permitted to save them.
This task authorizes inspection/documentation only, so no declaration/provider
write was attempted. After compliance completion, recheck internalBuildState;
Shelf Owner assignment/install remain separate pending steps. No rebuild or
repeat upload is required by the reported processing/compliance state.

All purchasing/real checkout and Apple sign-in/relay gates remain OFF; no gate,
.env, provider configuration, product title/price/territory, tester, deployment,
migration, build, upload, email or transaction change in this inspection.
Apple title discrepancy/MISSING_METADATA and genuine review screenshot remain
pending. Pricing-sync and second-phone-restore deferrals, deferred Apple
authentication/relay acceptance, Android/UI pause and joint public-launch hold
are preserved. Documentation-only change on ios/foundation; production and
unrelated work untouched. No completed test/audit rerun.


## One internal Apple purchase build prepared — 12 October local / 13 October 2026 UTC

Continued **dabedf552bd4b47b13e91baccbfac053141a3652** in isolated ios/foundation.
Owner authorized preparation only: **no build/upload, activation, transaction,
email, product metadata or interface change**. Reused deployed **560ef50** and
configured Apple sandbox webhook **whintgr769cb03ff2** evidence without re-audit.

**Reviewed application/workflow source SHA: dabedf552bd4b47b13e91baccbfac053141a3652**;
workflow **ios-testflight-internal**, iOS version **1.0.0**, proposed build **4**.
This documentation-only follow-up changes no application/workflow source. Later
run must use the current prepared ios/foundation revision and record its exact
clone SHA from source-commit.txt before claiming receipt; verify any intervening
application/YAML changes against this reviewed source. No build number reserved.

Authenticated read-only Apple GET at **2026-10-13 00:18:42 UTC** of
/v1/builds scoped to app **6821471140**, including preReleaseVersion, returned
only **1.0.0 (2)** and **1.0.0 (3)**, both **VALID**, not expired. Additional
/v1/apps/6821471140/buildUploads GET 200 returned **1 FAILED, 2 COMPLETE,
3 COMPLETE**, all IOS / 1.0.0, with no other/pending upload records. All pages read
with bounded pagination, no unrelated app request, private credentials/JWTs
withheld. **4 is unused in both received-build and upload lists**, rather than
assumed from the owner's last installed build. Recheck both lists immediately
before a separately authorized run/upload and refuse collisions/concurrent runs.
The failed build-1 upload is historical, not a failure in this preparation.
[Apple build-upload list schema](https://developer.apple.com/documentation/appstoreconnectapi/get-v1-apps-_id_-builduploads).

**No confirmed build configuration omission.** Parsed signed YAML and every shell/
inline-Python script checked. Existing App Store Connect integration
shelf_app_store_connect, distribution certificate shelf_ios_distribution and
profile shelf_ios_app_store retained; explicit 1.0.0/build-number variable,
locked dependencies (purchases_flutter **10.13.2**), native In-App Purchase
capability, photo-purpose guards, internal-only export, signing verification,
permission-preserving ditto extraction and explicit $CM_BUILD_DIR artifact/evidence
paths present. No automatic trigger or App Store/public tester submission.
SHELF_TEST_MODE=false keeps production numeric reader identities/API;
SHELF_INTERNAL_TEST_PURCHASES=false remains valid because the iOS platform branch
selects **sandbox independently**. Apple appl_ SDK key comes from server
purchases/config?platform=ios after gate activation; no Codemagic purchase key,
secret, new Dart define or dependency update is required. Current gate OFF means
no SDK key/checkout advertised to the app. Completed purchase PHP/Flutter/native
source evidence reused; no completed suite/analyzer/native compile/build rerun.
Only focused read-only source/syntax/configuration checks run.

Private read-only production record verification found **one owner admin and one
exact-email-matching reader**, already verified and buying allowed; the deployed
ApplePurchaseProof owner predicate accepts that reader. No email/ID/token printed
or reader created/modified. Intended account is the owner's existing exact admin
email **through normal reader email/password sign-in**, not the admin UI, a plus
alias, guest, new account or Apple relay sign-in. Both client and server restrict
Apple checkout to **book ID 3 / shelf_book_3**, sandbox only; server independently
verifies numeric reader identity, permanent entitlement and exact transaction/time.
Book 3 remains Published with the expected product mapping. Actual runtime Apple
sandbox/real checkout and both Apple sign-in/relay gates confirmed OFF.

**Exact future activation/rollback procedure** is in IOS_PURCHASES.md. After the
new internal build is received/processed, assigned only to existing Shelf Owner
and installed, separately authorize one owner-only sandbox acceptance window.
Codex takes/validates a fresh backup and private encrypted configuration checkpoint,
checks existing mappings/webhook, exact owner eligibility, tracking OFF/original-ID
restore and all disabled controls, then changes **only production
SHELF_APPLE_SANDBOX_ENABLED=false → true** and runs **php artisan config:clear**.
Real checkout, sign-in/relay and sync stay OFF; staging stays OFF; no deployment,
new credentials or product/price/territory writes are needed. Verify public iOS
config enabled/sandbox=true, production=false and only shelf_book_3 before purchase.
Rollback changes only that gate **true → false**, clears config and verifies disabled
advertisement/key withholding/503 Apple guard. Preserve schema, integration,
reader/purchase/refund/accounting history and private credentials; no deletion or
blanket database rollback. Existing Apple history may need later explicitly
authorized reconciliation; gate closure cannot count as refund acceptance.

Remaining phone steps: install the verified new internal build, sign in with the
existing owner reader email/password, confirm free sample and consent, inspect
Apple sandbox sheet and execute only separately authorized book-3 tests. Server
confirmation/full text/media/download, cancelled/pending lock behavior,
same-account restore, account isolation, refund removal and reversal without
second sale/real income, actual Apple→RevenueCat→Shelf delivery and live proof
shapes all remain pending. Second-phone restore is not requested. Existing title
discrepancy/MISSING_METADATA and absent genuine review screenshot stay pending;
no product metadata changes or fabricated screenshot. Apple sign-in/relay checks
remain owner-deferred and are not a prerequisite to this email/password purchase
flow. DSA/public metadata/territory rights remain separate launch dependencies.

Live Codemagic variable values/run queue cannot be authenticated with currently
documented access; preserve the prior no-token/no-connector limitation, no
unauthorized secret search or credential request needed for preparation. Under
later build authorization, set **SHELF_IOS_BUILD_NUMBER=4** in existing
shelf_ios_testflight, retain approved API/groups/signing identities, verify the
selected prepared branch/source and no competing run; use owner interaction only
if authenticated Codemagic action is still unavailable. Nothing saved/run now.

**One next action:** authorize one existing Codemagic **ios-testflight-internal**
run/upload from prepared ios/foundation as **1.0.0 (4)**, with all gates OFF;
recheck number availability before starting. Commit/push these scoped records
only; no deployment required. Production unrelated handler preserved. Checkout
OFF, pricing-sync and second-phone-restore deferrals, deferred Apple authentication/
relay checks, Android/UI pause and joint public-launch hold unchanged.

## Disabled Apple purchase backend deployed and sandbox webhook configured — 12 October local / 13 October 2026 UTC

Owner authorized the disabled purchase rollout from **61b0845**, existing staging,
required backup, private configuration and separate Apple-scoped sandbox webhook
only after backend readiness. **COMPLETED DISABLED BACKEND ROLLOUT / WEBHOOK
CONFIGURATION**, not transaction/device/notification-delivery acceptance.

**Deployed source on both staging and production: 560ef50ac35aa2db78492b1c7bfb92ee72a69f76.**
The purchase implementation and its tests match **08a2adf** byte-for-byte; no
speculative purchase-source or mobile changes. Rollout tools added in 59e139b,
corrected in c4dbf7d, guard regression checks in 560ef50. Operator-only webhook
helper follow-up **598ae45** uses a distinct integration URL; it changes no backend
application source and does not trigger another deployment. Documentation
follow-up commits likewise are not deployed application revisions.

Existing staging was reused. A focused workflow gate compares all purchase
implementation/test paths to 08a2adf and hashes its committed completed test record:
**16 PHP / 190 assertions, 9 Flutter + existing staging-identity evidence reused**.
No purchase audit, PHP/Flutter purchase suite, build or phone test repeated.
**Eight new synthetic rollout-helper safeguards passed** through
SHELF_TOOL_TEST_SCOPE=apple-rollout scripts/run_tests.sh: no provider call before
verified promotion/runtime guard; exact Apple-only sandbox create/read-back;
existing integration refusal; event mismatch and Google-change stop without
corrective writes; owned application-directory/private-file permissions,
collision refusal and private-parent/symlink refusal. Corrected helper required
necessary synthetic reruns; source syntax and git whitespace/scope checks passed.

Staged/production checks verified the actual new schema, sandbox/real checkout/
sign-in/sync OFF, disabled public iOS purchase config with no SDK key advertised,
Apple webhook guards **403 staging / 503 production**, no Apple purchase/event/
consent history, staging database/files/mail-log/null-queue isolation and real
HTTPS private gate/noindex/catalogue/locked paid text/Library/private-file checks.
No registration/lifecycle/mail check rerun or email sent. Promotion verified
historical reader/token/action, purchase/event/refund/ledger/entitlement/consent,
agreements and accounting fingerprints unchanged. Fingerprint normalization omits
only the migration's exact PLAY_STORE/null defaults; actual Apple/non-default
proof still changes the fingerprint. Accounting/owner shares and health passed;
maintenance lifted. Existing Google purchase configuration remains ready; staging
has no production purchase SDK/auth/V2 credential configuration and an independent
APP_KEY. Both Apple sign-in/relay flags remain false.

**Migration** 2026_10_12_120000_add_purchase_event_store_scope applied once:
production **batch 28**, staging **batch 11**, **zero pending migrations** on both.
Defaults historical event/consent stores to Play; nullable app/time proof columns,
no reader/financial backfill or purchase event. Reversible migration remains
committed; rollback refuses loss if later Apple history exists.

**Verified backups:** initial production **/home/shelf/backups/shelf/20261012-235634/**;
fresh successful configuration preparation **/home/shelf/backups/shelf/20261012-235938/**;
staging **/home/shelf/backups/shelf-staging/20261013-000113/**;
fresh promotion **/home/shelf/backups/shelf/20261013-000201/**. SQL/ZIP/SHA checks
passed before changes. Configuration preparation checkpoints include encrypted
private-configuration.tar.gpg, decrypted in memory and compared exactly, plus
exact original production documentation/handler bytes, diff and release state.
Successful **apple-purchase-checkpoint/** includes configuration-verification and
documentation-preservation records. Production's existing Master Record/sign-in
additions were already retained in the isolated source; three superseded lines
follow the newer owner deferral/source status. Exact originals remain recoverable
before normalization for fast-forward promotion. cPanel handler stayed
byte-identical and remains the only unrelated working diff. No unrelated settings
or historical data changed. This local checkpoint is not new offsite recovery
or unattended backup acceptance.

**Private configuration:** existing project projbbce26da, Apple app app8ef018b95b,
internal Apple product prod0d084e901f, existing appl_ SDK key fetched privately,
existing protected V2 verifier path and distinct Apple webhook Authorization
saved; SHELF_APPLE_SANDBOX_ENABLED=false and SHELF_REAL_PURCHASES_ENABLED=false.
Every unrelated .env value compared unchanged, config cache cleared, no key/token
contents printed. New authorization is private shelf:shelf/0600 outside Git at
/home/shelf/secrets/revenuecat-apple-sandbox-webhook-auth.txt; sanitized integration
record beside it is revenuecat-apple-sandbox-webhook.json, same protections.
Existing upload/login/purchase/V1/V2/recovery credentials unchanged.

**Apple sandbox webhook configured:** **whintgr769cb03ff2 / Shelf iOS sandbox**,
app **app8ef018b95b**, environment **sandbox**, URL
**https://shelf.services/api/purchases/webhook?source=apple**, events
**non_renewing_purchase, cancellation, expiration, refund_reversed**.
POST creation **201**, exact configuration GET/list read-back **200**. The query
only distinguishes the provider integration URL; authorization and store/app
checks remain authoritative. Google **whintgr75a4892970 / appf83cd58c5c / Both**,
its URL/filters and all other integration response fields compared unchanged.
No provider test notification, delivery, purchase, transaction, email or Apple
product/price/title/territory change. **With gate OFF, Apple events are deliberately
rejected 503; configured webhook does not mean active delivery/processing.**

**Recorded setup stops:** first helper run's SQL/media/encrypted checkpoint passed,
then an overly strict parent-directory guard stopped before .env change (the
application directory is normally 0755). Corrected in isolation, synthetic guard
checks passed, fresh backup taken; resume verified the earlier encrypted original
.env and reused that run's new private authorization without rotation. First
webhook create on the same base URL as Google returned **409**; stopped without
retry, read-only GET confirmed no Apple integration created/Google unchanged.
Distinct query URL reached the already deployed disabled guard and subsequent
create succeeded 201. No existing integration updated or provider setting relaxed;
no broader undocumented uniqueness rule inferred from this result.

**Remaining blockers / next batch:** installed TestFlight build 3 lacks purchase
source. Later separately authorized internal build/upload at a confirmed unused
number, owner-only sandbox activation and actual StoreKit retrieval/purchase/
restore/refund/reversal and Apple→RevenueCat→Shelf delivery acceptance remain.
Track-new-purchases OFF / Keep original App User ID are still not exposed by the
public app API; obtain only the necessary dashboard confirmation before activation,
without repeating earlier setup. Apple **MISSING_METADATA**, saved title
**څپو کې انڅورونه** versus recorded **څپو کې انځورونه**, and absent genuine review
screenshot remain pending; no text guessed/repaired. Apple 175 territories /
new-territories setting and DSA In Review retain prior evidence; final rights/
localization/territory/review acceptance remains public-release work.

Rollback target **31d123aaee8310b71e9550dfa1e13047e44541db** is retained in promotion
notes. Prefer code-only rollback retaining schema/private configuration/history;
never bulk restore over later reader/money activity. Review the scoped migration
before reversal; preserve Apple history if any later appears. Any later webhook
removal/configuration reversal is a separate authorized provider action.
Commit/push scoped work on **ios/foundation** only, no origin/main or default-branch change.
Checkout OFF, pricing-sync and second-phone-restore deferrals, deferred Apple
authentication/relay checks, stopped email task, Android/UI pause, deferred small
improvements and joint Android/iOS public-launch hold remain binding.

## Authenticated Apple inspection completed — 12 October 2026

Continued **dc054af** on isolated **ios/foundation**. Owner reported completing
IOS_ASC_KEY_UPLOAD.ps1 with verified transfer and no API call. Codex then checked
the documented private `.p8` and identifier JSON under
`/home/shelf/secrets/apple/`: regular files, no symlinks, **shelf:shelf / 0600**;
private secrets/apple directories **shelf:shelf / 0700**. OpenSSL parsed an EC
P-256 private key; identifier syntax passed. Key contents, Key ID/Issuer ID and
JWTs were not printed or committed. Authenticated Apple GETs returned **200**,
proving actual credential validity/access rather than trusting the filename.
This does not independently establish its dashboard name or assigned role.

First app GET at **2026-10-12 23:33:04 UTC** verified **6821471140 / Shelf - شیلف /
services.shelf.app**. Both saved fields, **subscriptionStatusUrlVersion** and
**subscriptionStatusUrlVersionForSandbox**, explicitly returned **V2**. The form's
missing version selector is resolved for this app; no selector search or version
change is needed. Production and Sandbox URLs are present, byte-for-byte equal,
HTTPS on api.revenuecat.com with the documented Apple server-notification path.
The endpoint identifier is withheld. Exact comparison to a separately retained
RevenueCat dashboard URL was unavailable: the public RevenueCat app API does not
expose that app-specific destination, and no independent exact private copy was
found in the documented records. The owner's correct dashboard-copy confirmation
is reused; host/path and Apple-to-Apple equality are independently verified,
app-specific opaque destination association remains owner-confirmed. **Actual
notification delivery remains unverified.** An initial tool URL summary exposed
the opaque endpoint suffix; the local summary was immediately sanitized, and no
suffix/key/token is included in these records.

| Apple book-3 item | Authenticated saved value |
| --- | --- |
| Product | 6822080809 / shelf_book_3 / Shelf book 3; NON_CONSUMABLE |
| State | MISSING_METADATA |
| Family Sharing / content hosting | false / false |
| Localization | One ar-SA; PREPARE_FOR_SUBMISSION |
| Display name / description | څپو کې انڅورونه / شعري ټولګه |
| Base price | USA / USD 2.99, one manual price, startDate/endDate null |
| Other prices | 174 automatic territory prices, all price/currency fields resolved; dates null |
| Examples | AFG USD 2.99; CAN CAD 3.99; GBR GBP 2.99; SAU SAR 12.99 |
| Availability | 175 explicit territories; AFG included, IRN absent; availableInNewTerritories=true |
| Review screenshot / review notes | Screenshot data null; reviewNote null |

The saved display name differs from recorded book title **څپو کې انځورونه**;
recorded verbatim, no text guessed or repaired. Apple availability is a distinct
saved list, not the approved 174-region Play list or new worldwide-rights approval.
Rights/territory acceptance and final localization wording remain owner matters
before public release. Pricing proceeds are not settled income or future author
accounting evidence. The missing screenshot is confirmed required review metadata;
review notes are optional. Apple does not return a field-by-field deficiency list,
so this is not proof the screenshot is the only reason for MISSING_METADATA.
[Apple sandbox guidance](https://developer.apple.com/documentation/technotes/tn3186-troubleshooting-in-app-purchases-availability-in-the-sandbox)
says product review submission is not required for sandbox testing;
[RevenueCat review metadata guidance](https://www.revenuecat.com/docs/getting-started/entitlements/ios-products)
requires a screenshot for submission. Actual StoreKit product retrieval is still
unverified; neither approval nor a fabricated screenshot is requested now.

GET-only inspection covered app, its IAP list, book-3 localization, review
screenshot, price schedule/manual and automatic prices, availability and all
available territories with bounded pagination. An initial availability query
returned 400 PARAMETER_ERROR.INVALID; removing the unsupported include-limit
query and reading the availability/territory relationship separately returned
200. No provider state changed. Completed **ae9016c RevenueCat verification** and
**08a2adf purchase tests** reused without rerun: Apple product prod0d084e901f remains
verified attached to entlb3d491e78e / shelf_book_3 with original Play product;
Apple app app8ef018b95b, purchase credential configured, required V2 reads verified.
The separate **Apple-scoped RevenueCat-to-Shelf sandbox webhook remains missing**.

**Smallest next separately authorized batch:** automate that distinct Apple app /
SANDBOX webhook to https://shelf.services/api/purchases/webhook with separate
private Authorization and NON_RENEWING_PURCHASE/CANCELLATION/EXPIRATION/
REFUND_REVERSED, preserving Play; privately configure the verified Apple app,
internal product and existing appl_ SDK key with the Apple sandbox gate OFF;
stage the existing backend source, then verified backup/reversible migration and
same-commit promotion. Check tracking-off/original-ID restore settings only where
existing access exposes them; no repeat credentials/product/selector setup.
Later separate internal build and owner-only sandbox activation/phone acceptance
must establish product retrieval and actual delivery. A genuine purchase-screen
review image and final localization/territory acceptance remain review/release
work. No extra access or owner action is needed for this completed inspection.

Documentation-only commit/push on ios/foundation; git whitespace/scope checks,
no purchase audit/tests repeated. Unrelated production records/.htaccess preserved.
No provider writes, test notifications, credentials/.env changes, deployment,
migration, build/upload, email or transaction. Checkout OFF, pricing-sync and
second-phone-restore deferrals, deferred Apple authentication/relay checks,
Android/UI pause, stopped email task and joint public-launch hold preserved.

## Secure Windows upload-key helper prepared — 12 October 2026

Continued **ae9016c** on isolated **ios/foundation**, reusing its completed Apple/
RevenueCat schema/configuration checks; no provider calls or purchase tests repeated.
Owner standing automation preference is now recorded in Part 5.2 and AGENTS.md:
automate all possible approved work, manual action only for necessary owner
interaction/access. The owner's local .p8 selection and missing Issuer ID are the
only interactive inputs here; no manual SFTP/JSON file creation is needed.

Checked current Shelf governing/purchase/foundation/signing documentation and
local configured/inherited upload identifier presence privately. The documented
integration is **shelf_app_store_connect**, the existing **Shelf Codemagic App
Store Connect upload team key**, with expected existing App Manager role. Actual
upload Key ID and Issuer ID are not recorded as verified values or locally
configured; protected provider secret values were not retrieved. **ShelfLogin /
2VQ43945YB** is a different Sign in with Apple key; **ShelfPurchases** is the
separate In-App Purchase key already supplied to RevenueCat. Neither is selected
for this helper. No credential created/rotated or key contents inspected.

Prepared **docs/IOS_ASC_KEY_UPLOAD.ps1**, reusing the established
IOS_APPLE_KEY_UPLOAD.ps1 Windows picker / binary SSH stdin / encoded public Python
receiver / strict existing SSH host trust approach. New picker explicitly names
the existing Codemagic upload key and excludes sign-in/purchase key use. Requires
original **AuthKey_<10-character-Key-ID>.p8** basename, derives Key ID without
retyping, rejects the known ShelfLogin ID and local reparse points/parent links.
Only unknown Issuer ID is requested once in a private GUI input; UUID syntax
checked. Owner reads it from the existing Codemagic integration. Actual key
capability/role/Apple acceptance remain unverified, to be inspected later.
Filename derivation is not independent dashboard association verification.

Original local file bytes/name/ACL are unchanged; one read-only stream prevents
concurrent ordinary writes/deletion while hashing/sending. Identifiers, expected
size/SHA-256 and key travel only through SSH binary stdin, not command arguments,
PowerShell text pipeline, clipboard, local metadata copies, tokens or logs. Public
receiver alone is encoded in the remote command; StrictHostKeyChecking=yes, no
automatic host acceptance, no PTY. No authentication token is generated.

Receiver opens every server directory component with directory descriptors and
O_NOFOLLOW, verifies existing secrets/apple ownership **shelf:shelf / 0700**, then
exclusively creates **/home/shelf/secrets/apple/shelf-asc-inspection.p8** and
**shelf-asc-inspection.json**, both **shelf:shelf / 0600** before data write. Existing
regular files or dangling/normal symlinks at either destination are refused; no
renaming/overwriting current keys. SHA-256/length key read-back and exact metadata
read-back/fsync/ownership/mode/link checks establish saved transfer integrity.
Handled failure removes only this attempt's created files with matching inode;
existing files and links preserved. Abrupt SSH/process interruption or unsuccessful
cleanup can leave restricted partial files: stop for private inspection rather
than overwrite or automatic retry. Source backup unchanged and independently kept.
Destination absence checked read-only; no live credential files created now.

**Synthetic verification passed: 13 focused checks** via
`SHELF_TOOL_TEST_SCOPE=asc-key-upload scripts/run_tests.sh`; persistent test is
scripts/ios/test_asc_key_upload.py. It extracts/executes the helper's actual receiver
with only synthetic binary data and overridden disposable root under the wrapper's
/home/shelf/tmp directory; no real key/provider/SSH/Windows picker invoked.
Success byte-for-byte key + parseable metadata/mode/owner; key and metadata
collisions, destination/dangling/parent symlinks, unsafe directory, truncated/hash-
mismatched/oversized input, extra bytes, invalid identifiers/header bounds and
secret-output absence checked. Client source contract checks cover filename,
private-input/stream/disposal, no original modification and strict SSH trust.
Existing wrapper gains only a focused tool scope that exits before PHP/mobile;
ordinary PHP/mobile/backup behavior retained. Shell syntax and git diff checks
passed. **Windows PowerShell runtime/picker and actual SSH transfer unexecuted**
(no PowerShell binary here); source checks are not a Windows execution claim.
No completed purchase/audit/build/app test repeated.

Updated IOS_PURCHASES.md replaces the earlier manual SFTP/JSON request with this
helper and two Windows PowerShell download/run commands. Download refuses a
preexisting helper filename; run opens the picker and asks only missing Issuer ID.
Owner must select the existing file for shelf_app_store_connect, not create a new
credential. After transfer, next separate task is **read-only Apple API inspection**
of Shelf's existing book-3 configuration and notification URLs/versions, with
completed RevenueCat evidence reused; no other provider action authorized.

Commit/push **ios/foundation only**. Revert helper/test/wrapper/document changes
normally before use; after transfer, any file removal needs separate review and
must never affect the owner's original or existing login/purchase/upload keys.
Production unrelated MASTER_RECORD/IOS_SIGN_IN/.htaccess untouched. No real
transfer in this preparation, provider writes/settings, checkout activation,
deploy/migration/build/app upload/mail/transactions. Checkout OFF, pricing-sync
and second-phone-restore deferrals, deferred Apple authentication/relay checks,
stopped email task, Android/UI pause, deferred improvements and joint public-
launch hold preserved.

## Apple purchase provider setup and read-only verification — 12 October 2026

Continued completed implementation **08a2adfd5363896ce27907deaaffbd52648116cc**;
no repeated purchase audit or completed tests. Owner/dashboard evidence: Paid
Apps Agreement, banking and W-9 **Active**; EU Digital Services Act verification
**In Review**, not cleared. ShelfPurchases In-App Purchase key added to RevenueCat
Shelf (App Store), dashboard **Valid credentials**. Apple non-consumable
`shelf_book_3` created; availability, pricing and localization owner-reported
complete, exact saved values **not independently inspected**. Owner confirms
Apple product attachment became visible after RevenueCat refresh. Same RevenueCat
notification URL saved in Apple's Production and Sandbox fields, owner-confirmed;
form offered **NO version selector**. No request to find it again.

**Notification version finding:** current Apple Help still describes a V1/V2
choice; current API App.Attributes independently exposes `subscriptionStatusUrl`,
`subscriptionStatusUrlForSandbox`, `subscriptionStatusUrlVersion` and
`subscriptionStatusUrlVersionForSandbox`. Version enum permits **V1 / V2**, with
V1 deprecated. These sources do not document a guaranteed default for a form
without that selector. Thus neither a forced V2 claim nor a missing-setup claim
is justified from this UI. Read the two version fields on Shelf app **6821471140**
when authenticated access is supplied; null/omitted fields remain unresolved.
RevenueCat accepts both versions and recommends V2, including for one-time refund
handling. Its app GET/schema does not expose Apple notification URLs/versions or
purchase-tracking/restore settings. Apple's TEST notification is always V2 even
when the registered configuration is V1: a later TEST success would establish
endpoint delivery, **not the saved ordinary-event version**, nor the full
RevenueCat-to-Shelf purchase/refund path. No notification test was requested.
Saved URLs alone establish no delivery. Earlier selector instructions are
superseded by this finding and the revised purchase guide.

**Authenticated RevenueCat verification**, existing Shelf V2 file reused privately
(mode 0600, regular, no symlink); bounded GETs only in `projbbce26da`, no customer
history retrieved or credentials printed. Apps/products/entitlements/webhooks
lists HTTP 200 with no further page; individual Apple app, book-3 entitlement
products and public-key metadata GETs also 200:
- Apple app **app8ef018b95b**, **Shelf (App Store)**, bundle **services.shelf.app**.
  `subscription_key_configured=true` establishes purchase-key configuration;
  dashboard Valid credentials remains owner evidence, not a fresh Apple validation.
  `app_store_connect_api_key_configured=false`: separate optional catalogue-import
  credential is absent in RevenueCat, not a failed purchase-key configuration.
- Apple product **prod0d084e901f**, store identifier **shelf_book_3**, API type
  **non_consumable**. Existing **entlb3d491e78e / shelf_book_3** has exactly that
  Apple product and original Play **prod03ffc5153f**; Play attachment preserved.
  RevenueCat catalogue evidence is not independent Apple metadata/price verification.
- One `appl_` public SDK key exists; its value withheld and not installed.
- Webhook list contains only **whintgr75a4892970**, Play app **appf83cd58c5c**,
  environment null (Both), original three event filters. **Apple-to-Shelf webhook
  absent**; Apple-to-RevenueCat URLs do not replace this separate integration.
- Apps/products/entitlements/integrations read access passed. At **21:56:32 UTC**,
  GET purchases search with unique non-transaction marker returned HTTP 200,
  empty list/no further page, establishing `customer_information:purchases:read`.
  Purchase-entitlement GET uses this same permission per current schema; actual
  Apple purchase/entitlement response acceptance remains unverified. No new
  RevenueCat read permission or key replacement is required for the verifier.

**Private access boundary:** production environment has the documented Shelf V2
key path, but no App Store Connect key/issuer configuration or inherited ASC
fields. No ASC-named candidate in documented Shelf private root/Apple directory.
Existing upload key is documented in protected GitHub/Codemagic setup, unavailable
as a callable credential here; no attempt to extract provider secrets. ShelfLogin
is a Sign in with Apple key and was not read/used for ASC. No unrelated project
secrets inspected. Apple product/price/availability/localization/Family Sharing,
saved URL comparison and saved versions cannot be independently verified yet.

**One consolidated next access request:** privately transfer the **existing Shelf
App Store Connect upload team key** from the owner's retained original, plus its
actual Key ID and Issuer ID. Reuse its existing **App Manager** role (sufficient
for the app/IAP/pricing/availability inspection); no Admin, Finance, reports or
credential/permission change requested. This task will use it for GETs only;
App Manager itself is not an Apple read-only role, and team keys are account-wide.
Do not substitute ShelfLogin or ShelfPurchases. Exact secure SFTP destination,
metadata format and permission instructions are in **[IOS_PURCHASES.md](IOS_PURCHASES.md)**.
No credentials were transferred or changed in this task. If the retained original
is unavailable, report that fact before proposing any new credential.

**Remaining setup**, under separate explicit authorization: inspect Apple saved
values/versions through that access; confirm RevenueCat Track new purchases stays
off and Keep with original App User ID remains selected (not exposed by these
GETs); create only the separate Apple app-scoped sandbox Shelf webhook with
NON_RENEWING_PURCHASE/CANCELLATION/EXPIRATION/REFUND_REVERSED and distinct private
authorization. Existing V2 integrations read_write permission was documented in
original setup but not re-probed by writing; use it for later authorized automation
only if still sufficient, otherwise owner dashboard setup, not automatic elevation.
Configure the verified app/product/SDK references privately with Apple sandbox
OFF; later stage exact source, back up and promote reversible migration, then
separately authorize internal build/owner-only sandbox activation and delivery/
phone acceptance. No offering, other Apple book or new purchase key needed.
Installed build 3 lacks purchase source. DSA In Review remains public-release
uncertainty; no new sandbox blocker inferred solely from that status.

Documentation-only isolated change; diff/scope checks, no suites/audit/build rerun.
Commit/push **ios/foundation only**; rollback ordinary documentation revert.
Production dirty MASTER_RECORD/IOS_SIGN_IN/.htaccess preserved. No provider writes,
credentials/settings/migrations/deployments/builds/uploads/mail/transactions.
Checkout OFF; pricing-sync and second-phone-restore deferrals, deferred Apple
authentication/relay checks and stopped email task, Android/UI pause, deferred
improvements and joint public-launch hold preserved.

Official sources checked today:
- [Apple URL setup](https://developer.apple.com/help/app-store-connect/configure-in-app-purchase-settings/enter-server-urls-for-app-store-server-notifications/)
- [Apple app attributes](https://developer.apple.com/documentation/appstoreconnectapi/app/attributes-data.dictionary), [version enum](https://developer.apple.com/documentation/appstoreconnectapi/subscriptionstatusurlversion)
- [Apple notification enablement and TEST limitations](https://developer.apple.com/documentation/appstoreservernotifications/enabling-app-store-server-notifications)
- [RevenueCat notifications](https://www.revenuecat.com/docs/platform-resources/server-notifications/apple-server-notifications)
- [RevenueCat apps](https://www.revenuecat.com/docs/api-v2/app), [products](https://www.revenuecat.com/docs/api-v2/product), [entitlements](https://www.revenuecat.com/docs/api-v2/entitlement), [purchases](https://www.revenuecat.com/docs/api-v2/purchase), [webhooks](https://www.revenuecat.com/docs/api-v2/integration)
- [Apple API key roles](https://developer.apple.com/help/app-store-connect/get-started/app-store-connect-api/)

## One-book Apple sandbox purchase source prepared — 12 October 2026

Owner authorized the completed purchase audit's smallest implementation batch,
`shelf_book_3`, in the existing isolated `ios/foundation` checkout based on
`a5047f495ca4ba79a0869fd837787089b06521ac`. Shared purchase infrastructure and
completed evidence are reused; no general readiness audit or broad unchanged
suite is repeated. Production's local Master Record, sign-in guide and
`public/.htaccess` changes are untouched. This record describes source preparation
only: **not staged, deployed, activated, built, uploaded or phone-accepted**.

Implemented native iOS `appl_` / Android `goog_` RevenueCat selection, exact
one-book Apple sandbox configuration/consent and the Runner In-App Purchase
capability. Existing Android behavior, numeric reader identity, purchase
confirmation and account-isolated Library/download protection are preserved.
SDK success/restore never grants access; server evidence remains authoritative.
Apple checkout is **default OFF**, owner-only (verified exact owner email, no
plus-alias/private-relay inference), sandbox only; Apple production acceptance is
not implemented/enabled and real checkout remains OFF.

Independent server RevenueCat V2 reads verify configured Apple app/project,
`services.shelf.app`, its permanent non-consumable `shelf_book_3`, original/current
reader ID, purchased ownership, quantity one, transaction ID, exact millisecond
purchase time, sandbox environment and active individual entitlement. Unknown,
invalid, ambiguous, transferred or production proof fails closed. Apple uses its
own webhook authorization/app scope. Event IDs, refund tombstones and consent
are store-scoped; duplicate events/state notifications do not repeat purchases or
money. Existing Play transaction/environment isolation and production-OFF gates
are retained.

Confirmation/restore/reconciliation use independent server proof. Refunds and
revocations lock access; the existing client removes the downloaded copy at the
next successful check. `REFUND_REVERSED` requires fresh owned proof and a matching
unreversed refund without revocation, restores only the original account and
appends a compensating ledger entry. Original sales/refunds are never rewritten;
sandbox remains Test and excluded from real income/author totals. REST-observed
refund time is labelled observation evidence, not an invented Apple refund date;
unknown money stays unknown. Stale/conflicting corrections fail closed. Buying
blocks stop new checkout without preventing historical refund verification.
Apple deleted-account recovery remains explicitly blocked before a recovery claim
or Google-proof call; reviewed Apple claimant proof/recovery-journal support and
Family Sharing remain outside this batch.

Committed reversible migration preparation adds event store/app/time, exact Apple
purchase milliseconds and consent store, defaults historical rows to Play and
refuses rollback that would discard Apple history/consents. **No migration has
been applied to production or staging.** A later separately authorized rollout
must verify the exact commit in existing staging with isolated synthetic data,
then take/verify the required production backup before migration and identical-
commit promotion through the established process. No new environment created.

Focused verification: **16 PHP tests / 190 assertions**, comprising 14 new Apple
synthetic tests and two selected shared-path Android compatibility checks; **9 new
Flutter tests** and the wrapper's existing **1 staging-identity guard**. PHP uses
SQLite in memory; provider HTTP is mocked/prevented from escaping. Coverage:
default/staging-off gates, owner-only book/mode consent, app/product/environment/
identity/transaction/time/entitlement proof rejection, duplicates and key-order
replay, cross-account paid-access denial, store collision isolation, refund/
reversal cycles, late duplicate sale, stale corrections, refund-before-sale,
restore/server confirmation, unavailable-proof lease refusal, buying-blocked
historical refunds, deleted-account recovery and reversible/history-preserving
migration guards. Flutter verifies iOS key/config/mode, native capability, book-3
restriction, store-bound consent, confirmation, native-then-server restore,
refund download removal, unavailable-proof lease, cancellation/pending and Android
key/mode preservation. Focused analyzer has no errors/warnings; informational
style notices remain. No general Flutter/PHP rerun or actual store request.

Signing checks, internal-only export, `ditto` extraction, photo-purpose pre-build
check and explicit `$CM_BUILD_DIR` IPA/verification-evidence artifact collection
remain unchanged, verified by focused source diff; completed signing/build
results are reused without rerunning them. No credentials/build numbers/dashboard
variables/mail/production flags changed. Installed build 3 does not contain this
purchase source; a later separately authorized internal build and owner-controlled
iPhone purchase/restore/refund/reversal checks remain pending. Verify the next
unused build number at that later step; none reserved here.

Remaining setup is documented in [Apple purchase setup](IOS_PURCHASES.md) and linked
from the existing purchase guide: actual purchase `.p8` key/Issuer ID; Apple Paid
Apps Agreement/tax/banking status; Apple book-3 product metadata/price/storefronts;
RevenueCat Apple app/public key/internal product ID and existing individual
entitlement attachment; least-privilege V2 purchases/entitlements/apps/products
read access; separate Apple sandbox notification/webhook configuration; later
backed-up deployment/default-off configuration and explicit owner sandbox
activation. None is assumed complete or changed by this batch. Actual Apple →
RevenueCat → Shelf notification delivery, real response shapes and iPhone results
remain unverified, separate from synthetic source evidence.

**Next single owner action:** App Store Connect → Users and Access → Integrations
→ In-App Purchase: inspect existing Active keys; if no suitable existing key/file,
Generate In-App Purchase Key / +, **Name: ShelfPurchases → Generate → Download API
Key**; preserve the downloaded `.p8` privately. This purchase key differs from
both the existing upload key and ShelfLogin Sign in with Apple key; reuse neither
as purchase proof. Do not activate, deploy, build, upload or transact in this task.

Apple sign-in phone acceptance/relay delivery stay owner-deferred and unverified;
the relay transfer/email task remains STOPPED and both Apple authentication flags
false. Checkout OFF, pricing-sync/second-phone-restore deferrals, Android/UI pause,
small improvements deferred until before launch and joint Android/iOS public-
launch hold remain binding. Step 4 and iOS acceptance are not marked complete.


## Owner correction: Apple phone/relay checks deferred; transfer/email stopped — 12 October 2026

Owner clarifies the build-3 rehearsal reached **Apple's Face ID prompt only**.
Authorization could not finish because the test iPhone belongs to somebody else.
Earlier “it works” did **not** establish successful Apple authorization, actual
Shelf relay-address acquisition or complete rehearsal acceptance. Correct the
prior acceptance wording below accordingly; preserve the confirmed build/install
and observed native prompt evidence. No signed-build source/artifact reread or
additional install/processing claim is made by this records-only correction.

**Apple sign-in phone acceptance and relay-email delivery are OWNER-DEFERRED,
UNVERIFIED, not passed.** No relay address was uploaded, SMTP test attempted or
inbox receipt established. Read-only file existence check confirms both
`owner-relay-address.txt` and `relay-test-attempt.json` absent. The pending private
transfer and single-email task is **STOPPED**; its earlier send authorization is
withdrawn by this instruction. Prepared tools remain historical preparation and
must not be run without a later explicit owner instruction. Do not request a
relay address or another phone check now; no email is sent.

Both **APPLE_SIGN_IN_ENABLED=false** and **APPLE_SIGN_IN_RELAY_READY=false** are
preserved. No code/configuration/key/account/data change, rebuild, upload,
deployment or purchase. This correction changes only isolated records; production
local Master Record/sign-in guide and cPanel `.htaccess` edits are preserved.

**Next useful task without the owner's iPhone:** a scoped **iOS per-book App Store
purchase readiness audit and preparation plan**, the unfinished iOS functionality
work in the single four-area list. Inspect existing shared Flutter purchase flow,
backend verification/ownership/accounting and store-specific configuration;
identify reusable Android functionality and exact iOS/RevenueCat/App Store gaps,
then prepare a minimal implementation/setup and sandbox-validation plan. This
can proceed as inspection/preparation without Apple phone login, production flag
changes, real transactions or a new build. Do not infer Android evidence proves
iOS purchases. Purchase implementation/dashboard changes and transactions need
separate task authorization; they are not begun by this correction.

Existing backend financial-retention construction remains in the Master Plan;
Android/UI work remains paused. Checkout OFF, pricing-sync and second-phone
restore deferrals, small improvements deferred until before launch and joint
Android/iOS public-launch hold are unchanged. No broad test rerun for records.


## Historical build-3 report and transfer preparation — corrected and stopped, 12 October 2026

Owner initially reported installed build **1.0.0 (3)** “Prepare Apple email check”
works and displays the hidden email address. **Owner corrected this report:**
only Apple’s Face ID prompt was reached; authorization could not finish on the
borrowed phone. Build/install and prompt evidence remain; **no successful
authorization, relay acquisition or rehearsal acceptance is confirmed**. No relay address, screenshot or token supplied in chat;
no inference of full Apple/Google login, inbox delivery, reset/deletion/revocation
or wider acceptance. Exact signed-build source/artifacts were not independently
reread in this task. Earlier build-3 unused/preparation status is historical.

Owner initially authorized secure transfer and exactly **one** harmless test
email; **that pending task is now stopped and authorization withdrawn**. Both activation flags must remain false. Inspected
existing key uploader and mail setup helper; key uploader only accepts .p8 and
mail setup changes configuration, so neither is used for this check. Prepared
`docs/IOS_APPLE_RELAY_UPLOAD.ps1`, reusing trusted SSH/stdin and original ownership/
mode checks. Hidden PowerShell prompt; no address in command arguments, printed
output, history, Git or local plaintext file. SSH host must already be trusted;
receiver validates Apple relay format, private mode-700 shelf directories,
creates only `owner-relay-address.txt` with exclusive/no-symlink mode-600 semantics,
sets shelf:shelf, verifies bytes privately and refuses overwrite.

Private directory verified shelf:shelf/700. Address destination and attempt file
were absent at inspection. **Transfer/email task STOPPED by owner; no upload and no email sent.** Exact Downloads scp/run commands are in `docs/IOS_SIGN_IN.md`.
Only generic upload result is requested; never ask the owner to paste the address.

Prepared operational `docs/IOS_APPLE_RELAY_CHECK.php`, run outside application
routes against the existing production Laravel bootstrap. Its non-sending
preflight passed: configured SMTP/default sender noreply@shelf.services; Apple
flags false; real checkout OFF and sync disabled. After transfer, verify file
owner/mode/regular-file identity/relay format without printing contents, then
send once using the existing mailer, subject **Shelf relay delivery check**,
body **This is the owner-only Shelf email delivery check. No action is required.**
No mail setting, reader account, database, token or purchase change. Before SMTP,
create an exclusive private `relay-test-attempt.json` with sanitized timestamps/
status, preventing another attempt even after failure or uncertain acceptance.
SMTP acceptance and **owner-confirmed inbox receipt** are separate evidence;
receipt and SPF/DKIM outcome remain unverified. No automatic resend.

Checks: PHP syntax and non-sending production preflight passed; synthetic Python
receiver tests passed invalid-input refusal, private mode/ownership, correct
transfer, overwrite/symlink refusal and sanitized output. Windows PowerShell
runtime is unavailable here; Windows prompt/SSH execution was not completed and is now owner-deferred.
Completed Flutter/backend tests reused; no app source changed or broad rerun.
Changes are isolated tools/documentation only, not deployed application code.
Production local records/cPanel handler and all unrelated changes preserved.

Checkout OFF; pricing-sync/second-phone restore deferrals, Android/UI pause,
deferred small improvements and joint Android/iOS public-launch hold preserved.
No activation, cloud build/store upload, deployment or purchases in this task.


## One-build iOS Apple relay bootstrap prepared — 12 October 2026

Owner confirms **iOS build 3 is still unused**. Plan: existing Shelf **1.0.0 (3)**
with app source **9194793edd1f33885af9522620efa7950bec19e0** and documentation-only
follow-ups. Build the exact final ios/foundation head reported with this preparation,
existing `ios-testflight-internal`, sole-owner **Shelf Owner** internal distribution.
No new build/upload has been started. Installed build 2 remains limited owner
acceptance (“seems good”), not acceptance of these authentication changes.

Prepared in isolated `/home/shelf/tmp/shelf-ios-foundation`: a temporary account
panel **Owner Apple email check**, natively limited to release services.shelf.app
1.0.0 (3). It invokes the existing Apple SDK before backend account creation,
requests email with a cryptographically random local nonce, and saves only an
actual SDK-supplied Apple private relay address to device-only Keychain. It does
not read/retain/return Apple tokens, codes or subject; Flutter receives booleans
only. Owner copy is device-local and expires after 60 seconds; private password
manager/SSH transfer only, never chat/git/logs. Remove saved address does not
revoke Apple authorization. Scope relies on sole-owner internal distribution,
not a claim that a build number authenticates the owner. No API/session/account
or purchase path is bypassed or changed. Existing email/password, Android Google,
account collision refusal, ownership and deletion behavior retained.

Backend stays at **31d123aaee8310b71e9550dfa1e13047e44541db**, Apple enabled=false
and relay-ready=false. Signing/photo guards/internal-only export/ditto and verified
explicit `$CM_BUILD_DIR` artifact paths are unchanged. No new dependency/credential,
production `.env`, migration, deployment, email, purchase or store operation.
Production's local Master Record/sign-in guide and cPanel `.htaccess` changes
are preserved; preparation is not a backend promotion.

Execution plan is in `docs/IOS_SIGN_IN.md`: one separately authorized internal
build → owner taps Prepare Apple email check/Hide My Email → private collection
of address only → one separately authorized harmless production relay email while
both flags remain false → actual inbox/authentication evidence → separately
approved backed-up activation → force-close/reopen and full Apple/Google login
on the **same build**. No second internal build expected because normal provider
flows and configuration refresh are already included. Native compilation and
signed-phone behavior remain pending; a discovered defect may require correction.
Green relay SPF alone is not delivery. Missing first-response address is a stop,
not permission to revoke a real account or guess an address.

Focused source checks use `scripts/run_tests.sh --mobile`, scope `ios-relay`,
with only the backend disabled/missing-configuration check. Native Swift is
source-inspected; this Linux host cannot compile/verify signed iPhone behavior.
Completed broad backend/Flutter evidence is reused. Development-test failures
in platform-override cleanup and viewport selection were corrected in isolation;
no production failure occurred. Final checks passed: **7 rehearsal Flutter tests,
1 staging-identity Flutter test, 1 focused PHP test / 3 assertions**. Focused
analyzer had no errors/warnings and four pre-existing account-screen style infos. UI has explicit preparation/copy/removal states and sanitized errors;
no relay address or credential is displayed. Actual device acceptance remains open.

Checkout OFF; pricing-sync and second-phone-restore deferrals, Android/UI pause,
small improvements deferred until before launch and joint public-launch hold remain.


## Disabled iOS authentication backend deployed — 12 October 2026 UTC

**COMPLETED DISABLED BACKEND ROLLOUT**, not Apple/provider/iPhone acceptance.
Staging and production release-state and deployed code identify exactly
**31d123aaee8310b71e9550dfa1e13047e44541db**. Initial baseline was
**5f5c8f6087d5d908b96558863129b8a09b44fcf8**; immediate previous code candidate
**15d873c60319a07ca6d0a8aa0fc877dcc01d94ac**. Documentation follow-up commits
are not deployed application revisions and do not trigger another rollout.

Owner dashboard evidence: **ShelfLogin / 2VQ43945YB / Sign in with Apple /
YSLWJQDH8B.services.shelf.app**. Key file remains original/private/unmodified at
`/home/shelf/secrets/apple/AuthKey_2VQ43945YB.p8`, shelf:shelf/600, parent 700.
Production privately configured approved client services.shelf.app, team
YSLWJQDH8B, Key ID 2VQ43945YB and original key path. **APPLE_SIGN_IN_ENABLED=false,
APPLE_SIGN_IN_RELAY_READY=false**; actual runtime configured=true/enabled=false.
Google Web client, APP_KEY and every unrelated .env setting compared unchanged;
no credentials printed/copied into staging. Config caches cleared, no key rotation.

**Verification:** focused staged **43 PHP tests / 386 assertions**, through
scripts/run_tests.sh without --mobile, covering email/password, Google signature/
audience/subject continuity/collision policy, no automatic email merging, account/
purchase isolation, Apple token/challenge/deletion and migration reversal. Staged
live isolation/lifecycle used disposable rolled-back data and logged mail only;
no delivery. Real HTTPS private gate/noindex, catalogue/locked paid text, Library,
webhook/private-file protection and production health passed. Staging has its own
APP_KEY, no Apple signing configuration; no production credentials copied. Prior
32 Flutter + 1 identity/config fixture evidence reused, no Flutter/Android rerun,
native compile, real Google/Apple exchange or signed-phone acceptance claimed.

**Reviewed migration** `2026_10_11_120000_add_apple_reader_identity` applied once:
production **batch 27**, staging **batch 10**. Nullable unique apple_subject_hash,
encrypted apple_refresh_token field and expiring challenge table present; **zero
pending migrations** on both copies. No backfill, reader/purchase/financial change.
Workflow historical fingerprints prove existing reader/token/action, purchase/
refund/ledger/entitlement/consent/agreement/accounting records unchanged, omitting
only NULL new schema fields (non-null identities still change the fingerprint).
Production purchases/accounting/owner-share and health checks passed; maintenance
lifted. HTTPS `/api/auth/config` advertises Apple false, accounts/Google true;
POST `/api/auth/apple-challenge` and `/api/auth/apple` return **404**. Real checkout
OFF, existing sandbox integration preserved, pricing-sync disabled/deferred.

**Backups/checkpoint:** staging SQL/media verification at
`/home/shelf/backups/shelf-staging/20261012-034642/` and corrected-candidate backup
`/home/shelf/backups/shelf-staging/20261012-035147/`. Production initial verified
backup **/home/shelf/backups/shelf/20261012-034809/** includes
**ios-auth-checkpoint/**: exact pre-existing local AGENTS/Master Record/handler
copies and checksums, git status/full diff, prior release-state/history metadata,
and **private-configuration.tar.gpg** with .env/Apple key. AES256 ciphertext was
decrypted in memory and compared exactly; no plaintext secret checkpoint/output,
no offsite upload. Independent owner recovery key unchanged. Governing document
insertions reconciled in isolated candidate before their backed-up production
working diffs were normalized; originals remain recoverable byte-for-byte.
Production cPanel handler stayed byte-identical. First promotion's verified backup
**/home/shelf/backups/shelf/20261012-034910/**; final corrected promotion fresh
verified backup **/home/shelf/backups/shelf/20261012-035221/**, with prior revision,
promotion-notes and **ios-auth-config-verification.json**. All SQL/ZIP/checksums
passed before changes. Secret backup verification here is local, not a claim of
new unattended/offsite recovery or provider acceptance.

**Honest gate correction:** initial 15d873c staging passed, production code/migration/
history checks passed, maintenance lifted; its new post-check falsely interpreted
existing sandbox purchases.enabled=true as real checkout. Stopped live changes,
read-only diagnosis established no live/control/data failure: actual
purchases.production_enabled=false. Corrected the gate in isolation, restaged
31d123a with the focused checks (necessary repeat after a source correction), then
promoted that identical commit with fresh backup. No setting was changed to make
this check pass. Original failed completion did not update release-state; final
passing workflow now records both copies/verified commit correctly. No bypass.

**Next essential owner-only step:** separately approve preparation/use of a native
Sign in with Apple rehearsal under existing Shelf App ID/team on the owner's signed
iPhone, with a locally generated nonce and **Hide My Email**. It must call Apple's
native authorization without the disabled public backend, create no Shelf reader,
and retain only the actual owner-controlled Shelf relay address privately; discard
all identity tokens/codes. This rehearsal is not implemented/built/authorized by
this rollout. Existing normal login requires both gates, so cannot bootstrap an
address while they stay false. If an active Shelf relay address already exists,
reuse it instead. Then separately authorize one harmless message through actual
production Shelf mailer/sender to that private @privaterelay.appleid.com address;
owner inbox receipt plus private sender/SPF/DKIM evidence required. Green SPF is
not delivery evidence; iCloud alias/SMTP acceptance is not a substitute. Both gates
remain false until evidence and later activation approval. Later disposable-reader
reset/deletion/revocation and signed iPhone Google/Apple acceptance remain separate.

No Apple activation/email/provider call/cloud build/store upload/Android rebuild/
purchase or App Store purchase implementation. Existing signing/internal-only
export/ditto/photo-purpose guard/explicit artifact paths and build numbers intact.
Checkout OFF, pricing-sync and second-phone-restore deferrals, deferred UI,
Android/UI pause and joint Android/iOS public-launch hold preserved. Code-only
rollback retains nullable Apple columns/private config/key and reader history;
never blanket migration rollback/database restore across later activity.


## Disabled iOS authentication backend rollout authorized — 12 October 2026 UTC

Owner authorizes focused staging verification and backed-up identical-commit
production promotion with Apple sign-in disabled. Dashboard evidence confirms
ShelfLogin / Key ID 2VQ43945YB / Sign in with Apple /
YSLWJQDH8B.services.shelf.app. Uploaded P-256 key remains verified and private.
Focused staging gate prepared in isolation; nullable-column history comparison
preserves all existing reader/money values. Production local AGENTS/Master Record
changes reconciled into candidate; exact-byte backup checkpoint precedes targeted
document cleanup for promotion, cPanel handler preserved. No staging credential
copy or live email. Completion/migration/backup evidence will be recorded only
after actual passing rollout. Only six approved Apple settings may be saved,
both flags false; Google Web/APP_KEY and all other settings unchanged. Source and
Flutter evidence reused; no build/store upload/Android rebuild/purchases.
Checkout OFF, pricing-sync and second-phone-restore deferrals, deferred UI,
Android/UI pause and joint public-launch hold unchanged. See IOS_SIGN_IN.md.


## Apple private key upload verified; backend rollout pending — 11 October 2026

Owner reports PowerShell upload verified original name/shelf:shelf/600. Codex
independently found exactly one regular, non-symlink file:
`/home/shelf/secrets/apple/AuthKey_2VQ43945YB.p8`, **shelf:shelf / 600**; parent
**shelf:shelf / 700**. OpenSSL consistency and PHP parsing/in-memory signing
verification passed: unencrypted **PKCS#8 EC P-256 / prime256v1**, suitable for
local ES256 signing. No key/public components, signatures, JWTs or contents
printed/copied/committed; no key or permissions altered. Filename does NOT prove
Apple Key ID/capability/active status/primary App ID. Owner must confirm those
from Apple Developer → team YSLWJQDH8B → Keys → ShelfLogin; preserve the separate
App Store Connect upload key. Prior transfer-pending notes are historical.

Google iOS client/Codemagic variable and Apple domain/green SPF setup remain
owner-confirmed. Actual relay delivery/provider acceptance remains unverified.
Exact ordered rollout/configuration/rollback and owner-only delivery procedure
are prepared in **[IOS_SIGN_IN.md](IOS_SIGN_IN.md)**. Reuse existing implementation
**32ca4a04929ad065af21d85241c5ca3a749b5007** and completed 43 PHP/386 assertions,
32 Flutter + 1 identity checks; no tests repeated for documentation. Staging and
production remain **5f5c8f6087d5d908b96558863129b8a09b44fcf8**. Later release must
have focused staged backend evidence at the identical production SHA, fresh
verified backups, reversible Apple migration and historical-reader/money checks.
Current rollout-helper fallback would repeat broad Flutter checks; a reviewed
focused iOS-auth gate is still needed. Production unrelated local AGENTS/Master
Record changes must be preserved/reconciled before helper promotion; cPanel
handler stays unchanged. Neither obstacle was changed in this task.

Remaining configuration: dashboard-confirmed Key ID/capability association;
future client services.shelf.app, team YSLWJQDH8B and actual private-key path;
both APPLE_SIGN_IN_ENABLED and APPLE_SIGN_IN_RELAY_READY stay false until their
separate evidence/approval gates. Existing web Google audience/mail/APP_KEY
preserved; no new Google backend credential. Protect key/config with encrypted
backup separately from SQL/media-only promotion backup; offsite inclusion source
exists but this newly uploaded key's recovery is not yet evidenced.

Practical relay check: later send one harmless Shelf Laravel mail to the owner's
actual active Shelf @privaterelay.appleid.com address, verify owner's inbox
receipt plus private envelope/SPF/DKIM evidence; no account/token/data edits.
If no Shelf relay address exists yet, the enabled-gate requirement creates a
bootstrap blocker: prepare a separately approved owner-only native authorization
rehearsal to obtain it without public activation/reader creation. Do not mark
relay-ready true prematurely or substitute an iCloud alias. Phone reset/deletion
and Apple revocation remain later disposable-account acceptance checks.

Documentation-only commit/push ios/foundation. No .env/settings change, gate
activation, migration, deployment, build, Apple upload, provider call, mail send
or purchase. Checkout OFF, pricing-sync and second-phone-restore deferrals,
Android/UI pause, deferred improvements and joint public-launch hold preserved.

## iOS owner setup and private Apple upload destination — 11 October 2026

Owner confirms the Google iOS client was created and its actual
`SHELF_GOOGLE_IOS_CLIENT_ID` saved in Codemagic group `shelf_ios_testflight`.
Owner downloaded the new Sign in with Apple key named **ShelfLogin** privately.
This is separate from the existing App Store Connect upload API key/integration,
which remains unchanged. Owner registered `shelf.services` for Apple email relay
and reports Apple's green SPF check. **Actual relay delivery remains UNVERIFIED**;
provider sign-in and signed-iPhone acceptance are not inferred from setup.

Codex inspected the destination first: `/home/shelf/secrets` was a real directory,
owned by `shelf:shelf`, mode **700**; `/home/shelf/secrets/apple` did not exist.
Created the empty Apple directory, verified **shelf:shelf / 700**, without reading,
replacing or overwriting any key. **Key transfer is pending owner execution**;
no server key filename, Key ID, capability association or key validity is claimed.
The supplied Windows file-picker script preserves the downloaded basename,
uses the existing trusted SSH connection to root@157.250.199.106, restricts the
selected local file ACL, and streams bytes over encrypted SSH without shell text
conversion, key output or staging copies. Server exclusive creation refuses any
existing path; the new file is **shelf:shelf / 600** before writing. It verifies
length/SHA-256 silently, deleting only its own incomplete file on handled failure.
An interrupted connection can leave a private partial file; never overwrite it.

No `.env` change, sign-in activation, migration, deployment, build, Apple upload,
provider request or purchase. No completed Flutter tests repeated. Public receiver checked with synthetic
nonsecret bytes: successful original-name/600 transfer, duplicate refusal with
existing bytes unchanged, and incomplete-transfer cleanup passed. Windows
PowerShell/file picker and actual SSH upload were not executed here. Existing
App Store Connect/signing configuration preserved. Checkout **OFF**, pricing-sync
and second-phone-restore deferrals, Android/UI pause, deferred improvements and
joint Android/iOS public-launch hold retained. Next: owner private transfer, then
separately authorized metadata/permissions/key association checks and later
approved backend rollout, relay-delivery evidence and signed-phone validation.


## iOS functionality phase — Apple/Google sign-in source, 11 October 2026

**Current owner-reported TestFlight status:** **1.0.0 build 2**, App Apple ID
**6821471140**, completed Apple processing, was assigned to **Shelf Owner** internal
group, and was installed/tested by the owner: **“seems good.”** Record this as
**LIMITED PHONE ACCEPTANCE**, not completion of all reading, account/provider,
offline, deletion, purchase or other acceptance checks. It supersedes earlier
uncertainty about build-2 processing/install availability. The new authentication
source below is not in installed build 2. Small Android/iOS improvements are
**DEFERRED UNTIL BEFORE LAUNCH**; only scoped authentication controls change now.

Owner authorized the iOS functionality phase starting with Apple/Google sign-in.
Implemented in the existing isolated **ios/foundation** checkout, reusing shared
email/password, Google SDK/server audience, secure session/offline identity,
Apple bundle/team/entitlement/signing and server-decided per-reader book access.
**Apple:** native AuthenticationServices channel and iOS-only sign-in control;
email scope only, five-minute single-use server challenge with hashed nonce;
server RSA signature/issuer/exact audience/expiry/subject/nonce checks, code
exchange with returned-subject verification, encrypted refresh-token storage;
repeat login by subject even without email. Relay email is retained exactly as
provider-verified apart from existing email casing; no guessing underlying email.
Apple-only password reset uses the existing mailbox-confirmation path. Deletion
revokes Apple authorization before existing journal/reader erasure; provider
failure retains reader/tokens, financial history stays unchanged.

**Google:** existing Flutter/Android flow retained; iOS native-client detection,
client initialization and build-time GIDClientID/reversed callback configuration
added. Missing iOS configuration hides Google instead of opening a broken flow.
Known Google subjects identify existing readers even if provider email changes.
**Neither provider automatically merges/links an existing reader merely because
emails match**; collisions return clear existing-method guidance. Existing Google
links are preserved; a new external-domain Google account still requires explicit
mailbox confirmation. Explicit cross-method linking is not implemented. Picker
cancellation preserves the reader session. No login token confers book ownership,
owner/admin rights or another reader's offline identity.

**Reversible source migration only:** nullable unique **apple_subject_hash**,
**apple_refresh_token** encrypted cast/private field, and **apple_login_challenges**.
Not applied to staging or production; no existing data/schema/settings changed.
Apple endpoint availability requires an explicit enable gate, signing config and
relay-ready gate, default OFF. Tokens/codes/nonces are excluded from flashed input,
provider errors sanitized, no credentials or provider response bodies logged.
Codemagic adds only the optional Google configuration step; all existing signing
verification, internal-only export, ditto extraction, photo-purpose guard and
verified explicit **$CM_BUILD_DIR** artifact/publishing wiring are preserved.
No dependency versions, credentials, build numbers or Android configuration changed.

**Focused verification PASSED:** **43 PHP tests / 386 assertions**, **32 Flutter
checks + 1 staging-identity check**, and Google client/callback plist fixture.
Includes cryptographic invalid-token/nonce checks, expired/replayed challenges,
code-subject mismatch/provider failure, relay repeat login, email collisions,
owner separation, encrypted-token secrecy, deletion failure/success and reversible
schema; existing account and purchase-isolation checks included. Static analysis
has no errors/warnings (existing style infos). Plist/YAML/shell/diff checks passed.
Tests ran through **scripts/run_tests.sh --mobile**, scoped **ios-auth**, synthetic
provider responses/keys, in-memory DB and disposable **/home/shelf/tmp** storage.
Fixture initialization/HTTP-fake selection and stale Library-icon/Dari selectors
were fixed during work. Replaced the isolated checkout's production-vendor symlink
with a private dependency copy/regenerated autoload so verification uses this
source; production dependencies untouched. No native Swift/Xcode compilation,
actual provider success or signed-iPhone sign-in acceptance claimed.

**Remaining blockers:** existing server Google web-client configuration is present;
Google iOS-client value is absent locally, actual dashboard existence unverified.
Apple backend client/team/key/path/enable/relay settings are absent locally; an
appropriate Apple Sign in with Apple key and relay delivery are unverified. Reuse
appropriate existing dashboard objects rather than recreate them. Source work is
complete for this batch, but end-to-end activation requires these values, relay
setup/evidence, and separately approved staged/backed-up backend promotion.
One consolidated owner guide with exact websites/fields/known values and
Create/Save/Register buttons: **[IOS_SIGN_IN.md](IOS_SIGN_IN.md)**. Unknown generated
IDs/key material must come from the owner dashboard, never guessed or exposed.
Apple ongoing account/revocation notifications, explicit linking, final provider
privacy/disclosure review and broader device acceptance remain release work.

**Next phone step:** after setup and separately approved backend promotion,
authorize one signed internal build at a confirmed unused number **greater than 2**,
then owner-only Apple Hide My Email/repeat/cancel/logout, Google choose/repeat/cancel,
email/password, identity/Library/offline isolation and disposable-account relay
reset/deletion/revocation checks. Do not use a real purchased account for deletion.
No cloud build/upload, dashboard/production setting change, backend deploy/migration,
real-provider request, purchase or App Store purchase implementation in this batch.
Commit/push **ios/foundation** only. Checkout **OFF**, pricing-sync and
second-phone-restore deferrals, deferred Android/iOS improvements and the joint
Android/iOS public-launch hold preserved. Before activation rollback is a normal
git revert; after real Apple identities exist, schema removal/identity changes
need a separately approved preservation plan. **Exact implementation commit:**
**32ca4a04929ad065af21d85241c5ca3a749b5007**. The subsequent documentation
commit records this evidence and setup guide without changing implementation.


## Apple ITMS-90683 rejection and photo-purpose correction — 11 October 2026

Owner reports Apple rejected **Shelf 1.0.0 build 1**, App Apple ID **6821471140**:
**ITMS-90683**, **Runner.app/Info.plist** missing
**NSPhotoLibraryUsageDescription**. Apple therefore received build 1; it is not
reusable for the next upload. This supersedes earlier uncertainty about Apple
receipt of build 1, without claiming processing/TestFlight acceptance or an
independently inspected signed artifact.

Inspected the existing avatar/share implementations and locked dependencies:
**AppDelegate.swift** uses **PHPickerViewController**, images only, one selected
image, re-encoded for a private account avatar; it does not request broad library
access or use a camera. **PlatformShareCardOutput** saves generated PNG reading
cards with **gal 2.3.3** (**hasAccess/requestAccess/putImage**, no custom album)
and shares files through **share_plus 13.3.0/UIActivityViewController**.
The installed gal native source contains PhotoKit add-only and read/write/legacy
permission paths; its README documents both photo-purpose keys. This establishes
dependency API usage, not which API caused Apple's static detection.

Added **NSPhotoLibraryUsageDescription**:
"Shelf uses your photo library so you can choose a private account photo and
save reading share cards to Photos."
Retained the accurate **NSPhotoLibraryAddUsageDescription**:
"Save a reading share card to your photos."
No camera, microphone or unrelated permissions, dependency or feature changes.
References: [Apple photo-library purpose key](https://developer.apple.com/documentation/bundleresources/information-property-list/nsphotolibraryusagedescription)
and [gal iOS setup](https://github.com/natsuk4ze/gal#ios-setup).

Added a mandatory **ios-testflight-internal** pre-build plist parse/check before
dependency resolution: both photo keys must be strings with nonempty trimmed
explanations. Missing, whitespace-only and non-string values fail closed.
Internal-only export, ditto extraction, all signing checks, explicit
**$CM_BUILD_DIR/shelf-ios-artifacts** collection patterns and publishing preserved.
Focused source-plist/YAML/shell/diff inspection and positive/negative check cases
passed; completed Flutter tests were not repeated. No build/upload, dashboard
variable/credential change, deployment or Android change.

**Next upload instructions (preparation only):** in Codemagic refresh YAML for
**ios/foundation**, select **ios-testflight-internal** at this correction commit,
and set **SHELF_IOS_BUILD_NUMBER=2** in **shelf_ios_testflight** yourself. Keep
version **1.0.0**, existing API/signing integration and internal-only export.
After separate run authorization, start one manual signed run; confirm the
pre-build photo check, exported signing verification and artifact collection
pass, then inspect Apple processing for **6821471140 / 1.0.0 (2)**. Do not retry
build 1. No variable was changed or run started by Codex. Actual acceptance and
photo/avatar/share behavior on iPhone remain unverified.
Checkout OFF, pricing-sync and second-phone-restore deferrals, Android/UI pause
and joint Android/iOS public-launch hold preserved. Commit/push **ios/foundation**
only. Rollback is an ordinary isolated git revert; no server/data changes.


## Completed Codemagic collection diagnostic — 11 October 2026

**Owner-reported completed diagnostic at source commit 2d0f0dc.** Both diagnostic
scripts completed in **/Users/builder/clone/mobile**. Both probe files existed,
confirmed by both logs. Publishing produced **shelf_1_artifacts.zip**; owner
inspection of that ZIP found only **explicit-path-probe.txt**.
**clone-relative-probe.txt was absent**.

This confirms that the explicit **$CM_BUILD_DIR** artifact pattern collected its
file in this diagnostic, while the tested relative pattern did not. It supports
the existing explicit-path correction **7967746**, but does not prove every
detail of the old run's failure, original verification execution, old-IPA
recoverability, IPA-specific collection, signing or Apple receipt.

Read-only inspection at **2d0f0dc** confirms **ios-testflight-internal** already
uses the successful explicit-path format:
**$CM_BUILD_DIR/shelf-ios-artifacts/*.ipa**,
**$CM_BUILD_DIR/shelf-ios-artifacts/evidence/*** and
**$CM_BUILD_DIR/shelf-ios-artifacts/collection-manifest.json**.
Its mandatory staging step copies exactly one nonempty verified IPA, checks
identical size/SHA-256, and requires the verification evidence before collection.
The **publishing.app_store_connect** block is a sibling of **artifacts** in that
same workflow, with **auth: integration**: the collected staged IPA is the IPA
provided to publishing. This confirms configuration wiring, not an observed IPA
upload. **submit_to_testflight: false** and **submit_to_app_store: false** remain;
internal-only export (**testFlightInternalTestingOnly: true**), disabled automatic
version/build-number management, **ditto -x -k** extraction and all mandatory
bundle/version/team/entitlement/codesign checks are preserved. No configuration
change was needed or made.

**Readiness:** artifact-path preparation is ready for one separately authorized
manual signed **ios-testflight-internal** Codemagic run on **ios/foundation**.
Use an explicitly confirmed unused positive build number; the old saved number
**1** is not assumed reusable. Actual signing, collected IPA, Apple upload/
processing and internal TestFlight/device acceptance remain unverified.
No tests repeated, diagnostic rerun, build/upload started, credentials/build
numbers changed or deployment performed. Documentation-only commit/push to
**ios/foundation**; checkout **OFF**, pricing-sync and second-phone-restore
deferrals and the joint Android/iOS public-launch hold remain unchanged.
Rollback is an ordinary revert of this record change; no runtime/data change.

## Manual text-only Codemagic collection diagnostic prepared — 11 October 2026

Owner authorized the smallest collection diagnostic, not another Shelf build or
support referral. Reused supplied run **6acb2794546ce6236ced6c44 / 456ec75** logs:
IPA export **31.1MB / 1.0.0 / build 1 / services.shelf.app**; verification panel
only **0**; complete Publishing output only **Gathering artifacts / No artifacts
were found**. No same-log request. Actual service-used configuration/verification
execution/collector paths remain unobserved; metadata request HTTP 401 persists.
**7967746** is explicit staging/diagnostics, NOT a confirmed root-cause fix.
Prior suggestion of full rebuild/support referral is superseded by this narrow
text-only diagnostic. No confirmed cause or old-IPA recovery claimed.

Added separate workflow ID **artifact-collection-diagnostic**, display name
**Shelf artifact collection diagnostic — text only** in codemagic.yaml.
**working_directory: mobile**, existing **mac_mini_m2**, max duration **5 minutes**.
Only shell/file operations; no environment groups, Apple integration/signing,
Flutter/Xcode/software selection/install, compilation, publishing, caching,
network upload, paid-service activation or automatic triggering. Hosted machine
has preinstalled tools; diagnostic invokes no SDK or app build. No Apple
credentials or build numbers are required or changed.

Two scripts visibly emit **SHELF_ARTIFACT_DIAGNOSTIC_STEP_1_STARTED/COMPLETED** and
**SHELF_ARTIFACT_DIAGNOSTIC_STEP_2_STARTED/COMPLETED**. Both print physical working
directory and check it equals **$CM_BUILD_DIR/mobile**. First also prints clone
directory and source SHA, rejects a preexisting probe directory and creates two
harmless plain-text probes at
**mobile/build/ios/ipa/shelf-artifact-diagnostic/**. Both check nonempty files.
Second prints original and resolved collection paths, file presence/ls/content,
and appends its execution marker to each file. No environment dump or secret
content. Diagnostic probes are isolated from .ipa/app/evidence patterns.

Collection patterns are distinct so results are attributable (no overlap):
**mobile/build/ios/ipa/shelf-artifact-diagnostic/clone-relative-*.txt** and
**$CM_BUILD_DIR/mobile/build/ios/ipa/shelf-artifact-diagnostic/explicit-path-*.txt**.
Both files are in the same output directory; only collector path form and filename
differ. Codemagic collects ordinary text in its downloadable artifacts ZIP;
inspect filenames/content there, not only separate artifact cards.

Local focused validation passed: YAML parse, diagnostic-only new key, original
YAML retained as exact byte prefix and both existing workflows parsed unchanged,
absence of environment/integration/publishing/triggers, shell syntax; sequential
scripts in isolated /home/shelf/tmp fixture show all four execution markers,
correct directories/files, each pattern matches exactly its intended file.
Incorrect working directory and missing file fail; temporary fixtures cleaned.
No native/Flutter tests or provider run repeated. git diff --check passed.

Later authorized diagnostic outcomes:
- Both probe files collected: both path forms work for text in that new run.
- Only one collected with both scripts/files confirmed: isolates path-form
  discrepancy for that run; does not retroactively prove the old IPA cause.
- Neither collected despite both completed markers/existence: reproduces a
  collection/configuration issue independent of Flutter/export/signing.
- Missing completion marker: execution failed/was skipped/wrong effective
  configuration; distinguish from collector failure using its named step.
No result proves original verification ran, old IPA recoverability, IPA-specific
handling, signing validity, Apple receipt, login/purchases or TestFlight success.
No dummy IPA, no Apple publishing. A harmless diagnostic is sufficient to test
path collection; another full Shelf compilation is not necessary for this test.

**Next:** under separate diagnostic-run authorization only, choose
**ios/foundation → artifact-collection-diagnostic** in Codemagic and run once;
inspect both step markers and the artifacts ZIP for the two named text probes.
Preparation does NOT start it. No support referral, full build or upload requested.
Commit/push ios/foundation only, existing app workflows/GitHub/mobile untouched.
Checkout OFF, pricing-sync and second-phone-restore deferrals and joint
public-launch hold preserved. Rollback is ordinary isolated-branch revert of
only this diagnostic addition/record. No deployment, Android or credential change.

## Explicit Codemagic artifact collection correction — 11 October 2026

New OWNER-PROVIDED evidence for **6acb2794546ce6236ced6c44 / 456ec75**:
Flutter log **Built IPA to build/ios/ipa (31.1MB)**, version **1.0.0**, build
**1**, bundle **services.shelf.app**. Verification panel shows only **0** after
hiding the command, no diagnostic output available. Publishing still reports
**No artifacts were found**. This supersedes absence of export-log evidence:
Flutter-reported IPA export is now evidenced, but independent file inspection,
verification execution details, collection/upload/Apple receipt remain unverified.
A panel value 0 alone is not proof of the full expected verification script.

Parsed exact committed **git show 456ec75:codemagic.yaml** with YAML parser:
**working_directory, scripts, artifacts, publishing** are sibling workflow keys;
artifacts is a list, correctly under **ios-testflight-internal**, not nested in a
script. Existing IPA pattern **mobile/build/ios/ipa/*.ipa**, evidence and archive
patterns match expected clone-root output. Current official documentation checked
again: scripts use workflow **mobile** directory; artifact patterns are relative
to clone directory, with absolute paths/environment-variable patterns supported.
Thus no confirmed doubled-mobile/indentation/path-resolution cause. Source YAML
is inspected; actual service-evaluated YAML and collector runtime not accessible.
Root cause of this run's collector miss remains **UNCONFIRMED**. No claim that
the previous ZIP extraction defect explains the collector message.

Prepared reliable explicit collection in **codemagic.yaml**:
- Build and verification steps require **CM_BUILD_DIR**, explicitly cd to
  **$CM_BUILD_DIR/mobile** and print working directory.
- Mandatory existing ZIP identity/version/CRC, strict codesign and team/app/debug/
  Apple-entitlement checks remain unchanged. **ditto -x -k** fix from **72e29ca**
  retained. No signing verification weakened or ignored.
- New final script requires exactly one nonempty IPA at
  **$CM_BUILD_DIR/mobile/build/ios/ipa**, and all five expected verification
  evidence files. Privacy-manifest listing may legitimately be empty; other
  evidence must be nonempty. Missing files fail with explicit paths.
- After successful mandatory verification, copies only that IPA and approved
  non-secret evidence to **$CM_BUILD_DIR/shelf-ios-artifacts**. Rejects stale
  staging directory; confirms copied IPA byte count/SHA-256. Prints source/output
  paths, size/hash/source commit and success. Writes collection-manifest.json.
- Artifact patterns now absolute **$CM_BUILD_DIR/shelf-ios-artifacts/*.ipa**,
  **.../evidence/*** and **.../collection-manifest.json**; optional dSYMs retain
  native archive location with explicit **$CM_BUILD_DIR/mobile/...** prefix.
  Avoids collecting decoded app/profile/private signing files.
- Publishing/integration/signing refs/groups/toolchain/build flags/internal-only
  export/manual assignment and automatic-trigger absence unchanged. Unsigned
  Codemagic workflow, GitHub workflows/helper/main registration/mobile unchanged.

Focused checks passed: parsed before/after comparison restricted to two explicit
working-directory additions, new staging script and artifact patterns; every
shell block and staging Python syntax; preserved signing checks/extraction.
Synthetic filesystem checks under /home/shelf/tmp: staging success and every
required collector pattern matches; missing IPA, zero-length IPA, missing
verification evidence and stale output directory fail closed; copied bytes match.
Fixtures cleaned. These are path/staging checks, not a native artifact/signing
or provider collection test. git diff --check passed. No completed Flutter tests,
native build/upload or production check repeated.

Existing run recovery remains unresolved: prior API request HTTP 401/no available
Codemagic authentication still applies. Owner must inspect existing run Artifacts
or any still-active remote session/support recovery before recommending another
build. No build recommended/launched here. To establish collector root cause,
obtain service-used YAML/workflow and full artifact-gathering searched-path log
for this exact run; a hidden command/green badge does not substitute for it.
Future separately authorized run can use explicit staging diagnostics, but no
run/upload authorized by this correction. Build 1 cannot be presumed unused;
Codemagic run completed, not confirmed cancelled before upload.

Master Record updated, commit/push **ios/foundation** only. No main/source merge,
Android/production/payment/credential/paid activation. Checkout OFF, pricing-sync
and second-phone-restore deferrals and joint public-launch hold preserved.
Rollback: normal isolated-branch revert of the narrow YAML correction.
Reference checked: https://docs.codemagic.io/yaml-basic-configuration/yaml-getting-started/

## Codemagic green run with no collected artifacts — 11 October 2026

Owner reports signed run **6acb2794546ce6236ced6c44**, source **456ec75**,
finished green in **8m52s**. Publishing reports **Gathering artifacts / No
artifacts were found**. Status/duration are OWNER-CONFIRMED; queue delay is
separate and unspecified. This is NOT evidence of successful signed IPA export,
artifact collection, upload or Apple processing. Earlier queued status is now
superseded for this reported attempt; it finished, not confirmed cancelled.
Do not claim number 1 reusable or Codemagic cancellation before upload.

Inspected committed **ios-testflight-internal** configuration: workflow working
directory **mobile**; each script starts there; the pod step's **cd ios** is
local to that script. Flutter **build ipa** output expected at
**$CM_BUILD_DIR/mobile/build/ios/ipa/*.ipa**. Verification uses relative
**build/ios/ipa/*.ipa** from mobile, requires exactly one IPA, checks ZIP/bundle/
version and signatures, then creates **mobile/build/ios-testflight-evidence**.
Codemagic documentation states artifact patterns are relative to clone directory,
NOT workflow working_directory. Therefore **mobile/build/ios/ipa/*.ipa** and
existing evidence/archive paths are correct; no doubled-mobile path defect was
confirmed. No speculative artifact-path/publishing changes made.

Publishing **auth: integration**, integration **shelf_app_store_connect**, flags
**submit_to_testflight=false / submit_to_app_store=false** are correct for upload
without beta/store review; false does not disable binary upload if an IPA is
collected. Internal-only export, signing references/groups, checkout OFF and
manual tester assignment retained. No publishing change or review enabled.

**Confirmed independent verification defect fixed:** Python **ZipFile.extractall**
writes ordinary files without restoring ZIP executable modes/framework symlinks
before codesign. Replaced extraction with macOS **ditto -x -k**, preserving native
bundle metadata, after existing one-IPA/identity/CRC checks. Inspected Python
extraction implementation confirms missing mode restoration. This defect could
break strict signature verification; it is NOT established as the explanation
for green status/no artifacts. The existing mandatory IPA verification should
fail on missing IPA; successful execution of the committed step would also leave
collectible evidence files. Actual run YAML/workflow/step execution and collector
behavior must be reconciled from logs before a root cause can be concluded.

**Exact access limitation:** no Codemagic connector or Codemagic token environment
available to this agent. Read-only unauthenticated GET
https://api.codemagic.io/builds/6acb2794546ce6236ced6c44 returned **HTTP 401**
after network-enabled retry. Build metadata/logs/artifact links and runner files
cannot be inspected through available authenticated account/API access. No secret
requested/read/printed. Recovery is not proven impossible or successful.

**Next evidence/recovery check BEFORE any rebuild recommendation:** owner opens
https://codemagic.io/ → existing Shelf run **6acb2794546ce6236ced6c44**.
Confirm selected workflow **ios-testflight-internal** and actual source/YAML.
Open full **Build signed internal TestFlight IPA with checkout OFF** log: include
archive/export tail, any export error and actual **Built ... .ipa** location.
Open full **Verify exported signing identity and preserve native dependency
evidence** log: confirm it existed, executed (not skipped) and exit status.
Open **Publishing → Gathering artifacts**, including searched paths/warnings and
any App Store upload log. Share only these non-secret logs, not environment,
private keys/passwords, signed download URLs or integration/signing-step details.
These are the exact required steps; build green badge alone is insufficient.

Check the same run's **Artifacts** list for existing IPA or artifact ZIP first;
download an existing IPA privately if listed (no build/upload). The documented
Builds API artifact list/download can also recover an already stored artifact
with owner authentication; lack of artifacts means no normal artifact-download
link, not proof no IPA ever existed. If an already enabled/connected remote debug
session is still available for this same run, inspect only
**$CM_BUILD_DIR/mobile/build/ios/ipa** and
**$CM_BUILD_DIR/mobile/build/ios/archive** and copy existing output privately,
without rebuilding, uploading or printing environment. Codemagic requires remote
access enabled before the run; session availability is time-limited (documentation
states 10-minute post-finish window). Cannot enable recovery retrospectively by
starting another run. If no stored artifact/live session, ask Codemagic support
through the owner account whether this run's output can be recovered; no support
message sent by agent. Archive alone is not a proven exported IPA. Recovery,
actual export and Apple receipt remain unresolved; no rebuild recommended here.

Focused checks passed: YAML parse, shell/inline-Python syntax, exact parsed
comparison allowing only verification extraction change, existing unsigned
workflow/all other signed scripts/environment/artifact/publishing settings
unchanged, root-relative path resolution, git diff --check. No completed Flutter
suite/native compilation or signed run repeated. GitHub workflows/helper/main
registration and unrelated source preserved. Commit/push ios/foundation only.
No build/upload/production/Android/payment/paid activation. Checkout OFF,
pricing-sync and second-phone-restore deferrals and joint public-launch hold
preserved. Rollback is a normal isolated-branch revert of this narrow change.

References checked:
https://docs.codemagic.io/yaml-basic-configuration/yaml-getting-started/
https://docs.codemagic.io/yaml-publishing/app-store-connect/
https://docs.codemagic.io/rest-api/builds/
https://docs.codemagic.io/rest-api/artifacts/
https://docs.codemagic.io/troubleshooting/accessing-builder-machine-via-ssh/

## GitHub signed workflow registered on main — 11 October 2026

Owner confirms all six GitHub signing repository secrets are saved. This is
**OWNER-CONFIRMED**, not agent inspection of secret values or credential validity.
No values were read/printed; actual signing/upload remain unverified.

Owner authorized workflow-only main registration in an isolated checkout.
Fetched main **72cabf07a953ff41b2191a3b67f7d18accdd6055** and ios/foundation
**9e013e6af172b6d6ed8999b5ac6edf1ca5da7922**. Main had no existing workflow
under .github/workflows. Created isolated checkout
**/home/shelf/tmp/shelf-ios-workflow-registration**; copied only
**.github/workflows/ios-testflight.yml** byte-for-byte from ios/foundation.
Committed/pushed main registration **3188020**. Parent-to-commit diff contains
exactly that one added file; no other main file, merge or default-branch change.
Existing iOS draft PR remains unmerged; production checkout untouched.

Focused checks: YAML parsed; sole event **workflow_dispatch**, permissions
contents: read, signed job requires refs/heads/ios/foundation. No push, pull_request
or pull_request_target trigger. Registration push cannot trigger this workflow.
Exact file equality, git diff --check, one-file commit scope and clean isolated
checkout confirmed. No build dispatch, upload, native compilation or app tests.

**Exact source_sha for a later authorized signed build:**
**9e013e6af172b6d6ed8999b5ac6edf1ca5da7922** on **ios/foundation**.
This immutable preparation commit contains both workflow and signing helper;
main's registration commit must not be used as app source. Select workflow branch
**ios/foundation** in Actions. Credential validity/Apple processing/phone checks
remain unverified. Build number has no default: number 1 still requires confirmed
Codemagic cancellation BEFORE upload and confirmation Apple has not received 1.
Neither cancellation nor Apple number state is newly confirmed in this task.

Default-branch file prerequisite is now complete and secrets are owner-confirmed
saved; earlier preparation/guide statements of pending registration are historical.
Next: reconcile Codemagic/Apple build-number state and obtain separate build
authorization. No build/upload/deployment/Android or paid activation performed.
Checkout OFF, pricing-sync and second-phone-restore deferrals and joint
public-launch hold preserved. Rollback: ordinary revert of the one-file main
registration; no application/data/signing-identity change.

## Primary signed GitHub iOS route preparation — 11 October 2026

Owner switched primary signed iOS/TestFlight route to GitHub because the signed
Codemagic attempt remains queued. Preparation authorized only: existing isolated
**ios/foundation**, initial **754ded70f6415ea3e515675c4f11c1ff599e5262**;
workflow/helper/guide/records/commit/push. Codemagic YAML and its queued attempt
left unchanged; no cancellation is assumed or performed.

**Passed GitHub unsigned evidence reused, not repeated:** public GitHub API
GET actions/runs status=success and jobs records **run 38102154478**, workflow
**Shelf iOS unsigned foundation**, source **93ec31262c0d8fffece1ad76ae9084c829050ba4**,
conclusion **success**, job **114360123124**, label **macos-15**. Job started
**2026-10-11 01:32:13 UTC**, completed **01:40:08 UTC**. This is independent
run/job metadata, not downloaded native logs or an exact resolved Xcode-version
claim. Build link: https://github.com/ajmalaand-ship-it/shelf/actions/runs/38102154478
Original owner-confirmed Codemagic unsigned success/durations remain preserved.

Prepared **.github/workflows/ios-testflight.yml**, name **Shelf signed iOS internal
TestFlight**, plus **scripts/ios/github_testflight.py**. Manual **workflow_dispatch
only**: no ordinary push/PR/pull_request_target trigger, least **contents: read**,
serialized signed jobs without cancelling another run. Uses passed **macos-15**,
Flutter **3.47.2**, checkout **11bd719...** and artifact **ea165f8...** pins,
same locked pub/config-only/pod commands. No unsigned compilation or completed
app tests repeated. Hosted image version can change; exact native version remains
unobserved. Owner selects a full immutable SHA; checkout equality and ancestry in
**origin/ios/foundation** enforced. Only dispatch from **ios/foundation** can run
signed job. Main/synthetic merged app code is never built. Runner-only signing
adaptation changes three Runner configurations, leaving RunnerTests/source assets
untouched; no persistent mobile source changes.

**Explicit workflow_dispatch resolution:** GitHub requires the workflow file on
the default branch. Current prepared file is pushed only to **ios/foundation**.
The complete guide supplies a later **workflow-file-only registration on main**:
copy this exact .github/workflows/ios-testflight.yml through the GitHub browser,
no helper/app/source merge, no default-branch change. Then Actions **Run workflow**
can select **ios/foundation** and the exact source_sha input. Registration is
**NOT PERFORMED** in this task; default-branch workflow absence is a remaining
execution prerequisite, not bypassed by CLI/API assertions. Main protection must
be respected; if it blocks direct one-file registration, report it for a separate
workflow-only task, never merge the iOS draft PR or relax protection. No PR merge
or main push in preparation. The registration file has no automatic triggers.

**Existing signing reused through six exact GitHub repository secrets:**
SHELF_IOS_DISTRIBUTION_P12_BASE64, SHELF_IOS_DISTRIBUTION_P12_PASSWORD,
SHELF_IOS_APP_STORE_PROFILE_BASE64, SHELF_APP_STORE_CONNECT_KEY_ID,
SHELF_APP_STORE_CONNECT_ISSUER_ID, SHELF_APP_STORE_CONNECT_PRIVATE_KEY.
No new Apple certificate/profile/API key or account. Existing Codemagic setup is
owner-confirmed; transfer to GitHub and validity remain unverified here.
Signing helper uses protected temporary files/keychain, validates explicit App
Store profile/team/expiry/certificate/private identity and existing Apple
entitlement. Signs **services.shelf.app / YSLWJQDH8B**, production API
**https://shelf.services/api/**, both purchase flags false, marketing **1.0.0**,
explicit build number. Manual export **testFlightInternalTestingOnly=true**,
manageAppVersionAndBuildNumber=false. Before upload: IPA CRC, strict signatures,
package/version/team/debug/Apple entitlements, embedded profile UUID and signer
certificate matching supplied identity; Apple binary validation. No beta review,
public release or tester assignment calls; owner assigns internal testers later.
No private files/profile/keys/passwords in artifacts; source SHA/signature/hash,
export options, IPA and native Podfile.lock evidence retained seven days.

**Build-number safety:** no default and no automatic reuse of saved Codemagic 1.
Requires owner confirmation Apple has not received the selected number. **1 is
blocked unless Codemagic cancellation BEFORE upload is explicitly confirmed**.
Cancellation/Apple received-number state remain **UNVERIFIED**, not assumed.
Helper queries the existing Shelf app and all received builds with Apple team-key
JWT, before compilation and again immediately before upload; duplicate/denied/
unavailable/ambiguous evidence fails closed. Serializes GitHub jobs, not Codemagic;
owner must reconcile that competing attempt before choosing 1. Upload error or
missing processed listing does not prove no received upload. No queued build
cancelled, credentials/API queries with private keys or transactions here.

**Cleanup:** finally/handled signal and separate always() retry restore original
keychain search list, remove this run's installed profiles from both supported
Xcode locations and private keychain/p12/p8/decoded workspace. Secret values are
removed from child environments; private subprocess arguments/output withheld.
Cleanup failure fails job/preserves original failure; GitHub-hosted VM disposal is
additional protection for abrupt runner loss. No unrelated profile/keychain deleted.

**Focused preparation checks passed:** YAML parse/manual-only inputs/false-default
cancellation/no default build number, source/action/runner/permissions/concurrency/
cleanup gates, Python and every shell block syntax, internal-only export/false
purchase flags and validate-before-upload construction. In-memory adaptation of
current pbxproj proves exactly three Runner configurations become manual and
RunnerTests remain automatic, with no actual native source mutation. Existing
mobile, unsigned GitHub workflow and Codemagic bytes unchanged; git diff --check.
No credential creation, native build, upload, app tests or production check repeated.
Actual signed archive/export/validation/upload/Apple processing/iPhone behavior
remain unverified; Apple/Google login and App Store purchases still unfinished.

**Single complete Windows/browser setup guide:** docs/IOS_FOUNDATION.md →
“Complete Windows/browser setup guide for the GitHub primary route”. Exact URLs,
six secret names, clipboard-only PowerShell Base64 for existing .p12/profile, raw
.p8 PEM, password/ID browser fields and every Add/Update/Commit button; explicit
workflow-only registration, build-number proof and later manual dispatch. Secrets
must never be pasted into chat, git, logs or server configuration. Setup/registration
and later build authorization remain separate from this preparation task.
Current official GitHub manual-dispatch/secrets/signing-cleanup and Apple API/
upload/internal-testing references are linked there. Prior account usage/Actions
permission limitations preserved; no paid usage/capacity assumed or activated.

**Next:** owner credential transfer and workflow-only default-branch registration
using that single guide, reconcile Codemagic cancellation/Apple received builds,
then separately authorize deliberate signed dispatch. No build/upload/cancellation,
new signing credentials, paid activation, main merge/deployment/Android changes.
Checkout OFF, pricing-sync DEFERRED/disabled, second-phone restore owner-deferred/
not passed/not a new blocker, financial expiry unimplemented and joint public-launch
hold unchanged. Rollback: normal isolated-branch revert; later registration rollback
removes only the one workflow file, not application code/data/signing identities.

## Internal TestFlight Codemagic validation correction — 10 October 2026

Owner reports Codemagic validation error at
**ios-testflight-internal → publishing → app_store_connect → release_type**:
“App Store review submission is required for release_type.” Owner authorized
removing that inapplicable setting only, focused YAML/diff validation, records,
commit and push on existing **ios/foundation**, initially **260a32c**.

Removed **release_type: MANUAL** from the internal workflow. Current official
Codemagic publishing documentation places release_type under App Store release
configuration, separately from internal-only TestFlight export. Retained
**auth: integration**, **submit_to_testflight=false** (no beta-review submission),
**submit_to_app_store=false**, internal-only export, no automatic trigger or
beta_groups, and manual owner tester assignment. No store review enabled.
All signing references, environment groups, build commands/flags, unsigned
workflow, GitHub fallback and mobile source unchanged. Rollback: ordinary
isolated-branch revert; no live rollback or database change.
Reference checked: https://docs.codemagic.io/yaml-publishing/app-store-connect/

**Owner-confirmed setup:** required Apple/Codemagic signing setup completed;
**SHELF_IOS_BUILD_NUMBER=1** saved in **shelf_ios_testflight**. This is owner
confirmation, not an independently inspected credential, certificate/profile or
unused-build-number check. Expected integration **shelf_app_store_connect**,
certificate **shelf_ios_distribution**, profile **shelf_ios_app_store** and groups
**shelf_ios_foundation / shelf_ios_testflight** retained. No secret printed/read
or committed. Actual signing, signed archive/export, upload/Apple processing and
iPhone acceptance remain **UNVERIFIED**. Existing unsigned success evidence reused.
Apple/Google login and App Store purchases remain unfinished.

Focused validation: YAML parse and exact parsed before/after comparison removing
only release_type, publishing/trigger/internal-export/false-flag assertions,
unchanged mobile/fallback and git diff --check passed. Provider-side revalidation
awaits owner refresh of branch YAML; no remote validation success invented.
No Flutter tests or unsigned compilation repeated; no build/upload/deployment,
Android change, credential creation, paid activation, transaction or PR merge.
Checkout OFF, pricing-sync DEFERRED/disabled, second-phone restore owner-deferred/
not passed/not a new blocker and joint public-launch hold preserved.

**Next:** refresh Codemagic YAML on ios/foundation and confirm validation clears;
do not start the signed build/upload without separate authorization. This
supersedes the earlier credential-setup next action, not its setup guide.

## Successful unsigned iOS build and signed internal TestFlight preparation — 10 October 2026

**UNSIGNED NATIVE COMPILATION: OWNER-CONFIRMED SUCCESS.** Owner supplied Codemagic
build **6acad0a1546ce6236ced5f95**, status **finished / green success**, source
**869ebde**, **ios/foundation**, **Mac mini M2**. Execution duration **4m25s**;
unsigned iOS compilation **2m03s**, included in that execution duration. Preceding
queue delay is separate, duration not supplied; never count it as execution.
No successful compilation or passed shared/source suite repeated. Logs/artifact/
Podfile.lock/exact resolved Xcode version have not been independently retrieved.
This verifies compilation by owner evidence; it does not verify signing, TestFlight
upload/processing, Apple/Google login, purchases or any iPhone behavior.

**Apple setup retained, owner-confirmed:** active Individual membership,
**Team YSLWJQDH8B**, explicit **services.shelf.app**, existing **Shelf - شیلف**
App Store Connect record. No account, app or identifier recreated.

Owner authorized preparation only in existing isolated checkout, initially
**93ec31262c0d8fffece1ad76ae9084c829050ba4**, with records/commit/push to
**ios/foundation**. Added separate **ios-testflight-internal** Codemagic workflow.
Original unsigned workflow parsed mapping unchanged; GitHub fallback and mobile
source unchanged. Same M2/Flutter 3.47.2/Xcode latest/CocoaPods default and locked
pub resolution. No trigger: pushes do not launch either Codemagic workflow.
Draft PR preserved/unmerged; default branch/main and production untouched.

Signed workflow references protected integration **shelf_app_store_connect**,
stored certificate **shelf_ios_distribution**, stored App Store profile
**shelf_ios_app_store**. It never creates signing credentials during execution.
Requires existing **shelf_ios_foundation** API variable exactly
**https://shelf.services/api/** and an explicit positive unused iOS-only
**SHELF_IOS_BUILD_NUMBER** in **shelf_ios_testflight**. Marketing version **1.0.0**
via build arguments; no source version bump/Android change. Both purchase flags
remain false. It configures **testFlightInternalTestingOnly=true**, prevents Xcode
from changing the supplied version/build number, and builds signed IPA. Prior to
publishing, checks export method/team/internal flag, IPA CRC/package/version,
code signature and team/application/Apple entitlement/get-task-allow. Records
source SHA, Podfile.lock, export options, entitlements and privacy-manifest paths.
No private signing file is an artifact.

A later separately authorized manual run uploads through **auth: integration**.
**submit_to_testflight=false** avoids beta-review submission;
**submit_to_app_store=false**, **release_type=MANUAL**, internal-only export and
no beta_groups prevent automatic external/public distribution or tester assignment.
Owner later assigns the processed build to an owner-only internal group manually.
Neither compilation-green nor publishing-green proves installation/acceptance.

**Focused preparation validation passed:** YAML parse, exact unsigned before/after
mapping and toolchain comparison, manual-only trigger/false flags/internal-only
export/no-credential-creation/publishing invariants, bash syntax for every new step,
embedded Python syntax, mobile/fallback unchanged and git diff --check. Existing
source verifier's no-publishing assertion now scopes to the unsigned workflow;
no weakened unsigned requirement. No broad PHP/Flutter tests/native build repeated.
These are static checks; signed archive/export/upload validation has not run.

**Source inspection for limited first iPhone test:** no additional compile-source
blocker established for Store/search/free samples/reader/native UX, supported only
by the owner-confirmed compilation. Actual signing inputs and Apple processing
acceptance are unverified prerequisites. Apple login not implemented. Google
provider supplies only serverClientId; no iOS client/callback URL configuration
present; shared UI may display Google under server config. Do not exercise Google
in this limited test; it remains unfinished, not passed. RevenueCat accepts goog_
keys and backend verification remains Play-specific: App Store products/Apple
provider credentials/receipt verification/restore/refund are unfinished. Checkout
OFF is preserved, no Apple purchase/real-money transaction authorized.
Email/password/Library, Keychain isolation/sign-out/offline, photos/save/share/audio,
RTL/fonts/accessibility and reading require actual iPhone evidence. Native dependency
lock/privacy review and truthful Apple export-compliance answers remain pending;
no guessed exemption or privacy declaration added. Missing Compliance/Apple upload
errors must be recorded separately if they arise in a later authorized run.

**Complete owner setup guide:** docs/IOS_FOUNDATION.md, section "Owner signing
setup guide", checked against current official sources 10 October. Exact websites,
fields, button labels and success indicators for API access/key, Codemagic
integration, stored distribution certificate, App Store provisioning profile,
variable groups/build number and later owner-only installation. API access request:
Account Holder; team key generation: Account Holder/Admin; Codemagic publishing
key minimum **App Manager**; profile website minimum Admin (existing Individual
owner is Account Holder); Codemagic identity management requires team admin.
No password/private key requested in chat or placed in git.

Official sources:
- https://developer.apple.com/help/app-store-connect/get-started/app-store-connect-api/
- https://developer.apple.com/help/account/provisioning-profiles/create-an-app-store-provisioning-profile/
- https://developer.apple.com/help/app-store-connect/test-a-beta-version/add-internal-testers
- https://developer.apple.com/help/app-store-connect/manage-app-information/overview-of-export-compliance/
- https://docs.codemagic.io/yaml-code-signing/signing-ios/
- https://docs.codemagic.io/yaml-quick-start/first-signed-build/
- https://docs.codemagic.io/yaml-quick-start/building-a-flutter-app/
- https://docs.codemagic.io/yaml-publishing/app-store-connect/

**Stopping point / next:** protected Apple signing integration, distribution
certificate and App Store profile must be set up by the owner in the existing
accounts under a later separately authorized credential step, following that
single guide; then verify saved references/API group/unused build number before
separately authorizing the manual signed build/upload. No credential created,
signed build started, TestFlight upload/tester invite/public submission, paid
activation, production deployment, Android change or transaction in this task.
Checkout OFF, pricing-sync DEFERRED/disabled, second-phone restore owner-deferred/
not passed/not a new blocker, financial expiry unimplemented and joint public-launch
hold retained. Rollback is a normal isolated-branch revert, no live rollback.
Prior fallback account-access limitations/visibility evidence remain unchanged.

## GitHub Actions unsigned iOS fallback preparation — 10 October 2026

Owner authorized continuing the existing isolated ios/foundation checkout,
preparing and validating only missing fallback work, account/API inspection,
trigger resolution and records. Initial local/remote branch was 869ebde;
no GitHub workflow existed. Added .github/workflows/ios-foundation.yml only;
existing mobile source and codemagic.yaml are byte-unchanged. Queued Codemagic
build left unchanged; no cloud build launched or cancelled by this task.

Workflow uses macos-15, existing Flutter 3.47.2, locked pub resolution,
config-only no-codesign generation, pod install and exactly the existing unsigned
release compilation command/version. Approved production API build setting
https://shelf.services/api/ and SHELF_TEST_MODE=false /
SHELF_INTERNAL_TEST_PURCHASES=false retained. No secrets/signing/publishing,
IPA export or store upload. Read-only contents token, no persisted checkout
credentials; unsigned app archive and Podfile.lock/source-SHA evidence retained
seven days. Actual Xcode/CocoaPods/toolchain compatibility remains UNVERIFIED.
No passed source/shared tests repeated. Static YAML/trigger/permissions/API/command
comparison, each run step's bash syntax, unchanged mobile/Codemagic and diff
whitespace checks passed. No production/Android/database changes.

Trigger resolution: workflow_dispatch requires a default-branch workflow.
Instead use pull_request types=[opened], base main, guarded to a draft PR from
this same repository's ios/foundation only. Explicitly check out PR head.sha,
never the synthetic merged tree. No push, synchronize, schedule or dispatch
trigger. Creation of the draft PR is the deliberate build action, not a merge;
subsequent pushes do not launch it. Default branch unchanged; remote main
72cabf0 is an ancestor of initial iOS HEAD. No outdated-main merge performed.
Official requirements and conflict caveat:
https://docs.github.com/en/actions/reference/workflows-and-actions/events-that-trigger-workflows
https://docs.github.com/en/actions/how-tos/manage-workflow-runs/manually-run-a-workflow

Authentication/access: gh binary absent, no GH_TOKEN/GITHUB_TOKEN environment,
no ~/.config/gh/hosts.yml, no configured git credential helper. Existing SSH
repository access passed with ssh -F /dev/null; ordinary SSH system configuration
failed on /etc/ssh/ssh_config.d/50-redhat.conf permissions, so existing documented
isolated SSH invocation was used without modifying system files/keys.
Unauthenticated repository metadata GET passed HTTP 200: default_branch=main,
private=false, visibility=public, permissions absent. This current API visibility
contradicts older private-repository records; no visibility/settings change made.
GET /repos/ajmalaand-ship-it/shelf/actions/permissions returned HTTP 401;
GET /users/ajmalaand-ship-it/settings/billing/usage returned HTTP 401.
No accessible authenticated GitHub API/account connector is available here.
Actions enabled/allowed-action policies, account plan, remaining included usage,
spending controls and billing eligibility cannot be verified; never assumed.
No paid activation or account/app recreation. Credentials never printed.

Next exact execution action after preparation is pushed: open
https://github.com/ajmalaand-ship-it/shelf/compare/main...ios/foundation?expand=1
and choose Create draft pull request (base main, compare ios/foundation), titled
"Run unsigned Shelf iOS foundation". This requests the build under existing
GitHub permissions/usage; if provider policy blocks it, report that exact block,
without enabling paid billing. Do not merge. No PR/build created in preparation.
For another run, use the existing run's Re-run jobs after an actual run exists;
branch updates alone deliberately do not rerun it. Merge conflicts block PR runs;
current ancestor check establishes no source divergence from remote main.

Rollback: normal branch revert of preparation, no live rollback. Checkout OFF,
pricing sync DEFERRED/disabled, second-phone restore owner-deferred/not passed,
financial expiry unimplemented and Android/iOS joint public-launch hold retained.
Native compilation, signing, provider/device acceptance remain unfinished.

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
| **1. Admin/backend** | **D14 reporting/disclosures completed:** append-only filing/mapping and scoped holds, logical recovery-proof separation, owner-only review report and published policy/deletion wording deployed at 4aa2f61. **Future work:** actual financial minimisation/expiry remains unimplemented; separately reviewed disposal/preservation design and authorization required before execution. | This batch changes no existing customer/financial history. Expiry/deletion execution OFF; D14 external evidence below remains unfinished. Preserve purchase recovery and all release holds. |
| **1. Admin/backend** | **Evidence/external dependencies:** RevenueCat onward integrations, hosting/mail and Microsoft terms, Apache/statistics retention, remaining diagnostic/identifier handling and applicable audience safeguards. Actual provider metadata erasure unverified, conditional on genuine approved deletion work; no eligible job to drain. | Reuse existing evidence; no blanket sharing assertion or manufactured reader deletion. Store-specific reconciliation when final review needs it; owner handles rating questionnaire, assistant poem review stays cancelled. |
| **1. Admin/backend** | **Operations/acceptance:** actual backup-alert delivery, full replacement-host recovery/cutover, owner admin/end-to-end acceptance and separately approved old-app retirement. | Reuse passed encrypted upload/download/isolated restore and October 7 unattended success. Preserve baseline/cPanel handler; no old-account access authorized here. |
| **2. Android app** | **Acceptance:** current Review 3 work owner-accepted; Play-installed build-20 phone acceptance NOT CONFIRMED. Retain unpassed Play-signed sign-in, reviewer access, reading/import, account isolation, sign-out, offline expiry/clock, withdrawal and platform acceptance requirements; earlier build-15 references are historical, not current-build acceptance. Further Android changes paused. | Batch 4 joint acceptance only when authorized. Reuse completed evidence; second-phone restore deferred. Current Android/UI pause remains binding. |
| **2. Android app** | **Final-release dependencies:** tester opt-in/install evidence when needed, final Play review/readiness and confirmation of removal of four temporary account-level grants, preserving app-level access; actual production event/acknowledgement/purchase/refund/restore evidence. | Batch 5 when release needs it. Closed-testing approval complete, not public approval. Real-money/gate checks require separate explicit owner authorization and coordinated launch readiness. |
| **3. iOS app** | **Internal phone/source status:** owner confirms TestFlight 1.0.0 (2) processed, assigned to Shelf Owner, installed/tested “seems good”; limited acceptance only. Apple/Google sign-in source implemented with focused checks, not in build 2. Owner confirms Google iOS/Codemagic setup, privately downloaded ShelfLogin key and Apple relay registration with green SPF. Private upload/permissions/P-256 format independently verified; Apple dashboard key/service/association confirmed; backend 31d123a deployed with both gates false. Owner confirms build 3 installed and rehearsal reached Apple’s Face ID prompt only; authorization did not finish on the borrowed phone. Apple phone acceptance/relay delivery are owner-deferred and unverified; transfer/email task stopped, both flags false. Full Apple/Google signed-phone authentication remains unfinished. Prior unsigned successes are preserved without rerun. | Apple phone/relay checks owner-deferred; pending transfer/email stopped. Keep gates false. One-book Apple sandbox backend 560ef50 is deployed with gate OFF; separate webhook configured. Internal 1.0.0 (4), owner-reported source 2702fbb/run 6acda60d546ce6236cee037c, is API-confirmed uploaded COMPLETE and processed VALID; build-4 compliance was API-cleared on 13 October (READY_FOR_BETA_TESTING). Assigned only to existing Shelf Owner internal group (IN_BETA_TESTING); owner confirms build 4 installed/launches, basic acceptance only. Apple purchase test blocked by existing owner book-3 ownership; activation and purchase/delivery acceptance remain pending. Later sign-in/relay checks require owner resumption. |
| **3. iOS app** | **Construction/acceptance:** complete platform project/configuration, signing, account/provider and per-book App Store purchase integration, reader/Library/offline/deletion/support flows as readiness establishes necessary; then real-iOS-phone acceptance and demonstrated fixes. | Batches 3–4 after inspection/agreed design. Reuse shared Flutter/backend. Exact gaps unverified until inspection; no Apple identity/availability/approval/purchase success claimed. |
| **3. iOS app** | **External/release dependencies:** necessary Apple account/build/signing access, App Store configuration/disclosures/review/readiness and approved transaction/restore/refund acceptance. | Batch 5 joint launch. Android evidence does not establish iOS provider/device results. Checkout OFF absent separate authorization. |
| **4. Shared app design and UX/UI** | **Agreement/construction:** current Review 3 work owner-accepted; further UI changes paused. Preserve delivered shared components and completed checks; no new design batch authorized. | Batch 2 after iOS readiness. Preserve source text, RTL book layout, interface/account-language decisions and access rules. Concrete reader-facing changes need agreement; optional improvements excluded. |
| **4. Shared app design and UX/UI** | **Acceptance:** accept both apps together against agreed design, clear actions/touch targets, loading/empty/error states, text/layout and account/purchase/Library flows; fix demonstrated issues and reuse passed evidence. | Batch 4 with platform-specific device/reading checks. One ready platform cannot release publicly alone. |

**Next practical task (12 October owner correction):** iOS per-book App Store
purchase readiness audit and scoped preparation plan, without needing the owner's
iPhone. Reuse shared Flutter/backend and completed Android evidence; inspect exact
iOS product/RevenueCat/verification gaps before implementation. Apple phone login
and relay delivery are owner-deferred/unverified; transfer/email task stopped.
Preserve build-2 limited acceptance and build-3 install/native Face ID prompt
only. No new build/upload, settings, transaction or deployment is implied.
Existing backend financial-retention work remains in this list. Checkout OFF,
pricing-sync/second-phone-restore deferrals, Android/UI pause, deferred improvements
and joint public-launch hold remain. This identifies the next task; it does not
start App Store purchase implementation or authorize dashboard changes.

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
