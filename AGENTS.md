# AGENTS.md — Shelf

Read this file fully before doing anything in this repository.

## 1. What this project is
This repository is **Shelf**: a free-download Android app that is a Pashto and Farsi digital
bookstore and personal library. Readers discover books by many authors, read a free sample,
buy each book individually, and find bought books in My Library.
Version 1: managed catalogue (the owner, Ajmal Aand, publishes all books), no author accounts,
no subscriptions, no automatic author payouts, Android only.

Shelf is built by changing the existing poetry app (old name: Pitswal) in this repository.
The old poetry project's rules, roadmap and approvals no longer apply. Shelf is not part of
the Professional Platform, System C, Career Command or the Private Assistant.
Old Pitswal documents exist only in git tag `pitswal-baseline-2026-09-25` as technical history.
README.md and any remaining docs describe the old app technically; they are not rules.

## 2. Who decides
- The owner, Ajmal Aand, decides everything. The governing document is the
  **Shelf Master Record** (Version 2.2, approved 28 September 2026), kept by Ajmal.
  This file is its summary for coding agents.
- Do only the task Ajmal gives you. Do not start other work, even if it looks useful.
- If a task conflicts with this file, or something is not covered, STOP and ask.

## 3. Current step
Step 2 — Build the Shelf foundation (started 25 September 2026).
Focus: a foundation for a large multi-author online bookstore and reader. The admin must be
able to add unlimited authors and books. The 6 existing books are examples; do not spend
effort perfecting them.
Current task: Step 2, task 2 (move to shelf account) nearly done; waiting for DNS and SSL.

## 4. Rules that are never broken
1. No change without Ajmal's clear OK for that specific task.
2. R3: Database STRUCTURE changes and BULK or SCRIPTED changes to existing data
   require reversible Laravel migrations committed to git, backup first, run by the
   owner. Backups and their path notes belong in /home/shelf/backups/shelf/.
   Normal owner admin work (adding/editing books, authors, credits, categories,
   content, and importing through the admin) needs no migration; the nightly backup,
   bin and change record protect it. Never edit the live database by hand.
3. All code changes in git, small commits, clear messages. Never force-push, never rewrite
   history, never delete or move the tag pitswal-baseline-2026-09-25.
4. Never print, copy or commit secrets (.env values, keys, tokens, passwords).
   Never reuse old Pitswal tokens, RevenueCat keys or store settings for Shelf.
5. Pashto and Farsi source text is never repaired, guessed or rebuilt with OCR or AI.
   Uncertain text is marked and held. Never alter or delete committed source originals
   in storage/app/source/. Word import failure cleanup removes only new files created
   by that failed attempt, as required by Master Record 6.12.
6. The server decides access. Paid text, audio and artwork go only to readers who own
   that specific book. A locked screen in the app is not protection. Access protection
   is a foundation priority; prove its state with direct requests, never assume it.
7. Money records (sales, refunds, author shares) are never overwritten; corrections are
   new entries.
8. No book, cover, font, image or audio is published without known rights and correct
   credit (author and translator).
9. The old identifiers `pitswal_unlock_all_v1`, `unlock_all` and offering `default` must never
   grant access to Shelf books. Every Shelf purchase is for one book.
10. Do not hard-code the server address in new code. poetry.ajmalaand.com is Shelf's
    temporary address only; Shelf will move to its own domain.
11. Nothing is "done" without evidence. Reading and layout need real Android phone testing.
12. Real people's data: if real reader accounts or purchases appear, do not change,
    move or delete them without a plan approved by Ajmal.
13. Report failures honestly. If something goes wrong, stop and report before fixing.

## 5. The server
- Shelf runs in its own cPanel account "shelf": code in /home/shelf/apps/shelf,
  website root /home/shelf/public_html (a link to /home/shelf/apps/shelf/public),
  database shelf_app. Domain: shelf.services.
- The old poetry app in the "ajmalaand" account (poetry.ajmalaand.com) is NOT Shelf.
  Never read from, write to or change it. It stays untouched and will be retired.
- Staging boundary (v2.2): no staging until the boundary inside Step 5. Staging must
  be in place before the first real reader other than the owner or the first real
  payment, whichever comes first, and before any public or closed Google Play testing.
  This supersedes the earlier "until live with users" wording. Automated tests use
  an in-memory or temporary database only.
- Codex runs approved live-server commands itself (backup, migrations, file moves
  and checks), requesting sandbox escalation. The owner's approval of that
  escalation prompt is the owner's OK under R1. This supersedes earlier wording
  requiring the owner personally to run commands, including section 4's R3 wording
  and historical decisions below; reversible migrations remain mandatory.
- Backup first in the same run. Stop at the first failure and report it. Never edit
  the database by hand. For the Task 6 + 7 rollout, if anything fails after entering
  maintenance mode, run `php artisan up` before stopping.
- Claude (planner) makes technical decisions following the Master Record. Ask the
  owner only owner questions: books, rights, prices/money, what readers see,
  publishing and launch.

