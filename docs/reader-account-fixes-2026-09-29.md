# Reader-account fixes — owner verification report

The approved changes allow registration with any email while retaining validation,
verification and rate limits. Reader identities remain separate from owner admin.
Account screens and email-link web forms use English/LTR. Store exposes account
state and a compact current-language dropdown; Settings and first launch share its
persisted preference. Library offers a large account action, and Buy opens account
sign-in when signed out. New-password forms show seven live requirement checks.
Book/source strings and their RTL layout are preserved.

AGENTS.md sections 4.13 and 6 now distinguish normal development test/build errors
from live server/database failures, unexpected data changes, failed backup/rollback,
and fixes outside the approved task.

## Rollout and live evidence

Verified backup: `/home/shelf/backups/shelf/20260929-205604/` (SQL, media ZIP and
SHA-256 verification; 14 backups retained). Configuration/bootstrap caches were
cleared. Laravel reported no pending migrations. No manual database changes were
made. `shelf:check-publication` and `shelf:check-samples` passed their direct HTTPS
probes: six Draft books remain private, 342 items have no approved sample, and
legacy identifiers/headers do not unlock text or media.

Only the owner's Gmail plus-aliases `shelf-test-20260929-resume` and
`shelf-test-20260929-complete` were used. The first runner stopped when its input
stream closed; its existing account was recovered through emailed verification
and reset, then deleted through emailed confirmation. A fresh complete run passed
registration, sign-in, email verification, single-use-link rejection, password
reset with previous-token rejection, password change, all-device sign-out, and
confirmed deletion. Reader tokens received 404 for draft text and 401 for owner
preview. Deleted accounts could no longer sign in.

Final read-only live check: **0 readers, 0 reader action tokens, 0 reader mobile
tokens, 1 owner; maintenance mode off.** Both test accounts were deleted through
the normal account API and confirmation form.

## Automated and visual evidence

`scripts/run_tests.sh --mobile`: **149 PHP tests, 1,668 assertions; 127 Flutter
tests passed.** The original three failures were test-finder/scrolling issues.
Additional screenshot tests capture 44 states across English/Pashto and
320 × 568 / 430 × 932 sizes. Readable app fonts and icons were loaded; reviewed
captures include Store/account state, dropdown, sign-in/create/reset/change forms,
password checkmarks, validation errors and deletion confirmation. Tests also check
Buy's signed-out account navigation and unchanged RTL book content.

Private evidence directory:
`storage/app/private/owner-apks/reader-account-fixes-20260929-evidence/`.

## Private owner APK

Artifact: `storage/app/private/owner-apks/shelf-owner-preview-20260929-205623.apk`

- Android application ID: `services.shelf.app`.
- Debug build signed with the existing Shelf owner test certificate.
- SHA-256: `ab14303a0400d7230eb6ac382df3f707efbcec57c6822df7b7c669243ac3159e`.
- Preview expiry: **2026-10-06 20:54:12 UTC**.
- Signing certificate SHA-1: `8113e9f7c20c8f2cc26c4a3e6a580727531da1f8`.
- Signing certificate SHA-256: `6af1c740ecf3df34601718b154ef9b38327e53f505e9400c81a0a51af69359dd`.

The APK compiled successfully. The build helper initially could not locate Java
for its final signature check; its PATH was corrected and the existing APK's
signature/checksum verified. Tokens, passwords and email-link secrets were never
printed or committed. Build workspaces and their preview configuration were
removed; the APK is private and is not a Play release.

## Remaining checks

Gmail delivered verification, reset and deletion emails to **Spam**. Delivery works,
but inbox placement needs follow-up before reader testing. Google OAuth client IDs
are not configured, so Google sign-in is hidden and real Google sign-in was not
tested. Real Android phone checks of account navigation, email links, keyboard,
secure storage and reader layout remain for the owner. Widget screenshots do not
replace those phone checks. Staging remains required before other readers,
real payments or public/closed Play testing.

Code rollback uses new git revert commits, never history rewriting. Accounts can
be disabled through `READER_ACCOUNTS_ENABLED=false` and a configuration refresh.
Do not roll back the account migration or restore reader data without an approved
data plan and backup. Preview access expires automatically on the date above.
