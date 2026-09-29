<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class OwnerPreviewPoemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $hasArtwork = (bool) $this->artwork_path && Storage::disk('artwork')->exists($this->artwork_path);

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'collection_slug' => $this->collection->slug,
            'title' => $this->title,
            'work_type' => $this->work_type,
            'original_author' => $this->original_author,
            'translator' => $this->translator,
            'source_date_place' => $this->source_date_place,
            'source_note' => $this->source_note,
            'layout_mode' => $this->effective_layout_mode,
            'locked' => false,
            'requires_entitlement' => $this->sample_mode !== 'full',
            'is_free_sample' => $this->resource->hasSample(),
            'sample_mode' => $this->sample_mode,
            'has_more' => false,
            'is_active' => (bool) $this->is_active,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'artwork' => [
                'available' => $hasArtwork,
                'locked' => false,
                'url' => $hasArtwork ? URL::temporarySignedRoute(
                    'owner-preview.poems.artwork.stream', now()->addMinutes(10), ['poem' => $this->resource],
                ) : null,
                'cache_key' => $this->artworkCacheKey(),
            ],
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
