<?php

namespace App\Filament\Resources\Readers;

use App\Models\Reader;
use BackedEnum;
use UnitEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReaderResource extends Resource
{
    protected static ?string $model = Reader::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static string|UnitEnum|null $navigationGroup = 'Sales and readers';
    public static function canCreate(): bool { return false; }
    public static function canDelete($record): bool { return false; }
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('id')->label('Reader ID')->sortable(),
            TextColumn::make('email')->searchable(),
            TextColumn::make('buying_blocked')->label('Buying')->badge()
                ->formatStateUsing(fn ($state) => $state ? 'Blocked' : 'Allowed')
                ->color(fn ($state) => $state ? 'danger' : 'success'),
            TextColumn::make('purchases_count')->counts('purchases')->label('Purchases'),
            TextColumn::make('refunds_count')->getStateUsing(fn ($record) => \App\Models\SalesLedger::withTestPurchases()->where('status', 'refund')->whereHas('purchase', fn ($q) => $q->where('reader_id', $record->id))->count())->label('Refunds'),
        ])->recordActions([
            Action::make('recover')->label('Recover a purchase')->requiresConfirmation()
                ->modalDescription('Verify the claimant through support. Email matching is insufficient. Only a deleted account’s active purchase can be recovered. Never enter personal data in the case reference.')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('purchase_id')->label('Original purchase ID')->integer()->required(),
                    \Filament\Forms\Components\TextInput::make('purchase_token')->label('Private purchase token (optional)')->helperText('Leave empty to verify the recorded Google order on the server.')->password()->maxLength(4096),
                    \Filament\Forms\Components\TextInput::make('case_reference')->label('Support case reference')->required()->regex('/^[A-Za-z0-9_-]{3,80}$/D'),
                    \Filament\Forms\Components\Checkbox::make('claimant_verified')->label('I verified the claimant and their original store purchase')->accepted(),
                ])->action(function ($record, array $data): void {
                    app(\App\Services\Purchases\PurchaseRecoveryService::class)->recover(auth()->user(), $record,
                        (int) $data['purchase_id'], ($data['purchase_token'] ?? ''), $data['case_reference'], (bool) ($data['claimant_verified'] ?? false));
                    \Filament\Notifications\Notification::make()->title('Purchase recovered; original financial history preserved.')->success()->send();
                }),
            Action::make('buying')->label('Buying permission')->schema([Toggle::make('buying_blocked')->label('Block new purchases')])
                ->fillForm(fn ($record) => ['buying_blocked' => $record->buying_blocked])
                ->action(function ($record, array $data): void {
                    $record->forceFill(['buying_blocked' => $data['buying_blocked'], 'buying_blocked_by' => auth()->id(), 'buying_blocked_at' => now()])->save();
                }),
            Action::make('history')->label('Purchases and refunds')->url(fn ($record) => \App\Filament\Resources\Sales\SaleResource::getUrl('index', ['reader' => $record->id])),
        ]);
    }
    public static function getPages(): array { return ['index' => Pages\ListReaders::route('/')]; }
}
