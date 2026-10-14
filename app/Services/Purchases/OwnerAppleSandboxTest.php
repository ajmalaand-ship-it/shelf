<?php

namespace App\Services\Purchases;

use App\Models\{Collection, Purchase, Reader};
use Illuminate\Support\Facades\DB;

/** One owner-only Apple sandbox purchase without removing existing Play access. */
class OwnerAppleSandboxTest
{
    public const CONSENT = PurchaseService::CONSENT."\nOwner Apple sandbox test. Existing Google Play ownership stays unchanged.";

    public static function allowed(Reader $reader, Collection $book): bool
    {
        return config('purchases.apple_owned_book3_test_enabled') === true
            && ApplePurchaseProof::configured() && config('purchases.production_enabled') === false
            && ApplePurchaseProof::owner($reader) && $book->id === 3
            && $book->product_id === 'shelf_book_3' && $book->isPublished()
            && $book->price_usd > 0
            && DB::table('book_entitlements')->where('reader_id', $reader->id)
                ->where('collection_id', 3)->where('active', true)->exists()
            && Purchase::where('reader_id', $reader->id)->where('collection_id', 3)
                ->where('store', 'PLAY_STORE')->whereHas('entries', fn ($q) => $q->where('status', 'sale'))
                ->whereDoesntHave('entries', fn ($q) => $q->whereIn('status', ['refund', 'revoke']))->exists()
            && ! Purchase::where('reader_id', $reader->id)->where('collection_id', 3)
                ->where('store', 'APP_STORE')->exists();
    }
}
