<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use ZipArchive;

class BackupPoetry extends Command
{
    protected $signature = 'poetry:backup';

    protected $description = 'Create a private System C database and source-media backup';

    public function handle(): int
    {
        if (config('database.default') !== 'mysql') {
            $this->error('Production backup requires the isolated MySQL connection.');

            return self::FAILURE;
        }

        $root = rtrim((string) config('poetry.backup_path'), '/');
        $stamp = now()->format('Ymd-His');
        $directory = $root.'/'.$stamp;
        File::ensureDirectoryExists($directory, 0700, true);

        $database = (string) config('database.connections.mysql.database');
        $dump = $directory.'/database.sql';
        $process = new Process([
            'mysqldump', '--single-transaction', '--quick', '--skip-lock-tables',
            '--host='.(string) config('database.connections.mysql.host'),
            '--port='.(string) config('database.connections.mysql.port'),
            '--user='.(string) config('database.connections.mysql.username'),
            '--result-file='.$dump,
            $database,
        ], null, ['MYSQL_PWD' => (string) config('database.connections.mysql.password')]);
        $process->mustRun();
        chmod($dump, 0600);

        $archive = $directory.'/media.zip';
        $zip = new ZipArchive;
        if ($zip->open($archive, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
            throw new \RuntimeException('Could not create media backup archive.');
        }

        $zip->addFromString('.backup-scope.json', json_encode([
            'paths' => ['app/public/covers', 'app/private/covers', 'app/private/audio', 'app/private/artwork', 'app/source'],
        ], JSON_THROW_ON_ERROR));

        foreach (['app/public/covers', 'app/private/covers', 'app/private/audio', 'app/private/artwork', 'app/source'] as $relative) {
            $path = storage_path($relative);
            if (! is_dir($path)) {
                continue;
            }
            foreach (File::allFiles($path) as $file) {
                $zip->addFile($file->getPathname(), $relative.'/'.$file->getRelativePathname());
            }
        }
        $zip->close();
        chmod($archive, 0600);

        $manifest = [
            'created_at' => now()->toIso8601String(),
            'system' => 'System C — Pashto Poetry Platform',
            'database' => basename($dump),
            'database_sha256' => hash_file('sha256', $dump),
            'media' => basename($archive),
            'media_sha256' => hash_file('sha256', $archive),
        ];
        File::put($directory.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
        chmod($directory.'/manifest.json', 0600);

        $this->info('System C backup created and checksummed: '.$directory);

        return self::SUCCESS;
    }
}
