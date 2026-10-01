# Shelf's private test copy

Staging is a separate Shelf used to try a change before it reaches the real app.
It has its own database, files, login sessions and secrets. A price or book edit
there does not change the real Shelf. The admin and Android app show a red
**TEST COPY** banner. Search engines are told not to index it, and a private
access check protects its pages and API.

| Copy | Address | Android app |
| --- | --- | --- |
| Real Shelf | https://shelf.services/admin | Shelf (`services.shelf.app`) |
| Private test copy | https://staging.shelf.services/admin | Shelf Test (`services.shelf.app.staging`) |

For the test site's first browser prompt, use username `owner`. Its separate
password is kept privately at `/home/shelf/secrets/staging/access.txt`; read it
only in your private server terminal. Then sign in to the admin with your
normal owner admin credentials. The copied admin account has a separate session;
its production MFA secrets/recovery codes are not copied. Staging MFA can be set
up independently. Do not share the private test APK or access password.

Shelf Test installs beside Shelf. It browses the test catalogue and uses a
separate reader account: create an account in Shelf Test, then use email/password
sign-in. Your production reader account and purchases are deliberately absent.
Staging emails are logged under the test copy's `storage/logs/laravel.log`;
they are never delivered to readers. Open the verification/reset link from that
private log in a browser with the test-site access login. Never paste these
links or logs into chat or git. Email/password sign-in does not need Google setup.

The test copy contains books, authors, credits, categories, exact content and
referenced covers/audio/artwork. Only the owner admin is copied. Readers,
tokens, sessions, purchases, sales, refunds, queues, old backups, mail logs and
private source-import metadata are not imported. Media is copied as separate
files, never linked to production storage. Original source archives stay in
production, untouched.

Google Play price sync is permanently blocked on staging, even if a setting is
enabled by mistake. Mail transports are forced to logging. Staging has no cron
entry, scheduler or queue worker; its queues are disabled. Production's daily
03:00 backup and purchase webhook are unchanged.

Staging accepts no purchase webhooks. It uses independent REST verification for
sandbox purchases when configured, with identities such as `staging_12`;
production continues to use `12`. Neither environment accepts the other's
identity. Staging buying is initially disabled because production payment keys
are not copied. To enable store payment tests later, create a **separate Shelf
Test RevenueCat project**, with a Google Play app for `services.shelf.app.staging`,
its own sandbox catalogue and credentials. Attach each book product to its own
same-named entitlement, and keep restore behavior with the original App User ID.
The separate Play package must be uploaded to owner-only internal testing and
the owner must be a license tester. Supply the separate V1 verifier key privately
under `/home/shelf/secrets/staging/`, the staging app ID and its public `goog_`
SDK key, then set `SHELF_STAGING_REVENUECAT_PROJECT_CONFIRMED=true` and enable
staging purchases after verification. No staging webhook is needed. Do not
change the existing production project/webhook or enable Play price sync on
staging. No real payment is authorized.

Google sign-in is optional for Shelf Test. In Google Cloud project `shelf-510123`
→ APIs & Services → Credentials → Create credentials → OAuth client ID → Android:
name `Shelf Test`, package `services.shelf.app.staging`, SHA-1 of the signing
certificate shown in the APK build evidence below. This APK uses the existing
Shelf upload certificate, SHA-1
`DA:24:FF:12:6D:3A:D7:D2:83:E3:A3:36:B6:8A:A9:03:50:42:38:CB`.
Then configure the staging server with the web/server audience and that Android
client ID. The Google consent screen must include the owner as test user.
Staging currently omits Google configuration; email/password is available now.

## How a change reaches the real Shelf

1. Develop in an isolated git checkout. Never edit the running production folder.
2. Commit the change. Deploy that commit to staging and run its automated and
   live isolation checks. Check the result in the test admin and Shelf Test.
3. After the owner approves the live step, promote **that identical commit**.
   Promotion refuses a commit that is absent from staging, has failed checks,
   or whose check evidence changed. It makes a fresh verified production backup,
   puts the site briefly into maintenance, migrates, and checks health.
4. Record the commit on both copies. No force-push or history rewriting.

Run these as the `shelf` server user in a trusted checkout of this repository.
Every live action needs the owner's command approval. Temporary files stay
under `/home/shelf/tmp` and are cleaned up.

```bash
# READ-ONLY: show staged/production commits and passing check evidence.
python3 -B scripts/staging/workflow.py status

# CHANGES: back up staging, deploy the selected commit, migrate and test it.
python3 -B scripts/staging/workflow.py deploy COMMIT

# CHANGES: back up staging and refresh only its catalogue from the real Shelf.
python3 -B scripts/staging/workflow.py refresh

# CHECKS: direct isolation/health probes and scripts/run_tests.sh --mobile.
python3 -B scripts/staging/workflow.py check

# CHANGES: only an already staged and checked commit; production backup first.
python3 -B scripts/staging/workflow.py promote COMMIT

# CHANGES: build the private test app from the deployed staging commit.
cd /home/shelf/apps/shelf-staging
bash scripts/build_staging_apk.sh
```

Refresh creates a dated instance of the committed reversible catalogue migration
template, with private before/after snapshots. It keeps existing staging reader
accounts. If staging has purchase/access history, it refuses to replace its
catalogue until a preservation plan is approved. A refresh invalidates prior
promotion evidence; rerun checks afterward. It does not import production money
records or replace existing staging agreement versions.

If deployment fails, stop. Production maintenance is lifted even on failure.
The verified backup contains `promotion-notes.txt`, the prior commit and database
backup. Code rollback uses `git switch --detach PREVIOUS_COMMIT` without rewriting
history. Review the new migration batch before rollback; agreements with sales
must be kept, and a database restore must not overwrite later reader/purchase
activity. Restore SQL/media only with the owner's approval. Staging's prior
release remains under `/home/shelf/apps/shelf-staging-releases/`; switch back
only after considering its database migrations.

Release evidence and private APK download command will be recorded here after
the initial approved rollout passes.
