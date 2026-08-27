<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ZipArchive;

class VerifyPoetryBackup extends Command
{
    protected $signature = 'poetry:backup-verify {directory?}';

    protected $description = 'Verify a System C backup manifest and readable media archive';

    public function handle(): int
    {
        $directory = $this->argument('directory');
        if (! $directory) {
            $candidates = collect(File::directories((string) config('poetry.backup_path')))->sortDesc();
            $directory = $candidates->first();
        }
        if (! $directory || ! is_file($directory.'/manifest.json')) {
            $this->error('No System C backup manifest found.');

            return self::FAILURE;
        }

        $manifest = json_decode(File::get($directory.'/manifest.json'), true, flags: JSON_THROW_ON_ERROR);
        foreach (['database', 'database_sha256', 'media', 'media_sha256'] as $key) {
            if (empty($manifest[$key])) {
                throw new \RuntimeException('Incomplete backup manifest.');
            }
        }
        $database = $directory.'/'.$manifest['database'];
        $media = $directory.'/'.$manifest['media'];
        if (! hash_equals($manifest['database_sha256'], hash_file('sha256', $database)) ||
            ! hash_equals($manifest['media_sha256'], hash_file('sha256', $media))) {
            throw new \RuntimeException('Backup checksum mismatch.');
        }
        if (! str_contains((string) File::get($database), 'MariaDB dump') &&
            ! str_contains((string) File::get($database), 'MySQL dump')) {
            throw new \RuntimeException('Database dump is not recognizable.');
        }
        $zip = new ZipArchive;
        if ($zip->open($media, ZipArchive::CHECKCONS) !== true) {
            throw new \RuntimeException('Media archive cannot be restored.');
        }
        $zip->close();

        $this->info('System C backup integrity verified: '.$directory);

        return self::SUCCESS;
    }
}
