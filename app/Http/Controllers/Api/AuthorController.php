<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuthorController extends Controller
{
    private function publishedBooks(): \Closure
    {
        return fn ($query) => $query->where('collections.is_active', true)
            ->with(['credits.author', 'categories'])
            ->withCount(['poems' => fn ($poems) => $poems->where('is_active', true)])
            ->orderBy('sort_order')->orderBy('collections.id');
    }

    public function index(): AnonymousResourceCollection
    {
        return AuthorResource::collection(Author::query()->where('is_active', true)
            ->with(['books' => $this->publishedBooks()])->orderBy('name')->orderBy('id')->paginate(30));
    }

    public function show(Author $author): AuthorResource
    {
        abort_unless($author->is_active, 404);
        $author->load(['books' => $this->publishedBooks()]);

        return new AuthorResource($author);
    }

    public function image(Author $author): StreamedResponse
    {
        abort_unless($author->is_active && $author->image_path && Author::safeImagePath($author->image_path), 404);
        $disk = Storage::disk('author_images');
        $root = realpath($disk->path(''));
        $path = realpath($disk->path($author->image_path));
        abort_unless($root && $path && str_starts_with($path, $root.DIRECTORY_SEPARATOR) && is_file($path), 404);

        return $disk->response($author->image_path, null, ['X-Content-Type-Options' => 'nosniff']);
    }
}
