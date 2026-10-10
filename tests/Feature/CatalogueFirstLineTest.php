<?php
namespace Tests\Feature;
use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class CatalogueFirstLineTest extends TestCase
{
    use RefreshDatabase;
    public function test_only_first_nonempty_line_of_untitled_visible_published_poems_is_catalogue_metadata(): void
    {
        $book = Collection::create(['title' => 'Synthetic catalogue', 'status' => 'published']);
        $source = "\r\n  \r\n  Synthetic first line  \r\nPAID REMAINDER";
        $poem = $book->poems()->create(['body' => $source, 'excerpt' => 'Private legacy excerpt', 'title' => null, 'is_active' => true, 'sample_mode' => 'none']);
        $this->getJson('/api/collections/'.$book->slug.'/poems')->assertOk()
            ->assertJsonPath('data.0.first_line', '  Synthetic first line  ')
            ->assertJsonPath('data.0.title', null)->assertJsonPath('data.0.locked', true)
            ->assertJsonPath('data.0.excerpt', null)->assertJsonMissingPath('data.0.body')
            ->assertDontSee('PAID REMAINDER');
        $this->getJson('/api/poems/'.$poem->id)->assertOk()->assertJsonPath('data.body', null)
            ->assertJsonPath('data.excerpt', null)->assertDontSee('Synthetic first line')->assertDontSee('PAID REMAINDER');
        $this->assertSame($source, $poem->fresh()->body);
        $poem->update(['title' => 'Actual title']);
        $this->getJson('/api/collections/'.$book->slug.'/poems')->assertJsonPath('data.0.first_line', null)
            ->assertJsonPath('data.0.title', 'Actual title');
        $poem->update(['title' => null, 'is_active' => false]);
        $this->getJson('/api/collections/'.$book->slug.'/poems')->assertJsonCount(0, 'data');
        $poem->update(['is_active' => true]);
        $book->changeStatus('draft');
        $this->getJson('/api/collections/'.$book->slug.'/poems')->assertNotFound();
    }
}