- Daily backup: scripts/shelf_daily_backup.py at 03:00 (shelf crontab), stored in
  /home/shelf/backups/shelf, 14 kept.

## 6. How to work on every task
1. Plan: say what you will do, why, the risk, and how to undo it. Wait for OK if the
   task does not already approve it.
2. Label commands: READ-ONLY (only looks) or CHANGES (changes something).
3. Do the work in small steps. Stop if anything fails.
4. Check the result. Automated tests stay. Always run tests through
   `scripts/run_tests.sh` (PHP only by default). Flutter/Android tests run ONLY when
   files in `mobile/` changed in the current task; then use `scripts/run_tests.sh --mobile`.
   Never write to `/tmp`. All temporary files for tests, Flutter, Composer, and scripts
   must go under `/home/shelf/tmp`. Set TMPDIR, TMP, and TEMP accordingly; put tool
   caches there when needed. Clean up only the temporary files created by the run.
5. Commit and push to origin main only when the task says so.
6. Report in plain, short English with exactly these lines:
   Done: / Tested: / Problems: / Next: / Decision needed:

## 7. Technical facts (from inspection, 25 September 2026)
- Laravel 12, Filament 5, Livewire 4, PHP 8.3, MariaDB 10.11 (utf8mb4).
- Flutter app in mobile/ (package pitswal, applicationId com.hindara.pitswal; will change).
- 6 collections, 342 works. Collection 6 (سيند په پرخه کې) is a translation:
  original author پروین پژواک, translator Ajmal Aand.
- No author model, language field, reader accounts or per-book purchases exist yet.
- Payments: RevenueCat foundation built for one global unlock-all; not live.
- Git remote: git@github.com:ajmalaand-ship-it/shelf.git (private).

## 8. Owner decisions
- 2026-09-25: No staging/test copy until the app is live with users.
- 2026-09-25: Shelf is built on the existing poetry app work; the Pitswal name is retired.
- 2026-09-25: Permanent domain: shelf.services.
- 2026-09-25: The admin chooses each book's free sample; no fixed amount.
- 2026-09-27: Master Record amendment v2.1 approved: Shelf moved to its own cPanel account
  "shelf" and domain shelf.services now; old poetry app untouched then retired; no staging
  until live; each old collection becomes one book, priority is the foundation for
  unlimited books; admin chooses each book's free sample.
- 2026-09-27: Android app ID approved: `services.shelf.app`. Flutter package name: `shelf`.
- 2026-09-27: App identity approved: Android app ID `services.shelf.app`; app name Shelf;
  slogan "کتاب مو ژوند بدلوي".
- 2026-09-27: Author/language foundation approved: books have ordered author, translator,
  and editor credits; languages are configurable, initially ps and fa. Keep legacy
  collection author text and poem-level attribution unchanged.
- 2026-09-27: "اجمل اند" and "Ajmal Aand" are the same author: display name اجمل اند,
  Latin name Ajmal Aand. Do not merge other names by guessing; "نوم نه دی ښودل شوی"
  is unknown and must not become an author record.
- 2026-09-27: The six existing books are in Pashto (ps). Book 6 credits:
  author پروین پژواک, translator اجمل اند. Publishing requires an author and language.
- 2026-09-27: Task 4 correction: poetry books 3, 4, 5, 7, 8 have only اجمل اند
  as author, with no book translator. Book 6 سيند په پرخه کې has author پروین پژواک
  and translator اجمل اند. All six books are ps. Nothing else belongs to پروین پژواک:
  correct the four poems in book 4 attributed to her to ORIGINAL works by اجمل اند,
  with no translator. This explicitly supersedes preservation of those four poems’
  attribution only. Leave book 8’s one unknown-original-author poem unchanged and
  list it in the report.

- 2026-09-27: Word import is a separate task (5b) after task 5.

- 2026-09-28: Task 5: Each book manages its own content. No global content list in
  the admin. Manage poems/chapters, including bin and ordering, from the book only.
  This change is Filament admin only; no database or API changes.

- 2026-09-28: Automated tests stay. Flutter/Android tests run only when files in
  `mobile/` changed in the task. Always use `scripts/run_tests.sh`, with `--mobile`
  only for those tasks. Never use `/tmp`; temporary files for tests, Flutter,
  Composer, and scripts belong under `/home/shelf/tmp` and are cleaned up after use.

- 2026-09-28: Task 5b approved: Word (.docx, max 20 MB) import belongs only inside
  a book's Content tab, for poetry and prose. Heading 1 starts a titled item; an
  exact *** line starts an untitled item; preceding text is an untitled first item.
  Preserve source words, Unicode and breaks exactly; never guess, normalize or repair.
  Preview titles, first two lines, word counts and excluded-feature/empty-item warnings
  before Import. Append only; cancellation changes nothing. Archive each original
  privately under storage/app/source/imports/<book-id>/ with checksum and importer,
  time, filename and count. This authorizes adding new originals there, never changing
  or deleting existing source files. Reversible migrations are run by the owner only.
  Change the API home service name to Shelf API.

