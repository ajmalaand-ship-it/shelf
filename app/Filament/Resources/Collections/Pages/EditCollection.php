<?php

namespace App\Filament\Resources\Collections\Pages;

use App\Filament\Resources\Collections\CollectionResource;
use App\Filament\Resources\Poems\PoemResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
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
            Action::make('managePoems')->label('Manage poems')->icon('heroicon-o-document-text')
                ->url(fn (): string => PoemResource::getUrl('index', [
                    'filters' => ['collection' => ['value' => $this->record->getKey()]],
                ])),
            Action::make('addPoem')->label('Add poem to this book')->icon('heroicon-o-document-plus')
                ->url(fn (): string => PoemResource::getUrl('create', [
                    'collection_id' => $this->record->getKey(),
                ])),
            DeleteAction::make(),
        ];
    }
}
