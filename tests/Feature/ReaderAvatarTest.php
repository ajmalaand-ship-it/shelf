<?php

namespace Tests\Feature;

use App\Models\Reader;
use App\Services\Accounts\AccountActions;
use App\Services\Accounts\DeletionJournal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReaderAvatarTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        config(['reader_auth.enabled' => true]);
        Storage::fake('reader_avatars');
        $this->mock(DeletionJournal::class, fn ($mock) => $mock->shouldReceive('record')->andReturn(['record_id' => str_repeat('a', 32)]));
    }
    private function reader(): Reader
    {
        return Reader::create(['email' => uniqid().'@example.test']);
    }
    private function headers(Reader $reader): array
    {
        app('auth')->forgetGuards();
        return ['Authorization' => 'Bearer '.$reader->createToken('test', ['reader'])->plainTextToken];
    }
    private function photo(): string
    {
        $image = imagecreatetruecolor(12, 20);
        ob_start(); imagepng($image); $bytes = ob_get_clean(); imagedestroy($image);
        return base64_encode($bytes);
    }
    public function test_authenticated_upload_is_private_and_other_readers_cannot_read_it(): void
    {
        app('auth')->forgetGuards();
        $this->getJson('/api/auth/avatar')->assertUnauthorized();
        $reader = $this->reader(); $headers = $this->headers($reader);
        app('auth')->forgetGuards();
        $this->postJson('/api/auth/avatar', ['photo' => $this->photo(), 'reader_id' => 999], $headers)
            ->assertOk()->assertJsonPath('user.has_avatar', true)->assertJsonMissingPath('user.avatar_path');
        $path = $reader->fresh()->avatar_path;
        Storage::disk('reader_avatars')->assertExists($path);
        app('auth')->forgetGuards();
        $response = $this->getJson('/api/auth/avatar', $headers)->assertOk();
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $clean = base64_decode($response->json('photo'));
        $this->assertEquals(IMAGETYPE_JPEG, getimagesizefromstring($clean)[2]);
        $this->assertFalse(str_contains($clean, 'Exif'));
        $other = $this->reader(); $otherHeaders = $this->headers($other);
        app('auth')->forgetGuards();
        $this->getJson('/api/auth/avatar?reader_id='.$reader->id, $otherHeaders)->assertOk()->assertJsonPath('photo', null);
        app('auth')->forgetGuards();
        $this->deleteJson('/api/auth/avatar', ['reader_id' => $reader->id], $otherHeaders)->assertOk();
        Storage::disk('reader_avatars')->assertExists($path);
        $this->get('/storage/reader-avatars/'.$path)->assertForbidden();
        app('auth')->forgetGuards();
        $this->getJson('/api/auth/avatar/'.$reader->id, $otherHeaders)->assertNotFound();
    }
    public function test_replacing_and_removing_photo_erases_previous_files(): void
    {
        $reader = $this->reader(); $headers = $this->headers($reader);
        app('auth')->forgetGuards();
        $this->postJson('/api/auth/avatar', ['photo' => $this->photo()], $headers)->assertOk();
        $old = $reader->fresh()->avatar_path;
        app('auth')->forgetGuards();
        $this->postJson('/api/auth/avatar', ['photo' => $this->photo()], $headers)->assertOk();
        $new = $reader->fresh()->avatar_path;
        $this->assertNotSame($old, $new);
        Storage::disk('reader_avatars')->assertMissing($old);
        app('auth')->forgetGuards();
        $this->deleteJson('/api/auth/avatar', [], $headers)->assertOk()->assertJsonPath('user.has_avatar', false);
        Storage::disk('reader_avatars')->assertMissing($new);
        $this->assertNull($reader->fresh()->avatar_path);
    }
    public function test_invalid_photo_does_not_replace_existing_photo_or_leave_orphans(): void
    {
        $reader = $this->reader(); $headers = $this->headers($reader);
        app('auth')->forgetGuards();
        $this->postJson('/api/auth/avatar', ['photo' => $this->photo()], $headers)->assertOk();
        $old = $reader->fresh()->avatar_path;
        foreach (['not base64!', base64_encode('<svg><script/></svg>'), base64_encode(str_repeat('x', 2 * 1024 * 1024 + 1))] as $photo) {
            app('auth')->forgetGuards();
            $this->postJson('/api/auth/avatar', ['photo' => $photo], $headers)->assertUnprocessable();
            $this->assertSame($old, $reader->fresh()->avatar_path);
        }
        $this->assertCount(1, Storage::disk('reader_avatars')->allFiles());
    }
    public function test_account_deletion_cleans_private_avatar_after_durable_journal(): void
    {
        $reader = $this->reader(); $headers = $this->headers($reader);
        app('auth')->forgetGuards();
        $this->postJson('/api/auth/avatar', ['photo' => $this->photo()], $headers)->assertOk();
        $path = $reader->fresh()->avatar_path;
        app(AccountActions::class)->delete($reader);
        // RefreshDatabase wraps the test in a transaction; run commit callbacks
        // through the real callback manager after the nested deletion completes.
        app('db.transactions')->commit('sqlite', 1, 0);
        Storage::disk('reader_avatars')->assertMissing($path);
        $this->assertDatabaseMissing('readers', ['id' => $reader->id]);
        app('auth')->forgetGuards();
        $this->getJson('/api/auth/avatar', $headers)->assertUnauthorized();
    }
}
