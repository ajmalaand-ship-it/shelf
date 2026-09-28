<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\Poem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ReconcileTsapoDatePlaceTest extends TestCase
{
    use RefreshDatabase;

    private string $sourcePath;

    private string $manifestPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sourcePath = storage_path('app/source/testing/date-place-source.pdf');
        $this->manifestPath = storage_path('app/source/testing/date-place-manifest.json');
        File::ensureDirectoryExists(dirname($this->sourcePath));
        File::put($this->sourcePath, 'trusted synthetic source');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(dirname($this->sourcePath));
        parent::tearDown();
    }

    public function test_dry_run_writes_nothing_and_apply_is_missing_only_and_idempotent(): void
    {
        $collection = Collection::create(['title' => 'څپو کې انځورونه', 'slug' => 'tsapo-test']);
        $missing = $this->poem($collection, 1, null);
        $present = $this->poem($collection, 2, 'کابل');
        $before = $missing->fresh()->only(['body', 'title', 'sort_order', 'is_active', 'is_free_sample']);
        $this->writeManifest([
            $this->sourceRecord($missing, 'پېښور'),
            $this->sourceRecord($present, 'کابل'),
        ]);

        $this->artisan('poetry:reconcile-tsapo-date-place', ['manifest' => $this->manifestPath])
            ->expectsOutputToContain('EXACT_SAFE_MATCH=1')
            ->expectsOutputToContain('ALREADY_PRESENT_MATCHING=1')
            ->expectsOutputToContain('DRY_RUN=YES WRITES=0')
            ->assertSuccessful();
        $this->assertNull($missing->fresh()->source_date_place);

        $this->artisan('poetry:reconcile-tsapo-date-place', ['manifest' => $this->manifestPath, '--apply' => true])
            ->expectsOutputToContain('APPLIED=1')
            ->assertSuccessful();
        $this->assertSame('پېښور', $missing->fresh()->source_date_place);
        $this->assertSame($before, $missing->fresh()->only(array_keys($before)));

        $this->artisan('poetry:reconcile-tsapo-date-place', ['manifest' => $this->manifestPath, '--apply' => true])
            ->expectsOutputToContain('EXACT_SAFE_MATCH=0')
            ->expectsOutputToContain('ALREADY_PRESENT_MATCHING=2')
            ->expectsOutputToContain('APPLIED=0')
            ->assertSuccessful();
    }

    public function test_conflict_refuses_apply_and_never_overwrites_existing_value(): void
    {
        $collection = Collection::create(['title' => 'څپو کې انځورونه', 'slug' => 'tsapo-conflict']);
        $poem = $this->poem($collection, 1, 'existing');
        $this->writeManifest([$this->sourceRecord($poem, 'source')]);

        $this->artisan('poetry:reconcile-tsapo-date-place', ['manifest' => $this->manifestPath, '--apply' => true])
            ->expectsOutputToContain('CONFLICT=1')
            ->expectsOutputToContain('APPLY_REFUSED=YES')
            ->assertFailed();
        $this->assertSame('existing', $poem->fresh()->source_date_place);
    }

    public function test_ambiguous_sequence_refuses_apply(): void
    {
        $collection = Collection::create(['title' => 'څپو کې انځورونه', 'slug' => 'tsapo-ambiguous']);
        $poem = $this->poem($collection, 1, null);
        // Duplicate database positions are now forbidden; duplicate source
        // sequences must still be rejected by the reconciliation guard.
        $this->writeManifest([$this->sourceRecord($poem, 'source'), $this->sourceRecord($poem, 'source')]);

        $this->artisan('poetry:reconcile-tsapo-date-place', ['manifest' => $this->manifestPath, '--apply' => true])
            ->expectsOutputToContain('AMBIGUOUS_MATCH=2')
            ->expectsOutputToContain('APPLY_REFUSED=YES')
            ->assertFailed();
        $this->assertNull($poem->fresh()->source_date_place);
    }

    public function test_identity_mismatch_is_rejected_and_other_collection_is_untouched(): void
    {
        $collection = Collection::create(['title' => 'څپو کې انځورونه', 'slug' => 'tsapo-identity']);
        $poem = $this->poem($collection, 1, null);
        $other = Collection::create(['title' => 'OTHER', 'slug' => 'other']);
        $otherPoem = $this->poem($other, 1, null);
        $record = $this->sourceRecord($poem, 'source');
        $record['body'] = 'different body';
        $this->writeManifest([$record]);

        $this->artisan('poetry:reconcile-tsapo-date-place', ['manifest' => $this->manifestPath, '--apply' => true])
            ->expectsOutputToContain('AMBIGUOUS_MATCH=1')
            ->assertFailed();
        $this->assertNull($poem->fresh()->source_date_place);
        $this->assertNull($otherPoem->fresh()->source_date_place);
    }

    private function poem(Collection $collection, int $sequence, ?string $datePlace): Poem
    {
        return Poem::create([
            'collection_id' => $collection->id,
            'title' => null,
            'body' => "synthetic {$sequence}",
            'excerpt' => 'synthetic',
            'sort_order' => $sequence,
            'source_date_place' => $datePlace,
        ]);
    }

    /** @return array<string, mixed> */
    private function sourceRecord(Poem $poem, string $datePlace): array
    {
        return [
            'sequence' => $poem->sort_order,
            'title' => $poem->title,
            'body' => $poem->body,
            'source_date_place_text' => $datePlace,
        ];
    }

    /** @param array<int, array<string, mixed>> $poems */
    private function writeManifest(array $poems): void
    {
        File::put($this->manifestPath, json_encode([
            'source' => [
                'private_path' => 'source/testing/date-place-source.pdf',
                'sha256' => hash_file('sha256', $this->sourcePath),
            ],
            'collection' => ['title' => 'څپو کې انځورونه'],
            'poems' => $poems,
        ], JSON_UNESCAPED_UNICODE));
    }
}
