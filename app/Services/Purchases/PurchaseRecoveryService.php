<?php
namespace App\Services\Purchases;

use App\Models\{Purchase, PurchaseRecovery, Reader, User};
use App\Services\Play\GooglePlayClient;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class PurchaseRecoveryService
{
    public function recover(User $owner, Reader $target, int $purchaseId, string $token, string $case, bool $claimantVerified): PurchaseRecovery
    {
        abort_unless($owner->is_owner, 403);
        abort_unless($claimantVerified && preg_match('/^[A-Za-z0-9_-]{3,80}$/D', $case), 422,
            'Verify the claimant and enter a support case reference without personal data.');
        abort_unless($target->email_verified_at && ! $target->buying_blocked && ($token === '' || (strlen($token) >= 10 && strlen($token) <= 4096)), 422);
        $purchase = Purchase::with('book')->findOrFail($purchaseId);
        abort_unless($purchase->reader_id && ! Reader::whereKey($purchase->reader_id)->exists(), 409, 'Original account must be deleted.');
        abort_if($purchase->entries()->whereIn('status', ['refund', 'revoke'])->exists(), 409, 'Refunded or revoked purchases cannot be recovered.');
        $play = app(GooglePlayClient::class);
        $token = $token !== '' ? $token : $play->orderPurchaseToken($purchase);
        $google = $play->productPurchase($token);
        $items = $google['productLineItem'] ?? [];
        $offer = $items[0]['productOfferDetails'] ?? [];
        $environment = array_key_exists('testPurchaseContext', $google)
            ? (($google['testPurchaseContext']['fopType'] ?? null) === 'TEST' ? 'SANDBOX' : null) : 'PRODUCTION';
        abort_unless(($google['purchaseStateContext']['purchaseState'] ?? null) === 'PURCHASED'
            && ($google['acknowledgementState'] ?? null) === 'ACKNOWLEDGEMENT_STATE_ACKNOWLEDGED'
            && count($items) === 1 && ($items[0]['productId'] ?? null) === $purchase->product_id
            && ($offer['quantity'] ?? null) === 1 && ($offer['refundableQuantity'] ?? null) === 1
            && ($offer['consumptionState'] ?? null) === 'CONSUMPTION_STATE_YET_TO_BE_CONSUMED'
            && ! isset($offer['rentOfferDetails']) && ! isset($offer['preorderOfferDetails'])
            && ($google['orderId'] ?? null) === $purchase->transaction_id
            && $environment === $purchase->environment
            && is_string($google['purchaseCompletionTime'] ?? null), 409, 'Store ownership or transaction status is unverified.');
        try { $date = CarbonImmutable::parse($google['purchaseCompletionTime']); }
        catch (\Throwable) { abort(409, 'Store purchase date is unverified.'); }
        abort_unless(abs($date->getTimestamp() - $purchase->purchased_at->getTimestamp()) <= 1, 409);
        $original = new Reader;
        $original->id = $purchase->reader_id;
        $provider = app(RevenueCatClient::class);
        abort_unless($provider->confirms($provider->subscriber($original), $purchase->book, $purchase->purchased_at, $purchase->environment), 409);
        return DB::transaction(function () use ($owner, $target, $purchase, $token, $case) {
            $purchase = Purchase::whereKey($purchase->id)->lockForUpdate()->firstOrFail();
            $target = Reader::whereKey($target->id)->lockForUpdate()->firstOrFail();
            abort_if(Reader::whereKey($purchase->reader_id)->exists()
                || DB::table('purchase_recovery_claims')->where('purchase_id', $purchase->id)->exists()
                || $purchase->entries()->whereIn('status', ['refund', 'revoke'])->exists(), 409, 'Purchase already claimed or unavailable.');
            $proof = hash('sha256', $token);
            // Off-server claim journal prevents duplicate recovery after older backups.
            $receipt = app(\App\Services\Accounts\DeletionJournal::class)->claim($purchase, $target, $owner, $case, $proof);
            $audit = PurchaseRecovery::create(['purchase_id' => $purchase->id, 'reader_id' => $target->id,
                'owner_id' => $owner->id, 'case_reference' => $case, 'proof_hash' => $proof,
                'journal_id' => $receipt['record_id'], 'created_at' => now()]);
            DB::table('purchase_recovery_claims')->insert(['purchase_id' => $purchase->id, 'reader_id' => $target->id, 'recovery_id' => $audit->id]);
            app(PurchaseService::class)->refreshAccess($target, $purchase->book);
            return $audit;
        }, 1); // Never repeat an off-server claim publication implicitly.
    }
}
