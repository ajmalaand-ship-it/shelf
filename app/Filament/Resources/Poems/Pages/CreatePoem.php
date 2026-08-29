<?php

namespace App\Filament\Resources\Poems\Pages;

use App\Filament\Resources\Poems\PoemResource;
use App\Support\AudioDurationProbe;
use Filament\Resources\Pages\CreateRecord;

class CreatePoem extends CreateRecord
{
    protected static string $resource = PoemResource::class;

    protected function afterCreate(): void
    {
        $this->record->updateQuietly([
            'audio_duration_seconds' => app(AudioDurationProbe::class)->seconds($this->record->audio_path),
        ]);
    }
}
