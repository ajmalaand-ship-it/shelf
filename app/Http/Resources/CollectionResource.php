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
            'title' => $this->title,
            'slug' => $this->slug,
            'author' => $this->author,
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
