# AGENTS.md — Shelf

Read this file fully before doing anything in this repository.
Before every task, read docs/MASTER_RECORD.md; it is the governing document.

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
  **Shelf Master Record** (Version 2.3 with the 29 September 2026 owner addendum), in docs/MASTER_RECORD.md.
  This file is its summary for coding agents.
- Do only the task Ajmal gives you. Do not start other work, even if it looks useful.
- If a task conflicts with this file, or something is not covered, STOP and ask.

## 3. Current step
Step 4 — Accounts and purchases, test mode; construction continues.
Step 2 is closed. Step 3 catalogue/reader features exist; outstanding acceptance
checks are recorded separately from unfinished building work in
`docs/STATUS_RECONCILIATION_2026-10-02.md` and Master Record Part 10.
Step 4 sales/author accounting (6.9) is built with an append-only journal,
confirmed figures, payment records/reversals and per-currency owner balances.
Deployed at 5ff131f and retained in 5f75fa0; see docs/ACCOUNTING.md.
No real payments or automatic payouts are enabled. Step 4 is not all complete.
For the first 100 books, products/prices are managed manually in Play Console;
automatic sync is DEFERRED and disabled. Do not investigate/retry the 403.
Production verification and release-mode source support are deployed at 340634b;
real checkout remains OFF. Existing RevenueCat webhook now receives Both
Production and Sandbox; actual production delivery remains unverified.
D14 policy approved 7 October 2026; financial expiry remains unresolved. See
`docs/D14_DATA_POLICY.md` and the latest Master Record for implementation/evidence.
Step 5 OneDrive encrypted upload/download and isolated
recovery passed; daily 03:30 AST (UTC−04:00) offsite schedule installed,
90-day offsite retention with a reported protected-last-recovery-set exception; local copies 14. See docs/OFFSERVER_BACKUP.md.
OneDrive implementation/evidence is deployed at 5f75fa0. First unattended run
failed 4 October at 07:32:19 UTC after upload. Recorded automatic recovery on
7 October completed at 07:32:37 UTC with recovery_verified=true; independently
rechecked on D14 resume without rerunning recovery. D14 implementation e387b36
is staged and promoted. Owner granted existing V2 Customer information Read only;
one metadata GET passed HTTP 200 at 8 October 00:13:34 UTC (7 October local).
No eligible cleanup jobs exist; actual provider erasure remains unverified,
financial expiry unresolved and D14 not fully complete.
Step 5 / 6.11 repair deployed at c887f91: absolute MariaDB daemon path,
safe failure stages and original-error preservation on alert failure. See the
runbook for controlled scheduled verification; future unattended success must
be recorded separately. Actual alert delivery and replacement-host recovery
remain unverified; recovery key
is owner-confirmed on an unencrypted USB, preserved unchanged.
Second-phone restore is deferred to the pre-release checklist, not passed and
not requested now. Prioritize construction; automate necessary checks and request
phone checks only for a concrete essential risk. No broad test rerun for status-only
reconciliation. The owner confirmed refund/download removal, locked paid text with
sample retained, declined payment locked, and successful repurchase on 2 October.

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
13. Report failures honestly. Failing tests or build errors in the agent's own
    work-in-progress are normal development: fix them and continue within the
    approved task without asking. Stop and report before fixing only if something
    fails on the live server or database, data could be lost or changed unexpectedly,
    a backup or rollback fails, or the fix would go beyond the approved task.

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
From 1 October 2026, every change is developed and tested in an isolated checkout,
deployed to staging first, and promoted to production only at the same verified
commit. Never develop in the running production checkout. Use docs/STAGING.md.
1. Plan: say what you will do, why, the risk, and how to undo it. Wait for OK if the
   task does not already approve it.
2. Label commands: READ-ONLY (only looks) or CHANGES (changes something).
3. Do the work in small steps. Fix tests and build errors in your own work-in-progress
   and continue within the approved task. Stop and report before fixing live server
   or database failures, unexpected data loss or changes, backup or rollback failures,
   or fixes beyond the approved task, as required by section 4.13.
