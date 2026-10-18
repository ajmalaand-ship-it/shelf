<?php

namespace App\Filament\Pages;

use App\Models\FinancialRetentionRecord;
use App\Services\Accounting\FinancialRetention as Retention;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use UnitEnum;

class FinancialRetention extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-check';
    protected static string|UnitEnum|null $navigationGroup = 'Sales and readers';
    protected static ?string $navigationLabel = 'Financial retention';
    protected string $view = 'filament.pages.financial-retention';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->is_owner;
    }

    protected function getHeaderActions(): array
    {
        $reference = fn () => TextInput::make('reference')->label('Evidence / case reference')->required()->maxLength(160)->helperText('Use a private evidence reference, without names, emails, tokens or bank details.');
        $purchase = fn () => TextInput::make('purchase_id')->label('Purchase ID')->numeric()->required()->minValue(1)->helperText('Covers this purchase’s entire financial correction chain. Use the report IDs below.');
        $action = fn ($name, $label, $fields) => Action::make($name)->label($label)->schema($fields)
            ->action(function (array $data) use ($name) {
                abort_unless(static::canAccess(), 403);
                app(Retention::class)->record(auth()->user(), $name, $data);
                Notification::make()->title('Retention evidence recorded')->success()->send();
            });
        return [
            $action('tax_return', 'Record filed return', [TextInput::make('period')->required()->maxLength(100), TextInput::make('jurisdiction')->required()->maxLength(100), DatePicker::make('filed_on')->label('Actual filing date')->required()->maxDate(today()), $reference(), Checkbox::make('amendment_reviewed')->label('Filing evidence checked; any amended-return treatment has been separately verified.')->accepted()->required()]),
            $action('mapping', 'Map purchase to returns', [$purchase(), Select::make('return_ids')->label('All relevant filed returns')->multiple()->required()->options(fn () => FinancialRetentionRecord::where('kind', 'tax_return')->get()->mapWithKeys(fn ($r) => [$r->id => '#'.$r->id.' — '.$r->evidence['jurisdiction'].' / '.$r->evidence['period'].' / '.$r->evidence['filed_on']])), $reference(), Checkbox::make('complete')->label('Evidence confirms these are all relevant returns for the whole purchase and correction chain. This replaces the prior mapping; history remains.')->accepted()->required()]),
            $action('hold', 'Record exception hold', [$purchase(), Select::make('reason')->options(['legal' => 'Applicable legal requirement', 'dispute' => 'Unresolved dispute', 'unpaid_balance' => 'Unpaid author balance'])->required(), $reference(), Textarea::make('release_condition')->required()->maxLength(500)->helperText('Document the basis and release condition. Legal requirements must be verified; no deadline is inferred.'), DatePicker::make('review_on')->required()->minDate(today())]),
            $action('release', 'Record hold resolution', [TextInput::make('hold_id')->numeric()->required()->minValue(1), $reference(), Textarea::make('resolution')->required()->maxLength(500)->helperText('Record evidence that the hold’s release condition was satisfied. Overdue reviews never release a hold automatically.')]),
        ];
    }

    protected function getViewData(): array
    {
        abort_unless(static::canAccess(), 403);
        return app(Retention::class)->report();
    }
}
