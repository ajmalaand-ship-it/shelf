<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Category;
use App\Models\Collection;
use App\Services\OwnerPreviewTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookstoreTest extends TestCase
{
    use RefreshDatabase;

    private function book(string $status = 'published', string $title = 'Mountains'): Collection
    {
        return Collection::create(['title' => $title, 'subtitle' => 'River subtitle', 'language' => 'ps', 'book_type' => 'prose', 'status' => $status]);
    }

    public function test_searches_title_subtitle_and_credit_names_but_never_content_or_unpublished_books(): void
    {
        $author = Author::create(['name' => 'لیکوال', 'name_latin' => 'Writer Latin']);
        $book = $this->book();
        $book->credits()->create(['author_id' => $author->id, 'role' => 'author']);
        $book->poems()->create(['body' => 'Secret body needle', 'excerpt' => '', 'sample_mode' => 'none', 'is_active' => true]);
        foreach (['draft', 'ready', 'withdrawn'] as $status) {
            $hidden = $this->book($status);
            $hidden->credits()->create(['author_id' => $author->id, 'role' => 'author']);
        }
        $deleted = $this->book();
        $deleted->delete();
        foreach (['Mountains', 'River', 'لیکوال', 'Writer Latin'] as $term) {
            $this->getJson('/api/collections?q='.urlencode($term))->assertOk()
                ->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $book->id);
        }
        $this->getJson('/api/collections?q=needle')->assertJsonCount(0, 'data');
        $this->getJson('/api/poems/'.$book->poems()->first()->id)->assertOk()->assertJsonPath('data.body', null);
        $this->getJson('/api/collections?q[]=x')->assertUnprocessable();
        $this->getJson('/api/collections?book_type=invalid')->assertUnprocessable();
    }

    public function test_filters_combine_and_literal_wildcards_do_not_match_everything(): void
    {
        $category = Category::create(['name' => 'Stories']);
        $book = $this->book('published', '100%_exact');
        $book->categories()->attach($category);
        $this->book();
        $this->getJson('/api/collections?'.http_build_query(['q' => '%_', 'category' => $category->slug, 'language' => 'ps', 'book_type' => 'prose']))
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $book->id);
        $this->getJson('/api/collections?category='.$category->slug.'&book_type=poetry')->assertJsonCount(0, 'data');
        $this->getJson('/api/collections?category='.$category->slug.'&language=fa')->assertJsonCount(0, 'data');
    }

    public function test_category_discovery_hides_empty_inactive_and_unpublished_only_categories(): void
    {
        $public = Category::create(['name' => 'Public', 'sort_order' => 2]);
        $private = Category::create(['name' => 'Private']);
        $inactive = Category::create(['name' => 'Inactive', 'is_active' => false]);
        Category::create(['name' => 'Empty']);
        $this->book()->categories()->attach([$public->id, $inactive->id]);
        $draft = $this->book('draft');
        $draft->categories()->attach($private);
        $this->getJson('/api/categories')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.slug', $public->slug);
        $this->getJson('/api/owner-preview/categories')->assertUnauthorized();
        config(['poetry.owner_preview_secret' => str_repeat('p', 64)]);
        $token = app(OwnerPreviewTokenService::class)->issue(1)['token'];
        $this->withToken($token)->getJson('/api/owner-preview/categories')->assertOk()->assertJsonCount(2, 'data');
        $this->withToken($token)->getJson('/api/owner-preview/collections?q=Mountains&category='.$private->slug)
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.status', 'draft');
    }

    public function test_preview_author_routes_require_token_and_public_author_books_remain_published_only(): void
    {
        $author = Author::create(['name' => 'Writer', 'biography' => 'Biography']);
        $hiddenAuthor = Author::create(['name' => 'Private writer', 'is_active' => false, 'image_path' => 'private.jpg']);
        foreach (['draft', 'ready', 'published'] as $status) {
            $book = $this->book($status);
            $book->credits()->create(['author_id' => $author->id, 'role' => 'author']);
        }
        $this->getJson('/api/authors/'.$author->slug)->assertJsonCount(1, 'data.books');
        $this->getJson('/api/authors/'.$hiddenAuthor->slug)->assertNotFound();
        foreach (['authors', 'authors/'.$author->slug] as $path) {
            $this->getJson('/api/owner-preview/'.$path)->assertUnauthorized();
        }
        config(['poetry.owner_preview_secret' => str_repeat('p', 64)]);
        $token = app(OwnerPreviewTokenService::class)->issue(1)['token'];
        $this->withToken($token)->getJson('/api/owner-preview/authors')->assertOk()->assertJsonCount(2, 'data');
        $this->withToken($token)->getJson('/api/owner-preview/authors/'.$author->slug)->assertJsonPath('data.biography', 'Biography');
        $this->withToken($token)->getJson('/api/owner-preview/authors/'.$hiddenAuthor->slug)->assertJsonPath('data.image_url', null);
        $this->withToken($token)->getJson('/api/owner-preview/collections?author='.$author->slug)->assertJsonCount(3, 'data');
    }

    public function test_book_search_and_categories_have_stable_pagination(): void
    {
        for ($i = 0; $i < 51; $i++) {
            $category = Category::create(['name' => 'Category '.$i]);
            $this->book()->categories()->attach($category);
        }
        $response = $this->getJson('/api/collections?q=Mountains&book_type=prose')->assertJsonCount(50, 'data');
        $this->assertStringContainsString('q=Mountains', $response->json('links.next'));
        $this->getJson($response->json('links.next'))->assertJsonCount(1, 'data');
        $categories = $this->getJson('/api/categories')->assertJsonCount(50, 'data');
        $this->getJson($categories->json('links.next'))->assertJsonCount(1, 'data');
    }
}
