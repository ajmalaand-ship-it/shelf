<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnerPreviewPoemSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'work_type' => $this->work_type,
            'original_author' => $this->original_author,
            'translator' => $this->translator,
            'excerpt' => $this->excerpt,
            'locked' => false,
            'is_free_sample' => $this->resource->hasSample(),
            'sample_mode' => $this->sample_mode,
            'has_more' => false,
            'is_active' => (bool) $this->is_active,
            'has_audio' => (bool) $this->audio_path,
            'audio_duration_seconds' => $this->audio_duration_seconds,
            'audio_cache_key' => $this->audioCacheKey(),
            'sort_order' => $this->sort_order,
        ];
    }
}
