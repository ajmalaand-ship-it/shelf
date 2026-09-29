<?php

namespace Tests\Feature;

use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MoveBookCoversTest extends TestCase
{
    use RefreshDatabase;

    public function test_move_is_backup_gated_idempotent_reversible_and_preserves_three_unreferenced_files(): void
    {
        Storage::fake('covers');
        $public = Storage::build(['driver' => 'local', 'root' => storage_path('app/public/covers')]);
        $public->put('referenced.jpg', 'exact bytes');
        foreach (['one.jpg', 'two.jpg', 'three.jpg'] as $path) {
            $public->put($path, $path);
        }
        Collection::create(['title' => 'Synthetic', 'cover_image' => 'referenced.jpg']);
        $this->artisan('shelf:move-covers')->assertFailed();
        $this->artisan('shelf:move-covers', ['--dry-run' => true])->expectsOutput('Unreferenced public covers (untouched): 3')->assertSuccessful();
        $this->assertTrue($public->exists('referenced.jpg'));
        $this->artisan('shelf:move-covers', ['--after-backup' => true])->assertSuccessful();
        $this->artisan('shelf:move-covers', ['--after-backup' => true])->assertSuccessful();
        $this->assertFalse($public->exists('referenced.jpg'));
        $this->assertSame('exact bytes', Storage::disk('covers')->get('referenced.jpg'));
        foreach (['one.jpg', 'two.jpg', 'three.jpg'] as $path) {
            $this->assertSame($path, $public->get($path));
        }
        $this->artisan('shelf:move-covers', ['--after-backup' => true, '--restore-public' => true])->assertSuccessful();
        $this->assertSame('exact bytes', $public->get('referenced.jpg'));
        Storage::disk('covers')->assertMissing('referenced.jpg');
        $public->delete($public->allFiles());
    }

    public function test_conflicting_target_stops_before_any_moves(): void
    {
        Storage::fake('covers');
        $public = Storage::build(['driver' => 'local', 'root' => storage_path('app/public/covers')]);
        $public->put('conflict.jpg', 'original');
        Storage::disk('covers')->put('conflict.jpg', 'different');
        Collection::create(['title' => 'Synthetic', 'cover_image' => 'conflict.jpg']);
        try {
            $this->artisan('shelf:move-covers', ['--after-backup' => true])->run();
            $this->fail('Conflicting destination accepted.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('destination differs', $exception->getMessage());
            $this->assertSame('original', $public->get('conflict.jpg'));
            $this->assertSame('different', Storage::disk('covers')->get('conflict.jpg'));
        }
        $public->delete('conflict.jpg');
    }
}
