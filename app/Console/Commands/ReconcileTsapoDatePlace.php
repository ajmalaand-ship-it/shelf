<?php

namespace App\Console\Commands;

use App\Models\Collection;
use App\Models\Poem;
use Illuminate\Console\Command;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

class ReconcileTsapoDatePlace extends Command
{
    private const COLLECTION_TITLE = 'څپو کې انځورونه';

    private const DEFAULT_MANIFEST = 'source/collections/tsapo-ke-anzorona/import-manifest.json';

    protected $signature = 'poetry:reconcile-tsapo-date-place
        {manifest='.self::DEFAULT_MANIFEST.' : Private authoritative import manifest}
        {--apply : Apply exact missing-only matches}';

    protected $description = 'Dry-run or apply missing date/place metadata for the first collection';

    public function handle(): int
    {
        $manifest = $this->readManifest((string) $this->argument('manifest'));
        $plan = $this->buildPlan($manifest, false);
        $this->report($manifest, $plan);
        $this->reportAudit();

        if (! $this->option('apply')) {
            $this->info('DRY_RUN=YES WRITES=0');

            return self::SUCCESS;
        }

        if ($this->refusalCount($plan) > 0) {
            $this->error('APPLY_REFUSED=YES');

            return self::FAILURE;
        }

        $applied = DB::transaction(function () use ($manifest): int {
            $plan = $this->buildPlan($manifest, true);
            if ($this->refusalCount($plan) > 0) {
                throw new RuntimeException('Reconciliation state changed; apply aborted.');
            }

            foreach ($plan['exact_safe'] as $match) {
                $match['poem']->update(['source_date_place' => $match['value']]);
            }

            return $plan['exact_safe']->count();
        });

        $this->info("APPLIED={$applied}");

        return self::SUCCESS;
    }

