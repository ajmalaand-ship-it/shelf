# Shelf OneDrive backup and recovery

Step 5 OneDrive backup/recovery, authorized 2 October and continued 3 October 2026.
The owner chose their existing OneDrive. Production upload/download/isolated
restore and schedule installation passed on 3 October; evidence is below. Actual
email delivery and the first unattended cron execution remain unverified.
Product/price sync remains disabled with its unresolved 403. Second-phone purchase
restore is deferred, not passed. This task requires no broad PHP or phone rerun.

## What is prepared

Use the existing verified database/media helpers in `shelf_daily_backup.py`,
installed GnuPG 2.3.3, and dedicated rclone 1.75.1 installed at
`/home/shelf/tools/rclone-v1.75.1/rclone`. Its official ZIP SHA-256 was checked
against the version's published SHA256SUMS. No system tools are replaced.

`scripts/shelf_offsite_backup.py` packages and encrypts locally before any upload.
Transfers are **copyto --immutable**, never sync, move, purge, cleanup or delete.
Destination is `shelf_onedrive:Shelf-Backups/` with unique timestamp/random names.
No other OneDrive folder is touched. OAuth grants Files.ReadWrite/offline_access;
the grant is broader than the backup folder, but every transfer is fixed to that
folder. Credentials use a separate private config, never another project's remote.

Each production package includes:

- A transaction-consistent SQL dump of Shelf's database, schema and table rows,
  plus routines, triggers and events. The existing local helper's default is unchanged.
- All `storage/app/public`, `private` and `source`, including original source
  documents/imports, referenced and unreferenced media. Source bytes stay unchanged.
- The exact Git revision as a code archive, including dependency lock files.
- Production `.env` (including APP_KEY), Shelf provider credential files under
  `/home/shelf/secrets` excluding staging and backup-key/OAuth directories, and
  `/home/shelf/android/keys` signing key, password, properties and certificate.
- Current cPanel-generated public `.htaccess` and Shelf crontab.
- SHA-256 inventory for files/media and recovery metadata, inside the encryption.

Staging packages include only staging's independent database, storage, environment
and secrets. A production backup is never imported into staging. Neither package
includes server-global configuration, other accounts/projects, caches, logs,
dependencies that can be installed from locks, or TLS certificates (reissue them).
Recovery includes recreating the Shelf cPanel account/database, domain/document
root/HTTPS, tools and scheduler. It does not restore the entire host automatically.
Never overwrite active reader/purchase data with an older backup.

Local daily backups and their 14-copy retention remain unchanged. Offsite work is
under `/home/shelf/backups/shelf/offsite/`, outside the dated local retention set.
Plaintext workspace files are private, under `/home/shelf/tmp`, removed after each
run. AES256 encryption uses a random 256-bit recovery secret read through a file
descriptor, never command-line arguments, environment values, Git, chat or logs.

## The owner's one OneDrive authorization step

**CHANGES — on the owner's Windows PC, in PowerShell.** This SSH tunnel sends
the browser callback to the server; rclone writes OAuth privately. No password or
token is pasted into chat. The setup script below is at the staged/prepared checkout
until promotion. Keep this terminal open during browser authorization.

```powershell
ssh -t -o ExitOnForwardFailure=yes -L 53682:127.0.0.1:53682 root@157.250.199.106 "su - shelf -c 'python3 -B /home/shelf/tmp/shelf-onedrive-backup/scripts/shelf_offsite_backup.py authorize'"
```

Open the **127.0.0.1 browser link printed in that private terminal** on the same
PC. Sign in to the existing OneDrive and approve rclone access. If selection is
requested, choose that OneDrive (Personal or Business), not a SharePoint site.
The tool normally selects the default drive. Tell Codex only **authorized**.
Never send the terminal output, browser URL, tokens or configuration contents.

## Retain the recovery key outside this server

The key is `/home/shelf/secrets/backup/recovery-key.secret`, mode 0600, directory
0700. It is deliberately **not inside its own encrypted archive or OneDrive**.
Server loss without an independently retained key makes the backup unusable.

On the owner's PC, use a private folder on an encrypted disk, not a OneDrive-synced
folder, then download the file directly over SSH:

```powershell
scp "root@157.250.199.106:/home/shelf/secrets/backup/recovery-key.secret" .\shelf-recovery-key.secret
```

Import its contents privately into the owner's password manager or store the file
on an encrypted offline USB. Keep a second secure copy apart from this server and
OneDrive. Delete the temporary PC plaintext file after securing the copies. Never
paste it into chat, email or screenshots. Retain this runbook with the key. Do not
rotate/delete the key while older backups depend on it. Microsoft/GitHub/server
account recovery and MFA recovery codes must also be retained by the owner.

## After authorization — Codex's remaining work

