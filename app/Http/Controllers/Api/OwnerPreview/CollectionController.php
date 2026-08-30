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
            ->withCount('poems')
            ->orderBy('sort_order')
            ->get());
    }

    public function show(Collection $collection): OwnerPreviewCollectionResource
    {
        $collection->loadCount('poems');

        return new OwnerPreviewCollectionResource($collection);
    }

    public function poems(Collection $collection): AnonymousResourceCollection
    {
        return OwnerPreviewPoemSummaryResource::collection($collection->poems()->get());
    }
}
