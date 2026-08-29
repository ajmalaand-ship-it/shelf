<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PoemSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'work_type' => $this->work_type,
            'original_author' => $this->original_author,
            'translator' => $this->translator,
            'excerpt' => $this->excerpt,
            'locked' => ! $this->is_free_sample,
            'has_audio' => (bool) $this->audio_path,
            'audio_duration_seconds' => $this->audio_duration_seconds,
            'audio_cache_key' => $this->audioCacheKey(),
            'sort_order' => $this->sort_order,
        ];
    }
}
