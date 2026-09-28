<?php

namespace App\Filament\Resources\Poems\Pages;

use App\Filament\Resources\Poems\PoemResource;
use App\Models\Collection;
use App\Support\AudioDurationProbe;
use Filament\Resources\Pages\CreateRecord;
use Livewire\Attributes\Locked;

class CreatePoem extends CreateRecord
{
    use ReturnsToBook;

    protected static string $resource = PoemResource::class;

    #[Locked]
    public int $bookId;

    public function mount(): void
    {
        $this->bookId = Collection::findOrFail(request()->integer('collection_id'))->getKey();
        parent::mount();
    }

    public function contentBookId(): int
    {
        return $this->bookId;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['sort_order']);
        $data['collection_id'] = $this->bookId;

        return $data;
    }

    public function getTitle(): string
    {
        $bookId = $this->bookId;

        return Collection::find($bookId)?->book_type === 'prose' ? 'Create chapter' : 'Create poem';
    }

    protected function afterCreate(): void
    {
        $this->record->updateQuietly([
            'audio_duration_seconds' => app(AudioDurationProbe::class)->seconds($this->record->audio_path),
        ]);
    }
}
