<?php

return [
    'backup_path' => env('POETRY_BACKUP_PATH', '/home/ajmalaand/backups/poetry'),
    'source_archive_path' => env(
        'POETRY_SOURCE_ARCHIVE_PATH',
        storage_path('app/source/collections/tsapo-ke-anzorona/authoritative-source.pdf'),
    ),
];
