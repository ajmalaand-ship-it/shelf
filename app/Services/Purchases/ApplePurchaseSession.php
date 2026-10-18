<?php

namespace App\Services\Purchases;

use App\Models\{Collection, Purchase, Reader};
use Illuminate\Support\Facades\DB;

/** Unpredictable, authenticated consent identity; never replaces the Play identity. */
class ApplePurchaseSession
{
    public static function issue(Reader $reader, Collection $book, int $consent): object
    {
        abort_unless(ApplePurchaseProof::configured() && ApplePurchaseProof::owner($reader)
            && config('purchases.production_enabled') === false && $book->id === 3, 403);
        abort_if(Purchase::where('reader_id', $reader->id)->where('store', 'APP_STORE')->exists()
            || DB::table('apple_purchase_sessions')->where('reader_id', $reader->id)->whereNotNull('transaction_id')->exists(), 409);
        $ms = now()->getTimestampMs();
        DB::table('apple_purchase_sessions')->where('reader_id', $reader->id)
            ->whereNull('transaction_id')->whereNull('revoked_at_ms')->update(['revoked_at_ms' => $ms]);
        $id = DB::table('apple_purchase_sessions')->insertGetId([
            'reader_id' => $reader->id, 'collection_id' => 3, 'consent_id' => $consent,
            'provider_identity' => 'shelf_apple_'.bin2hex(random_bytes(32)),
            'issued_at_ms' => $ms, 'expires_at_ms' => $ms + 15 * 60 * 1000,
        ]);
        return DB::table('apple_purchase_sessions')->find($id);
    }

    public static function forIdentity(string $identity): object
    {
        abort_unless(preg_match('/^shelf_apple_[a-f0-9]{64}$/D', $identity), 409, 'Apple purchase session missing.');
        $session = DB::table('apple_purchase_sessions')->where('provider_identity', $identity)->first();
        abort_unless($session && (int) $session->collection_id === 3, 409, 'Apple purchase session missing.');
        return $session;
    }

    public static function check(object $session, int $reader, string $transaction, int $at): void
    {
        abort_unless((int) $session->reader_id === $reader && (int) $session->collection_id === 3
            && $session->revoked_at_ms === null
            && $at >= (int) $session->issued_at_ms && $at <= (int) $session->expires_at_ms
            && (($session->transaction_id === $transaction && (int) $session->store_purchased_at_ms === $at)
                || ($session->transaction_id === null && now()->getTimestampMs() <= (int) $session->expires_at_ms)),
            409, 'Apple purchase session is expired, replayed or does not match.');
        if ($session->transaction_id !== null) { return; }
        abort_unless(DB::table('purchase_consents')->where('id', $session->consent_id)
            ->where('reader_id', $reader)->where('collection_id', 3)->where('store', 'APP_STORE')
            ->whereIn('wording', [PurchaseService::CONSENT, OwnerAppleSandboxTest::CONSENT])->exists(), 409);
    }

    public static function identity(int $reader, string $transaction, int $at): string
    {
        $session = DB::table('apple_purchase_sessions')->where('reader_id', $reader)
            ->where('collection_id', 3)->whereNull('revoked_at_ms')
            ->where(fn ($q) => $q->where('transaction_id', $transaction)
                ->orWhere(fn ($q) => $q->whereNull('transaction_id')->where('issued_at_ms', '<=', $at)
                    ->where('expires_at_ms', '>=', $at)))->orderByDesc('id')->first();
        if ($session) {
            self::check($session, $reader, $transaction, $at);
            return $session->provider_identity;
        }
        // Existing pre-session history may only be independently reconciled/corrected.
        abort_unless(Purchase::where('reader_id', $reader)->where('store', 'APP_STORE')
            ->where('transaction_id', $transaction)->where('store_purchased_at_ms', $at)->exists(), 409, 'Unbound Apple purchase.');
        return (string) $reader;
    }
}
