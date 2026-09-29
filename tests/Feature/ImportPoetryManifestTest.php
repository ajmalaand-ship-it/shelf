<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\Poem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
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
        Collection::create(['title' => 'څپو کې انځورونه', 'slug' => 'source-test', 'status' => 'draft']);

        $this->artisan('poetry:import-manifest', ['manifest' => $manifestPath, '--apply' => true])->assertSuccessful();
        $this->artisan('poetry:import-manifest', ['manifest' => $manifestPath, '--apply' => true])->assertSuccessful();

        $this->assertDatabaseCount('poems', 1);
        $this->assertDatabaseHas('poems', [
            'title' => null, 'sort_order' => 1, 'is_active' => false, 'sample_mode' => 'none',
        ]);

        File::delete($manifestPath);
        File::delete($source);
    }

    public function test_manifest_can_create_a_draft_collection_with_truthful_translation_attribution(): void
    {
        $source = storage_path('app/source/testing/collection-three.doc');
        File::ensureDirectoryExists(dirname($source));
        File::put($source, 'private word source');
        $manifestPath = storage_path('app/test-collection-three-manifest.json');
        File::put($manifestPath, json_encode([
            'source' => [
                'private_path' => 'source/testing/collection-three.doc',
                'sha256' => hash_file('sha256', $source),
            ],
            'collection' => [
                'title' => 'هېندارې او چینې', 'slug' => 'hindare-test', 'author' => 'اجمل اند',
                'catalogue_order' => 3, 'dedication' => 'سپېڅلي رياضت ته',
                'introduction' => 'درنو لوستونکيو!', 'foreword_author' => 'غفور لېوال',
                'foreword' => 'اې عشقه نامراده', 'publication_info' => '١٣٨٥ لمريز کال',
            ],
            'poems' => [[
                'sequence' => 1, 'title' => 'ژمى', 'untitled' => false,
                'body' => "لومړۍ کرښه\nدويمه کرښه", 'source_location' => 'Word paragraphs 1–2',
                'source_date_place_text' => null, 'source_note' => 'دپروین پژواک ديو شعرژباړه',
                'work_type' => 'TRANSLATION', 'original_author' => 'پروین پژواک', 'translator' => 'اجمل اند',
            ]],
        ], JSON_UNESCAPED_UNICODE));

        $this->artisan('poetry:import-manifest', ['manifest' => $manifestPath, '--apply' => true])->assertSuccessful();
        $this->artisan('poetry:import-manifest', ['manifest' => $manifestPath, '--apply' => true])->assertSuccessful();

        $this->assertDatabaseHas('collections', [
            'title' => 'هېندارې او چینې', 'sort_order' => 3, 'is_active' => false,
            'foreword_author' => 'غفور لېوال',
        ]);
        $this->assertDatabaseHas('poems', [
            'title' => 'ژمى', 'work_type' => 'TRANSLATION', 'original_author' => 'پروین پژواک',
            'translator' => 'اجمل اند', 'is_active' => false, 'sample_mode' => 'none',
        ]);
        $this->assertDatabaseCount('poems', 1);

        File::delete($manifestPath);
        File::delete($source);
        File::deleteDirectory(dirname($source));
    }

    public function test_manifest_imports_a_checksum_guarded_cover_idempotently(): void
    {
        Storage::fake('covers');
        $source = storage_path('app/source/testing/collection-cover-source.docx');
        $cover = storage_path('app/source/testing/collection-cover.jpg');
        File::ensureDirectoryExists(dirname($source));
        File::put($source, 'private word source');
        File::put($cover, 'private cover source');
        $manifestPath = storage_path('app/test-cover-import-manifest.json');
        File::put($manifestPath, json_encode([
            'source' => [
                'private_path' => 'source/testing/collection-cover-source.docx',
                'sha256' => hash_file('sha256', $source),
            ],
            'cover' => [
                'private_path' => 'source/testing/collection-cover.jpg',
                'sha256' => hash_file('sha256', $cover),
                'target_path' => 'collection-test/original-cover.jpg',
            ],
            'collection' => [
                'title' => 'TEST COVER', 'slug' => 'test-cover', 'author' => 'اجمل اند',
                'cover_image' => 'collection-test/original-cover.jpg',
            ],
            'poems' => [[
                'sequence' => 1, 'title' => null, 'untitled' => true,
                'body' => 'متن', 'source_location' => 'Word paragraph 1',
            ]],
        ], JSON_UNESCAPED_UNICODE));

        $this->artisan('poetry:import-manifest', ['manifest' => $manifestPath, '--apply' => true])->assertSuccessful();
        $this->artisan('poetry:import-manifest', ['manifest' => $manifestPath, '--apply' => true])->assertSuccessful();

        Storage::disk('covers')->assertExists('collection-test/original-cover.jpg');
        $this->assertSame('private cover source', Storage::disk('covers')->get('collection-test/original-cover.jpg'));
        $this->assertDatabaseHas('collections', [
            'title' => 'TEST COVER', 'cover_image' => 'collection-test/original-cover.jpg', 'is_active' => false,
        ]);

        File::delete($manifestPath);
        File::delete($source);
        File::delete($cover);
        File::deleteDirectory(dirname($source));
    }

    public function test_manifest_imports_checksum_guarded_private_artwork_idempotently(): void
    {
        Storage::fake('artwork');
        $source = storage_path('app/source/testing/artwork-source.docx');
        $artwork = storage_path('app/source/testing/artwork.png');
        File::ensureDirectoryExists(dirname($source));
        File::put($source, 'scanned source');
        File::put($artwork, 'original illustration');
        $manifestPath = storage_path('app/test-artwork-import-manifest.json');
        File::put($manifestPath, json_encode([
            'source' => ['private_path' => 'source/testing/artwork-source.docx', 'sha256' => hash_file('sha256', $source)],
            'collection' => ['title' => 'TEST ARTWORK', 'slug' => 'test-artwork'],
            'poems' => [[
                'sequence' => 1, 'title' => null, 'untitled' => true, 'body' => 'متن',
                'source_location' => 'Part 1 spread 9 left',
                'artwork' => [
                    'private_path' => 'source/testing/artwork.png',
                    'sha256' => hash_file('sha256', $artwork),
                    'target_path' => 'sind-pa-parkha-ke/001.png',
                ],
            ]],
        ], JSON_UNESCAPED_UNICODE));

        $this->artisan('poetry:import-manifest', ['manifest' => $manifestPath, '--apply' => true])->assertSuccessful();
        $this->artisan('poetry:import-manifest', ['manifest' => $manifestPath, '--apply' => true])->assertSuccessful();

        $this->assertDatabaseCount('poems', 1);
        $this->assertDatabaseHas('poems', ['artwork_path' => 'sind-pa-parkha-ke/001.png']);
        Storage::disk('artwork')->assertExists('sind-pa-parkha-ke/001.png');

        File::delete($manifestPath);
        File::deleteDirectory(dirname($source));
    }

    public function test_manifest_rejects_a_private_source_path_outside_system_c_source_storage(): void
    {
        $manifestPath = storage_path('app/test-invalid-source-manifest.json');
        File::put($manifestPath, json_encode([
            'source' => ['private_path' => '../logs/laravel.log', 'sha256' => str_repeat('0', 64)],
            'collection' => ['title' => 'TEST ONLY'],
            'poems' => [[
                'sequence' => 1, 'title' => null, 'untitled' => true, 'body' => 'متن',
                'source_location' => 'Word paragraph 1',
            ]],
        ], JSON_UNESCAPED_UNICODE));

        try {
            $this->artisan('poetry:import-manifest', ['manifest' => $manifestPath])->run();
            $this->fail('Unsafe private source path was accepted.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Manifest source path is outside private System C source storage.', $exception->getMessage());
        } finally {
            File::delete($manifestPath);
        }
    }

    public function test_manifest_rejects_a_changed_authoritative_source_member(): void
    {
        $directory = storage_path('app/source/testing/source-bundle');
        File::ensureDirectoryExists($directory);
        $authority = $directory.'/authority.json';
        $page = $directory.'/page-001.png';
        File::put($authority, 'authority record');
        File::put($page, 'changed page');
        $manifestPath = storage_path('app/test-source-member-manifest.json');
        File::put($manifestPath, json_encode([
            'source' => [
                'private_path' => 'source/testing/source-bundle/authority.json',
                'sha256' => hash_file('sha256', $authority),
                'members' => [[
                    'private_path' => 'source/testing/source-bundle/page-001.png',
                    'sha256' => hash('sha256', 'original page'),
                ]],
            ],
            'collection' => ['title' => 'TEST SOURCE MEMBERS'],
            'poems' => [[
                'sequence' => 1, 'title' => null, 'untitled' => true,
                'body' => 'متن', 'source_location' => 'Page 1',
            ]],
        ], JSON_UNESCAPED_UNICODE));

        try {
            $this->artisan('poetry:import-manifest', ['manifest' => $manifestPath])->run();
            $this->fail('A changed source-bundle member was accepted.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Authoritative source member checksum mismatch.', $exception->getMessage());
        } finally {
            File::delete($manifestPath);
            File::deleteDirectory($directory);
        }
    }
}
