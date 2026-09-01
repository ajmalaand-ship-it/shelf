<?php

namespace Tests\Feature;

use App\Filament\Resources\AppSettings\AppSettingResource;
use App\Filament\Resources\AppSettings\Pages\EditAppSetting;
use App\Filament\Resources\AppSettings\Pages\ListAppSettings;
use App\Filament\Resources\Collections\CollectionResource;
use App\Filament\Resources\Collections\Pages\CreateCollection;
use App\Filament\Resources\Collections\Pages\EditCollection;
use App\Filament\Resources\Collections\Pages\ListCollections;
use App\Filament\Resources\Poems\Pages\CreatePoem;
use App\Filament\Resources\Poems\Pages\EditPoem;
use App\Filament\Resources\Poems\Pages\ListPoems;
use App\Filament\Resources\Poems\PoemResource;
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

    public function test_owner_can_replace_and_remove_audio_without_losing_private_files(): void
    {
        Storage::fake('audio');
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $collection = Collection::create(['title' => 'TEST ONLY', 'slug' => 'audio-owner-flow']);

        Livewire::test(CreatePoem::class)
            ->fillForm([
                'collection_id' => $collection->id, 'title' => 'TEST ONLY', 'body' => 'متن',
                'excerpt' => 'متن',
                'audio_path' => [UploadedFile::fake()->create('first.m4a', 20, 'audio/mp4')],
                'sort_order' => 1, 'is_free_sample' => true, 'is_active' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $poem = Poem::where('title', 'TEST ONLY')->firstOrFail();
        $firstPath = $poem->audio_path;
        Storage::disk('audio')->assertExists($firstPath);
        $version = (int) AppSetting::where('key', 'content_version')->value('value');

        Livewire::test(EditPoem::class, ['record' => $poem->getRouteKey()])
            ->fillForm([
                'audio_path' => [UploadedFile::fake()->create('replacement.mp3', 20, 'audio/mpeg')],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $poem->refresh();
        $replacementPath = $poem->audio_path;
        $this->assertNotSame($firstPath, $replacementPath);
        Storage::disk('audio')->assertExists($firstPath);
        Storage::disk('audio')->assertExists($replacementPath);
        $this->assertSame($version + 1, (int) AppSetting::where('key', 'content_version')->value('value'));

        Livewire::test(EditPoem::class, ['record' => $poem->getRouteKey()])
            ->fillForm(['audio_path' => []])
            ->call('save')
            ->assertHasNoFormErrors();

        $poem->refresh();
        $this->assertNull($poem->audio_path);
        $this->assertNull($poem->audio_duration_seconds);
        Storage::disk('audio')->assertExists($replacementPath);
        $this->assertSame($version + 2, (int) AppSetting::where('key', 'content_version')->value('value'));
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
                'foreword_author' => 'غفور لېوال',
                'foreword' => "اې عشقه نامراده....\nدويمه کرښه",
                'publication_info' => 'لومړی چاپ: ۱۳۷۹ لمريز — وږى',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $collection->refresh();
        $this->assertSame("لومړۍ کرښه\n\nدويم بند", $collection->introduction);
        $this->assertSame('غفور لېوال', $collection->foreword_author);
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
                'work_type' => 'TRANSLATION', 'original_author' => 'پروین پژواک', 'translator' => 'اجمل اند',
                'audio_path' => [UploadedFile::fake()->create('voice.m4a', 20, 'audio/mp4')],
                'audio_duration_seconds' => 12, 'sort_order' => 4,
                'is_free_sample' => false, 'is_active' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $poem = Poem::where('title', 'TEST ONLY شعر')->firstOrFail();
        $this->assertSame($body, $poem->body);
        $this->assertNotNull($poem->audio_path);
        $this->assertSame('TRANSLATION', $poem->work_type);
        $this->assertSame('پروین پژواک', $poem->original_author);
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

    public function test_poem_list_supports_owner_search_filters_and_inline_free_control(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $firstCollection = Collection::create(['title' => 'لومړۍ ټولګه', 'slug' => 'first', 'is_active' => false]);
        $secondCollection = Collection::create(['title' => 'دويمه ټولګه', 'slug' => 'second', 'is_active' => true]);
        $untitled = Poem::create([
            'collection_id' => $firstCollection->id,
            'body' => "د لټون لومړۍ کرښه\nدويمه کرښه",
            'excerpt' => 'لنډ متن', 'sort_order' => 1,
            'is_active' => false, 'is_free_sample' => false,
        ]);
        $published = Poem::create([
            'collection_id' => $secondCollection->id, 'title' => 'ليکلی سرليک',
            'body' => 'متن', 'excerpt' => 'لنډ متن', 'audio_path' => 'test-only.m4a',
            'sort_order' => 2, 'is_active' => true, 'is_free_sample' => true,
        ]);

        Livewire::test(ListPoems::class)
            ->assertTableColumnExists('admin_display_title')
            ->assertTableColumnExists('collection.title')
            ->assertTableColumnExists('work_type')
            ->assertTableColumnExists('is_active')
            ->assertTableColumnExists('is_free_sample')
            ->assertTableColumnExists('audio_path')
            ->searchTable('د لټون لومړۍ کرښه')
            ->assertCanSeeTableRecords([$untitled])
            ->assertCanNotSeeTableRecords([$published]);

        Livewire::test(ListPoems::class)
            ->searchTable('ليکلی سرليک')
            ->assertCanSeeTableRecords([$published])
            ->assertCanNotSeeTableRecords([$untitled]);

        Livewire::test(ListPoems::class)
            ->assertTableActionVisible('publish', $untitled)
            ->callTableAction('publish', $untitled);
        $this->assertTrue($untitled->fresh()->is_active);

        Livewire::test(ListPoems::class)
            ->assertTableActionVisible('unpublish', $untitled->fresh())
            ->callTableAction('unpublish', $untitled->fresh());
        $this->assertFalse($untitled->fresh()->is_active);

        Livewire::test(ListPoems::class)
            ->filterTable('collection', $firstCollection)
            ->assertCanSeeTableRecords([$untitled])
            ->assertCanNotSeeTableRecords([$published])
            ->call('updateTableColumnState', 'is_free_sample', (string) $untitled->id, true);

        $this->assertDatabaseHas('poems', [
            'id' => $untitled->id, 'is_free_sample' => true, 'is_active' => false,
        ]);

        Livewire::test(ListPoems::class)
            ->filterTable('work_type', 'ORIGINAL')
            ->assertCanSeeTableRecords([$published])
            ->assertCanSeeTableRecords([$untitled]);

        Livewire::test(ListPoems::class)
            ->filterTable('is_active', true)
            ->filterTable('is_free_sample', true)
            ->filterTable('audio_path', true)
            ->assertCanSeeTableRecords([$published])
            ->assertCanNotSeeTableRecords([$untitled]);
    }

    public function test_poem_bulk_editorial_actions_do_not_mix_free_and_publication_states(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $collection = Collection::create(['title' => 'TEST ONLY', 'slug' => 'bulk-actions', 'is_active' => false]);
        $poems = collect([1, 2])->map(fn (int $order) => Poem::create([
            'collection_id' => $collection->id, 'body' => "شعر {$order}", 'excerpt' => 'لنډ',
            'sort_order' => $order, 'is_active' => false, 'is_free_sample' => false,
        ]));

        Livewire::test(ListPoems::class)
            ->assertTableBulkActionExists('setFree')
            ->assertTableBulkActionExists('setLocked')
            ->assertTableBulkActionExists('publish')
            ->assertTableBulkActionExists('unpublish')
            ->callTableBulkAction('setFree', $poems);

        $this->assertSame(2, Poem::where('is_free_sample', true)->where('is_active', false)->count());

        Livewire::test(ListPoems::class)->callTableBulkAction('publish', $poems);
        $this->assertSame(2, Poem::where('is_free_sample', true)->where('is_active', true)->count());
        $this->assertFalse($collection->fresh()->is_active);

        Livewire::test(ListPoems::class)->callTableBulkAction('unpublish', $poems);
        Livewire::test(ListPoems::class)->callTableBulkAction('setLocked', $poems);
        $this->assertSame(2, Poem::where('is_free_sample', false)->where('is_active', false)->count());
    }

    public function test_collection_list_shows_counts_and_uses_deliberate_publication_actions(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $collection = Collection::create([
            'title' => 'TEST ONLY', 'slug' => 'collection-controls', 'cover_image' => 'test.webp', 'is_active' => false,
        ]);
        Poem::create([
            'collection_id' => $collection->id, 'body' => 'لومړی', 'excerpt' => 'لنډ',
            'is_free_sample' => true, 'is_active' => true, 'audio_path' => 'test.m4a',
        ]);
        Poem::create([
            'collection_id' => $collection->id, 'body' => 'دويم', 'excerpt' => 'لنډ',
            'is_free_sample' => false, 'is_active' => false,
        ]);

        Livewire::test(ListCollections::class)
            ->assertTableColumnStateSet('poems_count', 2, $collection)
            ->assertTableColumnStateSet('free_poems_count', 1, $collection)
            ->assertTableColumnStateSet('published_poems_count', 1, $collection)
            ->assertTableColumnStateSet('audio_poems_count', 1, $collection)
            ->assertTableActionVisible('publish', $collection)
            ->callTableAction('publish', $collection);

        $this->assertTrue($collection->fresh()->is_active);
        $this->assertSame(1, $collection->poems()->where('is_active', true)->count());

        Livewire::test(ListCollections::class)
            ->assertTableActionVisible('unpublish', $collection->fresh())
            ->callTableAction('unpublish', $collection->fresh());

        $this->assertFalse($collection->fresh()->is_active);
        $this->assertSame(1, $collection->poems()->where('is_active', true)->count());
    }

    public function test_legacy_poem_layout_can_be_preserved_and_safely_changed_on_edit(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $collection = Collection::create(['title' => 'TEST ONLY', 'slug' => 'layout-owner']);

        Livewire::test(CreatePoem::class)
            ->fillForm([
                'collection_id' => $collection->id,
                'body' => "لومړۍ\nدويمه",
                'excerpt' => 'لنډ',
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $poem = Poem::where('collection_id', $collection->id)->firstOrFail();
        $this->assertSame(Poem::LAYOUT_SOURCE, $poem->layout_mode);

        Livewire::test(EditPoem::class, ['record' => $poem->getRouteKey()])
            ->fillForm(['layout_mode' => Poem::LAYOUT_COUPLET])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame(Poem::LAYOUT_COUPLET, $poem->fresh()->layout_mode);

        Livewire::test(EditPoem::class, ['record' => $poem->getRouteKey()])
            ->fillForm(['layout_mode' => 'GUESSED_BY_AI'])
            ->call('save')
            ->assertHasFormErrors(['layout_mode']);
        $this->assertSame(Poem::LAYOUT_COUPLET, $poem->fresh()->layout_mode);
    }

    public function test_plain_poetry_editor_preserves_lines_paragraphs_and_dormant_spacing_column(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $collection = Collection::create(['title' => 'TEST ONLY', 'slug' => 'poetry-preview']);
        $body = "د زړه کرښه — «همداسې»\nدويمه کرښه\n\nدرېيمه کرښه\nڅلورمه کرښه";

        Livewire::test(CreatePoem::class)
            ->fillForm([
                'collection_id' => $collection->id,
                'title' => null,
                'body' => $body,
                'excerpt' => 'لنډ',
                'source_date_place' => 'کابل — ۱۳۶۰',
                'sort_order' => 1,
            ])
            ->assertSeeHtml('data-testid="poem-presentation-preview"')
            ->assertSeeHtml('data-testid="preview-untitled-marker"')
            ->assertSeeHtml('data-gap-em="0.5"')
            ->assertDontSee('Choose spacing manually')
            ->assertDontSee('No extra gap')
            ->assertSee('کابل — ۱۳۶۰')
            ->call('create')
            ->assertHasNoFormErrors();

        $poem = Poem::where('collection_id', $collection->id)->firstOrFail();
        $this->assertNull($poem->title);
        $this->assertSame($body, $poem->body);
        $this->assertSame(Poem::LAYOUT_SOURCE, $poem->layout_mode);
        $this->assertNull($poem->getRawOriginal('presentation_spacing'));

        $preview = view('filament.poem-presentation-preview', [
            'title' => null,
            'body' => $body,
            'layoutMode' => Poem::LAYOUT_SOURCE,
            'datePlace' => 'کابل — ۱۳۶۰',
        ])->render();
        $this->assertLessThan(strpos($preview, 'data-testid="preview-body"'), strpos($preview, 'data-testid="preview-untitled-marker"'));
        $this->assertLessThan(strpos($preview, 'data-testid="preview-date-place"'), strpos($preview, 'data-testid="preview-body"'));

        Livewire::test(EditPoem::class, ['record' => $poem->getRouteKey()])
            ->assertFormSet(['body' => $body])
            ->assertDontSee('Shift+Enter')
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame($body, $poem->fresh()->body);
    }

    public function test_owner_navigation_prominently_names_books_and_poems(): void
    {
        $this->assertSame('Collections / کتابونه', CollectionResource::getNavigationLabel());
        $this->assertSame('Poems / شعرونه', PoemResource::getNavigationLabel());
        $this->assertSame('Poetry Library / شعري کتابتون', CollectionResource::getNavigationGroup());
        $this->assertSame('Poetry Library / شعري کتابتون', PoemResource::getNavigationGroup());
    }

    public function test_collection_actions_open_filtered_poems_and_prefilled_poem_creation(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $collection = Collection::create(['title' => 'TEST ONLY', 'slug' => 'owner-navigation']);
        $manageUrl = PoemResource::getUrl('index', [
            'filters' => ['collection' => ['value' => $collection->getKey()]],
        ]);
        $createUrl = PoemResource::getUrl('create', ['collection_id' => $collection->getKey()]);

        Livewire::test(ListCollections::class)
            ->assertTableActionHasUrl('managePoems', $manageUrl, $collection)
            ->assertTableActionHasUrl('addPoem', $createUrl, $collection);

        Livewire::test(EditCollection::class, ['record' => $collection->getRouteKey()])
            ->assertActionHasUrl('managePoems', $manageUrl)
            ->assertActionHasUrl('addPoem', $createUrl);

        Livewire::withQueryParams(['collection_id' => $collection->getKey()])
            ->test(CreatePoem::class)
            ->assertFormSet(['collection_id' => $collection->getKey()]);

        $included = Poem::create([
            'collection_id' => $collection->id,
            'body' => 'د همدې کتاب شعر',
            'excerpt' => 'لنډ',
        ]);
        $otherCollection = Collection::create(['title' => 'OTHER', 'slug' => 'other-navigation']);
        $excluded = Poem::create([
            'collection_id' => $otherCollection->id,
            'body' => 'د بل کتاب شعر',
            'excerpt' => 'لنډ',
        ]);

        Livewire::withQueryParams([
            'filters' => ['collection' => ['value' => $collection->getKey()]],
        ])->test(ListPoems::class)
            ->assertCanSeeTableRecords([$included])
            ->assertCanNotSeeTableRecords([$excluded]);
    }

    public function test_poem_edit_links_back_to_its_collection(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $collection = Collection::create(['title' => 'TEST ONLY', 'slug' => 'poem-back-link']);
        $poem = Poem::create([
            'collection_id' => $collection->id,
            'body' => 'متن',
            'excerpt' => 'لنډ متن',
        ]);

        Livewire::test(EditPoem::class, ['record' => $poem->getRouteKey()])
            ->assertActionHasUrl('editCollection', CollectionResource::getUrl('edit', ['record' => $collection]));
    }

    public function test_translation_requires_truthful_attribution_but_original_does_not(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $collection = Collection::create(['title' => 'TEST ONLY', 'slug' => 'translation-validation']);

        Livewire::test(CreatePoem::class)
            ->fillForm([
                'collection_id' => $collection->id,
                'title' => null,
                'body' => "لومړۍ کرښه\nدويمه کرښه",
                'excerpt' => 'لومړۍ کرښه',
                'work_type' => 'TRANSLATION',
                'original_author' => null,
                'translator' => null,
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasFormErrors(['original_author', 'translator']);

        Livewire::test(CreatePoem::class)
            ->fillForm([
                'collection_id' => $collection->id,
                'title' => null,
                'body' => "لومړۍ کرښه\nدويمه کرښه",
                'excerpt' => 'لومړۍ کرښه',
                'work_type' => 'ORIGINAL',
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $poem = Poem::where('collection_id', $collection->id)->firstOrFail();
        $this->assertNull($poem->title);
        $this->assertNull($poem->original_author);
        $this->assertNull($poem->translator);
    }

    public function test_untitled_admin_label_uses_first_non_empty_line_without_storing_title(): void
    {
        $collection = Collection::create(['title' => 'TEST ONLY', 'slug' => 'untitled-admin']);
        $poem = Poem::create([
            'collection_id' => $collection->id,
            'title' => null,
            'body' => "\n   \nد شعر لومړۍ رښتینې کرښه\nدويمه کرښه",
            'excerpt' => 'لنډ متن',
        ]);

        $this->assertSame('د شعر لومړۍ رښتینې کرښه', $poem->admin_display_title);
        $this->assertNull($poem->title);
        $this->assertDatabaseHas('poems', ['id' => $poem->id, 'title' => null]);
    }

    public function test_filament_crud_continues_to_increment_content_version(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $collection = Collection::create(['title' => 'TEST ONLY', 'slug' => 'version-admin']);
        $version = (int) AppSetting::where('key', 'content_version')->value('value');

        Livewire::test(CreatePoem::class)
            ->fillForm([
                'collection_id' => $collection->id,
                'body' => 'متن',
                'excerpt' => 'لنډ متن',
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $poem = Poem::where('collection_id', $collection->id)->firstOrFail();
        $this->assertSame($version + 1, (int) AppSetting::where('key', 'content_version')->value('value'));

        Livewire::test(EditPoem::class, ['record' => $poem->getRouteKey()])
            ->fillForm(['sort_order' => 2])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame($version + 2, (int) AppSetting::where('key', 'content_version')->value('value'));
    }

    public function test_owner_can_deliberately_delete_poems_and_empty_collections(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $collection = Collection::create(['title' => 'TEST ONLY', 'slug' => 'delete-owner-flow']);
        $poem = Poem::create([
            'collection_id' => $collection->id,
            'body' => 'د ړنګولو ازموينه',
            'excerpt' => 'لنډ متن',
        ]);

        Livewire::test(EditPoem::class, ['record' => $poem->getRouteKey()])
            ->callAction('delete');
        $this->assertDatabaseMissing('poems', ['id' => $poem->id]);

        Livewire::test(EditCollection::class, ['record' => $collection->getRouteKey()])
            ->callAction('delete');
        $this->assertDatabaseMissing('collections', ['id' => $collection->id]);
    }
}
