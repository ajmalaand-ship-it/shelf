<?php

return [
    'backup_path' => env('POETRY_BACKUP_PATH', '/home/ajmalaand/backups/poetry'),
    'owner_archive_path' => env('POETRY_OWNER_ARCHIVE_PATH', '/home/ajmalaand/backups/poetry/owner-downloads'),
    'audio_inbox_path' => env('POETRY_AUDIO_INBOX_PATH', storage_path('app/private/audio-inbox')),
    'source_archive_path' => env(
        'POETRY_SOURCE_ARCHIVE_PATH',
        storage_path('app/source/collections/tsapo-ke-anzorona/authoritative-source.pdf'),
    ),
];
