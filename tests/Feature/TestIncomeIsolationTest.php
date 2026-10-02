<?php

namespace Tests\Feature;

use App\Models\{Collection, Purchase, SalesLedger, User};
use App\Filament\Resources\Sales\SaleResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestIncomeIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_totals_author_amounts_and_exports_exclude_test_and_unknown_modes_but_history_retains_them(): void
    {
        $book = Collection::create(['title' => 'Income isolation']);
        foreach (['SANDBOX', 'PRODUCTION', 'UNKNOWN'] as $i => $mode) {
            $purchase = Purchase::create(['collection_id' => $book->id, 'store' => 'PLAY_STORE',
                'environment' => $mode, 'transaction_id' => 'synthetic-'.$i,
                'product_id' => $book->product_id, 'purchased_at' => now()]);
            SalesLedger::create(['purchase_id' => $purchase->id, 'entry_key' => 'sale:'.$purchase->id,
                'status' => 'sale', 'currency' => 'USD', 'amount' => 10,
                'confirmed_owed' => 7, 'payments_made' => 6,
                'estimated_earnings' => [['author_id' => 1, 'amount' => 8]], 'occurred_at' => now()]);
        }
        $this->assertEquals(10, SalesLedger::sum('amount'));
        $this->assertEquals(7, SalesLedger::sum('confirmed_owed'));
        $this->assertEquals(6, SalesLedger::sum('payments_made'));
        $export = SalesLedger::with('purchase')->get()->toArray();
        $this->assertCount(1, $export);
        $this->assertSame('PRODUCTION', $export[0]['purchase']['environment']);
        $this->assertEquals(8, $export[0]['estimated_earnings'][0]['amount']);
        $this->assertCount(3, SalesLedger::withTestPurchases()->get());
        $test = SalesLedger::withTestPurchases()->whereHas('purchase', fn ($q) => $q->where('environment', 'SANDBOX'))->firstOrFail();
        $this->assertSame('Test', $test->income_mode);
        $this->assertSame('Real', SalesLedger::firstOrFail()->income_mode);
        // Old test estimates remain immutable but must not appear as income in admin.
        $this->actingAs(User::factory()->create(['is_owner' => true]));
        $this->get(SaleResource::getUrl())->assertOk()->assertSee('Test — no income')->assertSee('Test');
        $this->assertDatabaseCount('sales_ledger', 3);
    }
}