    /** @return array<string, mixed> */
    private function readManifest(string $argument): array
    {
        $sourceRoot = realpath(storage_path('app/source'));
        $candidate = str_starts_with($argument, '/') ? realpath($argument) : realpath(storage_path('app/'.$argument));
        if ($sourceRoot === false || $candidate === false || ! str_starts_with($candidate, $sourceRoot.DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('Manifest must remain in private System C source storage.');
        }

        $manifest = json_decode(File::get($candidate), true, flags: JSON_THROW_ON_ERROR);
        if (($manifest['collection']['title'] ?? null) !== self::COLLECTION_TITLE || ! is_array($manifest['poems'] ?? null)) {
            throw new RuntimeException('Manifest is not the guarded first-collection source.');
        }

        $sourcePath = isset($manifest['source']['private_path'])
            ? realpath(storage_path('app/'.$manifest['source']['private_path']))
            : realpath(storage_path('app/source/collections/tsapo-ke-anzorona/authoritative-source.pdf'));
        if ($sourcePath === false || ! str_starts_with($sourcePath, $sourceRoot.DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('Authoritative source is outside private System C source storage.');
        }
        if (! isset($manifest['source']['sha256']) || ! hash_equals($manifest['source']['sha256'], hash_file('sha256', $sourcePath))) {
            throw new RuntimeException('Authoritative source checksum mismatch.');
        }

        return $manifest;
    }

    /** @return array<string, SupportCollection<int, mixed>> */
    private function buildPlan(array $manifest, bool $lock): array
    {
        $collectionQuery = Collection::query()->where('title', self::COLLECTION_TITLE);
        if ($lock) {
            $collectionQuery->lockForUpdate();
        }
        $collections = $collectionQuery->get();
        if ($collections->count() !== 1) {
            throw new RuntimeException('Expected exactly one guarded production collection.');
        }

        $poemQuery = $collections->first()->poems();
        if ($lock) {
            $poemQuery->lockForUpdate();
        }
        $productionBySequence = $poemQuery->get()->groupBy('sort_order');
        $sourceRecords = collect($manifest['poems']);
        $sourceSequenceCounts = $sourceRecords->countBy('sequence');

        $plan = [
            'exact_safe' => collect(),
            'already_present' => collect(),
            'conflicts' => collect(),
            'ambiguous' => collect(),
            'source_invalid' => collect(),
            'production_missing' => collect(),
        ];

        foreach ($sourceRecords as $source) {
            $value = $source['source_date_place_text'] ?? null;
            if ($value === null || $value === '') {
                continue;
            }
            if (! is_string($value) || ! mb_check_encoding($value, 'UTF-8') || mb_strlen($value) > 255 || trim($value) === '') {
                $plan['source_invalid']->push(true);

                continue;
            }

            $sequence = $source['sequence'] ?? null;
            if (! is_int($sequence) || ($sourceSequenceCounts[$sequence] ?? 0) !== 1) {
                $plan['ambiguous']->push(true);

                continue;
            }

            $matches = $productionBySequence->get($sequence, collect());
            if ($matches->isEmpty()) {
                $plan['production_missing']->push(true);

                continue;
            }
            if ($matches->count() !== 1) {
                $plan['ambiguous']->push(true);

                continue;
            }

            /** @var Poem $poem */
            $poem = $matches->first();
            if ($poem->title !== ($source['title'] ?? null)
                || ! hash_equals(hash('sha256', $poem->body), hash('sha256', (string) ($source['body'] ?? '')))) {
                $plan['ambiguous']->push(true);

                continue;
            }

            if ($poem->source_date_place === null || $poem->source_date_place === '') {
                $plan['exact_safe']->push(['poem' => $poem, 'value' => $value]);
            } elseif (hash_equals($value, $poem->source_date_place)) {
                $plan['already_present']->push(true);
            } else {
                $plan['conflicts']->push(true);
            }
        }

        return $plan;
    }

    /** @param array<string, SupportCollection<int, mixed>> $plan */
    private function refusalCount(array $plan): int
    {
        return $plan['conflicts']->count()
            + $plan['ambiguous']->count()
            + $plan['source_invalid']->count()
            + $plan['production_missing']->count();
    }

    /** @param array<string, SupportCollection<int, mixed>> $plan */
    private function report(array $manifest, array $plan): void
    {
        $withDatePlace = collect($manifest['poems'])->filter(
            fn (array $poem): bool => ($poem['source_date_place_text'] ?? null) !== null
                && ($poem['source_date_place_text'] ?? null) !== '',
        )->count();

        $this->line('SOURCE_WORKS='.count($manifest['poems']));
        $this->line("SOURCE_WITH_DATE_PLACE={$withDatePlace}");
        $this->line('EXACT_SAFE_MATCH='.$plan['exact_safe']->count());
        $this->line('ALREADY_PRESENT_MATCHING='.$plan['already_present']->count());
        $this->line('CONFLICT='.$plan['conflicts']->count());
        $this->line('AMBIGUOUS_MATCH='.$plan['ambiguous']->count());
        $this->line('SOURCE_VALUE_UNCLEAR_INVALID='.$plan['source_invalid']->count());
        $this->line('PRODUCTION_RECORD_MISSING='.$plan['production_missing']->count());
    }

    private function reportAudit(): void
    {
        $collection = Collection::query()->where('title', self::COLLECTION_TITLE)->sole();
        $target = DB::table('poems')->where('collection_id', $collection->id)->orderBy('id');
        $other = DB::table('poems')->where('collection_id', '!=', $collection->id)->orderBy('id');

        $this->line('PRODUCTION_POEM_COUNT='.$target->count());
        $this->line('CONTENT_VERSION='.(string) DB::table('app_settings')->where('key', 'content_version')->value('value'));
        $this->line('TARGET_FULL_FINGERPRINT='.$this->fingerprint(clone $target));
        $this->line('TARGET_PROTECTED_FINGERPRINT='.$this->fingerprint(clone $target, ['source_date_place', 'updated_at']));
        $this->line('OTHER_COLLECTIONS_FINGERPRINT='.$this->fingerprint($other));
    }

    /** @param array<int, string> $excluded */
    private function fingerprint(mixed $query, array $excluded = []): string
    {
        $hash = hash_init('sha256');
        foreach ($query->cursor() as $row) {
            $attributes = (array) $row;
            foreach ($excluded as $key) {
                unset($attributes[$key]);
            }
            ksort($attributes);
            hash_update($hash, serialize($attributes));
        }

        return hash_final($hash);
    }
}
