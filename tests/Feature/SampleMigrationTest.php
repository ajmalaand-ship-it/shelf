<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SampleMigrationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_reset_includes_hidden_and_binned_items_and_restores_flags_without_changing_text(): void
    {
        $migration = require database_path('migrations/2026_09_28_190000_reset_legacy_samples.php');
        $migration->down();
        $book = DB::table('collections')->insertGetId(['title' => 'Synthetic', 'slug' => 'synthetic']);
        foreach ([0, 1, 2] as $id) {
            DB::table('poems')->insert(['collection_id' => $book, 'body' => "Exact پښتو فارسی\r\nText", 'excerpt' => 'Original excerpt',
                'sort_order' => $id + 1, 'is_free_sample' => $id !== 0, 'is_active' => $id === 1, 'deleted_at' => $id === 2 ? now() : null]);
        }
        $before = DB::table('poems')->orderBy('id')->get(['id', 'body', 'excerpt', 'is_free_sample', 'is_active', 'deleted_at'])->toJson();
        $migration->up();
        $this->assertSame(0, DB::table('poems')->where('is_free_sample', true)->count());
        $this->assertSame(3, DB::table('poems')->where('sample_mode', 'none')->count());
        $this->assertSame(2, DB::table('legacy_sample_flags')->where('is_free_sample', true)->count());
        $migration->down();
        $this->assertSame($before, DB::table('poems')->orderBy('id')->get(['id', 'body', 'excerpt', 'is_free_sample', 'is_active', 'deleted_at'])->toJson());
        $migration->up();
    }
}
