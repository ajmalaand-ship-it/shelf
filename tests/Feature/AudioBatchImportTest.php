<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\Poem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class AudioBatchImportTest extends TestCase
{
    use RefreshDatabase;

    private string $inbox;

    private string $manifest;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('audio');
        $this->inbox = storage_path('framework/testing/audio-inbox');
        config(['poetry.audio_inbox_path' => $this->inbox]);
        File::ensureDirectoryExists($this->inbox.'/batch');
        $this->manifest = storage_path('app/source/owner-recording-manifests/test-audio.json');
        File::ensureDirectoryExists(dirname($this->manifest));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->inbox);
        File::delete($this->manifest);
        File::delete(storage_path('app/source/owner-recording-manifests/test-generated.json'));
        parent::tearDown();
    }

    public function test_owner_recording_manifest_uses_sequence_identity_and_expected_filename(): void
    {
        $collection = Collection::create(['title' => 'څپو کې انځورونه', 'slug' => 'tsapo-ke-anzorona']);
        $poem = Poem::create([
            'collection_id' => $collection->id, 'body' => "لومړۍ کرښه\nدويمه کرښه",
            'excerpt' => 'لومړۍ کرښه', 'sort_order' => 1, 'is_free_sample' => true,
        ]);

        $this->artisan('poetry:audio-manifest', [
            'collection' => $collection->slug,
            'sequences' => '1',
            '--filename-prefix' => 'tsapo-ke-anzorona',
            '--output' => 'source/owner-recording-manifests/test-generated.json',
        ])->assertSuccessful();

        $manifest = json_decode(File::get(storage_path('app/source/owner-recording-manifests/test-generated.json')), true);
        $this->assertSame($poem->id, $manifest['recordings'][0]['poem_id']);
        $this->assertNull($manifest['recordings'][0]['authored_title']);
        $this->assertSame('لومړۍ کرښه', $manifest['recordings'][0]['identifier']);
        $this->assertSame('tsapo-ke-anzorona-001.m4a', $manifest['recordings'][0]['expected_filename']);
        $this->assertSame(0600, fileperms(storage_path('app/source/owner-recording-manifests/test-generated.json')) & 0777);
    }

    public function test_valid_audio_batch_is_dry_run_then_attached_and_idempotent(): void
    {
        [$collection, $poem] = $this->createPoemAndManifest();
        File::put($this->inbox.'/batch/tsapo-ke-anzorona-001.m4a', $this->minimalM4a());

        $this->artisan('poetry:audio-import', ['directory' => 'batch', 'manifest' => $this->manifest])
            ->expectsOutputToContain('Validated 1 recording')
            ->assertSuccessful();
        $this->assertNull($poem->fresh()->audio_path);

        $this->artisan('poetry:audio-import', [
            'directory' => 'batch', 'manifest' => $this->manifest, '--apply' => true,
        ])->assertSuccessful();
        $poem->refresh();
        $this->assertNotNull($poem->audio_path);
        $this->assertStringContainsString("collections/{$collection->slug}/001-{$poem->id}-", $poem->audio_path);
        Storage::disk('audio')->assertExists($poem->audio_path);

        $this->artisan('poetry:audio-import', [
            'directory' => 'batch', 'manifest' => $this->manifest, '--apply' => true,
        ])->expectsOutputToContain('1 already matched exactly')->assertSuccessful();
    }

    public function test_audio_batch_rejects_unknown_filename_and_non_audio_content(): void
    {
        $this->createPoemAndManifest();
        File::put($this->inbox.'/batch/unknown-999.m4a', $this->minimalM4a());

        $this->assertCommandThrows('Unknown recording filename', fn () => $this->artisan('poetry:audio-import', [
            'directory' => 'batch', 'manifest' => $this->manifest,
        ])->run());

        File::delete($this->inbox.'/batch/unknown-999.m4a');
        File::put($this->inbox.'/batch/tsapo-ke-anzorona-001.m4a', 'not audio');
        $this->assertCommandThrows('Recording MIME type is not allowed', fn () => $this->artisan('poetry:audio-import', [
            'directory' => 'batch', 'manifest' => $this->manifest,
        ])->run());
    }

    public function test_audio_batch_never_replaces_an_existing_recording(): void
    {
        [, $poem] = $this->createPoemAndManifest();
        $poem->update(['audio_path' => 'existing/original.m4a']);
        Storage::disk('audio')->put('existing/original.m4a', $this->minimalM4a());
        File::put($this->inbox.'/batch/tsapo-ke-anzorona-001.m4a', $this->minimalM4a().'different');

        $this->assertCommandThrows('already has a recording', fn () => $this->artisan('poetry:audio-import', [
            'directory' => 'batch', 'manifest' => $this->manifest, '--apply' => true,
        ])->run());
        $this->assertSame('existing/original.m4a', $poem->fresh()->audio_path);
    }

    /** @return array{Collection, Poem} */
    private function createPoemAndManifest(): array
    {
        $collection = Collection::create(['title' => 'څپو کې انځورونه', 'slug' => 'tsapo-ke-anzorona']);
        $poem = Poem::create([
            'collection_id' => $collection->id, 'title' => 'TEST ONLY', 'body' => 'ازمېښتي متن',
            'excerpt' => 'ازمېښتي متن', 'sort_order' => 1, 'is_free_sample' => true,
        ]);
        File::put($this->manifest, json_encode([
            'collection' => ['id' => $collection->id, 'title' => $collection->title, 'slug' => $collection->slug],
            'recordings' => [[
                'sequence' => 1, 'poem_id' => $poem->id,
                'expected_filename' => 'tsapo-ke-anzorona-001.m4a',
            ]],
        ], JSON_UNESCAPED_UNICODE));

        return [$collection, $poem];
    }

    private function minimalM4a(): string
    {
        return "\x00\x00\x00\x18ftypM4A \x00\x00\x00\x00M4A isommp42";
    }

    private function assertCommandThrows(string $message, callable $callback): void
    {
        try {
            $callback();
            $this->fail('Expected audio import to fail.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString($message, $exception->getMessage());
        }
    }
}
