<?php

namespace App\Filament\Resources\Poems\Pages;

use App\Filament\Resources\Collections\CollectionResource;
use App\Filament\Resources\Poems\PoemResource;
use App\Support\AudioDurationProbe;
use App\Support\PoetryPresentation;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPoem extends EditRecord
{
    protected static string $resource = PoemResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $spacing = is_array($data['presentation_spacing'] ?? null) ? $data['presentation_spacing'] : null;
        $data['manual_spacing_enabled'] = $spacing !== null;
        $data['manual_spacing_controls'] = PoetryPresentation::controlRowsFromStored((string) $data['body'], $spacing);
        unset($data['presentation_spacing']);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['presentation_spacing'] = PoetryPresentation::fromControls(
            (string) $data['body'],
            (bool) ($data['manual_spacing_enabled'] ?? false),
            $data['manual_spacing_controls'] ?? null,
        );
        unset($data['manual_spacing_enabled'], $data['manual_spacing_controls']);

        return $data;
    }

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
