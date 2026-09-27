<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'name_latin' => $this->name_latin,
            'biography' => $this->biography,
            'image_url' => $this->image_path ? route('authors.image', ['author' => $this->slug]) : null,
            'books' => CollectionResource::collection($this->whenLoaded('books', fn () => $this->books->unique('id')->values())),
        ];
    }
}
