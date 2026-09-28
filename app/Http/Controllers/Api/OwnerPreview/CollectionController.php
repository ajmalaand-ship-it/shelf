<?php

namespace App\Http\Controllers\Api\OwnerPreview;

use App\Http\Controllers\Controller;
use App\Http\Resources\OwnerPreviewCollectionResource;
use App\Http\Resources\OwnerPreviewPoemSummaryResource;
use App\Models\Collection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CollectionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return OwnerPreviewCollectionResource::collection(Collection::query()
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
