<?php

namespace Tests\Feature;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Collections\Pages\CreateCollection;
use App\Filament\Resources\Collections\Pages\EditCollection;
use App\Filament\Resources\Collections\Pages\ListCollections;
use App\Filament\Resources\Collections\RelationManagers\ContentRelationManager;
use App\Filament\Resources\Poems\Pages\CreatePoem;
use App\Filament\Resources\Poems\Pages\EditPoem;
use App\Models\AppSetting;
use App\Models\Author;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Poem;
use App\Models\User;
use App\Support\BookContentOrder;
use Filament\Facades\Filament;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class BookFoundationTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        return $user;
    }

    private function book(array $data = []): Collection
    {
        return Collection::create($data + ['title' => 'A book', 'language' => 'ps', 'book_type' => 'poetry']);
    }

    private function content(Collection $book, array $data = []): Poem
    {
        return $book->poems()->create($data + ['title' => 'A title', 'body' => "Exact source\n\nSecond paragraph", 'excerpt' => 'Exact source']);
    }

    public function test_categories_admin_multiple_assignment_and_stable_category_slug(): void
    {
        $this->owner();
        Livewire::test(CreateCategory::class)->fillForm(['name' => 'History', 'sort_order' => 2, 'is_active' => true])
            ->call('create')->assertHasNoFormErrors();
        $category = Category::firstOrFail();
        $this->assertSame('history', $category->slug);
        Livewire::test(EditCategory::class, ['record' => $category->id])->fillForm(['name' => 'Historical books', 'sort_order' => 3, 'is_active' => false])
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame('history', $category->fresh()->slug);
        $this->assertFalse($category->fresh()->is_active);
        $second = Category::create(['name' => 'Literature']);
        Livewire::test(CreateCollection::class)->fillForm([
            'title' => 'Book with categories', 'categories' => [$category->id, $second->id], 'book_type' => 'prose',
        ])->call('create')->assertHasNoFormErrors();
        $book = Collection::where('title', 'Book with categories')->firstOrFail();
        $this->assertCount(2, $book->categories);
        $this->assertSame('prose', $book->book_type);
        $this->expectException(ValidationException::class);
        $category->update(['slug' => 'new-slug']);
    }

    public function test_generated_book_and_content_slugs_are_unique_editable_and_do_not_follow_title_edits(): void
    {
        $this->owner();
        $book = $this->book(['title' => 'Same Title']);
        $other = $this->book(['title' => 'Same Title']);
        $this->assertSame('same-title', $book->slug);
        $this->assertSame('same-title-2', $other->slug);
        $item = $this->content($book);
        $next = $this->content($book);
        $this->assertNotSame($item->slug, $next->slug);
        $slug = $item->slug;
        $item->update(['title' => 'Edited title']);
        $book->update(['title' => 'Edited book']);
        $this->assertSame($slug, $item->fresh()->slug);
        $this->assertSame('same-title', $book->fresh()->slug);
        Livewire::test(EditCollection::class, ['record' => $book->id])->fillForm(['slug' => 'deliberately-edited'])
            ->call('save')->assertHasNoFormErrors();
        Livewire::test(EditPoem::class, ['record' => $item->id])->fillForm(['slug' => 'edited-content'])
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame('edited-content', $item->fresh()->slug);
        $this->assertSame('deliberately-edited', $book->fresh()->slug);
        $untitled = $this->content($book, ['title' => null]);
        $this->assertStringStartsWith('content', $untitled->slug);
        $this->assertNull($untitled->title);
        $other->delete();
        $this->assertNotSame($other->slug, $this->book(['title' => 'Same Title'])->slug);
    }

    public function test_admin_labels_layouts_append_and_book_selector(): void
    {
        $this->owner();
        $author = Author::create(['name' => 'First author']);
        $book = $this->book(['book_type' => 'prose']);
        $book->credits()->create(['author_id' => $author->id, 'role' => 'author']);
        $this->assertSame('A book — First author', $book->fresh()->selector_label);
        $existing = $this->content($book, ['sort_order' => 12]);
        Livewire::withQueryParams(['collection_id' => $book->id])->test(CreatePoem::class)->fillForm([
            'collection_id' => $book->id, 'title' => 'Chapter one', 'body' => 'Unmodified chapter text', 'excerpt' => 'Excerpt',
        ])->assertSee('Chapter text')->assertDontSee('Poetry layout')
            ->call('create')->assertHasNoFormErrors();
        $chapter = Poem::where('title', 'Chapter one')->firstOrFail();
        $this->assertSame(13, $chapter->sort_order);
        $this->assertSame(14, $this->content($book, ['sort_order' => 0])->sort_order);
        $this->assertSame('Chapter', $chapter->content_label);
        $this->assertSame('Unmodified chapter text', $chapter->body);
        $poetry = $this->book(['book_type' => 'poetry']);
        Livewire::withQueryParams(['collection_id' => $poetry->id])->test(CreatePoem::class)
            ->assertSee('Poem text')->assertSee('Poetry layout');
        $existing->update(['collection_id' => $poetry->id]);
        $this->assertSame(1, $existing->fresh()->sort_order);
    }

    public function test_reorder_is_scoped_complete_unique_and_refreshes_cache_and_audit(): void
    {
        $owner = $this->owner();
        $book = $this->book();
        $a = $this->content($book);
        $b = $this->content($book);
        $deleted = $this->content($book);
        $deleted->delete();
        $other = $this->content($this->book());
        $before = (int) AppSetting::where('key', 'content_version')->value('value');
        Livewire::test(ContentRelationManager::class, ['ownerRecord' => $book, 'pageClass' => EditCollection::class])
            ->call('reorderTable', [$b->id, $a->id])->assertHasNoErrors();
        $this->assertSame([$b->id, $a->id], $book->poems()->pluck('id')->all());
        $this->assertSame(1, $other->fresh()->sort_order);
        $this->assertSame($owner->id, $book->fresh()->updated_by);
        $this->assertGreaterThan($before, (int) AppSetting::where('key', 'content_version')->value('value'));
        $deleted->restore();
        $this->assertSame(3, $book->poems()->distinct()->count('sort_order'));
        foreach ([[$a->id], [$a->id, $b->id, $other->id], [$a->id, $a->id, $b->id]] as $ids) {
            try {
                BookContentOrder::reorder($book->id, $ids);
                $this->fail('Invalid reorder accepted');
            } catch (ValidationException) {
                $this->assertSame([1, 2, 3], $book->poems()->pluck('sort_order')->all());
            }
        }
        Livewire::test(ContentRelationManager::class, ['ownerRecord' => $book, 'pageClass' => EditCollection::class])->call('reorderTable', [$other->id])->assertHasErrors(['order']);
    }

    public function test_database_rejects_duplicate_order_numbers(): void
    {
        $book = $this->book();
        $a = $this->content($book);
        $b = $this->content($book);
        $this->expectException(QueryException::class);
        DB::table('poems')->where('id', $b->id)->update(['sort_order' => $a->sort_order]);
    }

    public function test_book_and_content_bin_restore_and_permanent_delete_guard(): void
    {
        $this->owner();
        $book = $this->book(['is_active' => true]);
        $item = $this->content($book, ['is_active' => true, 'is_free_sample' => true]);
        $body = $item->body;
        Livewire::test(EditPoem::class, ['record' => $item->id])->callAction('delete');
        $this->assertSoftDeleted($item);
        $this->getJson('/api/poems/'.$item->id)->assertNotFound();
        Livewire::test(ContentRelationManager::class, ['ownerRecord' => $book, 'pageClass' => EditCollection::class])->filterTable('trashed', false)->assertCanSeeTableRecords([$item]);
        Livewire::test(EditPoem::class, ['record' => $item->id])->callAction('restore');
        $this->assertSame($body, $item->fresh()->body);
        Livewire::test(EditCollection::class, ['record' => $book->id])->callAction('delete');
        $this->assertSoftDeleted($book);
        $this->getJson('/api/collections/'.$book->slug)->assertNotFound();
        $this->getJson('/api/poems/'.$item->id)->assertNotFound();
        Livewire::test(ListCollections::class)->filterTable('trashed', false)->assertCanSeeTableRecords([$book]);
        Livewire::test(EditCollection::class, ['record' => $book->id])->assertActionHidden('forceDelete');
        try {
            $book->fresh()->forceDelete();
            $this->fail('Book with content permanently deleted');
        } catch (ValidationException) {
            $this->assertDatabaseHas('poems', ['id' => $item->id, 'body' => $body]);
        }
        Livewire::test(EditCollection::class, ['record' => $book->id])->callAction('restore');
        $this->getJson('/api/poems/'.$item->id)->assertOk();
        $this->assertSame($book->id, $book->fresh()->id);
        $this->assertSame($book->slug, $book->fresh()->slug);
    }

    public function test_book_filters_and_changes_track_actor_without_changing_identity_or_text(): void
    {
        $creator = $this->owner();
        $book = $this->book(['book_type' => 'prose', 'language' => 'fa', 'is_active' => true]);
        $other = $this->book();
        $author = Author::create(['name' => 'Named author']);
        $book->credits()->create(['author_id' => $author->id, 'role' => 'author']);
        $category = Category::create(['name' => 'Fiction']);
        $book->categories()->attach($category);
        $item = $this->content($book);
        foreach (['author' => $author->id, 'language' => 'fa', 'categories' => $category->id, 'book_type' => 'prose'] as $filter => $value) {
            Livewire::test(ListCollections::class)->filterTable($filter, $value)
                ->assertCanSeeTableRecords([$book])->assertCanNotSeeTableRecords([$other]);
        }
        $editor = $this->owner();
        $this->travel(2)->minutes();
        Livewire::test(EditCollection::class, ['record' => $book->id])->fillForm(['title' => 'New title'])
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame($book->slug, $book->fresh()->slug);
        $this->assertSame($book->id, $book->fresh()->id);
        $this->assertSame($creator->id, $book->fresh()->created_by);
        $this->assertSame($editor->id, $book->fresh()->updated_by);
        $this->assertTrue($book->fresh()->updated_at->greaterThan($book->created_at));
        $this->assertSame($item->body, $item->fresh()->body);
        $book->categories()->detach();
        $this->assertSame($editor->id, $book->fresh()->updated_by);
    }

    public function test_public_api_paginates_books_and_content_and_exposes_stable_id_and_type(): void
    {
        $book = $this->book(['book_type' => 'prose', 'is_active' => true]);
        for ($i = 0; $i < 51; $i++) {
            $this->book(['is_active' => true]);
            $this->content($book, ['title' => 'Chapter '.$i, 'is_active' => true, 'is_free_sample' => true]);
        }
        $this->getJson('/api/collections')->assertOk()->assertJsonCount(50, 'data')->assertJsonPath('meta.total', 52);
        $this->getJson('/api/collections?page=2')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/collections/'.$book->slug)->assertJsonPath('data.id', $book->id)->assertJsonPath('data.book_type', 'prose');
        $this->getJson('/api/collections/'.$book->slug.'/poems')->assertJsonCount(50, 'data')->assertJsonPath('meta.total', 51);
        $this->getJson('/api/collections/'.$book->slug.'/poems?page=2')->assertJsonCount(1, 'data');
        $item = $book->poems()->first();
        DB::table('poems')->where('id', $item->id)->update(['layout_mode' => 'COUPLET']);
        $this->getJson('/api/poems/'.$item->id)->assertJsonPath('data.layout_mode', 'SOURCE')->assertJsonPath('data.body', $item->body);
        $this->assertSame('COUPLET', $item->fresh()->layout_mode);
    }

    public function test_bin_permanent_delete_checks_binned_content_and_allows_empty_books(): void
    {
        $this->owner();
        $book = $this->book();
        $item = $this->content($book);
        $item->delete();
        $book->delete();
        Livewire::test(EditCollection::class, ['record' => $book->id])->assertActionHidden('forceDelete');
        Livewire::test(EditPoem::class, ['record' => $item->id])->callAction('forceDelete');
        $this->assertDatabaseMissing('poems', ['id' => $item->id]);
        Livewire::test(EditCollection::class, ['record' => $book->id])->assertActionVisible('forceDelete')->callAction('forceDelete');
        $this->assertDatabaseMissing('collections', ['id' => $book->id]);
    }

    public function test_credit_category_content_and_book_reorder_changes_record_the_editor(): void
    {
        $this->owner();
        $book = $this->book();
        $author = Author::create(['name' => 'An author']);
        $category = Category::create(['name' => 'A category']);
        $editor = $this->owner();
        $this->travel(1)->minutes();
        $book->credits()->create(['author_id' => $author->id, 'role' => 'author']);
        $this->assertSame($editor->id, $book->fresh()->updated_by);
        $this->assertTrue($book->fresh()->updated_at->greaterThan($book->created_at));
        $nextEditor = $this->owner();
        $book->categories()->attach($category);
        $this->assertSame($nextEditor->id, $book->fresh()->updated_by);
        $contentEditor = $this->owner();
        $this->content($book);
        $this->assertSame($contentEditor->id, $book->fresh()->updated_by);
        $reorderEditor = $this->owner();
        $other = $this->book();
        $version = (int) AppSetting::where('key', 'content_version')->value('value');
        Livewire::test(ListCollections::class)->call('reorderTable', [$other->id, $book->id])->assertHasNoErrors();
        $this->assertSame($reorderEditor->id, $book->fresh()->updated_by);
        $this->assertSame(2, $book->fresh()->sort_order);
        $this->assertGreaterThan($version, (int) AppSetting::where('key', 'content_version')->value('value'));
    }

    public function test_category_api_metadata_and_duplicate_slug_validation(): void
    {
        $this->owner();
        $book = $this->book(['is_active' => true]);
        $category = Category::create(['name' => 'History']);
        $book->categories()->attach($category);
        $this->getJson('/api/collections/'.$book->slug)
            ->assertJsonPath('data.categories.0.slug', 'history')->assertJsonPath('data.categories.0.name', 'History');
        Livewire::test(CreateCollection::class)->fillForm(['title' => 'Duplicate', 'slug' => $book->slug])
            ->call('create')->assertHasFormErrors(['slug']);
        $item = $this->content($book);
        Livewire::withQueryParams(['collection_id' => $book->id])->test(CreatePoem::class)->fillForm([
            'collection_id' => $book->id, 'body' => 'Synthetic', 'excerpt' => 'Synthetic', 'slug' => $item->slug,
        ])->call('create')->assertHasFormErrors(['slug']);
        Livewire::test(CreateCollection::class)->fillForm(['title' => 'Invalid', 'book_type' => 'invalid'])
            ->call('create')->assertHasFormErrors(['book_type']);
    }
}
