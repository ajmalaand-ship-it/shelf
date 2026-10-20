<?php

namespace Tests\Feature;

use App\Filament\Resources\Collections\CollectionResource;
use App\Filament\Resources\Collections\Pages\PreviewCollection;
use App\Models\{Collection, Poem, User};
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminBookPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_preview_preserves_drafts_and_exact_sample_boundaries(): void
    {
        $book = Collection::create(['title' => 'Private draft', 'description' => '<script>unsafe</script>']);
        $item = Poem::create(['collection_id' => $book->id, 'title' => 'Partial sample', 'body' => "Sample line\nPaid line", 'excerpt' => '', 'is_active' => true, 'sample_mode' => 'partial', 'sample_unit' => 'lines', 'sample_count' => 1]);
        Poem::create(['collection_id' => $book->id, 'title' => 'Hidden source', 'body' => 'Hidden exact text', 'excerpt' => '', 'is_active' => false, 'sample_mode' => 'full']);
        $url = CollectionResource::getUrl('preview', ['record' => $book]);
        $this->get($url)->assertRedirect();
        $this->actingAs(User::factory()->create(['is_owner' => false]))->get($url)->assertForbidden();
        $this->actingAs(User::factory()->create(['is_owner' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $response = $this->get($url)->assertOk();
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $response->assertSee('Paid line')->assertSee('Hidden exact text')->assertSee('&lt;script&gt;unsafe&lt;/script&gt;', false)->assertDontSee('<script>unsafe</script>', false);
        Livewire::withQueryParams(['sample' => 1])->test(PreviewCollection::class, ['record' => $book->id])
            ->assertSee('Sample line')->assertDontSee('Paid line')->assertDontSee('Hidden exact text');
        $this->assertSame('draft', $book->fresh()->status);
        $this->assertSame("Sample line\nPaid line", $item->fresh()->body);
        $this->getJson('/api/collections/'.$book->slug)->assertNotFound();
    }

    public function test_preview_is_paginated_and_empty_sample_is_explained(): void
    {
        $this->actingAs(User::factory()->create(['is_owner' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $book = Collection::create(['title' => 'Draft']);
        for ($i = 1; $i <= 11; $i++) {
            Poem::create(['collection_id' => $book->id, 'title' => 'Item '.$i, 'body' => 'Exact source '.$i, 'excerpt' => '']);
        }
        Livewire::test(PreviewCollection::class, ['record' => $book->id])->assertSee('Exact source 10')->assertDontSee('Exact source 11')
            ->call('setPage', 2)->assertSee('Exact source 11')->assertDontSee('Exact source 10');
        Livewire::withQueryParams(['sample' => 1])->test(PreviewCollection::class, ['record' => $book->id])
            ->assertSee('No visible sample selected');
    }
}
