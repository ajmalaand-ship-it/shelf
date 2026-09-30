<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Author;
use App\Models\Collection;
use App\Models\User;
use App\Services\OwnerPreviewTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class InternalTestPublicationMigrationTest extends TestCase
{
    use RefreshDatabase;

    private array $source = [];

    private array $before = [];

    private array $samples = [];

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        config(['purchases.enabled' => false, 'play_sync.enabled' => false,
            'poetry.owner_preview_secret' => str_repeat('p', 64)]);
        Storage::fake('covers');
        Storage::disk('covers')->put('synthetic.jpg', 'Synthetic cover');
        $this->owner = User::factory()->state(['is_owner' => true])->create();
        $author = Author::create(['name' => 'Synthetic author']);
        foreach ([3, 4, 5, 6, 7, 8] as $id) {
            $book = new Collection;
            $book->forceFill(['id' => $id, 'title' => 'Synthetic '.$id, 'language' => 'ps',
                'price_usd' => '2.99', 'cover_image' => 'synthetic.jpg'])->save();
            $book->credits()->create(['role' => 'author', 'author_id' => $author->id]);
            foreach ([1, 2, 3, 4] as $position) {
                $item = $book->poems()->create(['body' => "Exact source $id/$position\r\nSecond line  ",
                    'excerpt' => 'Private old excerpt', 'is_active' => $position === 2]);
                $this->source[$item->id] = $item->body;
                $this->before[$item->id] = $item->is_active;
            }
            // Prove first means content order rather than insertion order.
            $first = $book->poems()->first();
            $first->update(['sort_order' => 10]);
            $this->samples[$id] = $book->poems()->orderBy('id')->get()->take(2)->pluck('id')->all();
        }
    }

    private function migration(): object
    {
        return require base_path('database/migrations/2026_09_30_070000_publish_owner_internal_test_catalogue.php');
    }

    public function test_publication_samples_access_and_rollback_preserve_exact_source_and_history(): void
    {
        $unrelated = Collection::create(['title' => 'Unrelated draft']);
        $hidden = $unrelated->poems()->create(['body' => 'Private unrelated source', 'excerpt' => '', 'is_active' => false]);
        $bin = Collection::findOrFail(3)->poems()->create(['body' => 'Deleted source', 'excerpt' => '', 'is_active' => false]);
        $bin->delete();
        $migration = $this->migration();
        $migration->up();
        $this->assertGuest();
        $this->getJson('/api/collections')->assertOk()->assertJsonCount(6, 'data');
        $this->getJson('/api/purchases/config')->assertJsonPath('enabled', false);
        $snapshot = json_decode(AppSetting::where('key', 'shelf_internal_test_catalogue_20260930')->value('value'), true);
        $this->assertSame($this->owner->id, $snapshot['owner_id']);
        $this->assertNotEmpty($snapshot['changed_at']);
        foreach (Collection::whereIn('id', [3, 4, 5, 6, 7, 8])->get() as $book) {
            $this->assertSame('published', $book->status);
            $this->assertSame($this->owner->id, $book->status_changed_by);
            $this->assertSame($this->samples[$book->id], $book->poems()->where('sample_mode', 'full')->pluck('id')->all());
            foreach ($book->poems as $item) {
                $this->assertTrue($item->is_active);
                $this->assertSame($this->source[$item->id], $item->body);
                $response = $this->getJson('/api/poems/'.$item->id)->assertOk();
                $response->assertJsonPath('data.body', $item->sample_mode === 'full' ? $item->body : null)
                    ->assertJsonPath('data.locked', $item->sample_mode !== 'full');
            }
            $this->assertDatabaseHas('book_status_changes', ['collection_id' => $book->id,
                'from_status' => 'draft', 'to_status' => 'published', 'changed_by' => $this->owner->id]);
        }
        $this->assertFalse($bin->fresh()->is_active);
        $this->assertFalse($hidden->fresh()->is_active);
        $this->getJson('/api/poems/'.$hidden->id)->assertNotFound();
        $token = app(OwnerPreviewTokenService::class)->issue(1)['token'];
        $this->withToken($token)->getJson('/api/owner-preview/poems/'.$hidden->id)
            ->assertOk()->assertJsonPath('data.body', $hidden->body);
        $this->flushHeaders();
        $migration->down();
        $this->getJson('/api/collections')->assertJsonCount(0, 'data');
        foreach (Collection::whereIn('id', [3, 4, 5, 6, 7, 8])->get() as $book) {
            $this->assertSame('draft', $book->status);
            foreach ($book->poems as $item) {
                $this->assertSame($this->before[$item->id], $item->is_active);
                $this->assertSame('none', $item->sample_mode);
                $this->assertSame($this->source[$item->id], $item->body);
            }
            $this->assertDatabaseHas('book_status_changes', ['collection_id' => $book->id,
                'from_status' => 'published', 'to_status' => 'draft', 'changed_by' => $this->owner->id]);
        }
        $this->assertFalse(AppSetting::where('key', 'shelf_internal_test_catalogue_20260930')->exists());
    }

    public function test_publish_failure_rolls_back_every_book_item_and_audit(): void
    {
        Collection::findOrFail(8)->credits()->delete();
        $auditCount = DB::table('book_status_changes')->count();
        try {
            $this->migration()->up();
            $this->fail('Missing author must prevent the rollout.');
        } catch (\Illuminate\Validation\ValidationException $error) {
            $this->assertArrayHasKey('credits', $error->errors());
        }
        $this->assertSame(6, Collection::where('status', 'draft')->count());
        $this->assertSame(0, DB::table('poems')->where('sample_mode', 'full')->count());
        $this->assertSame(6, DB::table('poems')->where('is_active', true)->count());
        $this->assertSame($auditCount, DB::table('book_status_changes')->count());
        $this->assertGuest();
    }

    public function test_rollback_refuses_newer_owner_work_without_partial_changes(): void
    {
        $migration = $this->migration();
        $migration->up();
        Collection::findOrFail(8)->poems()->first()->update(['sample_mode' => 'none']);
        try {
            $migration->down();
            $this->fail('Rollback must not overwrite later sample choices.');
        } catch (RuntimeException $error) {
            $this->assertStringContainsString('refusing rollback', $error->getMessage());
        }
        $this->assertSame(6, Collection::where('status', 'published')->count());
        $this->assertGuest();
    }

    public function test_enabled_payments_prevent_publication(): void
    {
        config(['purchases.enabled' => true]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('requires purchases and Play sync disabled');
        $this->migration()->up();
    }
}
