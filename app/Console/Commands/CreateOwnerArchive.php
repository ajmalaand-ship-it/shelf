<?php

namespace App\Console\Commands;

use App\Models\Collection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use ZipArchive;

class CreateOwnerArchive extends Command
{
    protected $signature = 'poetry:owner-archive';

    protected $description = 'Create a secret-free owner-downloadable System C preservation archive';

    public function handle(): int
    {
        if (config('database.default') !== 'mysql') {
            $this->error('Owner archive creation requires the isolated production MySQL connection.');

            return self::FAILURE;
        }

        $root = rtrim((string) config('poetry.owner_archive_path'), '/');
        File::ensureDirectoryExists($root, 0700, true);
        chmod($root, 0700);
        $stamp = now()->format('Ymd-His');
        $archivePath = "{$root}/poetry-owner-offserver-{$stamp}.zip";
        $checksumPath = $archivePath.'.sha256';
        $dump = storage_path("framework/cache/poetry-owner-{$stamp}.sql");

        $database = (string) config('database.connections.mysql.database');
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

        $zip = new ZipArchive;
        if ($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
            File::delete($dump);
            throw new \RuntimeException('Could not create owner archive.');
        }

        try {
            $checksums = [];
            $this->addFile($zip, $dump, 'database/database.sql', $checksums);
            $catalogue = json_encode([
                'exported_at' => now()->toIso8601String(),
                'system' => 'Shelf',
                'collections' => Collection::query()->with(['poems' => fn ($query) => $query->orderBy('sort_order')])
                    ->orderBy('sort_order')->get()->toArray(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n";
            $zip->addFromString('catalogue/catalogue.json', $catalogue);
            $checksums['catalogue/catalogue.json'] = hash('sha256', $catalogue);

            foreach ([
                storage_path('app/source') => 'sources',
                storage_path('app/public/covers') => 'covers',
                storage_path('app/private/audio') => 'audio',
                storage_path('app/private/artwork') => 'artwork',
            ] as $directory => $prefix) {
                if (! is_dir($directory)) {
                    continue;
                }
                foreach (File::allFiles($directory) as $file) {
                    $this->addFile($zip, $file->getPathname(), $prefix.'/'.$file->getRelativePathname(), $checksums);
                }
            }

            $readme = "Shelf owner off-server preservation package\n"
                ."Contains a clean database export, portable catalogue JSON, private source/manifests, covers, and original audio currently on the server.\n"
                ."Explicitly excludes .env files, application secrets, database passwords, private keys, and credentials.\n";
            $zip->addFromString('README.txt', $readme);
            $checksums['README.txt'] = hash('sha256', $readme);
            ksort($checksums);
            $checksumManifest = collect($checksums)
                ->map(fn (string $hash, string $name): string => "{$hash}  {$name}")
                ->implode("\n")."\n";
            $zip->addFromString('SHA256SUMS', $checksumManifest);
        } finally {
            $zip->close();
            File::delete($dump);
        }

        chmod($archivePath, 0600);
        File::put($checksumPath, hash_file('sha256', $archivePath).'  '.basename($archivePath)."\n");
        chmod($checksumPath, 0600);

        $this->call('poetry:owner-archive-verify', ['archive' => $archivePath]);
        $this->info("Owner archive created: {$archivePath}");

        return self::SUCCESS;
    }

    /** @param array<string, string> $checksums */
    private function addFile(ZipArchive $zip, string $source, string $name, array &$checksums): void
    {
        if (! $zip->addFile($source, $name)) {
            throw new \RuntimeException("Could not add {$name} to owner archive.");
        }
        $checksums[$name] = hash_file('sha256', $source);
    }
}
