<?php

namespace App\Filament\Resources\Poems\Pages;

use App\Filament\Resources\Collections\CollectionResource;
use App\Filament\Resources\Poems\PoemResource;
use App\Support\AudioDurationProbe;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPoem extends EditRecord
{
    protected static string $resource = PoemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('editCollection')->label('Edit collection / book')->icon('heroicon-o-book-open')
                ->url(fn (): string => CollectionResource::getUrl('edit', [
                    'record' => $this->record->collection_id,
                ])),
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        if ($this->record->wasChanged('audio_path')) {
            $this->record->updateQuietly([
                'audio_duration_seconds' => app(AudioDurationProbe::class)->seconds($this->record->audio_path),
            ]);
        }
    }
}
