<?php

namespace App\Console\Commands;

use App\Models\Collection;
use App\Models\Poem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

class ImportPoetryManifest extends Command
{
    protected $signature = 'poetry:import-manifest {manifest} {--apply}';

    protected $description = 'Validate and idempotently import a private poetry source manifest';

    public function handle(): int
    {
        $path = $this->argument('manifest');
        $path = str_starts_with($path, '/') ? $path : storage_path('app/'.$path);

        if (! File::isFile($path)) {
            $this->error('Manifest not found.');

            return self::FAILURE;
        }

        $manifest = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);
        $this->validateManifest($manifest);

        $collection = Collection::query()->where('title', $manifest['collection']['title'])->sole();
        $poems = collect($manifest['poems']);
        $existing = $collection->poems()->orderBy('sort_order')->get();

        if ($existing->isNotEmpty()) {
            $matches = $existing->count() === $poems->count()
                && $existing->values()->every(function (Poem $poem, int $index) use ($poems): bool {
                    $source = $poems[$index];

                    return $poem->sort_order === $source['sequence']
                        && $poem->title === $source['title']
                        && hash_equals(hash('sha256', $poem->body), hash('sha256', $source['body']));
                });

            if (! $matches) {
                throw new RuntimeException('Collection already has conflicting poem data; import aborted without changes.');
            }

            $this->info('Manifest already imported exactly; no changes made.');

            return self::SUCCESS;
        }

        if (! $this->option('apply')) {
            $this->info("Validated {$poems->count()} poems; use --apply to import.");

            return self::SUCCESS;
        }

        DB::transaction(function () use ($collection, $poems): void {
            foreach ($poems as $source) {
                $collection->poems()->create([
                    'title' => $source['title'],
                    'body' => $source['body'],
                    'excerpt' => str($source['body'])->before("\n")->limit(500)->toString(),
                    'sort_order' => $source['sequence'],
                    'is_free_sample' => false,
                    'is_active' => false,
                    'audio_path' => null,
                    'audio_duration_seconds' => null,
                ]);
            }
        });

        $this->info("Imported {$poems->count()} draft poems into the existing collection.");

        return self::SUCCESS;
    }

    private function validateManifest(array $manifest): void
    {
        foreach (['source', 'collection', 'poems'] as $key) {
            if (! array_key_exists($key, $manifest)) {
                throw new RuntimeException("Manifest is missing {$key}.");
            }
        }

        $source = (string) config(
            'poetry.source_archive_path',
            storage_path('app/source/collections/tsapo-ke-anzorona/authoritative-source.pdf'),
        );
        if (! File::isFile($source) || ! hash_equals($manifest['source']['sha256'], hash_file('sha256', $source))) {
            throw new RuntimeException('Authoritative source checksum mismatch.');
        }

        $sequences = array_column($manifest['poems'], 'sequence');
        if ($sequences !== range(1, count($sequences))) {
            throw new RuntimeException('Poem sequence is not continuous.');
        }

        foreach ($manifest['poems'] as $poem) {
            if (! is_bool($poem['untitled']) || $poem['untitled'] !== ($poem['title'] === null)) {
                throw new RuntimeException('Poem title/untitled state is inconsistent.');
            }
            if (trim($poem['body']) === '' || $poem['source_page_start'] > $poem['source_page_end']) {
                throw new RuntimeException('Poem body or source pages are invalid.');
            }
        }
    }
}
