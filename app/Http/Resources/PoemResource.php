<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class PoemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locked = ! $this->resource->hasSample();
        $mediaAllowed = $this->resource->allowsPublicMedia();
        $hasArtwork = (bool) $this->artwork_path && Storage::disk('artwork')->exists($this->artwork_path);

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'collection_slug' => $this->collection->slug,
            'title' => $this->title,
            'work_type' => $this->work_type,
            'original_author' => $this->original_author,
            'translator' => $this->translator,
            'source_date_place' => $mediaAllowed ? $this->source_date_place : null,
            'source_note' => $mediaAllowed ? $this->source_note : null,
            'layout_mode' => $this->effective_layout_mode,
            'locked' => $locked,
            'is_free_sample' => ! $locked,
            'sample_mode' => $this->sample_mode,
            'has_more' => $this->sample_mode !== 'full',
            'requires_entitlement' => $this->sample_mode !== 'full',
            'excerpt' => $this->resource->sampleExcerpt(),
            'body' => $this->resource->sampleText(),
            'artwork' => [
                'available' => $hasArtwork,
                'locked' => $hasArtwork && ! $mediaAllowed,
                'url' => $hasArtwork && $mediaAllowed ? URL::temporarySignedRoute(
                    'poems.artwork.stream', now()->addMinutes(10),
                    ['poem' => $this->resource],
                ) : null,
                'cache_key' => $this->artworkCacheKey(),
            ],
            'audio' => [
                'available' => (bool) $this->audio_path,
                'locked' => ! $mediaAllowed,
                'duration_seconds' => $this->audio_duration_seconds,
                'metadata_url' => $this->audio_path ? route('poems.audio', $this->resource) : null,
                'cache_key' => $this->audioCacheKey(),
                'format' => $this->audioFormat(),
            ],
        ];
    }
}
