<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CollectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'book_type' => $this->book_type,
            'categories' => $this->categories->map(fn ($category) => ['id' => $category->id, 'name' => $category->name, 'slug' => $category->slug])->values(),
            'title' => $this->title,
            'slug' => $this->slug,
            'author' => $this->author ?? $this->credits->where('role', 'author')->map(fn ($credit) => $credit->author->name)->implode('، '),
            'language' => $this->language,
            'authors' => $this->credits->map(fn ($credit) => [
                'id' => $credit->author->id,
                'slug' => $credit->author->slug,
                'name' => $credit->author->name,
                'role' => $credit->role,
            ])->values(),
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'dedication' => $this->dedication,
            'introduction' => $this->introduction,
            'foreword_author' => $this->foreword_author,
            'foreword' => $this->foreword,
            'publication_info' => $this->publication_info,
            'cover_url' => $this->cover_image ? Storage::disk('covers')->url($this->cover_image) : null,
            'poem_count' => $this->whenCounted('poems'),
            'sort_order' => $this->sort_order,
        ];
    }
}
