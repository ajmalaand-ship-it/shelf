<?php

namespace App\Filament\Resources\AppSettings\Tables;

use App\Filament\Resources\AppSettings\AppSettingResource;
use App\Models\AppSetting;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AppSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')->searchable()->sortable(),
                TextColumn::make('value')->limit(60),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->recordUrl(fn (AppSetting $record): string => AppSettingResource::getUrl('edit', ['record' => $record]))
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
