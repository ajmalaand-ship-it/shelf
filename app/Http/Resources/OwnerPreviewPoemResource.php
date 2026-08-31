<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnerPreviewPoemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'collection_slug' => $this->collection->slug,
            'title' => $this->title,
            'work_type' => $this->work_type,
            'original_author' => $this->original_author,
            'translator' => $this->translator,
            'source_date_place' => $this->source_date_place,
            'source_note' => $this->source_note,
            'layout_mode' => $this->layout_mode,
            'locked' => false,
            'requires_entitlement' => ! $this->is_free_sample,
            'is_free_sample' => (bool) $this->is_free_sample,
            'is_active' => (bool) $this->is_active,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'audio' => [
                'available' => (bool) $this->audio_path,
                'locked' => false,
                'duration_seconds' => $this->audio_duration_seconds,
                'metadata_url' => $this->audio_path ? route('owner-preview.poems.audio', $this->resource) : null,
                'cache_key' => $this->audioCacheKey(),
                'format' => $this->audioFormat(),
            ],
        ];
    }
}
