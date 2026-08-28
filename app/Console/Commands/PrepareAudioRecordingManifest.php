<?php

namespace App\Console\Commands;

use App\Models\Collection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RuntimeException;

class PrepareAudioRecordingManifest extends Command
{
    protected $signature = 'poetry:audio-manifest
        {collection : Collection slug}
        {sequences : Comma-separated poem sequence numbers}
        {--filename-prefix= : Stable ASCII recording filename prefix}
        {--output= : Private path relative to storage/app}';

    protected $description = 'Prepare a private owner recording manifest from existing poem identities';

    public function handle(): int
    {
        $collection = Collection::query()->where('slug', $this->argument('collection'))->sole();
        $sequences = collect(explode(',', (string) $this->argument('sequences')))
            ->map(fn (string $value): int => (int) trim($value))
            ->filter(fn (int $value): bool => $value > 0)
            ->values();

        if ($sequences->isEmpty() || $sequences->duplicates()->isNotEmpty()) {
            throw new RuntimeException('Recording sequences must be unique positive integers.');
        }

        $poems = $collection->poems()->whereIn('sort_order', $sequences)->get()->keyBy('sort_order');
        if ($poems->count() !== $sequences->count()) {
            throw new RuntimeException('One or more recording sequences do not exist in the collection.');
        }

        $filenamePrefix = (string) ($this->option('filename-prefix') ?: $collection->slug);
        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $filenamePrefix)) {
            throw new RuntimeException('Recording filename prefix must be lowercase ASCII letters, numbers, and hyphens.');
        }

        $recordings = $sequences->sort()->values()->map(function (int $sequence) use ($collection, $filenamePrefix, $poems): array {
            $poem = $poems->get($sequence);

            return [
                'sequence' => $sequence,
                'poem_id' => $poem->id,
                'collection_id' => $collection->id,
                'collection' => $collection->title,
                'collection_slug' => $collection->slug,
                'authored_title' => $poem->title,
                'identifier' => $poem->admin_display_title,
                'expected_filename' => sprintf('%s-%03d.m4a', $filenamePrefix, $sequence),
                'audio_present' => filled($poem->audio_path),
            ];
        });

        $relative = $this->option('output') ?: "source/owner-recording-manifests/{$collection->slug}.json";
        $relative = ltrim((string) $relative, '/');
        if (! str_starts_with($relative, 'source/') || in_array('..', explode('/', $relative), true)) {
            throw new RuntimeException('Recording manifest must remain in private System C source storage.');
        }
        $path = storage_path('app/'.$relative);
        $sourceRoot = realpath(storage_path('app/source'));
        File::ensureDirectoryExists(dirname($path), 0700, true);
        $parent = realpath(dirname($path));
        if ($sourceRoot === false || $parent === false || ! str_starts_with($parent, $sourceRoot.DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('Recording manifest must remain in private System C source storage.');
        }

        File::put($path, json_encode([
            'manifest_version' => 1,
            'purpose' => "Ajmal Aand original-voice recording batch for {$collection->title}",
            'minimum_recordings_required' => 10,
            'collection' => [
                'id' => $collection->id,
                'title' => $collection->title,
                'slug' => $collection->slug,
            ],
            'recordings' => $recordings,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
        chmod($path, 0600);

        $this->info("Prepared {$recordings->count()} recording identities: {$path}");

        return self::SUCCESS;
    }
}
