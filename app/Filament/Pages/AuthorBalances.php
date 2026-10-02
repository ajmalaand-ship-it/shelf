<?php

namespace App\Filament\Pages;

use App\Models\SalesLedger;
use App\Services\Accounting\AccountingService;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class AuthorBalances extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Author balances';

    protected static string|UnitEnum|null $navigationGroup = 'Sales and readers';

    protected string $view = 'filament.pages.author-balances';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->is_owner;
    }

    protected function getViewData(): array
    {
        return ['balances' => app(AccountingService::class)->overview(),
            'unassigned' => SalesLedger::whereIn('status', ['sale', 'refund', 'adjustment'])->whereNull('agreement_snapshot')->count()];
    }
}
