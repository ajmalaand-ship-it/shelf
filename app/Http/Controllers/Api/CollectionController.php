<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\PoemSummaryResource;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class CollectionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'author' => ['sometimes', 'string', 'max:255'],
            'language' => ['sometimes', 'string', Rule::in(array_keys(config('books.languages')))],
        ]);

        return CollectionResource::collection(Collection::query()
            ->where('is_active', true)
            ->with('credits.author')
            ->when(isset($filters['language']), fn ($query) => $query->where('language', $filters['language']))
            ->when(isset($filters['author']), fn ($query) => $query->whereHas('credits.author', function ($authors) use ($filters): void {
                $authors->where('is_active', true)->where(function ($identity) use ($filters): void {
                    $identity->where('slug', $filters['author']);
                    if (ctype_digit($filters['author'])) {
                        $identity->orWhere('id', (int) $filters['author']);
                    }
                });
            }))
            ->withCount(['poems' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('sort_order')
            ->get());
    }

    public function show(Collection $collection): CollectionResource
    {
        abort_unless($collection->is_active, 404);
        $collection->load('credits.author');
        $collection->loadCount(['poems' => fn ($query) => $query->where('is_active', true)]);

        return new CollectionResource($collection);
    }

    public function poems(Collection $collection): AnonymousResourceCollection
    {
        abort_unless($collection->is_active, 404);

        return PoemSummaryResource::collection($collection->poems()->where('is_active', true)->get());
    }
}
