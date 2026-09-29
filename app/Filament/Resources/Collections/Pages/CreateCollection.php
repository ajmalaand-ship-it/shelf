<?php

namespace App\Filament\Resources\Collections\Pages;

use App\Filament\Resources\Collections\CollectionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateCollection extends CreateRecord
{
    protected static string $resource = CollectionResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function afterCreate(): void
    {
        if ($this->record->isPublished()) {
            try {
                $this->record->assertPublishable();
            } catch (ValidationException $exception) {
                throw ValidationException::withMessages(collect($exception->errors())->mapWithKeys(fn ($messages, $field) => ['data.'.$field => $messages])->all());
            }
        }
    }
}
