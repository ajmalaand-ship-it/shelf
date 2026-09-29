<?php

namespace Tests\Feature;

use App\Filament\Resources\Collections\Pages\EditCollection;
use App\Filament\Resources\Collections\Pages\ListCollections;
use App\Filament\Resources\Collections\RelationManagers\ContentRelationManager;
use App\Filament\Resources\Poems\Pages\EditPoem;
use App\Models\Author;
use App\Models\Collection;
use App\Models\User;
use App\Services\OwnerPreviewTokenService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class SamplesTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_and_partial_samples_preserve_exact_source_for_poetry_and_prose(): void
    {
        foreach (['poetry', 'prose'] as $type) {
            $book = Collection::create(['title' => 'Synthetic '.$type, 'book_type' => $type, 'status' => 'published']);
            $body = "  پښتو ټ ډ ړ ږ ښ ڼ ې ۍ\r\nفارسی می‌روم  \r\n\r\nEnglish é — untouched\r\nPrivate remainder";
            $item = $book->poems()->create(['body' => $body, 'excerpt' => 'UNAPPROVED', 'is_active' => true, 'sample_mode' => 'full']);
            $this->getJson('/api/poems/'.$item->id)->assertJsonPath('data.body', $body)->assertJsonPath('data.sample_mode', 'full')->assertJsonPath('data.has_more', false);
            foreach (['lines' => 2, 'paragraphs' => 1] as $unit => $count) {
                $item->update(['sample_mode' => 'partial', 'sample_unit' => $unit, 'sample_count' => $count]);
                $expected = "  پښتو ټ ډ ړ ږ ښ ڼ ې ۍ\r\nفارسی می‌روم  ";
                $this->getJson('/api/poems/'.$item->id)->assertJsonPath('data.body', $expected)->assertJsonPath('data.excerpt', $expected)
                    ->assertJsonPath('data.locked', false)->assertJsonPath('data.has_more', true)->assertJsonPath('data.sample_mode', 'partial')
                    ->assertDontSee('Private remainder')->assertDontSee('UNAPPROVED');
                $this->getJson('/api/collections/'.$book->slug.'/poems')->assertJsonPath('data.0.excerpt', $expected)->assertJsonMissingPath('data.0.body');
                $this->assertSame($body, $item->fresh()->body);
            }
            $item->update(['sample_mode' => 'none']);
            $this->getJson('/api/poems/'.$item->id)->assertJsonPath('data.body', null)->assertJsonPath('data.excerpt', null);
        }
    }

    public function test_media_requires_current_full_sample_and_owner_preview_still_gets_everything(): void
    {
        Storage::fake('audio');
        Storage::fake('artwork');
        Storage::disk('audio')->put('synthetic.mp3', 'Synthetic audio');
        Storage::disk('artwork')->put('synthetic.png', 'Synthetic image');
        $book = Collection::create(['title' => 'Synthetic', 'status' => 'published']);
        $item = $book->poems()->create(['body' => "Free line\nPrivate line", 'excerpt' => 'Private excerpt', 'is_active' => true,
            'sample_mode' => 'full', 'audio_path' => 'synthetic.mp3', 'artwork_path' => 'synthetic.png']);
        $audioUrl = $this->getJson('/api/poems/'.$item->id.'/audio')->assertOk()->json('url');
        $artUrl = $this->getJson('/api/poems/'.$item->id)->assertOk()->json('data.artwork.url');
        $this->get($audioUrl)->assertOk();
        $this->get($artUrl)->assertOk();
        foreach (['partial', 'none'] as $mode) {
            $item->update(['sample_mode' => $mode, 'sample_unit' => 'lines', 'sample_count' => 1]);
            $this->get($audioUrl)->assertNotFound();
            $this->get($artUrl)->assertNotFound();
            $this->getJson('/api/poems/'.$item->id.'/audio')->assertJsonPath('locked', true)->assertJsonMissingPath('url');
            $this->getJson('/api/poems/'.$item->id)->assertJsonPath('data.artwork.url', null)->assertJsonPath('data.audio.locked', true);
            $this->get(URL::temporarySignedRoute('poems.audio.stream', now()->addMinutes(5), ['poem' => $item, 'access' => 'paid']))->assertNotFound();
        }
        config(['poetry.owner_preview_secret' => str_repeat('p', 64)]);
        $token = app(OwnerPreviewTokenService::class)->issue(1)['token'];
        $book->changeStatus('draft');
        $item->update(['is_active' => false]);
        $data = $this->withToken($token)->getJson('/api/owner-preview/poems/'.$item->id)->assertOk()->assertJsonPath('data.body', $item->body)->json('data');
        $this->get($data['artwork']['url'])->assertOk();
        $previewAudio = $this->withToken($token)->getJson('/api/owner-preview/poems/'.$item->id.'/audio')->assertOk()->json('url');
        $this->get($previewAudio)->assertOk();
        $this->flushHeaders();
        $this->get('/media/owner-preview/audio/'.$item->id)->assertForbidden();
        $this->getJson('/api/owner-preview/poems/'.$item->id)->assertUnauthorized();
    }

    public function test_admin_sets_partial_samples_filters_and_summary_and_publish_requires_visible_sample(): void
    {
        $this->actingAs(User::factory()->state(['is_owner' => true])->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Storage::fake('covers');
        Storage::disk('covers')->put('cover.jpg', 'Synthetic');
        $book = Collection::create(['title' => 'Synthetic', 'language' => 'ps', 'cover_image' => 'cover.jpg']);
        $book->credits()->create(['role' => 'author', 'author_id' => Author::create(['name' => 'Synthetic'])->id]);
        $item = $book->poems()->create(['body' => "First line\nSecond line\nThird line", 'excerpt' => 'Private legacy excerpt', 'is_active' => true]);
        Livewire::test(ListCollections::class)->callTableAction('publish', $book)->assertHasErrors(['status']);
        Livewire::test(EditPoem::class, ['record' => $item->id])->fillForm(['sample_mode' => 'partial', 'sample_unit' => 'lines', 'sample_count' => 1])->call('save')->assertHasNoFormErrors();
        $this->assertSame('First line', $item->fresh()->sampleText());
        $this->assertSame($item->body, $item->fresh()->body);
        Livewire::test(ContentRelationManager::class, ['ownerRecord' => $book, 'pageClass' => EditCollection::class])
            ->filterTable('sample_mode', 'partial')->assertCanSeeTableRecords([$item])->assertTableColumnStateSet('sample_label', 'First 1 lines', $item);
        Livewire::test(EditCollection::class, ['record' => $book->id])->assertSee('0 full items, 1 first parts');
        $item->update(['is_active' => false]);
        Livewire::test(ListCollections::class)->callTableAction('publish', $book)->assertHasErrors(['status']);
        $item->update(['is_active' => true]);
        Livewire::test(ListCollections::class)->callTableAction('publish', $book)->assertHasNoErrors();
        foreach ([0, -1, 99] as $count) {
            Livewire::test(EditPoem::class, ['record' => $item->id])->fillForm(['sample_count' => $count])->call('save')->assertHasFormErrors(['sample_count']);
        }
        $this->assertSame(1, $item->fresh()->sample_count);
    }
}
