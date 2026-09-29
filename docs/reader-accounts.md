# Reader accounts — Step 4 Task 1

Readers use their own `readers` table and Sanctum bearer tokens. Owner admin
sessions remain in `users`; a reader token never grants admin or book access.
There are no purchases or owned-book data in this task. Catalogue requests remain
anonymous or use the separate owner-preview mechanism.

Registration collects an email and optional display name. Passwords use Laravel's
hashing; new passwords require 12 characters, mixed case, a number and a symbol,
with a 72-byte limit. Tokens expire after 30 days. Sign-out revokes all of the
reader's device tokens. Password changes, reset and deletion revoke tokens too.
The app stores only its token in protected device storage; profiles stay in memory.

Verification, reset, Google email confirmation and deletion links last one hour,
are single-use and use hashed random tokens. GET requests never consume links.
Link secrets use URL fragments to keep them out of normal server access logs.
Deletion removes the reader, all action tokens and all mobile tokens. No financial
records exist yet. Before adding purchases, implement the owner's retention and
per-book ownership decisions separately.

## Configuration and rollout

`READER_ACCOUNTS_ENABLED=false` disables all account mutations and authenticated
account routes. `READER_PUBLIC_REGISTRATION=false` allows new accounts only for the
existing owner's email, in the separate reader table. Keep this restriction until
the staging boundary is satisfied. Existing book access rules are unchanged.

Back up with `scripts/shelf_daily_backup.py` before migrations or live setup.
Apply the reversible migration using `php artisan migrate --force` and clear
configuration. Run `php scripts/configure_reader_mail.php --after-backup` as
`shelf` to create the mailbox or reuse its existing `.env` credentials. The helper
sets accounts disabled pending SMTP checks. It never resets an existing mailbox
without matching configuration, and prints no credentials. `MAIL_URL` must be
`null`, not an empty string. Confirm SMTP delivery before enabling accounts.

The mailbox helper uses cPanel's documented account-local UAPI interface:
https://api.docs.cpanel.net/cpanel/introduction

To disable accounts, set `READER_ACCOUNTS_ENABLED=false` and clear configuration.
The migration is reversible, but rollback deletes reader data: do not roll it back
once anyone has registered without an owner-approved data plan and backup.

## Google Cloud setup

Create an Android OAuth client with package `services.shelf.app` and the SHA-1 of
the signing certificate supplied with the APK report. Create a Web application
OAuth client in the same Google Cloud project. Put the client IDs in `.env` as
`GOOGLE_ANDROID_CLIENT_ID` and `GOOGLE_WEB_CLIENT_ID`, then clear configuration.
No client secret belongs in the app. Both IDs must be set for the button to appear.
The mobile client fetches the Web ID from account configuration and supplies it as
its server client ID; the backend validates audience, signature, issuer, expiry
and verified email. Actual Google sign-in needs a real-phone check after setup.

For Google identities with non-Gmail emails and no hosted-domain assertion, a
fresh email confirmation is required before linking. A Google-confirmed identity
replacing an unverified registration clears the unverified password and sessions.
Reference: https://developers.google.com/identity/gsi/web/guides/verify-google-id-token

## Verification

Run `scripts/run_tests.sh --mobile`; databases and generated files are isolated
under `/home/shelf/tmp`. Tests exercise account flows, revocation, deletion,
Google cryptographic checks, direct access denials, bilingual widgets and account
switching, alongside the existing reader, source-text and layout regression tests.
Real-phone sign-in, email-link handling and secure-storage behavior still need
owner verification. Do not begin public or closed Play testing before staging.