4. Check the result. Automated tests stay. Always run tests through
   `scripts/run_tests.sh` (PHP only by default). Flutter/Android tests run ONLY when
   files in `mobile/` changed in the current task; then use `scripts/run_tests.sh --mobile`.
   Never write to `/tmp`. All temporary files for tests, Flutter, Composer, and scripts
   must go under `/home/shelf/tmp`. Set TMPDIR, TMP, and TEMP accordingly; put tool
   caches there when needed. Clean up only the temporary files created by the run.
5. Permanent UX rule: every screen must be friendly and clear for non-technical
   readers: obvious primary action, large touch targets, clear hierarchy, short
   plain text, helpful empty/error/loading states, no mixed-direction punctuation
   or clipped text, consistent with the existing Shelf look. Check every new or
   changed screen for this before reporting.
6. Commit and push to origin main only when the task says so.
7. Report in plain, short English with exactly these lines:
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
- 2026-10-07: Owner approved constructing production-mode purchase verification behind disabled controls, with existing RevenueCat/Google Play integration. Real transactions require authenticated provider-event environment and independent server REST is_sandbox evidence to agree, with the exact reader, book, permanent entitlement and unique purchase-time match. Unknown, malformed, ambiguous or failed evidence never grants access or renews an offline lease. Test transactions remain excluded from real income. Production acceptance defaults OFF and is forced OFF on staging; no real payment or public release is authorized. Manual Play workflow for first 100 books remains approved; automatic sync DEFERRED, 403 work stopped. Focused isolated tests, staging, verified-backup promotion, records and commit/push authorized. No product, price, credential, permission or historical data change.
- 2026-10-07: For Shelf’s first 100 books, the owner creates and manages Google Play products, prices and availability manually in Play Console. Automatic admin-to-Play product/price synchronization is **DEFERRED, NOT COMPLETED** and stays disabled. Stop 403 investigations, calculations, retries and support follow-ups unless the owner explicitly reopens automatic sync. Existing product mappings, credentials, app-level access, verification, acknowledgements, entitlements, refunds, accounting and historical records are preserved. Admin USD prices are approved reference values; saving does not update Play. Checkout uses Google’s localized store price. No Play product creation/change is authorized in this task.

- 2026-10-03: Owner confirms external recovery key at D:\shelf-recovery-key.secret
  on an unencrypted USB (SCP 100%, 65 bytes). Preserve existing key and OAuth.
  Complete staging/promotion, encrypted upload/download/isolated restore, schedule,
  mocked failure verification (no owner test notification), records and commit/push.
- 2026-10-02: Owner chose existing OneDrive for Step 5 off-server backup/recovery.
  Prepare in isolation, stage first, encrypt database/content/configuration before
  copy-only upload to dedicated Shelf-Backups, keep OAuth/key private and retain
  recovery key independently. Check connected quota before retention. Give one
  private browser authorization step; no secrets in chat. Completion requires
  download and isolated restore evidence, scheduling and failure reporting.
  No broad PHP/phone rerun; product-sync 403 unfinished, second-phone restore deferred.
- 2026-10-02: Owner approved completing sales/author accounting under 6.9/D10:
  inspect source history by book/rights holder; immutable agreement-based estimates,
  confirmed owed and actual payment records, linked corrections/reversals, duplicate
  prevention, unknown net/fees/taxes retained and Test excluded; per-currency totals.
  Recordkeeping only, no payouts. Isolated implementation, necessary automation,
  backed-up reversible schema, staging then identical-commit production promotion,
  project records, commit/push; preserve purchase/access behavior and deferred restore.
- 2026-10-02: Owner confirmed Google Play phone refund removes Library book and
  download, locks paid content while sample works; declined test payment stays
  locked; repurchase restores Library and paid poem. Second-phone restore deferred
  to pre-release, never marked passed or requested now. Prioritize construction;
  use necessary automation, avoid repetitive/broad tests and phone checks unless
  resolving a concrete essential risk. This task authorizes read-only reconciliation
  and documentation-only commit/push, with no deployment, rebuild or data changes.