1. Read OneDrive capacity using `quota` (`rclone about --json`). Record only total,
   used and free bytes, and encrypted package size. Choose a capacity-based policy
   then; retention is unset before connection. This integration is append-only:
   retained remote copies are never removed automatically. Reserve at least 100 MB
   free and alert rather than consume the last space. Document a manual oldest-copy
   review cadence/capacity forecast using actual quota and daily package size.
2. Verify the selected drive and dedicated folder. Upload only encrypted packages.
3. Run `drill --package PATH`: download ciphertext, compare SHA-256, decrypt,
   extract safely, compare every configuration and source/media file, unpack code,
   and import SQL into a temporary MariaDB **with no network listener**, its own
   datadir/socket, no production/staging DB credentials. Re-dump and compare schema
   and rows. Shut it down and remove that run's workspace. Evidence is private JSON
   under the offsite directory. A local drill alone is not remote recovery evidence.
4. Promote only the identical tested staging commit. Preserve production's existing
   cPanel `.htaccess` block. No database schema/data migration is introduced here.
5. Retain the recovery key independently. Verify generic failure recording and owner-only notification handling with a
   mocked transport; do not send an owner test notification. Real email delivery
   remains unverified until an actual authorized failure alert occurs.
6. Set the documented retention policy in the private settings file, then use
   `install-schedule`. It requires successful production remote-recovery evidence,
   takes a fresh verified local backup and saves the previous crontab. It adds only
   the 03:30 daily job, preserving 03:00 local backup and existing purchase jobs.
7. Exercise `scheduled` once; inspect cron and last-success/evidence. Each scheduled
   run creates, encrypts, uploads, downloads and restores a fresh backup. On failure
   it records `failure.json`, exits nonzero and sends a generic owner email using
   the server's existing sendmail. Do not send a test notification; record actual delivery as unverified.

**READ-ONLY — server as shelf, after promotion:**

```bash
cd /home/shelf/apps/shelf
python3 -B scripts/shelf_offsite_backup.py quota
```

For restore after total server loss, download a `.tar.gpg` **and its `.tar.json`
checksum/metadata sidecar** from Shelf-Backups on
the replacement machine, retrieve the separately retained key, and install GnuPG
and matching MariaDB. Place the retained key at the private KEY path above (0600)
and create its private gnupg directory (0700). With the recovered script, run:

```bash
# CHANGES — replacement server as shelf; creates only a new isolated directory.
python3 -B scripts/shelf_offsite_backup.py recover --package /home/shelf/tmp/DOWNLOADED-SHELF-NAME.tar.gpg --destination /home/shelf/tmp/shelf-recovered
```

Use the actual downloaded filename; the `.tar.json` must be beside it. This verifies
and retains restored files and proves SQL import in an isolated socket-only DB,
which is shut down afterward. Restore code from code.tar, install dependencies from
locks, restore storage to a new private application location, secure configuration
files, and create a new DB user. Preserve the recovered APP_KEY, change infrastructure
credentials/paths as required, reissue HTTPS and validate protected access. Keep
mail, payments, webhooks and scheduler disabled until the owner-approved cutover.
Do not run migrations as a substitute for importing historical records. A live
replacement/cutover needs its own backup and owner approval; this drill never
replaces production.

## Stop/undo and evidence

Disable only the added 03:30 offsite cron or set schedule_enabled=false privately;
preserve the other jobs and all local/remote backups. Code rollback returns to the
prior commit; no database rollback is needed. To disconnect, revoke rclone's grant
in the Microsoft account and remove only Shelf's private OAuth config after keeping
required recovery material. Do not delete the recovery key or unrelated remotes.

Focused checks run through `scripts/run_tests.sh --backup`. Staging's backup-only
gate is limited to these scripts/runbook/governing records. Application changes
still require their established test gate. Remote recovery and scheduling evidence
will be added only after they actually pass.

