<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Services\BookAccessService;
use App\Services\RevenueCatEntitlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class RevenueCatEntitlementTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_old_identifiers_headers_provider_responses_and_caches_cannot_grant_text_or_media(): void
    {
        Storage::fake('audio');
        Storage::fake('artwork');
        Storage::disk('audio')->put('full.mp3', 'PRIVATE AUDIO');
        Storage::disk('artwork')->put('full.png', 'PRIVATE ARTWORK');
        Http::fake(['*' => Http::response(['subscriber' => ['entitlements' => ['unlock_all' => ['expires_date' => null, 'product_identifier' => 'pitswal_unlock_all_v1']]]])]);
        config(['services.revenuecat.entitlement' => 'unlock_all', 'services.revenuecat.secret_api_key' => 'synthetic']);
        $book = Collection::create(['title' => 'Synthetic', 'status' => 'published']);
        $item = $book->poems()->create(['body' => 'PRIVATE FULL BODY', 'excerpt' => 'UNAPPROVED EXCERPT', 'is_active' => true, 'audio_path' => 'full.mp3', 'artwork_path' => 'full.png']);
        // Even an old database flag and positive cache cannot authorize the new API.
        DB::table('poems')->where('id', $item->id)->update(['is_free_sample' => true]);
        foreach (['unlock_all', 'pitswal_unlock_all_v1', 'default', 'previously-entitled-user'] as $identity) {
            Cache::put('revenuecat:entitlement:'.hash_hmac('sha256', $identity, config('app.key')), 'entitled', 3600);
            $headers = ['X-RC-User-Id' => $identity, 'X-RC-Refresh' => '1', 'Authorization' => 'Bearer '.$identity,
                'X-Entitlement' => $identity, 'X-Product-Id' => $identity, 'X-Unlock-All' => 'true'];
            $this->withHeaders($headers)->getJson('/api/poems/'.$item->id.'?access=paid&unlock_all=1')
                ->assertOk()->assertJsonPath('data.locked', true)->assertJsonPath('data.body', null)
                ->assertJsonPath('data.excerpt', null)->assertJsonPath('data.artwork.url', null)->assertJsonPath('data.audio.locked', true);
            $this->getJson('/api/collections/'.$book->slug.'/poems')->assertJsonPath('data.0.excerpt', null)->assertJsonPath('data.0.locked', true);
            $this->getJson('/api/poems/'.$item->id.'/audio')->assertExactJson(['locked' => true, 'excerpt' => null]);
            $this->assertFalse(app(RevenueCatEntitlementService::class)->isEntitled($identity, true));
        }
        foreach (['poems.audio.stream', 'poems.artwork.stream'] as $route) {
            // Formerly valid paid signatures must also fail after the switch-off.
            $this->get(URL::temporarySignedRoute($route, now()->addMinutes(5), ['poem' => $item, 'access' => 'paid']))->assertNotFound();
            $this->get(route($route, $item))->assertForbidden();
        }
        foreach (['unlock_all', 'pitswal_unlock_all_v1', 'default'] as $path) {
            $this->getJson('/api/'.$path)->assertNotFound();
        }
        $this->assertFalse(app(BookAccessService::class)->ownsBook(null, $book));
        Http::assertNothingSent();
    }
}
