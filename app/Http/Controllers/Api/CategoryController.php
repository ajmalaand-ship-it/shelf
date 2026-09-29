<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return $this->listing(false);
    }

    public function preview(): AnonymousResourceCollection
    {
        return $this->listing(true);
    }

    private function listing(bool $preview): AnonymousResourceCollection
    {
        return CategoryResource::collection(Category::where('is_active', true)
            ->whereHas('books', fn ($books) => $books->when(! $preview, fn ($books) => $books->where('status', 'published')))
            ->orderBy('sort_order')->orderBy('id')->paginate(50));
    }
}
