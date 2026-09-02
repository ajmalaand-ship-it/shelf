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
