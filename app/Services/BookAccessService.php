<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\Reader;
use Illuminate\Support\Facades\DB;

class BookAccessService
{
    private function storeAccessAllowed(Reader $reader, Collection $book): bool
    {
        $purchases = \App\Models\Purchase::where('reader_id', $reader->id)->where('collection_id', $book->id)->get();
        if (! $purchases->contains(fn ($p) => $p->store === 'APP_STORE')) { return true; }
        // Gate closure never mutates immutable history or the shared entitlement row.
        return $purchases->contains(fn ($p) => ($p->store !== 'APP_STORE'
                || \App\Services\Purchases\ApplePurchaseProof::configured())
            && \App\Services\Purchases\AppleSandboxPurchases::active($p))
            || \App\Models\Purchase::whereIn('id', DB::table('purchase_recovery_claims')->where('reader_id', $reader->id)->select('purchase_id'))
                ->where('collection_id', $book->id)->where('store', 'PLAY_STORE')->get()
                ->contains(fn ($p) => \App\Services\Purchases\AppleSandboxPurchases::active($p));
    }

    public function ownsBook(?Reader $reader, Collection $book): bool
    {
        return $reader !== null && (DB::table('book_entitlements')->where('reader_id', $reader->id)
            ->where('collection_id', $book->id)->where('active', true)->exists() && $this->storeAccessAllowed($reader, $book)
            || DB::table('reviewer_book_grants')->where('reader_id', $reader->id)
                ->where('collection_id', $book->id)->whereNull('revoked_at')->exists());
    }
}
