<?php

namespace App\Filament\Resources\Poems\Pages;

use App\Filament\Resources\Poems\PoemResource;
use App\Support\BookContentOrder;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Validation\ValidationException;

class ListPoems extends ListRecords
{
    protected static string $resource = PoemResource::class;

    public function selectedBookId(): ?int
    {
        $value = $this->getTableFilterState('collection')['value'] ?? null;

        return filled($value) ? (int) $value : null;
    }

    public function reorderTable(array $order, int|string|null $draggedRecordKey = null): void
    {
        $bookId = $this->selectedBookId();
        if (! $bookId) {
            throw ValidationException::withMessages(['order' => 'Select one book before reordering.']);
        }
        BookContentOrder::reorder($bookId, $order);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->url(fn (): string => PoemResource::getUrl('create', array_filter(['collection_id' => $this->selectedBookId()]))),
        ];
    }
}
