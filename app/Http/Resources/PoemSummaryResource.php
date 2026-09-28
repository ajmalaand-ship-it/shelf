<?php

namespace App\Http\Resources;

use App\Services\RevenueCatEntitlementService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PoemSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locked = ! $this->is_free_sample
            && ! app(RevenueCatEntitlementService::class)->requestIsEntitled($request);

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'work_type' => $this->work_type,
            'original_author' => $this->original_author,
            'translator' => $this->translator,
            'excerpt' => $this->excerpt,
            'locked' => $locked,
            'has_audio' => (bool) $this->audio_path,
            'audio_duration_seconds' => $this->audio_duration_seconds,
            'audio_cache_key' => $this->audioCacheKey(),
            'sort_order' => $this->sort_order,
        ];
    }
}
