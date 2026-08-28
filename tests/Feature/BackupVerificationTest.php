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

    public function test_owner_archive_verifier_checks_member_and_archive_checksums(): void
    {
        $directory = storage_path('framework/testing/owner-archive-test');
        File::ensureDirectoryExists($directory);
        $archive = $directory.'/owner.zip';
        $members = [
            'database/database.sql' => '-- MariaDB dump',
            'catalogue/catalogue.json' => '{"collections":[]}',
            'README.txt' => 'secret-free owner archive',
        ];
        $zip = new ZipArchive;
        $zip->open($archive, ZipArchive::CREATE);
        foreach ($members as $name => $contents) {
            $zip->addFromString($name, $contents);
        }
        $checksums = collect($members)
            ->map(fn (string $contents, string $name): string => hash('sha256', $contents).'  '.$name)
            ->implode("\n")."\n";
        $zip->addFromString('SHA256SUMS', $checksums);
        $zip->close();
        File::put($archive.'.sha256', hash_file('sha256', $archive).'  owner.zip'."\n");

        $this->artisan('poetry:owner-archive-verify', ['archive' => $archive])->assertSuccessful();

        File::deleteDirectory($directory);
    }
}
