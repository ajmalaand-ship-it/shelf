<?php

namespace Tests\Feature;

use App\Filament\Pages\FinancialRetention as Page;
use App\Models\{User, Purchase, Collection, Author, AuthorShareAgreement, SalesLedger, AccountingEntry, FinancialRetentionRecord};
use App\Services\Accounting\{FinancialRetention, AccountingService};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class FinancialRetentionTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        $owner = User::factory()->create(['is_owner' => true]);
        $this->actingAs($owner);
        $author = Author::create(['name' => 'Synthetic']);
        $book = Collection::create(['title' => 'Synthetic']);
        $agreement = AuthorShareAgreement::create(['collection_id' => $book->id, 'contributors' => [['author_id' => $author->id, 'percentage' => 100]], 'basis' => 'net', 'deductions' => 'Synthetic', 'sharing_terms' => 'Synthetic', 'starts_at' => now()->subDay()]);
        $purchase = Purchase::create(['collection_id' => $book->id, 'store' => 'PLAY_STORE', 'environment' => 'PRODUCTION', 'transaction_id' => 'synthetic-retention', 'product_id' => $book->product_id, 'purchased_at' => now()]);
        $sale = SalesLedger::create(['purchase_id' => $purchase->id, 'entry_key' => 'synthetic-retention', 'status' => 'sale', 'currency' => 'USD', 'amount' => '0', 'occurred_at' => now(), 'agreement_snapshot' => $agreement->toArray()]);
        return [$owner, $purchase, $sale];
    }

    private function filed(User $owner, string $date): FinancialRetentionRecord
    {
        return app(FinancialRetention::class)->record($owner, 'tax_return', ['period' => 'Synthetic', 'jurisdiction' => 'Synthetic', 'filed_on' => $date, 'reference' => 'synthetic-filing', 'amendment_reviewed' => true]);
    }

    private function map(User $owner, Purchase $purchase, array $returns): void
    {
        app(FinancialRetention::class)->record($owner, 'mapping', ['purchase_id' => $purchase->id, 'return_ids' => $returns, 'complete' => true, 'reference' => 'synthetic-map']);
    }

    private function confirm(User $owner, SalesLedger $sale, string $net = '0'): void
    {
        app(AccountingService::class)->record($owner, ['sales_ledger_id' => $sale->id, 'kind' => 'confirmation', 'request_key' => 'synthetic-confirm', 'reference' => 'synthetic-confirm', 'note' => 'Synthetic', 'currency' => 'USD', 'net_amount' => $net, 'occurred_at' => now()]);
    }

    public function test_missing_mapping_and_unknown_balances_fail_closed_without_changing_history(): void
    {
        [$owner, $p] = $this->fixture();
        $before = DB::table('purchases')->get()->toJson();
        $report = app(FinancialRetention::class)->report();
        $this->assertSame(0, $report['eligible']);
        $this->assertContains('Missing complete tax-return mapping', $report['rows'][0]['reasons']);
        $this->assertContains('Unknown settlement or author balance', $report['rows'][0]['reasons']);
        $this->assertSame($before, DB::table('purchases')->get()->toJson());
    }

    public function test_leap_boundary_multiple_returns_and_stale_correction_mapping(): void
    {
        $this->travelTo(now()->setDate(2027, 2, 28)->startOfDay());
        [$owner, $p, $sale] = $this->fixture();
        $this->confirm($owner, $sale);
        $r = $this->filed($owner, '2024-02-29');
        $this->map($owner, $p, [$r->id]);
        $this->assertSame('2027-03-01', app(FinancialRetention::class)->report()['rows'][0]['review_after']);
        $this->assertSame(0, app(FinancialRetention::class)->report()['eligible']);
        $this->travelTo(now()->addDay()->startOfDay());
        $this->assertSame(1, app(FinancialRetention::class)->report()['eligible']);
        $later = $this->filed($owner, '2025-06-30');
        $this->map($owner, $p, [$r->id, $later->id]);
        $this->assertSame(0, app(FinancialRetention::class)->report()['eligible']);
        $this->assertSame('2028-07-01', app(FinancialRetention::class)->report()['rows'][0]['review_after']);
        app(AccountingService::class)->reverse($owner, AccountingEntry::sole(), 'synthetic-reverse', 'Synthetic', 'synthetic-reverse');
        $this->assertContains('Financial correction chain changed; review and remap', app(FinancialRetention::class)->report()['rows'][0]['reasons']);
    }

    public function test_scoped_holds_stay_until_explicit_resolution_and_unpaid_is_not_presumed_settled(): void
    {
        [$owner, $p, $sale] = $this->fixture();
        $this->confirm($owner, $sale, '8');
        $r = $this->filed($owner, '2020-01-01');
        $this->map($owner, $p, [$r->id]);
        $service = app(FinancialRetention::class);
        $hold = $service->record($owner, 'hold', ['purchase_id' => $p->id, 'reason' => 'dispute', 'reference' => 'synthetic-case', 'release_condition' => 'Reviewed resolution', 'review_on' => today()->toDateString()]);
        $this->travel(2)->days();
        $this->assertStringContainsString('review overdue; still held', implode(' ', $service->report()['rows'][0]['reasons']));
        $service->record($owner, 'release', ['hold_id' => $hold->id, 'reference' => 'synthetic-resolution', 'resolution' => 'Case resolved']);
        $this->assertSame([], $service->report()['rows'][0]['hold_ids']);
        $this->assertContains('Unsettled author balance', $service->report()['rows'][0]['reasons']);
        $this->assertSame(0, $service->report()['eligible']);
        $this->assertDatabaseCount('financial_retention_records', 4);
    }

    public function test_owner_actions_render_and_record_evidence_without_disposal_controls(): void
    {
        [$owner, $p] = $this->fixture();
        Livewire::test(Page::class)->assertSee('Automatic expiry and deletion are OFF')
            ->callAction('tax_return', data: ['period' => '2020', 'jurisdiction' => 'Synthetic', 'filed_on' => '2021-01-01', 'reference' => 'Synthetic', 'amendment_reviewed' => true])->assertHasNoErrors();
        $this->artisan('shelf:check-financial-retention')->assertSuccessful();
        $id = FinancialRetentionRecord::sole()->id;
        Livewire::test(Page::class)->callAction('mapping', data: ['purchase_id' => $p->id, 'return_ids' => [$id], 'reference' => 'Synthetic', 'complete' => true])->assertHasNoErrors()->assertSee('Evidence history');
        Livewire::test(Page::class)->callAction('hold', data: ['purchase_id' => $p->id, 'reason' => 'legal', 'reference' => 'Synthetic', 'release_condition' => 'Verified requirement ends', 'review_on' => today()->toDateString()])->assertHasNoErrors();
        $hold = FinancialRetentionRecord::where('kind', 'hold')->sole();
        Livewire::test(Page::class)->callAction('release', data: ['hold_id' => $hold->id, 'reference' => 'Synthetic', 'resolution' => 'Requirement resolved'])->assertHasNoErrors();
        $this->assertDatabaseCount('purchases', 1);
        $this->assertDatabaseCount('sales_ledger', 1);
        $this->assertDatabaseCount('accounting_entries', 0);
    }

    public function test_non_owner_cannot_view_or_write_and_journal_cannot_be_rewritten(): void
    {
        $owner = User::factory()->create(['is_owner' => true]);
        $r = $this->filed($owner, '2020-01-01');
        try { $r->update(['evidence' => []]); $this->fail('History changed'); } catch (\LogicException) {}
        try { $r->delete(); $this->fail('History deleted'); } catch (\LogicException) {}
        $user = User::factory()->create(['is_owner' => false]);
        $this->actingAs($user);
        Livewire::test(Page::class)->assertForbidden();
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(FinancialRetention::class)->record($user, 'tax_return', []);
    }

    public function test_test_history_is_retained_and_rollback_refuses_evidence_loss(): void
    {
        [$owner, $p] = $this->fixture();
        // Synthetic fixture only: no live/history mutation.
        DB::table('purchases')->where('id', $p->id)->update(['environment' => 'SANDBOX']);
        $r = $this->filed($owner, '2020-01-01');
        $this->map($owner, $p, [$r->id]);
        $report = app(FinancialRetention::class)->report();
        $this->assertSame(0, $report['eligible']);
        $this->assertStringContainsString('Test / unverified history', implode(' ', $report['rows'][0]['reasons']));
        $migration = require database_path('migrations/2026_10_16_230000_create_financial_retention_records.php');
        $this->expectException(\LogicException::class);
        $migration->down();
    }

    public function test_empty_migration_is_reversible_and_missing_filing_is_rejected(): void
    {
        $migration = require database_path('migrations/2026_10_16_230000_create_financial_retention_records.php');
        $migration->down(); $migration->up();
        $owner = User::factory()->create(['is_owner' => true]);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(FinancialRetention::class)->record($owner, 'tax_return', ['period' => 'Synthetic', 'jurisdiction' => 'Synthetic', 'reference' => 'Synthetic', 'amendment_reviewed' => true]);
    }
}
