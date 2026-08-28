<?php

namespace App\Filament\Resources\Collections\Tables;

use App\Models\Collection;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CollectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('Order')->sortable(),
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('author')->searchable(),
                IconColumn::make('is_active')->label('Published')->boolean(),
                IconColumn::make('cover_image')->label('Cover')->boolean()
                    ->getStateUsing(fn (Collection $record): bool => filled($record->cover_image)),
                TextColumn::make('poems_count')->label('Poems'),
                TextColumn::make('free_poems_count')->label('Free'),
                TextColumn::make('published_poems_count')->label('Published poems'),
                TextColumn::make('audio_poems_count')->label('Audio'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                TernaryFilter::make('is_active')->label('Publication')
                    ->trueLabel('Published')->falseLabel('Draft')->placeholder('All publication states'),
            ])
            ->recordActions([
                Action::make('publish')->label('Publish')->color('success')
                    ->visible(fn (Collection $record): bool => ! $record->is_active)
                    ->requiresConfirmation()
                    ->modalDescription('Publish this collection? Individual poem publication states will not change.')
                    ->action(fn (Collection $record) => $record->update(['is_active' => true])),
                Action::make('unpublish')->label('Unpublish')->color('warning')
                    ->visible(fn (Collection $record): bool => $record->is_active)
                    ->requiresConfirmation()
                    ->action(fn (Collection $record) => $record->update(['is_active' => false])),
                EditAction::make(),
            ]);
    }
}
