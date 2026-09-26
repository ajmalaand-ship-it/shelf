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
  **Shelf Master Record** (Version 2.0, approved 25 September 2026), kept by Ajmal.
  This file is its summary for coding agents.
- Do only the task Ajmal gives you. Do not start other work, even if it looks useful.
- If a task conflicts with this file, or something is not covered, STOP and ask.

## 3. Current step
Step 2 — Build the Shelf foundation (started 25 September 2026).
Focus: a foundation for a large multi-author online bookstore and reader. The admin must be
able to add unlimited authors and books. The 6 existing books are examples; do not spend
effort perfecting them.

## 4. Rules that are never broken
1. No change without Ajmal's clear OK for that specific task.
2. Backup before any database change: database dump plus a note of the path, in
   /home/ajmalaand/backups/poetry/. Database structure changes only through Laravel
   migrations committed to git. Never edit the live database by hand.
3. All code changes in git, small commits, clear messages. Never force-push, never rewrite
   history, never delete or move the tag pitswal-baseline-2026-09-25.
4. Never print, copy or commit secrets (.env values, keys, tokens, passwords).
   Never reuse old Pitswal tokens, RevenueCat keys or store settings for Shelf.
5. Pashto and Farsi source text is never repaired, guessed or rebuilt with OCR or AI.
   Uncertain text is marked and held. Never modify files in storage/app/source/.
6. The server decides access. Paid text, audio and artwork go only to readers who own
   that specific book. A locked screen in the app is not protection.
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
The app is not public yet and has no real readers. There is no separate test copy or
staging environment (owner decision, 25 September 2026); we build directly on this server.
Automated tests must use an in-memory or temporary test database, never the real database.
Shelf's permanent domain is shelf.services (the move is a separate task).
poetry.ajmalaand.com is temporary.

## 6. How to work on every task
1. Plan: say what you will do, why, the risk, and how to undo it. Wait for OK if the
   task does not already approve it.
2. Label commands: READ-ONLY (only looks) or CHANGES (changes something).
3. Do the work in small steps. Stop if anything fails.
4. Check the result.
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
