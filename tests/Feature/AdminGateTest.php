<?php

namespace Tests\Feature;

use App\Filament\Resources\AppSettings\AppSettingResource;
use App\Filament\Resources\AppSettings\Pages\EditAppSetting;
use App\Filament\Resources\AppSettings\Pages\ListAppSettings;
use App\Filament\Resources\Collections\Pages\CreateCollection;
use App\Filament\Resources\Collections\Pages\EditCollection;
use App\Filament\Resources\Poems\Pages\CreatePoem;
use App\Filament\Resources\Poems\Pages\EditPoem;
use App\Models\AppSetting;
use App\Models\Collection;
use App\Models\Poem;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_anonymous_user_cannot_access_admin(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_owner_can_see_and_open_app_setting_edit_action(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $setting = AppSetting::where('key', 'content_version')->firstOrFail();

        Livewire::test(ListAppSettings::class)
            ->assertCanSeeTableRecords([$setting])
            ->assertTableActionVisible('edit', $setting);

        $this->get(AppSettingResource::getUrl('edit', ['record' => $setting]))->assertOk();
    }

    public function test_owner_can_create_collection_with_validated_cover(): void
    {
        Storage::fake('covers');
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(CreateCollection::class)
            ->fillForm([
                'title' => 'ازمېښتي ټولګه', 'slug' => 'admin-test',
                'cover_image' => [UploadedFile::fake()->create('cover.jpg', 10, 'image/jpeg')],
                'sort_order' => 1, 'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('collections', ['slug' => 'admin-test']);
    }

    public function test_poem_form_rejects_non_audio_upload(): void
    {
        Storage::fake('audio');
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $collection = Collection::create(['title' => 'ټولګه', 'slug' => 'upload-test', 'is_active' => true]);

        Livewire::test(CreatePoem::class)
            ->fillForm([
                'collection_id' => $collection->id, 'title' => 'شعر', 'body' => 'متن',
                'excerpt' => 'لنډ متن', 'audio_path' => [UploadedFile::fake()->create('attack.php', 2, 'application/x-php')],
                'sort_order' => 1, 'is_free_sample' => true, 'is_active' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['audio_path']);
    }

    public function test_collection_form_rejects_non_image_cover(): void
    {
        Storage::fake('covers');
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(CreateCollection::class)
            ->fillForm([
                'title' => 'TEST ONLY', 'slug' => 'invalid-cover',
                'cover_image' => [UploadedFile::fake()->create('cover.php', 2, 'application/x-php')],
                'sort_order' => 1, 'is_active' => false,
            ])
            ->call('create')
            ->assertHasFormErrors(['cover_image']);
    }

    public function test_owner_can_edit_collection_front_matter_in_pashto(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $collection = Collection::create(['title' => 'TEST ONLY', 'slug' => 'front-matter', 'is_active' => false]);

        Livewire::test(EditCollection::class, ['record' => $collection->getRouteKey()])
            ->fillForm([
                'author' => 'اجمل اند',
                'dedication' => 'يوازې ښکلا تهـ',
                'introduction' => "لومړۍ کرښه\n\nدويم بند",
                'publication_info' => 'لومړی چاپ: ۱۳۷۹ لمريز — وږى',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $collection->refresh();
        $this->assertSame("لومړۍ کرښه\n\nدويم بند", $collection->introduction);
        $this->assertFalse($collection->is_active);
    }

    public function test_owner_can_edit_order_state_pashto_audio_and_settings(): void
    {
        Storage::fake('audio');
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $collection = Collection::create([
            'title' => 'TEST ONLY', 'slug' => 'test-only', 'sort_order' => 9, 'is_active' => false,
        ]);

        Livewire::test(EditCollection::class, ['record' => $collection->getRouteKey()])
            ->fillForm(['sort_order' => 2, 'is_active' => true])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertDatabaseHas('collections', ['id' => $collection->id, 'sort_order' => 2, 'is_active' => true]);

        $body = "لومړۍ کرښه\nدويمه کرښه\n\nدويم بند";
        Livewire::test(CreatePoem::class)
            ->fillForm([
                'collection_id' => $collection->id, 'title' => 'TEST ONLY شعر', 'body' => $body,
                'excerpt' => 'TEST ONLY',
                'audio_path' => [UploadedFile::fake()->create('voice.m4a', 20, 'audio/mp4')],
                'audio_duration_seconds' => 12, 'sort_order' => 4,
                'is_free_sample' => false, 'is_active' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $poem = Poem::where('title', 'TEST ONLY شعر')->firstOrFail();
        $this->assertSame($body, $poem->body);
        $this->assertNotNull($poem->audio_path);
        Storage::disk('audio')->assertExists($poem->audio_path);

        Livewire::test(EditPoem::class, ['record' => $poem->getRouteKey()])
            ->fillForm(['sort_order' => 1, 'is_free_sample' => true, 'is_active' => true])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertDatabaseHas('poems', [
            'id' => $poem->id, 'sort_order' => 1, 'is_free_sample' => true, 'is_active' => true,
        ]);

        $setting = AppSetting::where('key', 'content_version')->firstOrFail();
        Livewire::test(EditAppSetting::class, ['record' => $setting->getRouteKey()])
            ->fillForm(['value' => '2'])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertDatabaseHas('app_settings', ['key' => 'content_version', 'value' => '2']);
    }
}