Official references consulted: [rclone OneDrive](https://rclone.org/onedrive/),
[SSH tunnel authorization](https://rclone.org/remote_setup/#configuring-using-ssh-tunnel),
[copyto](https://rclone.org/commands/rclone_copyto/),
[quota](https://rclone.org/commands/rclone_about/),
[GnuPG file-descriptor passphrases](https://www.gnupg.org/documentation/manuals/gnupg/GPG-Esoteric-Options.html),
[MariaDB dump/recovery](https://mariadb.com/docs/server/clients-and-utilities/backup-restore-and-import-clients/mariadb-dump).

## Authorization recovery — 3 October 2026

The interrupted browser flow saved access and refresh credentials privately, but
no drive ID/type. Graph `/me/drives` returned several handles whose root query
failed with HTTP 400 invalidRequest / ObjectHandle is Invalid; the canonical
default `/me/drive` returned a working personal drive. Setup now resumes existing
OAuth, verifies default drive root identity and quota before saving selection,
and stops persistent errors without repeating browser sign-in or regenerating
the encryption key. Initial browser setup has a five-minute deadline and stops
on its first discovery error; raw provider output is withheld.

Default personal root verified, quota total 1,104,880,336,896 bytes, used
181,891,864,986 bytes, free 922,988,471,910 bytes. This is connection evidence only;
upload/download/isolated restore and scheduling remain required.

## External recovery key confirmation — 3 October 2026

Owner confirms SCP completed (100%, 65 bytes) to
`D:\shelf-recovery-key.secret` on a separate **unencrypted USB**. This is
owner-confirmed external storage, not agent verification or encrypted storage.
The existing server key is preserved. Possession of the USB key permits backup
decryption; retain it securely. No key rotation or extra owner action is required
for this authorized task.

Retention policy: retain encrypted OneDrive copies and local offsite ciphertext
append-only, with no automatic deletion. Existing local nightly backups keep their
unchanged 14-copy policy. Review offsite capacity and oldest copies monthly;
reserve 100 MiB free and fail rather than consume that reserve. No unrelated
OneDrive files are listed for retention or deleted.

## Deployment and remote recovery evidence — 3 October 2026

Implementation `44ce420d2bef9113380b58e3a738c86d7e31d49c` passed
14 focused backup tests on staging and direct HTTPS isolation/health checks.
Staging local encrypted drill restored 34 tables and 88 media/source files.
Identical-commit production promotion passed history/health checks; verified
backup `/home/shelf/backups/shelf/20261003-200920/`. Runtime cPanel PHP handler
was preserved. The final evidence/record update is also staged and promoted;
the authoritative exact final commit and backup are in
`/home/shelf/staging-runtime/release-state.json`.

Production encrypted package:
`shelf-production-20261003T201052Z-4660ab40d2edb6b5.tar.gpg`
(794,019,925 bytes), OneDrive folder **Shelf-Backups**. Ciphertext SHA-256:
`8acf529fa0127770b85c860f066fc93617e2f8b40b49dd4b4a6ae65a7229c48d`.
Uploaded by immutable copy, downloaded back, checksum/decryption/extraction
passed. Restore at **2026-10-03 20:31:01 UTC** imported **34 tables** in a
disposable socket-only MariaDB with networking, event scheduler and replication
startup disabled. Canonical schema/rows re-dump was identical. All **774
media/source files**, required environment/provider/signing files, runtime
handler/crontab and Git code archive matched their inventories/checksums.
Downloaded `.tar.json` sidecar independently matched local metadata. Restored
Laravel was never booted: no restored-app mail, payments, webhooks or cron.
The isolated database was stopped and its private workspace removed.

Schedule: **03:30 daily, America/Lower_Princes (AST, UTC−04:00), 07:30 UTC**.
The pre-schedule verified backup and previous crontab are at
`/home/shelf/backups/shelf/20261003-203119/`. Exactly one offsite job was
installed; existing 03:00 local, disabled product-sync runner and 15-minute
refund jobs were preserved. Every run uploads and downloads/restores a new
encrypted package. Check `last-success.json`, `last-upload.json`, recovery
evidence and `storage/logs/offsite-backup.log`; if timestamps are older than
a day, ask Codex to investigate rather than assuming a cron entry proves success.

At current package size/free capacity, roughly 1,162 further daily copies fit
before other OneDrive use or package growth. This is a planning estimate, not a
guarantee. Monthly review remains necessary. Neither remote copies nor local
offsite ciphertext are automatically deleted. On-server nightly retention is
still 14. Folder-capacity shortage fails without deletion; existing copies stay.

Failure verification injected a synthetic packaging failure in temporary files,
proved private failure recording and exception propagation, and intercepted
the generic owner-only sendmail request. **No owner test notification sent;
actual email delivery is unverified.** Safe evidence:
`/home/shelf/backups/shelf/offsite/failure-handling-check.json`. The first
unattended 03:30 run is not claimed passed by a manual scheduler exercise.

For replacement-server recovery, retain this runbook and the USB key, download
both the chosen `.tar.gpg` and `.tar.json` from Shelf-Backups, then use the
`recover` procedure above. It verifies/imports only a new isolated workspace,
not the live database. Code/dependencies/domain/HTTPS reinstallation and any
production cutover still need the documented owner-approved plan. A complete
replacement-host cutover has not been exercised.

Manual scheduled-entry evidence (3 October 2026): fresh package
`shelf-production-20261003T203339Z-db49e08f6b471fde.tar.gpg` uploaded,
downloaded and restored successfully at 20:35:10 UTC (34 tables, 774
media/source files, schema/rows and configuration/code checksums identical).
`/home/shelf/backups/shelf/offsite/last-success.json` records successful
completion with recovery_verified=true. This verifies the installed entry point,
not an unattended cron launch.
