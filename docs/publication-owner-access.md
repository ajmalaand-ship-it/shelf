# Publication and owner access — Tasks 6 + 7

Approved 28 September 2026. No live migration or cover move is performed by the coding agent.

## Behavior

Books have `draft`, `ready`, `published`, or `withdrawn` status. The former book
`is_active` column is retained only as an untouched rollback snapshot; it never
controls current public access. Items retain their own Visible (`is_active`) flag.
Public lists (including author pages), slug/detail/content requests, and signed
item media recheck publication and item visibility. Owner preview remains private
and can inspect Draft and Ready books.

Publish requires a supported language, author credit, existing private cover and
at least one visible, non-binned item. The table action and edit form enforce this;
failed form saves roll back changes and status audit entries. `book_status_changes`
records every transition's old/new state, actor and time, alongside latest-status
fields. Unpublish returns to Draft; Withdraw preserves every item and file.
`Collection::allowsPriorPurchaserAccess()` is a fail-closed Step 4 extension point:
it does not yet grant access or interpret the old global unlock as ownership.

Public app discovery and content requests need a successful server check. Old
public cache cannot restore a book after a connection failure or 404. The migration
increments content_version; public catalogue refresh always fetches the server
list. Owner preview retains its separate offline cache. Already delivered bytes
or an already-open screen cannot be recalled remotely; verified purchaser offline
access belongs to Step 4. This task does not implement per-book purchases.

## Owner login

`is_owner` defaults false and is not mass assignable. The migration marks the one
existing user; it stops before DDL if multiple users exist. Authentication plus the
owner flag gates admin, including the login page for an already-authenticated
non-owner, and persistent Livewire requests. Filament limits login to five attempts
per minute and rate-limits MFA challenges. In Profile, the owner may choose
Authentication app → Set up, scan the QR code and verify a code. Save the recovery
codes privately. The authenticator secret and recovery-code data are encrypted and
hidden from serialization. MFA is optional and remains disabled until owner setup.

## Covers and owner rollout

The covers disk is private at `storage/app/private/covers`. Published cover routes
use public caching with mandatory revalidation; unpublished covers require an owner
session or a short-lived signed owner-preview URL. Unsigned owner-preview routes
are denied. Old `/storage/covers/...` URLs are blocked by the web server, including
copies not yet moved. Unreferenced files remain physically unchanged.

Run the following as shelf. Stop if any command fails. Backup includes database,
public and private media, and source originals. Maintenance stays on if migration
or relocation fails; investigate before continuing. Cached config/routes are cleared
before migration so the private disk and new routes are active.

```bash
set -euo pipefail
cd /home/shelf/apps/shelf
export TMPDIR=/home/shelf/tmp TMP=/home/shelf/tmp TEMP=/home/shelf/tmp
mkdir -p /home/shelf/tmp
python3 scripts/shelf_daily_backup.py
php artisan down
php artisan optimize:clear
php artisan migrate --force
php artisan shelf:move-covers --after-backup
php artisan up
php artisan shelf:check-publication
```

The move command lists all unreferenced files, preflights every referenced path
(including binned books), rejects symlinks and differing destinations, and moves
files without changing the stored relative path or bytes. A rerun finishes safely.
The check is read-only: it prints each book's status and private-cover location,
legacy-copy presence and owner count, then uses HTTPS curl for each book slug,
content list, protected cover route and legacy static cover URL. It exits nonzero
on any mismatch. Expected immediately after rollout: all six Draft, one owner,
referenced covers private, every tested book/content/cover public request 404.

Owner-confirmed dry-run inventory (three unreferenced files; untouched):

- `storage/app/public/covers/da-shaar-hagha-gharona/historical-cover-page.png`
- `storage/app/public/covers/dalta-der-lare-la-gharono/original-cover.jpg`
- `storage/app/public/covers/tsapo-ke-anzorona/original-cover-page-1.webp`

Six referenced files were confirmed in the same inventory. The move command
recomputes these references at execution time, so it never guesses from filenames.

## Undo

Use a fresh backup and maintenance mode. Before rolling back code/schema, the owner
can run `php artisan shelf:move-covers --after-backup --restore-public` with this
code still present. It restores only referenced files and refuses conflicts.
Rollback only the new `2026_09_28_180000_add_publication_and_owner_access.php`
migration (verify it is the latest applied migration before using `migrate:rollback
--step=1`). Restore the prior code commit and clear cached configuration/routes.
The migration's down restores the old schema with original on/off values intact;
it removes the new status audit and MFA columns, so retain the backup. Reverting
code removes the new protection and must be an explicit owner choice. No rollback
is performed automatically.