- 2026-10-02: Owner approved first-purchase fixes: exclude sandbox purchases from all real income while retaining clearly labelled Test history; automatic ownership refresh with clear errors; Allowed/Blocked admin badges; Library covers, Read book, downloaded/remove state, one account icon and pull-to-refresh. Develop isolated, stage and promote identical commit with backup/release script; tests, bumped Play AAB and Shelf Test APK. No rewriting purchase/source history.
- 2026-10-01: D10 decided. The owner sets author-share percentages per book in
  the admin. All six current books: 100% of the net amount received to اجمل اند,
  effective 1 October 2026. For سيند په پرخه کې, author پروین پژواک permits the
  owner to keep all income; the owner retains written permission. Its 100% share
  goes to اجمل اند as translator/publisher. New agreement versions record owner
  and time; past agreements and financial snapshots are unchanged.
- 2026-10-01: Private staging at staging.shelf.services approved, with its own
  files, database/user, secrets, logged email, disabled Play sync and queue,
  prefixed RevenueCat identities, REST-only sandbox confirmation and private
  Shelf Test Android app services.shelf.app.staging. Production webhook and daily
  backup stay unchanged. Every future change must pass staging before promotion.
- 2026-09-30: Step 4 purchase connection approved, owner-only sandbox/test mode.
  New Shelf service account and separate RevenueCat V2 configuration/V1 verifier
  keys secured outside git. Six per-book products/entitlements and app-scoped
  sandbox webhook configured; sandbox server config and independent V1 check on.
  Google sync stopped at book 3 (initial 400/409 product/price error; read-only
  diagnostic conversion denied 403). Scheduled sync remains disabled, no retry
  loop; remaining five Pending. Backup `/home/shelf/backups/shelf/20260930-211054/`.
  Tests and rolled-back live simulation passed. AAB 1.0.3 (12) and fresh seven-day
  owner APK built; owner uploads AAB. See docs/PURCHASES_TEST_SETUP.md for evidence,
  download command and deferred Play retry. No real-money or public release.
- 2026-09-30: Superseding the earlier same-day visibility clarification, the
  owner confirms the hidden item flags in books 3–8 came from the old app's
  private-draft period, not editorial choices. Make all non-deleted items in
  these six books visible with a backed-up reversible migration, recording
  owner and time. Continue the approved temporary first-two-full-item samples
  and publication through existing publish logic; verify all six publicly
  listed, non-sample text locked and purchases still disabled. No source text,
  access rules or old poetry project changes; no app rebuild.
  Applied successfully: all 342 non-deleted items visible, first two items per
  book full samples, all six Published with owner/time/status history. Verified
  backup: /home/shelf/backups/shelf/20260930-070203/. Live catalogue, locked
  non-sample text/media, exact source, disabled purchases and protected owner
  preview passed. Existing rate limiting was respected, not cleared or changed.
- 2026-09-30: Publish all six books (IDs 3–8) for Google Play internal testing;
  the owner accepts visibility through the public catalogue. For a book without
  a sample, temporarily sample its first two visible items in full; the owner
  can change samples later in the admin. Check author credit, language, cover,
  visible content, sample, USD 2.99 price and canonical product ID; fix only
  missing approved test values and report other blockers. Backup first, use a
  reversible migration and existing publish logic with status history. Keep
  non-sample text locked and purchases disabled pending Play/RevenueCat setup.
  Verify the six-book public catalogue, non-sample body denial and unchanged
  draft/owner-preview protection. This approves publication, not a rebuild.
  Same-day clarification: keep item visibility unchanged; the owner will choose
  visible items in the admin. Publication is pending that choice: preflight
  found no visible items in books 3, 4, 6, 7 or 8 and only item 152 in book 5.
