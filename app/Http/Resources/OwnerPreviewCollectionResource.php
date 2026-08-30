<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class OwnerPreviewCollectionResource extends CollectionResource
{
    public function toArray(Request $request): array
    {
        return parent::toArray($request) + [
            'is_active' => (bool) $this->is_active,
        ];
    }
}