- 2026-09-28: Master Record v2.2 approved (R3 clarified, staging boundary, Word import safeguards, v2.0 requirements restored).
- 2026-09-28: Master Record 6.12 Word import safeguards: preview clearly lists all
  omitted images, tables, footnotes, endnotes, comments, tracked changes and text boxes.
  If anything is omitted, require "I understand this content will not be imported"
  before enabling Import. Import is all-or-nothing across database and file handling:
  failure leaves neither partial items nor orphan files. A one-time preview token/lock
  prevents duplicate submissions. Append only; never replace or delete existing content.
  This supersedes Task 5b's retention of files created by failed imports.

- 2026-09-28: Tasks 6 + 7 approved (Master Record 6.1, 6.8, 6.10): book states
  are Draft, Ready for review, Published and Withdrawn. A reversible owner-run
  migration makes all six existing books Draft, retaining old flags for rollback.
  Only Published books and Visible items are public, including their media.
  Publishing requires an author credit, language, cover and visible item; sample
  and per-book store-product checks follow in task 8 / Step 4. Record status actor
  and time. Withdrawal deletes nothing; Step 4 will preserve verified prior buyers'
  access while stopping discovery and new sales. Until then that access fails closed.
- 2026-09-28: Only users explicitly marked owner may enter admin. The reversible
  migration marks the existing single user owner; all future users default to
  non-owner. Keep login rate limiting and offer optional authenticator-app MFA
  with recovery codes in the owner profile; do not enable it for the owner.
- 2026-09-28: Unpublished covers must not be public. Serve covers through status-
  checked routes; private admin/owner-preview access remains. The owner backs up,
  migrates and moves only referenced covers into private storage. Preserve and
  list the three unreferenced cover files. Do not run live migrations or moves as
  an agent. Public app browsing checks the server rather than falling back to
  potentially withdrawn cached content; owner-preview caching remains separate.

- 2026-09-28: Codex runs live-server commands itself (backup, migrate, file moves,
  checks), requesting sandbox escalation; owner approval of the escalation prompt
  is the owner's OK (R1). Backup first in the same run, stop at the first failure,
  and report. Never edit the database by hand. This supersedes prior owner-run-only
  restrictions; it does not waive reversible migrations or backup requirements.
- 2026-09-28: Claude (planner) makes technical decisions following the Master
  Record; the owner is asked only owner questions (books, rights, prices/money,
  what readers see, publishing, launch).
- 2026-09-28: Codex is approved to run the Task 6 + 7 rollout in order: backup,
  maintenance down, optimize:clear, migrate --force, shelf:move-covers --after-backup,
  maintenance up, shelf:check-publication. Stop at the first failure; if a failure
  occurs after down, run php artisan up before stopping.

- 2026-09-28: Tasks 8 + 9 approved: samples work for every book type. The owner
  chooses no sample, full item, or an exact first part counted by lines/paragraphs.
  A reversible migration clears ALL legacy free flags; retain source text and
  legacy excerpts privately, and restore old flags only on migration rollback.
  A visible approved sample is required to publish. Admin shows sample controls,
  column/filter and book summary. The app clearly labels samples and the full-book
  remainder. Until Step 4 only approved sample text is readable publicly.
- 2026-09-28: Retire global unlock access completely. No legacy product, offering,
  entitlement, cached provider result or caller header can grant access. Public
  audio/artwork require a full-item sample and a signed URL; partial samples must
  not expose their full recording or artwork. Owner preview still sees everything.
  Remove old purchase/restore UI and product IDs from the app; show Coming soon.
  Future ownership must be verified for the exact book in Step 4.
- 2026-09-28: Codex runs the Task 8 + 9 rollout with escalation: backup first in
  the same run, down, optimize:clear, migrate, up, then sample/access checks and
  direct HTTPS probes. Stop at the first failure, running up first if down began.

- 2026-09-29: Step 3 task 2 addition: Pashto/English interface choice on first
  launch, a visible one-tap "EN | پښتو" button in the Store header, and the same
  control in Settings. All share one persisted preference. Book/source language
  and the existing reader layout stay unchanged. Include widget tests.
- 2026-09-29: Language addition clarified: English changes interface words only.
  Book text, titles, author names, descriptions, contents, reader and share cards
  remain RTL and right-aligned in both modes, with unchanged source strings.
  Switching language must not mirror or reflow book content. Test this explicitly.

- 2026-09-29: Step 4 Task 1 reader accounts approved. D7: Google sign-in and
  email/password. D8: Google Play payments (App Store later with iOS). D13:
  bought books readable offline with periodic server checks; refunded copies
  removed. Purchases/offline ownership are Task 2, not account access rights.
  Readers remain separate from owner admin. Register, verify, reset/change
  password, revoke mobile tokens and delete accounts with email confirmation.
  Configure noreply@shelf.services; privacy/deletion pages and bilingual account
  screens required. Owner runs are automated with backup first. Public reader
  registration waits for staging; initial live testing uses the owner's email.
