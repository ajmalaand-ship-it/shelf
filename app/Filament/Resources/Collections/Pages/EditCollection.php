<?php

namespace App\Filament\Resources\Collections\Pages;

use App\Filament\Resources\Collections\CollectionResource;
use App\Filament\Resources\Poems\PoemResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCollection extends EditRecord
{
    protected static string $resource = CollectionResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function afterSave(): void
    {
        if ($this->record->is_active) {
            $this->record->assertPublishable();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('managePoems')->label('Manage content')->icon('heroicon-o-document-text')
                ->url(fn (): string => PoemResource::getUrl('index', [
                    'filters' => ['collection' => ['value' => $this->record->getKey()]],
                ])),
            Action::make('addPoem')->label(fn (): string => $this->record->book_type === 'prose' ? 'Add chapter to this book' : 'Add poem to this book')->icon('heroicon-o-document-plus')
                ->url(fn (): string => PoemResource::getUrl('create', [
                    'collection_id' => $this->record->getKey(),
                ])),
            DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make()->visible(fn (): bool => $this->record->trashed() && ! $this->record->poems()->withTrashed()->exists()),
        ];
    }
}
