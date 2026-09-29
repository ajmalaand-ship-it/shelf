<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CoverController extends Controller
{
    public function show(Request $request, Collection $collection)
    {
        $public = $collection->isPublished();
        abort_unless($public || $request->user()?->is_owner, 404);

        return $this->file($collection, $public);
    }

    public function preview(Collection $collection)
    {
        return $this->file($collection, false);
    }

    private function file(Collection $book, bool $public)
    {
        abort_unless($book->cover_image && Collection::safeCoverPath($book->cover_image)
            && Storage::disk('covers')->exists($book->cover_image), 404);

        $root = realpath(Storage::disk('covers')->path(''));
        $path = realpath(Storage::disk('covers')->path($book->cover_image));
        abort_unless($root && $path && str_starts_with($path, $root.DIRECTORY_SEPARATOR) && is_file($path), 404);

        // Revalidate every reuse, so withdrawal also revokes previously cached covers.
        $response = Storage::disk('covers')->response($book->cover_image, null, [
            'Cache-Control' => $public ? 'public, max-age=0, must-revalidate' : 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
        if ($public) {
            $response->setEtag(hash_file('sha256', $path));
            $response->isNotModified(request());
        }

        return $response;
    }
}
