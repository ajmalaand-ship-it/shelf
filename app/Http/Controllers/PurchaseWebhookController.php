<?php

namespace App\Http\Controllers;

use App\Services\Purchases\{PurchaseService, RevenueCatClient};
use Illuminate\Http\Request;

class PurchaseWebhookController extends Controller
{
    public function __invoke(Request $request, PurchaseService $service)
    {
        abort_unless(RevenueCatClient::configured(), 503);
        abort_unless(hash_equals((string) config('purchases.webhook_authorization'), (string) $request->header('Authorization')), 401);
        $data = $request->validate([
            'event' => ['required', 'array'], 'event.id' => ['required', 'string', 'max:255'],
            'event.type' => ['required', 'string', 'max:40'], 'event.environment' => ['required', 'string'],
            'event.store' => ['required', 'string'], 'event.app_id' => ['required', 'string'],
            'event.app_user_id' => ['required', 'regex:/^[1-9][0-9]*$/'],
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
