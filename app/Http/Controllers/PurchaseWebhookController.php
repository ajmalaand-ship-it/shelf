<?php

namespace App\Http\Controllers;

use App\Services\Purchases\{PurchaseService, RevenueCatClient};
use Illuminate\Http\Request;

class PurchaseWebhookController extends Controller
{
    public function __invoke(Request $request, PurchaseService $service)
    {
        abort_if(\App\Support\Staging::active(), 403, 'Test copy uses REST confirmation; webhooks are disabled.');
        $apple = $request->input('event.store') === 'APP_STORE';
        $appleReady = $apple && (\App\Services\Purchases\ApplePurchaseProof::configured()
            || \App\Services\Purchases\ApplePurchaseProof::customersConfigured()
            || (\App\Services\Purchases\ApplePurchaseProof::credentialsConfigured()
                && \App\Models\Purchase::where('store', 'APP_STORE')->where('transaction_id', $request->input('event.transaction_id'))
                    ->get()->contains(fn ($p) => \App\Services\Purchases\ApplePurchaseProof::customerPurchase($p))));
        abort_unless($apple ? $appleReady : RevenueCatClient::configured(), 503);
        $authorization = config($apple ? 'purchases.apple_webhook_authorization' : 'purchases.webhook_authorization');
        abort_unless(hash_equals((string) $authorization, (string) $request->header('Authorization')), 401);
        $data = $request->validate([
            'event' => ['required', 'array'], 'event.id' => ['required', 'string', 'max:255'],
            'event.type' => ['required', 'string', 'max:40'], 'event.environment' => ['required', 'string'],
            'event.store' => ['required', 'string'], 'event.app_id' => ['required', 'string'],
            'event.app_user_id' => ['required', $apple ? 'regex:/^(?:[1-9][0-9]*|shelf_apple_[a-f0-9]{64})$/D' : 'regex:/^[1-9][0-9]*$/'],
            'event.product_id' => ['required', 'string', 'max:255'], 'event.transaction_id' => ['required', 'string', 'max:255'],
            'event.purchased_at_ms' => ['required', 'integer', 'min:1'], 'event.event_timestamp_ms' => ['required', 'integer', 'min:1'],
            'event.currency' => ['sometimes', 'nullable', 'regex:/^[A-Z]{3}$/'],
            'event.price_in_purchased_currency' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:999999999'],
            'event.cancel_reason' => ['sometimes', 'nullable', 'string', 'max:100'],
        ]);
        $service->receive($data['event']);
        return response()->json(['received' => true]);
    }
}
