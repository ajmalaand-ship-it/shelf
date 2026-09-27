<?php

namespace Tests\Feature;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class ShelfBrandingMigrationTest extends TestCase
{
    use RefreshDatabase;

    private const SNAPSHOT_KEY = '_migration_20260927_shelf_branding_previous';

    private function migration(): Migration
    {
        return require database_path('migrations/2026_09_27_120000_set_shelf_branding.php');
    }

    public function test_migration_sets_owner_approved_branding_without_exposing_snapshot(): void
    {
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->getJson('/api/app-config')->assertOk()
            ->assertJsonPath('app_name', 'Shelf')
            ->assertJsonPath('slogan', 'کتاب مو ژوند بدلوي')
            ->assertJsonCount(4)
            ->assertDontSee(self::SNAPSHOT_KEY);
        $this->assertDatabaseHas('app_settings', ['key' => self::SNAPSHOT_KEY]);
    }

    public function test_rollback_restores_actual_previous_values_and_timestamps(): void
    {
        $this->migration()->down();
        foreach (['public_app_name' => 'Previous custom name', 'public_slogan' => 'Previous custom slogan'] as $key => $value) {
            DB::table('app_settings')->where('key', $key)->update([
                'value' => $value,
                'created_at' => '2026-01-01 00:00:00',
                'updated_at' => '2026-02-01 00:00:00',
            ]);
        }
        $before = DB::table('app_settings')->orderBy('key')->get()->toJson();
        $this->migration()->up();
        $this->assertDatabaseHas('app_settings', ['key' => 'public_app_name', 'value' => 'Shelf']);
        // Use a new migration instance, as a later rollback command would.
        $this->migration()->down();
        $this->assertSame($before, DB::table('app_settings')->orderBy('key')->get()->toJson());
    }

    public function test_rollback_removes_settings_that_were_previously_absent(): void
    {
        $this->migration()->down();
        DB::table('app_settings')->whereIn('key', ['public_app_name', 'public_slogan'])->delete();
        $before = DB::table('app_settings')->orderBy('key')->get()->toJson();
        $this->migration()->up();
        $this->assertDatabaseHas('app_settings', ['key' => 'public_slogan', 'value' => 'کتاب مو ژوند بدلوي']);
        $this->migration()->down();
        $this->assertSame($before, DB::table('app_settings')->orderBy('key')->get()->toJson());
    }

    public function test_rollback_refuses_missing_snapshot_without_changing_branding(): void
    {
        DB::table('app_settings')->where('key', self::SNAPSHOT_KEY)->delete();
        try {
            $this->migration()->down();
            $this->fail('Rollback must refuse to guess previous values.');
        } catch (RuntimeException $error) {
            $this->assertSame('Branding rollback snapshot is missing; refusing to guess.', $error->getMessage());
        }
        $this->assertDatabaseHas('app_settings', ['key' => 'public_app_name', 'value' => 'Shelf']);
        $this->assertDatabaseHas('app_settings', ['key' => 'public_slogan', 'value' => 'کتاب مو ژوند بدلوي']);
    }
}
