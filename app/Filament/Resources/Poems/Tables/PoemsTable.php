<?php

namespace App\Filament\Resources\Poems\Tables;

use App\Models\Poem;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class PoemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('Order')->sortable(),
                TextColumn::make('admin_display_title')->label('Poem')
                    ->description(fn (Poem $record): ?string => blank($record->title) ? 'Untitled — first line for administration only' : null)
                    ->wrap()
                    ->searchable(query: fn ($query, string $search) => $query->where(function ($query) use ($search): void {
                        $query->where('title', 'like', "%{$search}%")
                            ->orWhere('body', 'like', "%{$search}%");
                    })),
                TextColumn::make('collection.title')->label('Collection')->searchable()->sortable(),
                TextColumn::make('work_type')->label('Work type')->badge()
                    ->color(fn (string $state): string => $state === 'TRANSLATION' ? 'info' : 'gray'),
                IconColumn::make('is_active')->label('Published')->boolean(),
                ToggleColumn::make('is_free_sample')->label('Free sample')
                    ->tooltip(fn (bool $state): string => $state ? 'Free sample' : 'Locked'),
                IconColumn::make('audio_path')->label('Audio')->boolean()
                    ->getStateUsing(fn (Poem $record): bool => filled($record->audio_path)),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('collection')->relationship('collection', 'title')->searchable()->preload(),
                SelectFilter::make('work_type')->label('Work type')->options([
                    'ORIGINAL' => 'Original work',
                    'TRANSLATION' => 'Translation',
                ]),
                TernaryFilter::make('is_active')->label('Publication')
                    ->trueLabel('Published')->falseLabel('Draft')->placeholder('All publication states'),
                TernaryFilter::make('is_free_sample')->label('Access')
                    ->trueLabel('Free sample')->falseLabel('Locked')->placeholder('All access states'),
                TernaryFilter::make('audio_path')->label('Audio')
                    ->trueLabel('Audio present')->falseLabel('Audio missing')->placeholder('All audio states')
                    ->nullable(),
            ])
            ->recordActions([
                Action::make('publish')->label('Publish')->color('success')
                    ->visible(fn (Poem $record): bool => ! $record->is_active)
                    ->requiresConfirmation()
                    ->modalDescription('Publish this poem? It becomes publicly eligible only when its collection is also published.')
                    ->action(fn (Poem $record) => $record->update(['is_active' => true])),
                Action::make('unpublish')->label('Unpublish')->color('warning')
                    ->visible(fn (Poem $record): bool => $record->is_active)
                    ->requiresConfirmation()
                    ->action(fn (Poem $record) => $record->update(['is_active' => false])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('setFree')->label('Set Free')
                        ->requiresConfirmation()
                        ->modalDescription('Mark the selected poems as free samples. Draft poems remain unpublished.')
                        ->action(fn (Collection $records) => $records->each->update(['is_free_sample' => true]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('setLocked')->label('Set Locked')
                        ->requiresConfirmation()
                        ->modalDescription('Mark the selected poems as locked. Publication state will not change.')
                        ->action(fn (Collection $records) => $records->each->update(['is_free_sample' => false]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('publish')->label('Publish')
                        ->requiresConfirmation()
                        ->modalDescription('Publish the selected poems? They become publicly eligible only when their collection is also published.')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('unpublish')->label('Unpublish')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
