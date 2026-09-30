<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class OwnedPoemResource extends OwnerPreviewPoemResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $data['requires_entitlement'] = false;
        $data['is_free_sample'] = false;
        $data['artwork']['url'] = $this->artwork_path ? route('library.media', ['poem' => $this->id, 'kind' => 'artwork']) : null;
        $data['audio']['metadata_url'] = $this->audio_path ? url('/api/library/poems/'.$this->id.'/audio') : null;
        return $data;
    }
}
