<?php

namespace App\Http\Controllers\Api\OwnerPreview;

use App\Http\Controllers\Controller;
use App\Http\Resources\OwnerPreviewPoemResource;
use App\Models\Poem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class PoemController extends Controller
{
    public function show(Poem $poem): OwnerPreviewPoemResource
    {
        return new OwnerPreviewPoemResource($poem);
    }

    public function audio(Poem $poem): JsonResponse
    {
        abort_unless($poem->audio_path && Storage::disk('audio')->exists($poem->audio_path), 404);

        return response()->json([
            'locked' => false,
            'url' => URL::temporarySignedRoute(
                'owner-preview.poems.audio.stream',
                now()->addMinutes(10),
                ['poem' => $poem],
            ),
            'duration_seconds' => $poem->audio_duration_seconds,
            'cache_key' => $poem->audioCacheKey(),
            'format' => $poem->audioFormat(),
        ]);
    }
}
