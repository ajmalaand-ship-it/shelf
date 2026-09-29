<?php

namespace App\Filament\Resources\Collections\Tables;

use App\Filament\Resources\Collections\CollectionResource;
use App\Models\Author;
use App\Models\Collection;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CollectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('Order')->sortable(),
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('credits.author.name')->label('Credits')->searchable(),
                TextColumn::make('language')->label('Language'),
                TextColumn::make('book_type')->label('Type'),
                TextColumn::make('categories.name')->label('Categories'),
                TextColumn::make('updated_at')->label('Last changed')->dateTime()->sortable(),
                TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn (string $state): string => Collection::STATUSES[$state])->sortable(),
                TextColumn::make('status_changed_at')->label('Status changed')->dateTime(),
                IconColumn::make('cover_image')->label('Cover')->boolean()
                    ->getStateUsing(fn (Collection $record): bool => filled($record->cover_image)),
                TextColumn::make('poems_count')->label('Content'),
                TextColumn::make('free_poems_count')->label('Free'),
                TextColumn::make('published_poems_count')->label('Published content'),
                TextColumn::make('audio_poems_count')->label('Audio'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->paginationPageOptions([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->filters([
                TrashedFilter::make()->label('Bin')->placeholder('Current books')->trueLabel('Include bin')->falseLabel('Bin only'),
                SelectFilter::make('author')->label('Author')->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => Author::where('name', 'like', "%{$search}%")->orWhere('name_latin', 'like', "%{$search}%")->limit(50)->pluck('name', 'id')->all())
                    ->getOptionLabelUsing(fn ($value): ?string => Author::find($value)?->name)
                    ->query(fn ($query, array $data) => $query->when($data['value'] ?? null, fn ($query, $id) => $query->whereHas('credits', fn ($credits) => $credits->where('role', 'author')->where('author_id', $id)))),
                SelectFilter::make('language')->options(config('books.languages')),
                SelectFilter::make('categories')->label('Category')->relationship('categories', 'name')->searchable(),
                SelectFilter::make('book_type')->label('Book type')->options(['poetry' => 'Poetry', 'prose' => 'Prose']),
                SelectFilter::make('status')->label('Status')->options(Collection::STATUSES),
            ])
            ->recordActions([
                Action::make('managePoems')->label('Manage content')->icon('heroicon-o-document-text')
                    ->url(fn (Collection $record): string => CollectionResource::getUrl('edit', ['record' => $record])),
                Action::make('publish')->label('Publish')->color('success')
                    ->visible(fn (Collection $record): bool => ! $record->isPublished())
                    ->requiresConfirmation()
                    ->modalDescription('Publish this book? Individual content publication states will not change.')
                    ->action(function (Collection $record): void {
                        $record->assertPublishable();
                        $record->changeStatus('published');
                    }),
                Action::make('unpublish')->label('Unpublish')->color('warning')
                    ->visible(fn (Collection $record): bool => $record->isPublished())
                    ->requiresConfirmation()
                    ->action(fn (Collection $record) => $record->changeStatus('draft')),
                Action::make('withdraw')->label('Withdraw')->color('danger')->requiresConfirmation()
                    ->visible(fn (Collection $record): bool => $record->status !== 'withdrawn')
                    ->action(fn (Collection $record) => $record->changeStatus('withdrawn')),
                EditAction::make(),
                RestoreAction::make(),
            ]);
    }
}
