<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Collection;
use App\Models\Poem;
use App\Services\OwnerPreviewTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OwnerPreviewApiTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    private Collection $draftCollection;

    private Poem $draftLockedPoem;

    protected function setUp(): void
    {
        parent::setUp();
        config(['poetry.owner_preview_secret' => str_repeat('p', 64)]);
        AppSetting::query()->updateOrCreate(['key' => 'content_version'], ['value' => '17']);
        $this->token = app(OwnerPreviewTokenService::class)->issue(7)['token'];
        $this->draftCollection = Collection::create([
            'title' => 'څپو کې انځورونه',
            'slug' => 'draft-real-review',
            'author' => 'اجمل اند',
            'sort_order' => 2,
            'status' => 'draft',
        ]);
        $this->draftLockedPoem = Poem::create([
            'collection_id' => $this->draftCollection->id,
            'title' => 'ژمى',
            'body' => "ټ ډ ړ ږ ښ ڼ ې ۍ\n\nبله مسره",
            'excerpt' => 'ټ ډ ړ',
            'work_type' => 'TRANSLATION',
            'original_author' => 'پروین پژواک',
            'translator' => 'اجمل اند',
            'sort_order' => 3,
            'sample_mode' => 'none',
            'is_active' => false,
        ]);
    }

    public function test_missing_invalid_and_expired_tokens_are_denied(): void
    {
        $this->getJson('/api/owner-preview/collections')->assertUnauthorized();
        $this->withToken('invalid')->getJson('/api/owner-preview/collections')->assertUnauthorized();

        $expires = now()->subMinute()->getTimestamp();
        $nonce = str_repeat('a', 32);
        $signature = hash_hmac('sha256', $expires.'.'.$nonce, str_repeat('p', 64));
        $this->withToken("v1.$expires.$nonce.$signature")
            ->getJson('/api/owner-preview/collections')
            ->assertUnauthorized();
    }

    public function test_valid_token_reveals_drafts_and_preserves_review_metadata(): void
    {
        $collections = $this->previewGet('/api/owner-preview/collections')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'څپو کې انځورونه')
            ->assertJsonPath('data.0.is_active', false)
            ->assertJsonPath('data.0.poem_count', 1);
        $this->assertStringContainsString('no-store', $collections->headers->get('Cache-Control'));

        $this->previewGet('/api/owner-preview/collections/draft-real-review/poems')
            ->assertOk()
            ->assertJsonPath('data.0.locked', false)
            ->assertJsonPath('data.0.is_free_sample', false)
            ->assertJsonPath('data.0.is_active', false)
            ->assertJsonMissingPath('data.0.body');

        $this->previewGet('/api/owner-preview/poems/'.$this->draftLockedPoem->id)
            ->assertOk()
            ->assertJsonPath('data.body', "ټ ډ ړ ږ ښ ڼ ې ۍ\n\nبله مسره")
            ->assertJsonPath('data.requires_entitlement', true)
            ->assertJsonPath('data.is_free_sample', false)
            ->assertJsonPath('data.is_active', false)
            ->assertJsonPath('data.work_type', 'TRANSLATION')
            ->assertJsonPath('data.original_author', 'پروین پژواک')
            ->assertJsonPath('data.translator', 'اجمل اند');
    }

    public function test_public_api_still_hides_drafts_and_revenuecat_cannot_substitute(): void
    {
        $this->getJson('/api/collections')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/poems/'.$this->draftLockedPoem->id)->assertNotFound();
        $this->withHeader('X-RC-User-Id', 'active-looking-user')
            ->getJson('/api/owner-preview/poems/'.$this->draftLockedPoem->id)
            ->assertUnauthorized();
    }

    public function test_preview_audio_requires_token_then_uses_short_signed_private_route(): void
    {
        Storage::fake('audio');
        Storage::disk('audio')->put('draft/private-test.m4a', 'PRIVATE PREVIEW AUDIO');
        $this->draftLockedPoem->update(['audio_path' => 'draft/private-test.m4a']);

        $path = '/api/owner-preview/poems/'.$this->draftLockedPoem->id.'/audio';
        $this->getJson($path)->assertUnauthorized();
        $response = $this->previewGet($path)
            ->assertOk()
            ->assertJsonPath('locked', false)
            ->assertJsonMissingPath('audio_path');
        $stream = $this->get($response->json('url'))->assertOk();
        $this->assertStringContainsString('no-store', $stream->headers->get('Cache-Control'));
    }

    public function test_preview_artwork_requires_token_and_never_leaks_private_path(): void
    {
        Storage::fake('artwork');
        Storage::disk('artwork')->put('draft/original.png', 'PRIVATE ARTWORK');
        $this->draftLockedPoem->update(['artwork_path' => 'draft/original.png']);

        $this->getJson('/api/owner-preview/poems/'.$this->draftLockedPoem->id)->assertUnauthorized();
        $response = $this->previewGet('/api/owner-preview/poems/'.$this->draftLockedPoem->id)
            ->assertOk()->assertJsonPath('data.artwork.available', true)
            ->assertJsonPath('data.artwork.locked', false)
            ->assertJsonMissingPath('data.artwork_path');
        $stream = $this->get($response->json('data.artwork.url'))->assertOk();
        $this->assertSame('PRIVATE ARTWORK', $stream->streamedContent());
        $this->assertStringContainsString('no-store', $stream->headers->get('Cache-Control'));
    }

    public function test_denial_does_not_leak_token_or_secret(): void
    {
        Log::spy();
        $secret = str_repeat('p', 64);
        $response = $this->withToken('invalid-sensitive-token')
            ->getJson('/api/owner-preview/collections')
            ->assertUnauthorized();

        $response->assertDontSee('invalid-sensitive-token')->assertDontSee($secret);
        Log::shouldNotHaveReceived('debug');
        Log::shouldNotHaveReceived('info');
    }

    private function previewGet(string $path)
    {
        return $this->withToken($this->token)->getJson($path);
    }
}
