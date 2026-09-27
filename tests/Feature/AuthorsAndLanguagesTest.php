<?php

namespace Tests\Feature;

use App\Filament\Resources\Authors\Pages\CreateAuthor;
use App\Filament\Resources\Authors\Pages\EditAuthor;
use App\Filament\Resources\Authors\Pages\ListAuthors;
use App\Filament\Resources\Collections\Pages\CreateCollection;
use App\Filament\Resources\Collections\Pages\EditCollection;
use App\Filament\Resources\Collections\Pages\ListCollections;
use App\Models\AppSetting;
use App\Models\Author;
use App\Models\Collection;
use App\Models\User;
use App\Services\OwnerPreviewTokenService;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class AuthorsAndLanguagesTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    private function book(string $slug, bool $published = true, string $language = 'ps'): Collection
    {
        return Collection::create(['title' => $slug, 'slug' => $slug, 'author' => 'Legacy text', 'is_active' => $published, 'language' => $language]);
    }

    public function test_author_slug_is_generated_once_and_images_cannot_escape_private_storage(): void
    {
        $author = Author::create(['name' => 'First name']);
        $slug = $author->slug;
        $author->update(['name' => 'Updated name']);
        $this->assertSame($slug, $author->fresh()->slug);
        foreach ([['slug' => 'replacement'], ['image_path' => '../secret.jpg'], ['image_path' => '/secret.jpg'], ['image_path' => 'https://example.test/image.png']] as $change) {
            try {
                $author->fresh()->update($change);
                $this->fail('Invalid author update accepted.');
            } catch (ValidationException) {
                $this->assertSame($slug, $author->fresh()->slug);
                $this->assertNull($author->fresh()->image_path);
            }
        }
    }

    public function test_public_api_returns_ordered_credits_filters_and_only_published_author_books(): void
    {
        $author = Author::create(['name' => 'Author', 'slug' => 'primary', 'is_active' => true]);
        $translator = Author::create(['name' => 'Translator', 'slug' => 'translator', 'is_active' => true]);
        $inactive = Author::create(['name' => 'Inactive', 'slug' => 'inactive', 'is_active' => false]);
        $book = $this->book('public-book');
        $book->credits()->create(['author_id' => $translator->id, 'role' => 'translator', 'position' => 2]);
        $book->credits()->create(['author_id' => $author->id, 'role' => 'author', 'position' => 1]);
        $book->credits()->create(['author_id' => $author->id, 'role' => 'editor', 'position' => 3]);
        $draft = $this->book('hidden-book', false);
        $draft->credits()->create(['author_id' => $author->id, 'role' => 'author']);
        $farsi = $this->book('farsi-book', true, 'fa');
        $farsi->credits()->create(['author_id' => $translator->id, 'role' => 'author']);

        $this->getJson('/api/collections/public-book')->assertOk()
            ->assertJsonPath('data.author', 'Legacy text')->assertJsonPath('data.language', 'ps')
            ->assertJsonPath('data.authors.0.slug', 'primary')->assertJsonPath('data.authors.1.role', 'translator');
        $this->getJson('/api/collections?author=primary&language=ps')->assertJsonCount(1, 'data');
        $this->getJson('/api/collections?author='.$author->id)->assertJsonCount(1, 'data');
        $this->getJson('/api/collections?language=fa')->assertJsonPath('data.0.slug', 'farsi-book')->assertJsonCount(1, 'data');
        $this->getJson('/api/collections?language=xx')->assertUnprocessable();
        $this->getJson('/api/collections?author=missing')->assertJsonCount(0, 'data');
        $this->getJson('/api/authors/primary')->assertOk()->assertJsonCount(1, 'data.books')
            ->assertJsonPath('data.books.0.slug', 'public-book')->assertDontSee('hidden-book');
        $this->getJson('/api/authors')->assertOk()->assertJsonCount(2, 'data')->assertDontSee('hidden-book')->assertDontSee('inactive');
        $this->getJson('/api/authors/inactive')->assertNotFound();
        $this->getJson('/api/authors/missing')->assertNotFound();
    }

    public function test_new_books_supply_legacy_author_text_from_credits_without_changing_column(): void
    {
        $book = $this->book('new-book');
        $book->update(['author' => null]);
        $author = Author::create(['name' => 'Credited author']);
        $book->credits()->create(['author_id' => $author->id, 'role' => 'author']);
        $this->getJson('/api/collections/new-book')->assertJsonPath('data.author', 'Credited author');
        $this->assertNull($book->fresh()->author);
    }

    public function test_author_images_are_private_and_inactive_authors_are_not_served(): void
    {
        Storage::fake('author_images');
        Storage::disk('author_images')->put('portrait.png', 'synthetic-image');
        $author = Author::create(['name' => 'Person', 'image_path' => 'portrait.png', 'is_active' => true]);
        $this->getJson('/api/authors/'.$author->slug)->assertOk()->assertJsonMissingPath('data.image_path')
            ->assertJsonPath('data.image_url', route('authors.image', ['author' => $author->slug]));
        $this->get('/api/authors/'.$author->slug.'/image')->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $author->update(['is_active' => false]);
        $this->get('/api/authors/'.$author->slug.'/image')->assertNotFound();
    }

    public function test_admin_author_create_search_edit_image_and_activation(): void
    {
        $this->get('/admin/authors')->assertRedirect('/admin/login');
        $this->owner();
        Storage::fake('author_images');
        Livewire::test(CreateAuthor::class)->fillForm([
            'name' => 'Person', 'name_latin' => 'Latin Person', 'biography' => 'Biography',
            'image_path' => [UploadedFile::fake()->image('portrait.png')], 'is_active' => true,
        ])->call('create')->assertHasNoFormErrors();
        $author = Author::where('name', 'Person')->firstOrFail();
        Storage::disk('author_images')->assertExists($author->image_path);
        Livewire::test(ListAuthors::class)->searchTable('Latin')->assertCanSeeTableRecords([$author]);
        Livewire::test(EditAuthor::class, ['record' => $author->id])->fillForm([
            'name' => 'Updated person', 'is_active' => false,
        ])->call('save')->assertHasNoFormErrors();
        $this->assertFalse($author->fresh()->is_active);
        $this->assertSame($author->slug, $author->fresh()->slug);
        Livewire::test(ListAuthors::class)->call('updateTableColumnState', 'is_active', (string) $author->id, true);
        $this->assertTrue($author->fresh()->is_active);
        Livewire::test(CreateAuthor::class)->fillForm([
            'name' => 'Invalid image', 'image_path' => [UploadedFile::fake()->create('script.php', 5, 'application/x-php')],
        ])->call('create')->assertHasFormErrors(['image_path']);
    }

    public function test_book_form_creates_and_reorders_credits_and_preserves_legacy_author(): void
    {
        $this->owner();
        $author = Author::create(['name' => 'Author']);
        $translator = Author::create(['name' => 'Translator']);
        Livewire::test(CreateCollection::class)->fillForm([
            'title' => 'Book', 'slug' => 'book', 'language' => 'ps', 'sort_order' => 0, 'is_active' => true,
            'credits' => [
                ['author_id' => $author->id, 'role' => 'author'],
                ['author_id' => $translator->id, 'role' => 'translator'],
            ],
        ])->call('create')->assertHasNoFormErrors();
        $book = Collection::where('slug', 'book')->firstOrFail();
        $this->assertSame([$author->id, $translator->id], $book->credits->pluck('author_id')->all());
        $book->update(['author' => 'Preserve old text']);
        $credits = $book->credits;
        Livewire::test(EditCollection::class, ['record' => $book->id])
            ->callAction(TestAction::make('reorder')->schemaComponent('credits')->arguments([
                'items' => ['record-'.$credits[1]->id, 'record-'.$credits[0]->id],
            ]))
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame([$translator->id, $author->id], $book->fresh()->credits->pluck('author_id')->all());
        $this->assertSame('Preserve old text', $book->fresh()->author);
    }

    public function test_publishing_requires_language_and_author_in_form_and_table_action(): void
    {
        $this->owner();
        $person = Author::create(['name' => 'Person']);
        Livewire::test(CreateCollection::class)->fillForm([
            'title' => 'Invalid', 'slug' => 'invalid', 'sort_order' => 0, 'is_active' => true,
            'credits' => [['author_id' => $person->id, 'role' => 'translator']],
        ])->call('create')->assertHasFormErrors(['language', 'credits']);
        $this->assertDatabaseMissing('collections', ['slug' => 'invalid']);
        Livewire::test(CreateCollection::class)->fillForm([
            'title' => 'Draft', 'slug' => 'draft', 'sort_order' => 0, 'is_active' => false,
        ])->call('create')->assertHasNoFormErrors();
        $draft = Collection::where('slug', 'draft')->firstOrFail();
        Livewire::test(ListCollections::class)->callTableAction('publish', $draft)->assertHasErrors(['language', 'credits']);
        $this->assertFalse($draft->fresh()->is_active);
        $draft->update(['language' => 'fa']);
        $draft->credits()->create(['author_id' => $person->id, 'role' => 'author']);
        Livewire::test(ListCollections::class)->callTableAction('publish', $draft)->assertHasNoErrors();
        $this->assertTrue($draft->fresh()->is_active);
        Livewire::test(EditCollection::class, ['record' => $draft->id])->fillForm(['credits' => []])
            ->call('save')->assertHasFormErrors(['credits']);
        $this->assertCount(1, $draft->fresh()->credits);
    }

    public function test_form_validates_role_duplicates_and_configurable_languages(): void
    {
        $this->owner();
        $author = Author::create(['name' => 'Person']);
        $base = ['title' => 'Test', 'slug' => 'test', 'sort_order' => 0, 'is_active' => false];
        Livewire::test(CreateCollection::class)->fillForm($base + ['language' => 'xx'])
            ->call('create')->assertHasFormErrors(['language']);
        Livewire::test(CreateCollection::class)->fillForm($base + ['credits' => [
            ['author_id' => $author->id, 'role' => 'author'], ['author_id' => $author->id, 'role' => 'author'],
        ]])->call('create')->assertHasFormErrors(['credits']);
        Livewire::test(CreateCollection::class)->fillForm($base + ['credits' => [
            ['author_id' => $author->id, 'role' => 'invalid'],
        ]])->call('create')->assertHasFormErrors();
        config(['books.languages.en' => 'English']);
        Livewire::test(CreateCollection::class)->fillForm($base + ['language' => 'en'])
            ->call('create')->assertHasNoFormErrors();
    }

    public function test_owner_preview_book_responses_include_credits_and_language(): void
    {
        config(['poetry.owner_preview_secret' => str_repeat('t', 64)]);
        $token = app(OwnerPreviewTokenService::class)->issue(1)['token'];
        $book = $this->book('preview', false, 'fa');
        $author = Author::create(['name' => 'Preview author']);
        $book->credits()->create(['author_id' => $author->id, 'role' => 'author']);
        foreach (['/api/owner-preview/collections', '/api/owner-preview/collections/preview'] as $url) {
            $prefix = str_ends_with($url, '/preview') ? 'data' : 'data.0';
            $this->withToken($token)->getJson($url)->assertOk()
                ->assertJsonPath($prefix.'.language', 'fa')
                ->assertJsonPath($prefix.'.authors.0.name', 'Preview author');
        }
    }

    public function test_credit_and_author_edits_invalidate_catalogue_cache(): void
    {
        $book = $this->book('cache');
        $author = Author::create(['name' => 'Person']);
        $before = (int) AppSetting::where('key', 'content_version')->value('value');
        $credit = $book->credits()->create(['author_id' => $author->id, 'role' => 'author']);
        $credit->update(['position' => 3]);
        $author->update(['name' => 'Changed']);
        $credit->delete();
        $this->assertSame($before + 4, (int) AppSetting::where('key', 'content_version')->value('value'));
    }
}
