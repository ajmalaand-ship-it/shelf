<?php

namespace App\Filament\Resources\Readers;

use App\Models\Reader;
use BackedEnum;
use UnitEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
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
            IconColumn::make('buying_blocked')->boolean()->label('Buying blocked'),
            TextColumn::make('purchases_count')->counts('purchases')->label('Purchases'),
            TextColumn::make('refunds_count')->getStateUsing(fn ($record) => \App\Models\SalesLedger::where('status', 'refund')->whereHas('purchase', fn ($q) => $q->where('reader_id', $record->id))->count())->label('Refunds'),
        ])->recordActions([
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
