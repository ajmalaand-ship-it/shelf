<?php

namespace App\Filament\Resources\Poems\Pages;

use App\Filament\Resources\Poems\PoemResource;
use App\Support\AudioDurationProbe;
use App\Support\PoetryPresentation;
use Filament\Resources\Pages\CreateRecord;

class CreatePoem extends CreateRecord
{
    protected static string $resource = PoemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['presentation_spacing'] = PoetryPresentation::fromControls(
            (string) $data['body'],
            (bool) ($data['manual_spacing_enabled'] ?? false),
            $data['manual_spacing_controls'] ?? null,
        );
        unset($data['manual_spacing_enabled'], $data['manual_spacing_controls']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->updateQuietly([
            'audio_duration_seconds' => app(AudioDurationProbe::class)->seconds($this->record->audio_path),
        ]);
    }
}
