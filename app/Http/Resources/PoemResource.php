<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PoemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locked = ! $this->is_free_sample;

        return [
            'id' => $this->id,
            'collection_slug' => $this->collection->slug,
            'title' => $this->title,
            'locked' => $locked,
            'excerpt' => $this->excerpt,
            'body' => $locked ? null : $this->body,
            'audio' => [
                'available' => (bool) $this->audio_path,
                'locked' => $locked,
                'duration_seconds' => $this->audio_duration_seconds,
                'metadata_url' => $this->audio_path ? route('poems.audio', $this->resource) : null,
            ],
        ];
    }
}
