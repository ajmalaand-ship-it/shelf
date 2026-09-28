<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\Poem;
use App\Support\BookFoundationCheck;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BookFoundationMigrationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_migration_is_reversible_and_preserves_source_identity_and_original_order(): void
    {
        $migration = require database_path('migrations/2026_09_27_150000_add_book_foundation.php');
        $migration->down();
        $ids = [];
        for ($id = 3; $id <= 8; $id++) {
            DB::table('collections')->insert(['id' => $id, 'title' => 'Original title '.$id, 'slug' => 'original-'.$id, 'language' => 'ps']);
            for ($n = 0; $n < 57; $n++) {
                $ids[] = DB::table('poems')->insertGetId(['collection_id' => $id, 'title' => null, 'body' => "Original text\n\n".$n, 'excerpt' => 'Original', 'sort_order' => 0]);
            }
        }
        $before = DB::table('poems')->orderBy('id')->get(['id', 'collection_id', 'title', 'body', 'sort_order'])->toJson();
        $migration->up();
        $this->assertTrue(BookFoundationCheck::report()['valid'], json_encode(BookFoundationCheck::report()));
        $this->assertSame(342, Poem::count());
        $this->assertSame('original-3', Collection::find(3)->slug);
        $this->assertSame("Original text\n\n0", Poem::find($ids[0])->body);
        $this->assertSame(57, Poem::where('collection_id', 3)->distinct()->count('sort_order'));
        $migration->down();
        $this->assertFalse(Schema::hasColumn('collections', 'book_type'));
        $this->assertSame($before, DB::table('poems')->orderBy('id')->get(['id', 'collection_id', 'title', 'body', 'sort_order'])->toJson());
        $migration->up();
    }
}
