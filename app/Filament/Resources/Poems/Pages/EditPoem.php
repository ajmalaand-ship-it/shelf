<?php

namespace App\Filament\Resources\Poems\Pages;

use App\Filament\Resources\Collections\CollectionResource;
use App\Filament\Resources\Poems\PoemResource;
use App\Support\AudioDurationProbe;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPoem extends EditRecord
{
    use ReturnsToBook;

    public function contentBookId(): int
    {
        return (int) $this->record->collection_id;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['collection_id'], $data['sort_order']);

        return $data;
    }

    protected static string $resource = PoemResource::class;

    public function getTitle(): string
    {
        return 'Edit '.$this->record->content_label;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('editCollection')->label('Edit book')->icon('heroicon-o-book-open')
                ->url(fn (): string => CollectionResource::getUrl('edit', [
                    'record' => $this->record->collection_id,
                ])),
            DeleteAction::make()->successRedirectUrl($this->bookUrl()),
            RestoreAction::make(),
            ForceDeleteAction::make()->successRedirectUrl($this->bookUrl()),
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
