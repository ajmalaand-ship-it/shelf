<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\Poem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ImportPoetryManifestTest extends TestCase
{
    use RefreshDatabase;

    public function test_untitled_poem_uses_first_line_only_for_admin_display(): void
    {
        $poem = new Poem(['title' => null, 'body' => "لومړۍ کرښه\nدويمه کرښه"]);

        $this->assertNull($poem->title);
        $this->assertSame('لومړۍ کرښه', $poem->admin_display_title);
    }

    public function test_manifest_import_is_draft_and_idempotent(): void
    {
        $source = storage_path('framework/testing/poetry-import-source.pdf');
        config(['poetry.source_archive_path' => $source]);
        File::ensureDirectoryExists(dirname($source));
        File::put($source, 'test source');
        $manifestPath = storage_path('app/test-import-manifest.json');
        File::put($manifestPath, json_encode([
            'source' => ['sha256' => hash_file('sha256', $source)],
            'collection' => ['title' => 'څپو کې انځورونه'],
            'poems' => [[
                'sequence' => 1, 'title' => null, 'untitled' => true,
                'body' => "لومړۍ کرښه\nدويمه کرښه", 'source_page_start' => 4, 'source_page_end' => 4,
            ]],
        ], JSON_UNESCAPED_UNICODE));
        Collection::create(['title' => 'څپو کې انځورونه', 'slug' => 'source-test', 'is_active' => false]);

        $this->artisan('poetry:import-manifest', ['manifest' => $manifestPath, '--apply' => true])->assertSuccessful();
        $this->artisan('poetry:import-manifest', ['manifest' => $manifestPath, '--apply' => true])->assertSuccessful();

        $this->assertDatabaseCount('poems', 1);
        $this->assertDatabaseHas('poems', [
            'title' => null, 'sort_order' => 1, 'is_active' => false, 'is_free_sample' => false,
        ]);

        File::delete($manifestPath);
        File::delete($source);
    }
}
