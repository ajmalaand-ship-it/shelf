<?php

namespace App\Filament\Resources\Collections\Pages;

use App\Filament\Resources\Collections\CollectionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditCollection extends EditRecord
{
    protected static string $resource = CollectionResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function afterSave(): void
    {
        if ($this->record->isPublished()) {
            try {
                $this->record->assertPublishable();
            } catch (ValidationException $exception) {
                throw ValidationException::withMessages(collect($exception->errors())->mapWithKeys(fn ($messages, $field) => ['data.'.$field => $messages])->all());
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make()->visible(fn (): bool => $this->record->trashed() && ! $this->record->poems()->withTrashed()->exists()),
        ];
    }
}
