<?php

namespace App\Filament\Resources\Poems\Pages;

use App\Filament\Resources\Poems\PoemResource;
use App\Models\Collection;
use App\Support\AudioDurationProbe;
use Filament\Resources\Pages\CreateRecord;

class CreatePoem extends CreateRecord
{
    protected static string $resource = PoemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['sort_order']);

        return $data;
    }

    public function getTitle(): string
    {
        $bookId = $this->data['collection_id'] ?? request()->integer('collection_id');

        return Collection::find($bookId)?->book_type === 'prose' ? 'Create chapter' : 'Create poem';
    }

    protected function afterCreate(): void
    {
        $this->record->updateQuietly([
            'audio_duration_seconds' => app(AudioDurationProbe::class)->seconds($this->record->audio_path),
        ]);
    }
}
