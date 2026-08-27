# P0/P1 Reconciliation — 2026-08-27

Task: SYSC-P0-P1-RECONCILE-01

This record uses the approved umbrella Constitution v1.2/A-002 supplied with
the task. The repository copy is v1.1/A-001; exact A-002 text is not locally
available and has not been invented.

## Starting reality

The repository contained governance documents only. The live HTTPS virtual
host returned an Apache directory index from an otherwise empty System C
document root. No Laravel application, Filament panel, schema, content,
recordings, covers, production database, service, or System C backup was
verified. HTTP did not redirect to HTTPS.

## P0 classification

| Requirement | Status | Evidence / minimum remaining input |
| --- | --- | --- |
| Product/app name | PARTIAL | Working name is “Pashto Poetry”; owner confirmation is still required. |
| Brand identity | OWNER CONTENT REQUIRED | Supply approved logo, colors, and store-facing identity. |
| First collection | OWNER CONTENT REQUIRED | Supply the approved collection identity and metadata. |
| 30–50 proofread Unicode poems | OWNER CONTENT REQUIRED | Supply final proofread Unicode text with preserved stanza breaks. |
| 10–15 free samples | OWNER CONTENT REQUIRED | Identify which supplied poems are free samples. |
| At least 10 Ajmal voice recordings | OWNER CONTENT REQUIRED | Supply original recordings and approved metadata. |
| Fonts and reading themes | OWNER CONTENT REQUIRED | Confirm approved Pashto font(s) and theme direction. |
| Off-server source-text backup | MISSING | No source corpus exists here and no off-server backup evidence was found. |
| Off-server original-audio backup | MISSING | No recordings exist here and no off-server backup evidence was found. |
| Play developer verification | EXTERNAL / STORE ACTION REQUIRED | Provide Play Console evidence; no local evidence exists. |
| Closed-test testers | EXTERNAL / STORE ACTION REQUIRED | Provide current tester list/status; no local evidence exists. |
| Privacy/terms preparation | MISSING | No drafts were found. Store-facing completion belongs to the later release gate. |

No poem, translation, recording, cover, or owner fact was fabricated.

## P1 classification

| Requirement | Status | Evidence / remaining gate |
| --- | --- | --- |
| Laravel API foundation | COMPLETE (code/test) | Laravel 12 routes, controllers, resources, and tests exist. |
| Filament owner administration | COMPLETE (code/test) | Collections, poems, settings, ordering, status, covers, and audio forms exist and are tested with TEST records. |
| Collections / poems / settings schema | COMPLETE (code/test) | Migrations include the Roadmap Revision 2 fields. |
| utf8mb4 and poem LONGTEXT | COMPLETE | Production schema/connection and poem body are utf8mb4_unicode_ci; body is LONGTEXT; exact Pashto/stanza round-trip passed. |
| Cover uploads | COMPLETE (code/test) | Public cover disk; JPEG/PNG/WebP allowlist; 5 MB limit. |
| Audio uploads | COMPLETE (code/test) | Private audio disk; audio MIME allowlist; 50 MB limit; hostile upload rejection tested. |
| Publication/order/sample controls | COMPLETE (code/test) | Active, sort order, free-sample, product mapping fields are present. |
| Public API | COMPLETE (code/test) | App config, collections, collection poems, poem detail, and audio metadata routes are covered. |
| Locked-content safety | COMPLETE (code/test) | Non-sample full body and audio URL are withheld. |
| Isolated production database/user | COMPLETE | Dedicated ajmalaand_poetry database/user connect successfully and are distinct from Systems A/B. |
| Isolated environment/cookie/media/logs | COMPLETE | Mode-0600 environment, distinct application key, host-only poetry_session, private audio, public covers, and application logs are active. |
| PHP runtime prerequisites | COMPLETE | ea-php83 ext-zip and ext-gd are loaded; Composer platform requirements pass. |
| HTTPS deployment | COMPLETE | HTTPS serves Laravel, HTTP redirects canonically, directory indexing is disabled, and anonymous admin access redirects to login. |
| Backup/restore gate | COMPLETE (local) | Mode-0700 System C backup directories contain checksummed production database and scoped media/source archives; integrity/readability passed. Off-server owner evidence remains a P0 owner requirement. |

## Content inventory

Actual owner content verified in repository, storage, or live API:

- Collections: 0
- Poems: 0
- Free samples: 0
- Audio recordings: 0
- Covers: 0

Automated tests create disposable TEST records only.

## Isolation and phase decision

The repository and domain document root are distinct. Code defines a separate
database connection, host-only cookie, media roots, logs, and Laravel runtime.
No System A/B database, secret, media, process, or code was changed. Production
database/user, environment, backups, and deployed runtime were verified for
System C only.

P0 is incomplete because owner content and external store/tester evidence are
absent. P1 technical acceptance is complete: admin behavior, uploads, ordering,
publication/sample state, MariaDB Unicode, public-safe API behavior, locked
denial, signed free audio, backups, isolation, and HTTPS deployment passed.
No permanent owner admin login was invented; Ajmal must create the owner login
with his chosen email/password before content entry. P2 is technically ready
only after the owner reviews this gate and explicitly authorizes it.

## Production acceptance

The cPanel WHM API provisioning path created only the Poetry database/user and
granted that user access only to the Poetry database. The script safely resumes
after partial success without recreating database objects. Deployment preserved
a copy of the former document root and created pre-deployment System C backups.
Live TEST ONLY records verified the complete public API and media behavior, then
were deleted; production content counts returned to zero.

## Narrow governance follow-up

Obtain the exact authoritative Constitution v1.2 and Amendment A-002 text, then
perform a separate governance-only sync of the repository copies. Product work
continues under the already-approved umbrella authority supplied in this task.
