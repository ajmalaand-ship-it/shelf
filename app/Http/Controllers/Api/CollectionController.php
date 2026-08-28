<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\PoemSummaryResource;
use App\Models\Collection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CollectionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return CollectionResource::collection(Collection::query()
            ->where('is_active', true)
            ->withCount(['poems' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('sort_order')
            ->get());
    }

    public function show(Collection $collection): CollectionResource
    {
        abort_unless($collection->is_active, 404);
        $collection->loadCount(['poems' => fn ($query) => $query->where('is_active', true)]);

        return new CollectionResource($collection);
    }

    public function poems(Collection $collection): AnonymousResourceCollection
    {
        abort_unless($collection->is_active, 404);

        return PoemSummaryResource::collection($collection->poems()->where('is_active', true)->get());
    }
}
