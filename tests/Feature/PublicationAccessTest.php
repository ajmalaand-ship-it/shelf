<?php

namespace Tests\Feature;

use App\Filament\Resources\Collections\Pages\EditCollection;
use App\Filament\Resources\Collections\Pages\ListCollections;
use App\Models\Author;
use App\Models\Collection;
use App\Models\User;
use App\Services\OwnerPreviewTokenService;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class PublicationAccessTest extends TestCase
{
    use RefreshDatabase;

    private function book(string $status = 'draft'): Collection
    {
        Storage::disk('covers')->put('synthetic.jpg', 'synthetic cover');
        $book = Collection::create(['title' => 'Synthetic', 'price_usd' => '2.99', 'language' => 'ps', 'status' => $status, 'cover_image' => 'synthetic.jpg']);
        $book->credits()->create(['author_id' => Author::create(['name' => 'Synthetic author'])->id, 'role' => 'author']);
        $book->poems()->create(['body' => 'ټ ډ ړ ږ ښ ڼ ې ۍ', 'excerpt' => '', 'is_active' => true,
            'sample_mode' => 'full', 'audio_path' => 'test.mp3', 'artwork_path' => 'test.png']);

        return $book;
    }

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('covers');
        Storage::fake('audio');
        Storage::fake('artwork');
        Storage::disk('audio')->put('test.mp3', 'synthetic audio');
        Storage::disk('artwork')->put('test.png', 'synthetic artwork');
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_every_unpublished_state_hides_book_items_and_even_previously_signed_media(): void
    {
        foreach (['draft', 'ready', 'withdrawn'] as $state) {
            $book = $this->book($state);
            $item = $book->poems()->first();
            // The legacy flag cannot grant access.
            DB::table('collections')->where('id', $book->id)->update(['is_active' => true]);
            $this->getJson('/api/collections')->assertJsonMissing(['id' => $book->id]);
            $this->getJson('/api/authors/'.$book->credits->first()->author->slug)->assertJsonCount(0, 'data.books');
            foreach (['/api/collections/'.$book->slug, '/api/collections/'.$book->slug.'/poems', '/api/poems/'.$item->id, '/api/poems/'.$item->id.'/audio', '/media/covers/'.$book->id] as $path) {
                $this->getJson($path)->assertNotFound();
            }
            foreach (['poems.audio.stream', 'poems.artwork.stream'] as $route) {
                $this->get(URL::temporarySignedRoute($route, now()->addMinutes(10), ['poem' => $item, 'access' => 'paid']))->assertNotFound();
            }
            $this->assertSame($state === 'withdrawn', $book->allowsPriorPurchaserAccess());
        }
    }

    public function test_published_book_and_cover_are_public_but_hidden_items_are_not(): void
    {
        $book = $this->book('published');
        $item = $book->poems()->first();
        $this->getJson('/api/collections')->assertJsonPath('data.0.id', $book->id);
        $this->getJson('/api/collections/'.$book->slug)->assertOk();
        $this->getJson('/api/poems/'.$item->id)->assertOk();
        $response = $this->get($book->coverUrl())->assertOk();
        $this->assertStringContainsString('public', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('must-revalidate', $response->headers->get('Cache-Control'));
        $etag = $response->headers->get('ETag');
        $this->assertNotNull($etag);
        $this->withHeader('If-None-Match', $etag)->get($book->coverUrl())->assertStatus(304);
        $this->flushHeaders();
        $item->update(['is_active' => false]);
        $this->getJson('/api/collections/'.$book->slug.'/poems')->assertJsonCount(0, 'data');
        $this->getJson('/api/poems/'.$item->id)->assertNotFound();
        $this->getJson('/api/poems/'.$item->id.'/audio')->assertNotFound();
        foreach (['poems.audio.stream', 'poems.artwork.stream'] as $route) {
            $this->get(URL::temporarySignedRoute($route, now()->addMinutes(10), ['poem' => $item, 'access' => 'paid']))->assertNotFound();
        }
        $book->changeStatus('withdrawn');
        $this->withHeader('If-None-Match', $etag)->get($book->coverUrl())->assertNotFound();
        $this->assertDatabaseHas('poems', ['id' => $item->id, 'body' => $item->body]);
    }

    public function test_owner_preview_draft_and_ready_covers_are_private_and_signed(): void
    {
        config(['poetry.owner_preview_secret' => str_repeat('p', 64)]);
        $token = app(OwnerPreviewTokenService::class)->issue(1)['token'];
        foreach (['draft', 'ready'] as $state) {
            $book = $this->book($state);
            $this->getJson('/api/owner-preview/collections/'.$book->slug)->assertUnauthorized();
            $response = $this->withToken($token)->getJson('/api/owner-preview/collections/'.$book->slug)
                ->assertOk()->assertJsonPath('data.status', $state);
            $url = $response->json('data.cover_url');
            $this->get($url)->assertOk()->assertHeader('Cache-Control', 'no-store, private');
            $this->get('/media/owner-preview/covers/'.$book->id)->assertForbidden();
            $this->withToken($token)->getJson('/api/owner-preview/poems/'.$book->poems()->first()->id)->assertOk();
            $this->flushHeaders();
        }
        $this->actingAs(User::factory()->state(['is_owner' => true])->create());
        $this->get($book->coverUrl())->assertOk()->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_non_owner_is_forbidden_on_all_admin_pages_and_draft_covers(): void
    {
        $book = $this->book();
        $reader = User::factory()->create();
        $this->assertFalse($reader->fresh()->is_owner);
        $this->actingAs($reader);
        foreach (['/admin', '/admin/login', '/admin/profile', '/admin/collections', '/admin/collections/create', '/admin/collections/'.$book->id.'/edit', '/admin/authors', '/admin/categories'] as $path) {
            $this->get($path)->assertForbidden();
        }
        $this->get($book->coverUrl())->assertNotFound();
        $reader->fill(['is_owner' => true])->save();
        $this->assertFalse($reader->fresh()->is_owner);
    }

    public function test_livewire_update_is_denied_after_owner_role_is_revoked(): void
    {
        $owner = User::factory()->state(['is_owner' => true])->create();
        $this->actingAs($owner);
        $html = $this->get('/admin/collections')->assertOk()->getContent();
        preg_match_all('/wire:snapshot="([^"]+)"/', $html, $matches);
        $snapshot = null;
        foreach ($matches[1] as $encoded) {
            $candidate = html_entity_decode($encoded, ENT_QUOTES);
            if (str_contains(json_decode($candidate, true)['memo']['name'], 'ListCollections')) {
                $snapshot = $candidate;
                break;
            }
        }
        $this->assertNotNull($snapshot);
        $owner->forceFill(['is_owner' => false])->save();
        $this->postJson(Livewire::getUpdateUri(), [
            'components' => [['snapshot' => $snapshot, 'updates' => [], 'calls' => [['method' => '$refresh', 'params' => []]]]],
        ], ['X-Livewire' => 'true'])->assertForbidden();
    }

    public function test_owner_can_open_admin_and_optional_mfa_profile(): void
    {
        $owner = User::factory()->state(['is_owner' => true])->create();
        $this->actingAs($owner);
        $this->get('/admin')->assertOk();
        $this->get('/admin/profile')->assertOk()->assertSee('setUpAppAuthentication', false);
        $this->assertNull($owner->getAppAuthenticationSecret());
        $this->assertFalse(Filament::getPanel('admin')->isMultiFactorAuthenticationRequired());
        $owner->saveAppAuthenticationSecret('SYNTHETICSECRET');
        $owner->saveAppAuthenticationRecoveryCodes(['synthetic-code']);
        $this->assertNotSame('SYNTHETICSECRET', DB::table('users')->where('id', $owner->id)->value('app_authentication_secret'));
        $this->assertArrayNotHasKey('app_authentication_secret', $owner->toArray());
    }

    public function test_password_login_requires_mfa_when_owner_has_enabled_it(): void
    {
        $owner = User::factory()->state(['is_owner' => true])->create();
        $provider = AppAuthentication::make();
        $owner->saveAppAuthenticationSecret($provider->generateSecret());
        $login = Livewire::test(Login::class)->fillForm(['email' => $owner->email, 'password' => 'password'])->call('authenticate');
        $this->assertGuest();
        $this->assertNotNull($login->get('userUndertakingMultiFactorAuthentication'));
    }

    public function test_login_rate_limit_blocks_correct_password_after_five_failures(): void
    {
        $owner = User::factory()->state(['is_owner' => true])->create();
        $login = Livewire::test(Login::class)->fillForm(['email' => $owner->email, 'password' => 'incorrect']);
        for ($i = 0; $i < 5; $i++) {
            $login->call('authenticate')->assertHasFormErrors(['email']);
        }
        $login->fillForm(['email' => $owner->email, 'password' => 'password'])->call('authenticate');
        $this->assertGuest();
    }

    public function test_publish_requirements_actions_filters_and_status_audit(): void
    {
        $owner = User::factory()->state(['is_owner' => true])->create();
        $this->actingAs($owner);
        $book = $this->book();
        foreach (['credits', 'language', 'cover_image', 'status'] as $missing) {
            $candidate = $this->book();
            match ($missing) {
                'credits' => $candidate->credits()->delete(),
                'language' => $candidate->update(['language' => null]),
                'cover_image' => $candidate->update(['cover_image' => null]),
                'status' => $candidate->poems()->update(['is_active' => false]),
            };
            Livewire::test(ListCollections::class)->callTableAction('publish', $candidate)->assertHasErrors([$missing]);
            $this->assertSame('draft', $candidate->fresh()->status);
        }
        Livewire::test(ListCollections::class)->callTableAction('publish', $book)->assertHasNoErrors();
        $this->assertSame('published', $book->fresh()->status);
        $this->assertSame($owner->id, $book->fresh()->status_changed_by);
        $this->assertDatabaseHas('book_status_changes', ['collection_id' => $book->id, 'from_status' => 'draft', 'to_status' => 'published', 'changed_by' => $owner->id]);
        Livewire::test(ListCollections::class)->filterTable('status', 'published')->assertCanSeeTableRecords([$book])->assertCanNotSeeTableRecords([$candidate]);
        Livewire::test(ListCollections::class)->callTableAction('withdraw', $book)->assertHasNoErrors();
        $this->assertSame('withdrawn', $book->fresh()->status);
        Livewire::test(EditCollection::class, ['record' => $book->id])->fillForm(['status' => 'ready'])->call('save')->assertHasNoFormErrors();
        $this->assertSame('ready', $book->fresh()->status);
        $this->assertNotNull($book->fresh()->status_changed_at);
    }

    public function test_form_cannot_bypass_publish_guard_and_failed_transition_leaves_no_audit(): void
    {
        $this->actingAs(User::factory()->state(['is_owner' => true])->create());
        $book = $this->book();
        $book->poems()->update(['is_active' => false]);
        $before = DB::table('book_status_changes')->count();
        Livewire::test(EditCollection::class, ['record' => $book->id])->fillForm(['status' => 'published'])->call('save')->assertHasFormErrors(['status']);
        $this->assertSame('draft', $book->fresh()->status);
        $this->assertSame($before, DB::table('book_status_changes')->count());
    }
}
