<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PoemResource;
use App\Models\Poem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class PoemController extends Controller
{
    public function show(Poem $poem): PoemResource
    {
        abort_unless($poem->is_active && $poem->collection?->is_active, 404);

        return new PoemResource($poem);
    }

    public function audio(Poem $poem): JsonResponse
    {
        abort_unless($poem->is_active && $poem->collection?->is_active, 404);

        if (! $poem->is_free_sample) {
            return response()->json(['locked' => true, 'excerpt' => $poem->excerpt]);
        }

        abort_unless($poem->audio_path && Storage::disk('audio')->exists($poem->audio_path), 404);

        return response()->json([
            'locked' => false,
            'url' => URL::temporarySignedRoute('poems.audio.stream', now()->addMinutes(10), ['poem' => $poem]),
            'duration_seconds' => $poem->audio_duration_seconds,
        ]);
    }

    public function stream(Poem $poem)
    {
        abort_unless($poem->is_active && $poem->collection?->is_active && $poem->is_free_sample, 404);
        abort_unless($poem->audio_path && Storage::disk('audio')->exists($poem->audio_path), 404);

        return Storage::disk('audio')->response($poem->audio_path);
    }
}
