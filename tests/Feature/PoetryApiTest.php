<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Collection;
use App\Models\Poem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PoetryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_pashto_unicode_and_stanzas_round_trip_for_free_poem(): void
    {
        $collection = Collection::create([
            'title' => 'ازمېښتي ټولګه', 'slug' => 'test-collection', 'sort_order' => 1, 'status' => 'published',
        ]);
        $body = "د زړه خبره\nد مينې سندره\n\nدويم بند";
        $poem = Poem::create([
            'collection_id' => $collection->id, 'title' => 'ازمېښتي شعر', 'body' => $body,
            'excerpt' => 'د زړه خبره', 'sort_order' => 1, 'sample_mode' => 'full', 'is_active' => true,
        ]);

        $this->getJson("/api/poems/{$poem->id}")
            ->assertOk()
            ->assertJsonPath('data.title', 'ازمېښتي شعر')
            ->assertJsonPath('data.body', $body)
            ->assertJsonPath('data.locked', false);
    }

    public function test_locked_poem_never_exposes_full_body_or_audio_url(): void
    {
        Storage::fake('audio');
        $collection = Collection::create(['title' => 'ټولګه', 'slug' => 'locked', 'status' => 'published']);
        $poem = Poem::create([
            'collection_id' => $collection->id, 'title' => 'پټ شعر', 'body' => 'بشپړ پټ متن',
            'excerpt' => 'لنډه برخه', 'audio_path' => 'source.mp3', 'sample_mode' => 'none', 'is_active' => true,
        ]);

        $this->getJson("/api/poems/{$poem->id}")
            ->assertOk()->assertJsonPath('data.locked', true)->assertJsonPath('data.body', null)
            ->assertJsonMissingPath('data.presentation_spacing');
        $this->getJson("/api/poems/{$poem->id}/audio")
            ->assertOk()->assertExactJson(['locked' => true, 'excerpt' => null]);
    }

    public function test_only_active_collections_and_poems_are_public(): void
    {
        $active = Collection::create(['title' => 'ښکاره', 'slug' => 'active', 'status' => 'published']);
        $hidden = Collection::create(['title' => 'پټ', 'slug' => 'hidden', 'status' => 'draft']);
        Poem::create(['collection_id' => $active->id, 'title' => 'ښکاره', 'body' => 'متن', 'excerpt' => 'متن', 'is_active' => true]);
        Poem::create(['collection_id' => $active->id, 'title' => 'پټ', 'body' => 'متن', 'excerpt' => 'متن', 'is_active' => false]);

        $this->getJson('/api/collections')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/collections/active/poems')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/collections/'.$hidden->slug)->assertNotFound();
    }

    public function test_app_config_exposes_only_public_version_keys(): void
    {
        AppSetting::where('key', 'public_app_name')->update(['value' => 'پېڅوَل']);
        AppSetting::where('key', 'public_slogan')->update(['value' => 'اجمل اند بشپړه شاعري']);
        AppSetting::where('key', 'content_version')->update(['value' => '7']);
        AppSetting::where('key', 'min_app_version')->update(['value' => '1.2.3']);
        AppSetting::create(['key' => 'private_value', 'value' => 'never expose']);

        $this->getJson('/api/app-config')->assertExactJson([
            'app_name' => 'پېڅوَل',
            'slogan' => 'اجمل اند بشپړه شاعري',
            'content_version' => 7,
            'min_app_version' => '1.2.3',
        ]);
    }

    public function test_cover_and_free_audio_metadata_are_public_safe(): void
    {
        Storage::fake('audio');
        Storage::fake('covers');
        Storage::disk('audio')->put('test-only.m4a', 'TEST ONLY AUDIO');
        $collection = Collection::create([
            'title' => 'TEST ONLY', 'slug' => 'media-test', 'cover_image' => 'cover.webp', 'status' => 'published',
        ]);
        $poem = Poem::create([
            'collection_id' => $collection->id, 'title' => 'TEST ONLY', 'body' => 'ازمېښتي متن',
            'excerpt' => 'ازمېښت', 'audio_path' => 'test-only.m4a', 'audio_duration_seconds' => 15,
            'sample_mode' => 'full', 'is_active' => true,
        ]);

        $this->getJson('/api/collections/media-test')
            ->assertOk()->assertJsonPath('data.cover_url', $collection->coverUrl());
        $response = $this->getJson("/api/poems/{$poem->id}/audio")
            ->assertOk()->assertJsonPath('locked', false)->assertJsonPath('duration_seconds', 15)
            ->assertJsonPath('cache_key', hash('sha256', 'test-only.m4a'))
            ->assertJsonPath('format', 'm4a')
            ->assertJsonMissingPath('audio_path');
        $stream = $this->get($response->json('url'))->assertOk();
        $this->assertSame('TEST ONLY AUDIO', $stream->streamedContent());
    }

    public function test_audio_replacement_and_removal_change_identity_and_public_availability(): void
    {
        Storage::fake('audio');
        Storage::disk('audio')->put('first/test.m4a', 'FIRST');
        Storage::disk('audio')->put('second/test.mp3', 'SECOND');
        $collection = Collection::create(['title' => 'TEST ONLY', 'slug' => 'audio-change', 'status' => 'published']);
        $poem = Poem::create([
            'collection_id' => $collection->id, 'title' => 'TEST ONLY', 'body' => 'متن',
            'excerpt' => 'متن', 'audio_path' => 'first/test.m4a',
            'sample_mode' => 'full', 'is_active' => true,
        ]);
        $initialVersion = (int) AppSetting::where('key', 'content_version')->value('value');

        $this->getJson("/api/poems/{$poem->id}")
            ->assertOk()
            ->assertJsonPath('data.audio.available', true)
            ->assertJsonPath('data.audio.cache_key', hash('sha256', 'first/test.m4a'));

        $poem->update(['audio_path' => 'second/test.mp3']);
        $this->assertSame($initialVersion + 1, (int) AppSetting::where('key', 'content_version')->value('value'));
        $this->getJson("/api/poems/{$poem->id}/audio")
            ->assertOk()
            ->assertJsonPath('cache_key', hash('sha256', 'second/test.mp3'))
            ->assertJsonPath('format', 'mp3');

        $poem->update(['audio_path' => null, 'audio_duration_seconds' => null]);
        $this->assertSame($initialVersion + 2, (int) AppSetting::where('key', 'content_version')->value('value'));
        $this->getJson("/api/poems/{$poem->id}")
            ->assertOk()->assertJsonPath('data.audio.available', false)
            ->assertJsonPath('data.audio.cache_key', null);
        $this->getJson("/api/poems/{$poem->id}/audio")->assertNotFound();
    }

    public function test_artwork_is_private_and_follows_free_locked_and_draft_boundaries(): void
    {
        Storage::fake('artwork');
        Storage::disk('artwork')->put('poems/test.png', 'PRIVATE ARTWORK');
        $collection = Collection::create(['title' => 'TEST ONLY', 'slug' => 'artwork-access', 'status' => 'published']);
        $free = Poem::create([
            'collection_id' => $collection->id, 'body' => 'متن', 'excerpt' => 'متن',
            'artwork_path' => 'poems/test.png', 'sample_mode' => 'full', 'is_active' => true,
        ]);
        $locked = Poem::create([
            'collection_id' => $collection->id, 'body' => 'پټ', 'excerpt' => 'لنډ',
            'artwork_path' => 'poems/test.png', 'sample_mode' => 'none', 'is_active' => true,
        ]);
        $draft = Poem::create([
            'collection_id' => $collection->id, 'body' => 'مسوده', 'excerpt' => 'مسوده',
            'artwork_path' => 'poems/test.png', 'sample_mode' => 'full', 'is_active' => false,
        ]);

        $freeResponse = $this->getJson("/api/poems/{$free->id}")
            ->assertOk()->assertJsonPath('data.artwork.available', true)
            ->assertJsonPath('data.artwork.locked', false)
            ->assertJsonMissingPath('data.artwork_path');
        $stream = $this->get($freeResponse->json('data.artwork.url'))->assertOk();
        $this->assertSame('PRIVATE ARTWORK', $stream->streamedContent());
        $this->getJson("/api/poems/{$locked->id}")
            ->assertOk()->assertJsonPath('data.artwork.locked', true)
            ->assertJsonPath('data.artwork.url', null)
            ->assertJsonMissingPath('data.artwork_path');
        $this->getJson("/api/poems/{$draft->id}")->assertNotFound();
    }

    public function test_missing_private_audio_file_returns_not_found_without_exposing_path(): void
    {
        Storage::fake('audio');
        $collection = Collection::create(['title' => 'TEST ONLY', 'slug' => 'missing-audio', 'status' => 'published']);
        $poem = Poem::create([
            'collection_id' => $collection->id, 'title' => 'TEST ONLY', 'body' => 'متن',
            'excerpt' => 'متن', 'audio_path' => 'private/missing.m4a',
            'sample_mode' => 'full', 'is_active' => true,
        ]);

        $this->getJson("/api/poems/{$poem->id}/audio")
            ->assertNotFound()
            ->assertJsonMissingPath('audio_path');
    }

    public function test_published_collection_returns_reader_safe_front_matter_and_active_poem_count(): void
    {
        Storage::fake('covers');
        $collection = Collection::create([
            'title' => 'څپو کې انځورونه',
            'slug' => 'presentation-test',
            'author' => 'اجمل اند',
            'dedication' => 'يوازې ښکلا تهـ',
            'introduction' => "خوږو لوستونکيو!\nدويمه کرښه",
            'foreword_author' => 'غفور لېوال',
            'foreword' => 'اې عشقه نامراده....',
            'publication_info' => 'لومړی چاپ: ۱۳۷۹ لمريز — وږى',
            'cover_image' => 'presentation/cover.webp',
            'status' => 'published',
        ]);
        Poem::create(['collection_id' => $collection->id, 'body' => 'لومړی', 'excerpt' => 'لومړی', 'is_active' => true]);
        Poem::create(['collection_id' => $collection->id, 'body' => 'دويم', 'excerpt' => 'دويم', 'is_active' => true]);
        Poem::create(['collection_id' => $collection->id, 'body' => 'پټ', 'excerpt' => 'پټ', 'is_active' => false]);

        $this->getJson('/api/collections/presentation-test')
            ->assertOk()
            ->assertJsonPath('data.title', 'څپو کې انځورونه')
            ->assertJsonPath('data.author', 'اجمل اند')
            ->assertJsonPath('data.dedication', 'يوازې ښکلا تهـ')
            ->assertJsonPath('data.introduction', "خوږو لوستونکيو!\nدويمه کرښه")
            ->assertJsonPath('data.foreword_author', 'غفور لېوال')
            ->assertJsonPath('data.foreword', 'اې عشقه نامراده....')
            ->assertJsonPath('data.publication_info', 'لومړی چاپ: ۱۳۷۹ لمريز — وږى')
            ->assertJsonPath('data.poem_count', 2)
            ->assertJsonMissingPath('data.source_path');
    }

    public function test_translation_attribution_is_public_but_private_source_location_is_not(): void
    {
        $collection = Collection::create([
            'title' => 'هېندارې او چینې', 'slug' => 'translation-attribution', 'status' => 'published',
        ]);
        $poem = Poem::create([
            'collection_id' => $collection->id, 'title' => 'ژمى', 'body' => 'ژباړل شوی متن',
            'excerpt' => 'ژباړل شوی متن', 'work_type' => 'TRANSLATION',
            'original_author' => 'پروین پژواک', 'translator' => 'اجمل اند',
            'source_note' => 'دپروین پژواک ديو شعرژباړه', 'sample_mode' => 'full', 'is_active' => true,
        ]);

        $this->getJson("/api/poems/{$poem->id}")
            ->assertOk()
            ->assertJsonPath('data.work_type', 'TRANSLATION')
            ->assertJsonPath('data.original_author', 'پروین پژواک')
            ->assertJsonPath('data.translator', 'اجمل اند')
            ->assertJsonMissingPath('data.source_location')
            ->assertJsonMissingPath('data.source_path');
    }

    public function test_legacy_layout_mode_remains_api_compatible_without_changing_body(): void
    {
        $collection = Collection::create(['title' => 'TEST ONLY', 'slug' => 'layout-api', 'status' => 'published']);
        $body = "لومړۍ کرښه\nدويمه کرښه\n\nڅلورمه کرښه";
        $poem = Poem::create([
            'collection_id' => $collection->id,
            'body' => $body,
            'excerpt' => 'لنډ',
            'layout_mode' => Poem::LAYOUT_COUPLET,
            'sample_mode' => 'full',
            'is_active' => true,
        ]);

        $this->getJson("/api/poems/{$poem->id}")
            ->assertOk()
            ->assertJsonPath('data.layout_mode', Poem::LAYOUT_COUPLET)
            ->assertJsonMissingPath('data.presentation_spacing')
            ->assertJsonPath('data.body', $body);
        $this->assertSame($body, $poem->fresh()->body);
    }

    public function test_editorial_and_collection_changes_increment_content_version(): void
    {
        $collection = Collection::create(['title' => 'TEST ONLY', 'slug' => 'version-all']);
        $poem = Poem::create(['collection_id' => $collection->id, 'body' => 'متن', 'excerpt' => 'لنډ']);
        $version = (int) AppSetting::where('key', 'content_version')->value('value');

        $poem->update(['layout_mode' => Poem::LAYOUT_FOUR_LINES]);
        $poem->update(['sort_order' => 8, 'is_active' => true, 'sample_mode' => 'full']);
        $collection->update(['title' => 'TEST ONLY UPDATED']);

        $this->assertSame($version + 3, (int) AppSetting::where('key', 'content_version')->value('value'));
    }
}
