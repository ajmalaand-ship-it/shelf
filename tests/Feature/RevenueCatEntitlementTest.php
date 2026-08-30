<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\Poem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RevenueCatEntitlementTest extends TestCase
{
    use RefreshDatabase;

    private Collection $collection;

    private Poem $free;

    private Poem $locked;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.revenuecat.secret_api_key' => 'test-server-secret',
            'services.revenuecat.entitlement' => 'unlock_all',
            'cache.default' => 'array',
        ]);
        Cache::setDefaultDriver('array');
        Cache::clear();
        $this->collection = Collection::create([
            'title' => 'TEST ONLY', 'slug' => 'p5-test', 'is_active' => true,
        ]);
        $this->free = Poem::create([
            'collection_id' => $this->collection->id, 'title' => 'Free',
            'body' => 'FREE FULL', 'excerpt' => 'FREE', 'is_free_sample' => true, 'is_active' => true,
        ]);
        $this->locked = Poem::create([
            'collection_id' => $this->collection->id, 'title' => 'Locked',
            'body' => 'PAID FULL', 'excerpt' => 'PAID EXCERPT', 'is_free_sample' => false, 'is_active' => true,
        ]);
    }

    public function test_free_poem_is_full_without_revenuecat_header(): void
    {
        $this->getJson("/api/poems/{$this->free->id}")
            ->assertOk()->assertJsonPath('data.locked', false)
            ->assertJsonPath('data.body', 'FREE FULL');
        Http::assertNothingSent();
    }

    public function test_locked_poem_without_or_with_invalid_header_stays_excerpt_only(): void
    {
        $this->getJson("/api/poems/{$this->locked->id}")
            ->assertOk()->assertJsonPath('data.locked', true)
            ->assertJsonPath('data.body', null);
        $this->withHeader('X-RC-User-Id', str_repeat('a', 101))
            ->getJson("/api/poems/{$this->locked->id}")
            ->assertOk()->assertJsonPath('data.locked', true);
        Http::assertNothingSent();
    }

    public function test_unknown_or_inactive_customer_stays_locked(): void
    {
        Http::fake([
            '*' => Http::sequence()
                ->push([], 404)
                ->push($this->customerInfo([])),
        ]);

        $this->entitledGet('unknown', "/api/poems/{$this->locked->id}")
            ->assertJsonPath('data.locked', true);
        $this->entitledGet('inactive', "/api/poems/{$this->locked->id}")
            ->assertJsonPath('data.locked', true);
    }

    public function test_active_unlock_all_returns_full_locked_poem(): void
    {
        Http::fake(['*' => Http::response($this->customerInfo([
            'unlock_all' => ['expires_date' => null, 'product_identifier' => 'pitswal_unlock_all_v1'],
        ]))]);

        $this->entitledGet('active-user', "/api/poems/{$this->locked->id}")
            ->assertOk()->assertJsonPath('data.locked', false)
            ->assertJsonPath('data.body', 'PAID FULL');
    }

    public function test_unpublished_poem_remains_unavailable_with_entitlement(): void
    {
        $this->locked->update(['is_active' => false]);
        Http::fake(['*' => Http::response($this->customerInfo([
            'unlock_all' => ['expires_date' => null],
        ]))]);

        $this->entitledGet('active-user', "/api/poems/{$this->locked->id}")
            ->assertNotFound();
        Http::assertNothingSent();
    }

    public function test_timeout_provider_error_and_malformed_response_fail_closed(): void
    {
        Log::spy();
        Http::fake([
            '*' => Http::sequence()
                ->pushStatus(401)
                ->pushStatus(403)
                ->push(['unexpected' => true])
                ->whenEmpty(Http::response([], 503)),
        ]);

        foreach (['unauthorized', 'forbidden', 'malformed', 'outage'] as $user) {
            $this->entitledGet($user, "/api/poems/{$this->locked->id}")
                ->assertOk()->assertJsonPath('data.locked', true)
                ->assertJsonMissing(['test-server-secret', $user]);
        }
    }

    public function test_positive_and_negative_cache_and_forced_refresh(): void
    {
        Http::fake(['*' => Http::sequence()
            ->push($this->customerInfo([]))
            ->push($this->customerInfo([
                'unlock_all' => ['expires_date' => null],
            ]))]);

        $path = "/api/poems/{$this->locked->id}";
        $this->entitledGet('cache-user', $path)->assertJsonPath('data.locked', true);
        $this->entitledGet('cache-user', $path)->assertJsonPath('data.locked', true);
        Http::assertSentCount(1);
        $this->withHeaders(['X-RC-User-Id' => 'cache-user', 'X-RC-Refresh' => '1'])
            ->getJson($path)->assertJsonPath('data.locked', false);
        $this->entitledGet('cache-user', $path)->assertJsonPath('data.locked', false);
        Http::assertSentCount(2);
    }

    public function test_list_endpoint_never_returns_body_and_reflects_access(): void
    {
        Http::fake(['*' => Http::response($this->customerInfo([
            'unlock_all' => ['expires_date' => null],
        ]))]);

        $response = $this->entitledGet('list-user', '/api/collections/p5-test/poems')
            ->assertOk()->assertJsonPath('data.1.locked', false);
        $response->assertJsonMissingPath('data.0.body')->assertJsonMissingPath('data.1.body');
    }

    public function test_locked_audio_requires_entitlement_and_uses_short_signed_route(): void
    {
        Storage::fake('audio');
        Storage::disk('audio')->put('paid/test.m4a', 'PAID AUDIO');
        $this->locked->update(['audio_path' => 'paid/test.m4a']);

        $this->getJson("/api/poems/{$this->locked->id}/audio")
            ->assertOk()->assertExactJson(['locked' => true, 'excerpt' => 'PAID EXCERPT']);
        Http::fake(['*' => Http::response($this->customerInfo([
            'unlock_all' => ['expires_date' => null],
        ]))]);
        $response = $this->entitledGet('audio-user', "/api/poems/{$this->locked->id}/audio")
            ->assertOk()->assertJsonPath('locked', false)->assertJsonMissingPath('audio_path');
        $this->get($response->json('url'))->assertOk();
    }

    public function test_unconfigured_server_fails_closed_without_provider_call(): void
    {
        config(['services.revenuecat.secret_api_key' => null]);
        Http::fake();

        $this->entitledGet('valid-user', "/api/poems/{$this->locked->id}")
            ->assertJsonPath('data.locked', true);
        Http::assertNothingSent();
    }

    private function entitledGet(string $userId, string $path)
    {
        return $this->withHeaders([
            'X-RC-User-Id' => $userId,
            'X-RC-Refresh' => '0',
        ])->getJson($path);
    }

    private function customerInfo(array $entitlements): array
    {
        return ['subscriber' => ['entitlements' => $entitlements]];
    }
}
