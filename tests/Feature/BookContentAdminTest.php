<?php

namespace Tests\Feature;

use App\Filament\Resources\Collections\CollectionResource;
use App\Filament\Resources\Collections\Pages\EditCollection;
use App\Filament\Resources\Collections\RelationManagers\ContentRelationManager;
use App\Filament\Resources\Poems\Pages\CreatePoem;
use App\Filament\Resources\Poems\Pages\EditPoem;
use App\Filament\Resources\Poems\PoemResource;
use App\Models\Collection;
use App\Models\Poem;
use App\Models\User;
use Filament\Actions\Exceptions\ActionNotResolvableException;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BookContentAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->state(['is_owner' => true])->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    private function manager(Collection $book)
    {
        return Livewire::test(ContentRelationManager::class, ['ownerRecord' => $book, 'pageClass' => EditCollection::class]);
    }

    public function test_global_list_is_unavailable_and_creation_requires_a_book(): void
    {
        $this->assertFalse(PoemResource::shouldRegisterNavigation());
        $this->assertFalse(PoemResource::canGloballySearch());
        $this->assertArrayNotHasKey('index', PoemResource::getPages());
        $this->get('/admin/poems')->assertNotFound();
        $this->get(PoemResource::getUrl('create'))->assertNotFound();
        $this->get(PoemResource::getUrl('create', ['collection_id' => 999999]))->assertNotFound();
        $book = Collection::create(['title' => 'Synthetic book']);
        $this->get(CollectionResource::getUrl('edit', ['record' => $book]))->assertOk()->assertSee('Content');
        $this->assertContains(ContentRelationManager::class, CollectionResource::getRelations());
    }

    public function test_book_scoping_survives_search_filters_bin_and_forged_actions(): void
    {
        $book = Collection::create(['title' => 'Synthetic book']);
        $own = Poem::create(['body' => 'Synthetic source', 'excerpt' => 'Synthetic excerpt', 'collection_id' => $book->id, 'title' => 'Shared search']);
        $foreign = Poem::create(['body' => 'Synthetic source', 'excerpt' => 'Synthetic excerpt', 'collection_id' => Collection::create(['title' => 'Other book'])->id, 'title' => 'Shared search', 'is_free_sample' => false]);
        $this->manager($book)->searchTable('Shared search')->assertCanSeeTableRecords([$own])->assertCanNotSeeTableRecords([$foreign]);
        try {
            $this->manager($book)->callTableAction('delete', $foreign);
            $this->fail('Foreign delete action was resolved.');
        } catch (ActionNotResolvableException) {
            // The relation query must refuse another book’s record.
        }
        $this->assertNotSoftDeleted($foreign);
        $this->manager($book)->callTableBulkAction('setFree', [$foreign]);
        $this->assertFalse($foreign->fresh()->is_free_sample);
        $this->manager($book)->callTableAction('delete', $own);
        $this->assertSoftDeleted($own);
        $foreign->delete();
        $this->manager($book)->filterTable('trashed', false)->assertCanSeeTableRecords([$own])->assertCanNotSeeTableRecords([$foreign]);
        try {
            $this->manager($book)->filterTable('trashed', false)->callTableAction('restore', $foreign);
            $this->fail('Foreign restore action was resolved.');
        } catch (ActionNotResolvableException) {
            // The bin uses the same book scope.
        }
        $this->assertSoftDeleted($foreign);
        $this->manager($book)->filterTable('trashed', false)->callTableAction('restore', $own);
        $this->assertNotSoftDeleted($own);
        $this->manager($book)->call('reorderTable', [$foreign->id])->assertHasErrors(['order']);
    }

    public function test_create_and_edit_stay_in_the_book_and_return_to_it(): void
    {
        foreach (['poetry' => 'Poem', 'prose' => 'Chapter'] as $type => $label) {
            $book = Collection::create(['title' => 'Synthetic book', 'book_type' => $type]);
            $other = Collection::create(['title' => 'Synthetic book']);
            $old = Poem::create(['body' => 'Synthetic source', 'excerpt' => 'Synthetic excerpt', 'collection_id' => $book->id, 'sort_order' => 10]);
            $old->delete();
            $url = CollectionResource::getUrl('edit', ['record' => $book]);
            $this->manager($book)->assertSee('Add '.$label)
                ->assertTableActionHasUrl('create', PoemResource::getUrl('create', ['collection_id' => $book->id]));
            $create = Livewire::withQueryParams(['collection_id' => $book->id])->test(CreatePoem::class)
                ->assertFormFieldDisabled('collection_id');
            $this->assertSame($url, $create->instance()->bookUrl());
            $this->assertSame($url, (new \ReflectionMethod(CreatePoem::class, 'getCancelFormAction'))->invoke($create->instance())->getUrl());
            $create->fillForm(['collection_id' => $other->id, 'title' => 'New '.$label, 'body' => 'Exact source', 'excerpt' => 'Exact', 'source_note' => 'Source note'])
                ->call('create')->assertHasNoFormErrors()->assertRedirect($url);
            $item = Poem::where('title', 'New '.$label)->firstOrFail();
            $this->assertSame($book->id, $item->collection_id);
            $this->assertSame(11, $item->sort_order);
            $this->manager($book)->assertTableActionHasUrl('edit', PoemResource::getUrl('edit', ['record' => $item]), $item);
            Livewire::test(EditPoem::class, ['record' => $item->id])->assertSee($label.' text')
                ->assertFormFieldDisabled('collection_id')->assertFormFieldExists('audio_path')->assertFormFieldExists('artwork_path')
                ->fillForm(['collection_id' => $other->id, 'source_date_place' => 'Exact date/place'])
                ->call('save')->assertHasNoFormErrors()->assertRedirect($url);
            $this->assertSame($book->id, $item->fresh()->collection_id);
            $this->assertSame('Exact source', $item->fresh()->body);
            $this->assertSame('Source note', $item->fresh()->source_note);
            $this->assertSame('Exact date/place', $item->fresh()->source_date_place);
            Livewire::test(EditPoem::class, ['record' => $item->id])->callAction('delete')->assertRedirect($url);
        }
    }
}
