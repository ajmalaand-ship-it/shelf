<?php

namespace App\Http\Controllers;

use App\Http\Resources\{CollectionResource, OwnedPoemResource, PoemSummaryResource};
use App\Models\{Collection, Poem};
use App\Services\BookAccessService;
use App\Services\Purchases\{PurchaseService, RevenueCatClient};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};

class LibraryController extends Controller
{
    private function allow(Request $r, Collection $book): void
    {
        abort_unless(($book->isPublished() || $book->allowsPriorPurchaserAccess())
            && app(BookAccessService::class)->ownsBook($r->user(), $book), 404);
    }

    public function config()
    {
        return response()->json(['enabled' => RevenueCatClient::configured(), 'test_mode' => ! RevenueCatClient::productionEnabled(),
            'production_checkout_enabled' => RevenueCatClient::configured() && RevenueCatClient::productionEnabled(),
            'sandbox_checkout_enabled' => RevenueCatClient::configured(),
            'public_sdk_key' => RevenueCatClient::configured() ? config('purchases.public_sdk_key') : null,
            'consent' => PurchaseService::CONSENT, 'offline_days' => 30,
            'identity_prefix' => \App\Support\Staging::active() ? 'staging_' : '']);
    }

    public function index(Request $r, PurchaseService $service)
    {
        $service->reconcile($r->user());
        $books = Collection::whereIn('status', ['published', 'withdrawn'])->whereIn('id', DB::table('book_entitlements')->where('reader_id', $r->user()->id)->where('active', true)->select('collection_id'))
            ->with(['credits.author', 'categories'])->withCount('poems')->get();
        $data = $books->map(fn ($book) => $this->bookData($r, $book))->all();
        return response()->json(['books' => $data,
            'checked_at' => now()->toIso8601String(), 'offline_valid_until' => now()->addDays(30)->toIso8601String(),
            'buying_blocked' => (bool) $r->user()->buying_blocked]);
    }

    public function consent(Request $r, Collection $collection)
    {
        abort_unless(RevenueCatClient::configured(), 503, 'Purchases are not configured yet.');
        $r->validate(['agree' => ['required', 'accepted'], 'checkout_mode' => ['sometimes', 'required', 'in:sandbox,production']]);
        // Legacy installed owner-test clients omit the mode. This authorizes checkout,
        // never labels a receipt: environment still comes only from provider evidence.
        $mode = $r->input('checkout_mode', RevenueCatClient::productionEnabled() ? 'production' : 'sandbox');
        abort_if($mode === 'production' && ! RevenueCatClient::productionEnabled(), 503, 'Real purchases are not enabled.');
        DB::transaction(function () use ($r, $collection, $mode): void {
            $reader = \App\Models\Reader::whereKey($r->user()->id)->lockForUpdate()->firstOrFail();
            $book = Collection::whereKey($collection->id)->lockForUpdate()->firstOrFail();
            abort_if($reader->buying_blocked || ! $reader->email_verified_at, 403, 'This account cannot buy books.');
            $email = strtolower($reader->email);
            $testAllowed = in_array($reader->id, config('purchases.test_reader_ids'), true);
            foreach (\App\Models\User::where('is_owner', true)->pluck('email') as $ownerEmail) {
                [$local, $domain] = explode('@', strtolower($ownerEmail), 2);
                $testAllowed = $testAllowed || $email === strtolower($ownerEmail)
                    || (str_starts_with($email, $local.'+') && str_ends_with($email, '@'.$domain));
            }
            abort_unless($mode === 'sandbox' ? $testAllowed : RevenueCatClient::productionEnabled(), 403, 'Test purchases are limited to the owner account until staging and release approval.');
            abort_unless($book->isPublished() && $book->price_usd > 0 && $book->product_id === 'shelf_book_'.$book->id, 409, 'This book is not for sale.');
            abort_if(app(BookAccessService::class)->ownsBook($reader, $book), 409, 'This account already owns the book.');
            DB::table('purchase_consents')->insert(['reader_id' => $reader->id, 'collection_id' => $book->id,
                'wording' => PurchaseService::CONSENT, 'created_at' => now()]);
        });
        return response()->json(['accepted' => true, 'checkout_mode' => $mode, 'product_id' => $collection->product_id, 'app_user_id' => \App\Support\Staging::identity($r->user()->id)]);
    }

    public function book(Request $r, Collection $collection)
    {
        $this->allow($r, $collection);
        $collection->load(['credits.author', 'categories']);
        return response()->json(['data' => $this->bookData($r, $collection)]);
    }

    private function bookData(Request $r, Collection $book): array
    {
        $data = (new CollectionResource($book))->resolve($r);
        $data['cover_url'] = $book->cover_image ? url('/api/library/books/'.$book->id.'/cover') : null;
        return $data;
    }

    public function cover(Request $r, Collection $collection)
    {
        $this->allow($r, $collection);
        abort_unless($collection->cover_image && Collection::safeCoverPath($collection->cover_image)
            && Storage::disk('covers')->exists($collection->cover_image), 404);
        return Storage::disk('covers')->response($collection->cover_image, null, ['Cache-Control' => 'private, no-store']);
    }

    public function content(Request $r, Collection $collection)
    {
        $this->allow($r, $collection);
        $items = $collection->poems()->where('is_active', true)->get();
        $summaries = $items->map(function ($item) use ($r): array {
            $data = (new PoemSummaryResource($item))->resolve($r);
            return array_merge($data, ['locked' => false, 'has_more' => false, 'is_free_sample' => false,
                'excerpt' => mb_substr($item->body, 0, 240)]);
        });
        return response()->json(['data' => $summaries]);
    }

    public function poem(Request $r, Poem $poem)
    {
        abort_unless($poem->is_active && $poem->collection, 404);
        $this->allow($r, $poem->collection);
        return new OwnedPoemResource($poem);
    }

    public function audio(Request $r, Poem $poem)
    {
        abort_unless($poem->is_active && $poem->collection, 404);
        $this->allow($r, $poem->collection);
        abort_unless($poem->audio_path, 404);
        return response()->json(['locked' => false, 'url' => route('library.media', ['poem' => $poem->id, 'kind' => 'audio']),
            'duration_seconds' => $poem->audio_duration_seconds, 'cache_key' => $poem->audioCacheKey(), 'format' => $poem->audioFormat()]);
    }

    public function media(Request $r, Poem $poem, string $kind)
    {
        abort_unless($poem->is_active && $poem->collection, 404);
        $this->allow($r, $poem->collection);
        abort_unless(in_array($kind, ['audio', 'artwork'], true), 404);
        $path = $poem->{$kind.'_path'};
        abort_unless($path && Storage::disk($kind)->exists($path), 404);
        return Storage::disk($kind)->response($path, null, ['Cache-Control' => 'private, no-store']);
    }
}
