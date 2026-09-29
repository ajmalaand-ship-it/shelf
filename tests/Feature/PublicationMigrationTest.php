<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PublicationMigrationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_all_six_books_become_draft_single_user_owner_and_down_preserves_legacy_values(): void
    {
        $migration = require database_path('migrations/2026_09_28_180000_add_publication_and_owner_access.php');
        $migration->down();
        DB::table('users')->insert(['id' => 1, 'name' => 'Synthetic', 'email' => 'synthetic@example.test', 'password' => 'synthetic']);
        foreach (range(3, 8) as $id) {
            DB::table('collections')->insert(['id' => $id, 'title' => 'Synthetic '.$id, 'slug' => 'synthetic-'.$id, 'is_active' => $id % 2 === 0]);
        }
        $before = DB::table('collections')->orderBy('id')->get(['id', 'title', 'slug', 'is_active'])->toJson();
        $migration->up();
        $this->assertSame(6, DB::table('collections')->where('status', 'draft')->count());
        $this->assertSame(1, DB::table('users')->where('is_owner', true)->count());
        $this->assertSame(6, DB::table('book_status_changes')->where('changed_by', 1)->where('to_status', 'draft')->count());
        $migration->down();
        $this->assertFalse(Schema::hasColumn('users', 'is_owner'));
        $this->assertFalse(Schema::hasColumn('collections', 'status'));
        $this->assertSame($before, DB::table('collections')->orderBy('id')->get(['id', 'title', 'slug', 'is_active'])->toJson());
        $migration->up();
    }

    public function test_migration_refuses_to_guess_owner_when_multiple_users_exist(): void
    {
        $migration = require database_path('migrations/2026_09_28_180000_add_publication_and_owner_access.php');
        $migration->down();
        foreach ([1, 2] as $id) {
            DB::table('users')->insert(['id' => $id, 'name' => 'Synthetic', 'email' => 'synthetic'.$id.'@example.test', 'password' => 'synthetic']);
        }
        try {
            $migration->up();
            $this->fail('Migration guessed an owner.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('Expected one existing owner', $exception->getMessage());
            $this->assertFalse(Schema::hasColumn('users', 'is_owner'));
        }
        DB::table('users')->where('id', 2)->delete();
        $migration->up();
    }
}
