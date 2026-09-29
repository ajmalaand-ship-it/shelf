# Word import (Task 5b)

Owner approval: 28 September 2026. Import is available only from a book's Content
relation manager. The button becomes available after the migration below.

Upload one `.docx` up to 20 MB, click **Preview**, review all items and warnings,
then click **Import**. Cancel changes no book records and removes the staged upload.

- Heading 1 paragraphs supply exact titles. Lower headings stay in the body.
- A line exactly equal to `***` starts an untitled item. Surrounding spaces make it
  an ordinary text line. Text before the first marker becomes an untitled first item.
- Paragraph boundaries and manual breaks become newlines. Blank paragraphs remain
  blank lines. Only empty boundary lines are removed; spaces, tabs, RTL marks,
  combining characters and all other source characters are preserved.
- Images, tables, notes, comments and tracked-change content are not imported and
  produce warnings. Tracked inserted/deleted/moved text is excluded, not accepted
  or reconstructed. Empty items are visibly warned about and retained as drafts.
- Unsupported/malformed documents are rejected; no XML or source text is repaired.
  Archive expansion is limited to 64 MB and 10,000 ZIP entries for safe parsing.
- Imported items are appended as locked drafts, with blank public excerpts. The
  owner reviews publication, samples and excerpts separately. Audio/artwork and
  source fields remain editable through the existing content editor.
- Originals are immutable private files in `storage/app/source/imports/<book-id>/`.
  `word_imports` records SHA-256, original name, importer, time, count and item IDs.
  Retrying the same confirmed preview does not duplicate items. All database writes
  are transactional. If a database write fails after archiving, the original is
  retained for retry; it is never deleted or overwritten.

## Owner commands: backup, migrate, check

Run as `shelf` on Shelf only. These commands have NOT been run against the live database.
The backup helper reuses the existing verified database/media backup routines, stages
all temporary files in `/home/shelf/tmp`, and writes the verified backup plus a path
note under Shelf's configured backup directory. It does not prune backups.

```bash
cd /home/shelf/apps/shelf
export TMPDIR=/home/shelf/tmp TMP=/home/shelf/tmp TEMP=/home/shelf/tmp
# BACKUP, MIGRATE, CHECK: stop immediately if any step fails.
bash scripts/backup_word_import.sh && \
php artisan migrate --path=database/migrations/2026_09_28_160000_create_word_imports_table.php --force && \
php scripts/check_word_import.php
```

The new migration's `down()` drops only the import audit table. It does not delete
imported content or source originals. Take a backup before any owner-approved rollback
so audit metadata can be recovered. Imported drafts can be moved to the book's bin.

The PHP web upload limits are now 20 MB per file / 24 MB per request; PHP may cache
`.user.ini` briefly. Other form fields retain their own limits (covers stay at 5 MB).
Livewire stages uploads privately under `/home/shelf/tmp/uploads` and removes expired
uploads through its standard cleanup. Automated tests use their isolated run directory.

## Verification

Run `scripts/run_tests.sh`. No files in `mobile/` are changed by this task, so do not
use `--mobile`. Synthetic tests cover Pashto, Farsi, English, Unicode preservation,
poetry/prose, splitting, preview/cancel, warnings, append-only writes, provenance,
checksums, invalid files, transaction rollback and reversible schema changes.
