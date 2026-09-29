<?php

namespace App\Http\Controllers\Api\OwnerPreview;

use App\Http\Controllers\Controller;
use App\Http\Resources\OwnerPreviewCollectionResource;
use App\Http\Resources\OwnerPreviewPoemSummaryResource;
use App\Models\Collection;
use App\Support\CatalogueFilters;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CollectionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return OwnerPreviewCollectionResource::collection(CatalogueFilters::apply(Collection::query(), $request)
            ->when($request->filled('language'), fn ($query) => $query->where('language', $request->string('language')->toString()))
            ->when($request->filled('author'), fn ($query) => $query->whereHas('credits.author', fn ($authors) => $authors->where('slug', $request->string('author')->toString())))
            ->with(['credits.author', 'categories'])->withCount('poems')
            ->orderBy('sort_order')->orderBy('id')
            ->paginate(50)->withQueryString());
    }

    public function show(Collection $collection): OwnerPreviewCollectionResource
    {
        $collection->load(['credits.author', 'categories'])->loadCount('poems');

        return new OwnerPreviewCollectionResource($collection);
    }

    public function poems(Collection $collection): AnonymousResourceCollection
    {
        return OwnerPreviewPoemSummaryResource::collection($collection->poems()->orderBy('id')->paginate(50)->withQueryString());
    }
}
