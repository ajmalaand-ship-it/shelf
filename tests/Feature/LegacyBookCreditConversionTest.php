<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Collection;
use App\Models\Poem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LegacyBookCreditConversionTest extends TestCase
{
    use RefreshDatabase;

    private function migration(): Migration
    {
        return require database_path('migrations/2026_09_27_130100_convert_legacy_book_credits.php');
    }

    public function test_conversion_is_exact_reversible_and_preserves_work_level_attribution(): void
    {
        $this->migration()->down(); // Remove the empty conversion from test database setup.
        $existing = Author::create(['name' => 'Existing person']);
        $names = ['اجمل اند', 'Ajmal Aand', 'نوم نه دی ښودل شوی', 'Name', 'name', 'Ajmal Aand'];
        foreach ($names as $index => $name) {
            Collection::create(['id' => $index + 1, 'title' => 'Book '.($index + 1), 'slug' => 'book-'.($index + 1), 'author' => $name, 'language' => null]);
        }
        $extra = Collection::create(['id' => 7, 'title' => 'Later book', 'slug' => 'later', 'author' => null, 'language' => 'fa']);
        $extra->credits()->create(['author_id' => $existing->id, 'role' => 'editor']);
        foreach ([
            [1, 'Original A', null, 'ORIGINAL'],
            [2, 'Original B', 'Ajmal Aand', 'TRANSLATION'],
            [2, 'Original C', 'اجمل اند', 'TRANSLATION'],
            [3, 'نوم نه دی ښودل شوی', 'نوم نه دی ښودل شوی', 'TRANSLATION'],
            [4, 'Original D', 'Translator A', 'TRANSLATION'],
            [4, 'Original D', 'Translator B', 'TRANSLATION'],
            [5, 'Original E', 'Translator A', 'TRANSLATION'],
            [5, 'Original E', null, 'TRANSLATION'],
            [6, 'پروین پژواک', 'Ajmal Aand', 'TRANSLATION'],
        ] as [$book, $original, $translator, $type]) {
            Poem::create(['collection_id' => $book, 'body' => 'Synthetic text', 'excerpt' => 'Synthetic', 'original_author' => $original, 'translator' => $translator, 'work_type' => $type]);
        }
        $beforeBooks = DB::table('collections')->orderBy('id')->get()->toJson();
        $beforePoems = DB::table('poems')->orderBy('id')->get()->toJson();
        $beforeCredits = DB::table('collection_author')->get()->toJson();
        $this->migration()->up();
        $this->assertSame(6, Collection::where('language', 'ps')->count());
        $this->assertSame('fa', $extra->fresh()->language);
        $ajmal = Author::where('name_latin', 'Ajmal Aand')->sole();
        $this->assertSame('اجمل اند', $ajmal->name);
        $this->assertSame(0, Author::all()->where('name', 'Ajmal Aand')->count());
        $this->assertSame(0, Author::all()->where('name', 'نوم نه دی ښودل شوی')->count());
        $this->assertSame(1, Author::all()->whereStrict('name', 'Name')->count());
        $this->assertSame(1, Author::all()->whereStrict('name', 'name')->count());
        $this->assertSame(['پروین پژواک', 'اجمل اند'], Collection::findOrFail(6)->credits->map(fn ($credit) => $credit->author->name)->all());
        $this->assertSame(['author', 'translator'], Collection::findOrFail(6)->credits->pluck('role')->all());
        $this->assertSame($ajmal->id, Collection::findOrFail(2)->credits()->where('role', 'translator')->sole()->author_id);
        foreach ([3, 4, 5] as $id) {
            $this->assertFalse(Collection::findOrFail($id)->credits()->where('role', 'translator')->exists());
        }
        $this->assertSame(['Name', 'Original D'], Collection::findOrFail(4)->credits->map(fn ($credit) => $credit->author->name)->all());
        $this->assertSame($beforePoems, DB::table('poems')->orderBy('id')->get()->toJson());
        $this->migration()->down();
        $this->assertSame($beforeBooks, DB::table('collections')->orderBy('id')->get()->toJson());
        $this->assertSame($beforeCredits, DB::table('collection_author')->get()->toJson());
        $this->assertSame([$existing->id], Author::pluck('id')->all());
    }

    public function test_schema_migration_can_be_rolled_back_and_reapplied(): void
    {
        $this->migration()->down();
        $schema = require database_path('migrations/2026_09_27_130000_create_authors_and_book_credits.php');
        $schema->down();
        $this->assertFalse(Schema::hasTable('authors'));
        $this->assertFalse(Schema::hasTable('collection_author'));
        $this->assertFalse(Schema::hasColumn('collections', 'language'));
        $schema->up();
        $this->assertTrue(Schema::hasColumn('collections', 'language'));
    }

    public function test_rollback_refuses_to_delete_new_credits_for_converted_authors(): void
    {
        $this->migration()->down();
        $book = Collection::create(['title' => 'Test', 'slug' => 'test', 'author' => 'Known name']);
        $this->migration()->up();
        $credit = $book->credits()->firstOrFail();
        $book->credits()->create(['author_id' => $credit->author_id, 'role' => 'editor']);
        try {
            $this->migration()->down();
            $this->fail('Rollback would delete newly added credits.');
        } catch (\RuntimeException $error) {
            $this->assertStringContainsString('new credits', $error->getMessage());
        }
        $this->assertCount(2, $book->fresh()->credits);
    }
}
