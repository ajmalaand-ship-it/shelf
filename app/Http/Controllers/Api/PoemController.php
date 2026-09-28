<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PoemResource;
use App\Models\Poem;
use App\Services\RevenueCatEntitlementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class PoemController extends Controller
{
    public function show(Poem $poem): PoemResource
    {
        abort_unless($poem->is_active && $poem->collection?->is_active, 404);

        return new PoemResource($poem);
    }

    public function audio(Request $request, Poem $poem, RevenueCatEntitlementService $entitlements): JsonResponse
    {
        abort_unless($poem->is_active && $poem->collection?->is_active, 404);

        $paidAccess = ! $poem->is_free_sample && $entitlements->requestIsEntitled($request);
        if (! $poem->is_free_sample && ! $paidAccess) {
            return response()->json(['locked' => true, 'excerpt' => $poem->excerpt]);
        }

        abort_unless($poem->audio_path && Storage::disk('audio')->exists($poem->audio_path), 404);

        return response()->json([
            'locked' => false,
            'url' => URL::temporarySignedRoute(
                'poems.audio.stream',
                now()->addMinutes(10),
                ['poem' => $poem, 'access' => $paidAccess ? 'paid' : 'free'],
            ),
            'duration_seconds' => $poem->audio_duration_seconds,
            'cache_key' => $poem->audioCacheKey(),
            'format' => $poem->audioFormat(),
        ]);
    }

    public function stream(Request $request, Poem $poem)
    {
        $signedPaidAccess = $request->query('access') === 'paid';
        abort_unless(
            $poem->is_active
            && $poem->collection?->is_active
            && ($poem->is_free_sample || $signedPaidAccess),
            404,
        );
        abort_unless($poem->audio_path && Storage::disk('audio')->exists($poem->audio_path), 404);

        return Storage::disk('audio')->response($poem->audio_path);
    }

    public function streamOwnerPreview(Poem $poem)
    {
        abort_unless($poem->collection, 404);
        abort_unless($poem->audio_path && Storage::disk('audio')->exists($poem->audio_path), 404);

        return Storage::disk('audio')->response($poem->audio_path, null, [
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function streamArtwork(Request $request, Poem $poem)
    {
        $signedPaidAccess = $request->query('access') === 'paid';
        abort_unless(
            $poem->is_active
            && $poem->collection?->is_active
            && ($poem->is_free_sample || $signedPaidAccess),
            404,
        );
        abort_unless($poem->artwork_path && Storage::disk('artwork')->exists($poem->artwork_path), 404);

        return Storage::disk('artwork')->response($poem->artwork_path, null, [
            'Cache-Control' => 'private, max-age=600',
        ]);
    }

    public function streamOwnerPreviewArtwork(Poem $poem)
    {
        abort_unless($poem->collection, 404);
        abort_unless($poem->artwork_path && Storage::disk('artwork')->exists($poem->artwork_path), 404);

        return Storage::disk('artwork')->response($poem->artwork_path, null, [
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
