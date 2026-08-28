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

        $collection = Collection::query()->where('title', $manifest['collection']['title'])->first();
        $poems = collect($manifest['poems']);

        if (! $collection && ! $this->option('apply')) {
            $this->info("Validated {$poems->count()} works and one new collection; use --apply to import.");

            return self::SUCCESS;
        }

        $existing = $collection?->poems()->orderBy('sort_order')->get() ?? collect();

        if ($existing->isNotEmpty()) {
            $matches = $this->collectionMatches($collection, $manifest['collection'])
                && $existing->count() === $poems->count()
                && $existing->values()->every(function (Poem $poem, int $index) use ($poems): bool {
                    $source = $poems[$index];

                    return $poem->sort_order === $source['sequence']
                        && $poem->title === $source['title']
                        && hash_equals(hash('sha256', $poem->body), hash('sha256', $source['body']))
                        && $poem->work_type === ($source['work_type'] ?? 'ORIGINAL')
                        && $poem->original_author === ($source['original_author'] ?? null)
                        && $poem->translator === ($source['translator'] ?? null)
                        && $poem->source_date_place === ($source['source_date_place_text'] ?? null)
                        && $poem->source_note === ($source['source_note'] ?? null);
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

        DB::transaction(function () use (&$collection, $manifest, $poems): void {
            $collection = $collection
                ? Collection::query()->lockForUpdate()->findOrFail($collection->id)
                : Collection::create($this->collectionAttributes($manifest['collection']));

            foreach ($poems as $source) {
                $collection->poems()->create([
                    'title' => $source['title'],
                    'body' => $source['body'],
                    'excerpt' => str($source['body'])->before("\n")->limit(500)->toString(),
                    'work_type' => $source['work_type'] ?? 'ORIGINAL',
                    'original_author' => $source['original_author'] ?? null,
                    'translator' => $source['translator'] ?? null,
                    'source_date_place' => $source['source_date_place_text'] ?? null,
                    'source_note' => $source['source_note'] ?? null,
                    'sort_order' => $source['sequence'],
                    'is_free_sample' => false,
                    'is_active' => false,
                    'audio_path' => null,
                    'audio_duration_seconds' => null,
                ]);
            }
        });

        $this->info("Imported {$poems->count()} draft works into the collection.");

        return self::SUCCESS;
    }

    private function validateManifest(array $manifest): void
    {
        foreach (['source', 'collection', 'poems'] as $key) {
            if (! array_key_exists($key, $manifest)) {
                throw new RuntimeException("Manifest is missing {$key}.");
            }
        }

        $source = $this->sourcePath($manifest['source']);
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
            $hasPageRange = isset($poem['source_page_start'], $poem['source_page_end'])
                && $poem['source_page_start'] <= $poem['source_page_end'];
            $hasLogicalLocation = filled($poem['source_location'] ?? null);
            if (trim($poem['body']) === '' || (! $hasPageRange && ! $hasLogicalLocation)) {
                throw new RuntimeException('Poem body or source location is invalid.');
            }

            $workType = $poem['work_type'] ?? 'ORIGINAL';
            if (! in_array($workType, ['ORIGINAL', 'TRANSLATION'], true)) {
                throw new RuntimeException('Work type must be ORIGINAL or TRANSLATION.');
            }
            if ($workType === 'TRANSLATION' && (blank($poem['original_author'] ?? null) || blank($poem['translator'] ?? null))) {
                throw new RuntimeException('Translated works require original-author and translator attribution.');
            }
        }
    }

    private function sourcePath(array $source): string
    {
        if (! isset($source['private_path'])) {
            return (string) config(
                'poetry.source_archive_path',
                storage_path('app/source/collections/tsapo-ke-anzorona/authoritative-source.pdf'),
            );
        }

        $sourceRoot = realpath(storage_path('app/source'));
        $path = realpath(storage_path('app/'.$source['private_path']));
        if ($sourceRoot === false || $path === false || ! str_starts_with($path, $sourceRoot.DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('Manifest source path is outside private System C source storage.');
        }

        return $path;
    }

    private function collectionAttributes(array $collection): array
    {
        return [
            'title' => $collection['title'],
            'slug' => $collection['slug'],
            'author' => $collection['author'] ?? null,
            'dedication' => $collection['dedication'] ?? null,
            'introduction' => $collection['introduction'] ?? null,
            'foreword_author' => $collection['foreword_author'] ?? null,
            'foreword' => $collection['foreword'] ?? null,
            'publication_info' => $collection['publication_info'] ?? null,
            'sort_order' => $collection['catalogue_order'] ?? 0,
            'is_active' => false,
        ];
    }

    private function collectionMatches(Collection $collection, array $source): bool
    {
        $mapping = [
            'title' => 'title',
            'slug' => 'slug',
            'author' => 'author',
            'dedication' => 'dedication',
            'introduction' => 'introduction',
            'foreword_author' => 'foreword_author',
            'foreword' => 'foreword',
            'publication_info' => 'publication_info',
            'catalogue_order' => 'sort_order',
        ];
        $expected = collect($mapping)
            ->filter(fn (string $modelKey, string $sourceKey): bool => array_key_exists($sourceKey, $source))
            ->mapWithKeys(fn (string $modelKey, string $sourceKey): array => [$modelKey => $source[$sourceKey]])
            ->put('is_active', false);

        return $expected->every(fn (mixed $value, string $key): bool => $collection->{$key} === $value);
    }
}
