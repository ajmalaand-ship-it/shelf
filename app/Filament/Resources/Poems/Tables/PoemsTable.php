<?php

namespace App\Filament\Resources\Poems\Tables;

use App\Filament\Resources\Poems\PoemResource;
use App\Models\Poem;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class PoemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('Order')->sortable(),
                TextColumn::make('admin_display_title')->label('Content')
                    ->description(fn (Poem $record): string => $record->content_label.(blank($record->title) ? ' — no original title; first line shown for navigation only' : ''))
                    ->wrap()
                    ->searchable(query: fn ($query, string $search) => $query->where(function ($query) use ($search): void {
                        $query->where('title', 'like', "%{$search}%")
                            ->orWhere('body', 'like', "%{$search}%");
                    })),
                TextColumn::make('work_type')->label('Work type')->badge()
                    ->color(fn (string $state): string => $state === 'TRANSLATION' ? 'info' : 'gray'),
                IconColumn::make('is_active')->label('Visible')->boolean(),
                ToggleColumn::make('is_free_sample')->label('Free sample')
                    ->tooltip(fn (bool $state): string => $state ? 'Free sample' : 'Locked'),
                IconColumn::make('audio_path')->label('Audio')->boolean()
                    ->getStateUsing(fn (Poem $record): bool => filled($record->audio_path)),
                IconColumn::make('artwork_path')->label('Artwork')->boolean()
                    ->getStateUsing(fn (Poem $record): bool => filled($record->artwork_path)),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->paginationPageOptions([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->filters([
                TrashedFilter::make()->label('Bin')->placeholder('Current content')->trueLabel('Include bin')->falseLabel('Bin only'),
                SelectFilter::make('work_type')->label('Work type')->options([
                    'ORIGINAL' => 'Original work',
                    'TRANSLATION' => 'Translation',
                ]),
                TernaryFilter::make('is_active')->label('Visibility')
                    ->trueLabel('Visible')->falseLabel('Hidden')->placeholder('All visibility states'),
                TernaryFilter::make('is_free_sample')->label('Access')
                    ->trueLabel('Free sample')->falseLabel('Locked')->placeholder('All access states'),
                TernaryFilter::make('audio_path')->label('Audio')
                    ->trueLabel('Audio present')->falseLabel('Audio missing')->placeholder('All audio states')
                    ->nullable(),
                TernaryFilter::make('artwork_path')->label('Artwork')
                    ->trueLabel('Artwork present')->falseLabel('Artwork missing')->placeholder('All artwork states')
                    ->nullable(),
            ])
            ->recordActions([
                Action::make('publish')->label('Show')->color('success')
                    ->visible(fn (Poem $record): bool => ! $record->is_active)
                    ->requiresConfirmation()
                    ->modalDescription('Show this item? It becomes publicly eligible only when its book is also published.')
                    ->action(fn (Poem $record) => $record->update(['is_active' => true])),
                Action::make('unpublish')->label('Hide')->color('warning')
                    ->visible(fn (Poem $record): bool => $record->is_active)
                    ->requiresConfirmation()
                    ->action(fn (Poem $record) => $record->update(['is_active' => false])),
                EditAction::make()->url(fn (Poem $record): string => PoemResource::getUrl('edit', ['record' => $record])),
                DeleteAction::make()->label('Move to bin'),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('setFree')->label('Set Free')
                        ->requiresConfirmation()
                        ->modalDescription('Mark the selected content as free samples. Hidden content remain hidden.')
                        ->action(fn (Collection $records) => $records->each->update(['is_free_sample' => true]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('setLocked')->label('Set Locked')
                        ->requiresConfirmation()
                        ->modalDescription('Mark the selected content as locked. Visibility will not change.')
                        ->action(fn (Collection $records) => $records->each->update(['is_free_sample' => false]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('publish')->label('Show')
                        ->requiresConfirmation()
                        ->modalDescription('Show the selected content? They become publicly eligible only when their book is also published.')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('unpublish')->label('Hide')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
