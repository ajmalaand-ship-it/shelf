<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class BackupVerificationTest extends TestCase
{
    public function test_backup_integrity_command_accepts_restorable_artifacts(): void
    {
        $directory = storage_path('framework/testing/backup-test');
        File::deleteDirectory($directory);
        File::ensureDirectoryExists($directory);
        File::put($directory.'/database.sql', '-- MariaDB dump\nCREATE TABLE test_only (id int);');
        $zip = new ZipArchive;
        $zip->open($directory.'/media.zip', ZipArchive::CREATE);
        $zip->addFromString('app/source/TEST-ONLY.txt', 'test');
        $zip->close();
        File::put($directory.'/manifest.json', json_encode([
            'database' => 'database.sql',
            'database_sha256' => hash_file('sha256', $directory.'/database.sql'),
            'media' => 'media.zip',
            'media_sha256' => hash_file('sha256', $directory.'/media.zip'),
        ], JSON_THROW_ON_ERROR));

        $this->artisan('poetry:backup-verify', ['directory' => $directory])->assertSuccessful();

        File::deleteDirectory($directory);
    }
}
