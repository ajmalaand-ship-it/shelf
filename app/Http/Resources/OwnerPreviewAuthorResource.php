<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class OwnerPreviewAuthorResource extends AuthorResource
{
    public function toArray(Request $request): array
    {
        // Books are fetched through the paginated, protected catalogue endpoint.
        $data = parent::toArray($request);
        $data['image_url'] = $this->is_active ? $data['image_url'] : null;

        return $data;
    }
}