- 2026-09-29: Step 4 Task 2b: book prices are set and edited ONLY in the
  admin, one USD price per book. Admin saves automatically create missing
  Google Play one-time products, update converted local prices and activate
  Published books or deactivate other states. The owner never edits prices
  or creates products in Play Console. One new service account with Play
  access is shared by Shelf sync and RevenueCat. Sync stays disabled until
  its private server-only credentials are provided. Approved test price:
  USD 2.99 on books 3, 4, 5, 6, 7, 8 through a backed-up reversible migration,
  with actor/time recorded. Queued retry, admin status, tests and live rollout
  are approved; no real payments.
- 2026-09-29: Step 4 Task 2 approved, TEST MODE ONLY, no real money. D9: one
  USD price per book; Google Play displays local currency. D11: verified buyers
  keep withdrawn books forever; new purchases stop. D12: all sales final except
  owner-approved duplicate/broken-book cases; Google refunds revoke access.
  Buying requires "Read the free sample first. All sales are final. I agree."
  Owner sees refunds per reader and may block buying. D13: account-isolated
  offline downloads expire 30 days after the last successful server check;
  refunded/revoked books and copies are removed at the next check. D10 values
  remain owner decisions before real sales. New Shelf upload key and verified
  private backup, release AAB and seven-day owner APK approved; simulated live
  webhook tests use disposable test readers. No old Pitswal settings or keys.
- 2026-09-29: Google sign-in enabled for project Shelf (`shelf-510123`), External
  consent with owner as test user. Android client uses `services.shelf.app` and
  current test signing SHA-1. In Step 5, ADD the Play Store signing key SHA-1
  to this Android client before release. Web client ID is the server audience
  and the app's serverClientId; Google sign-in appears only when configured.
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

- 2026-09-29: Step 4 Task 1 fixes approved: registration accepts any email; retain
  rate limits and verification. This supersedes owner-email-only registration;
  staging remains required before readers other than the owner use the app.
  Reader identities remain separate from owner/admin, including identical emails.
  Store shows account state; signed-out Library has a large sign-in/create-account
  action; Buy first requests sign-in. Store language control becomes a compact
  current-language dropdown, synchronized with first launch and Settings.
  All account screens are always English and LTR, superseding bilingual account
  screens. Password requirements show live checkmarks. Codex runs live rollout
  with backup first, tests the complete account lifecycle using only owner Gmail
  plus-aliases, reads their email links, and deletes all test accounts through
  confirmed deletion. Check screenshots of account screens and Store/dropdown in
  both languages and small/large phones; run scripts/run_tests.sh --mobile,
  commit, push, and build the owner-preview APK with checksum and expiry report.

- 2026-10-07: Owner approved Android release purchase source construction with real
  sales OFF. Default checkout requires fresh server production gate and exact
  reader/product/mode consent; owner-test builds opt in explicitly, never classify
  receipts. Restore remains independent of buying. No Android artifact/upload or
  provider settings changed; installed app unchanged. See
  docs/PRODUCTION_PURCHASE_VERIFICATION.md and latest Master Record status.

- 2026-10-07: Production webhook preparation: read-only provider inspection confirms
  existing Shelf webhook whintgr75a4892970 is sandbox-only. Owner edit to Both
  environments pending; preserve existing app, URL, auth and three event filters.
  Deployed 340634b needs no code change; real checkout stays OFF and prior-real
  refunds still process. D14 blocks retention/deletion policy implementation;
  D16 is the launch book list, not a generic purchase-construction blocker.
  USD 2.99 values are test-only. See latest Master Record and production runbook.

- 2026-10-07: Owner saved Both webhook environments around 21:17 UTC; read-only
  21:19:03 UTC verification confirms existing whintgr75a4892970 environment=null
  (no environment filter), unchanged app/URL/events. Configuration complete;
  actual production delivery unverified; real checkout OFF. Latest Master Record
  has D14 recommendations only, not approved policy or implementation, and
  separates remaining policy construction from launch setup/acceptance. No
  provider write, deployment, purchase or repeated suite; records-only commit/push.
