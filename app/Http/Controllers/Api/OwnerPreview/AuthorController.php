<?php

namespace App\Http\Controllers\Api\OwnerPreview;

use App\Http\Controllers\Controller;
use App\Http\Resources\OwnerPreviewAuthorResource;
use App\Models\Author;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuthorController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return OwnerPreviewAuthorResource::collection(Author::orderBy('name')->orderBy('id')->paginate(30));
    }

    public function show(Author $author): OwnerPreviewAuthorResource
    {
        return new OwnerPreviewAuthorResource($author);
    }
}
