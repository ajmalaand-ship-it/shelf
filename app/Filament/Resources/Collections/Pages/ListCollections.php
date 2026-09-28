<?php

namespace App\Filament\Resources\Collections\Pages;

use App\Filament\Resources\Collections\CollectionResource;
use App\Models\Collection;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListCollections extends ListRecords
{
    protected static string $resource = CollectionResource::class;

    public function reorderTable(array $order, int|string|null $draggedRecordKey = null): void
    {
        DB::transaction(function () use ($order, $draggedRecordKey): void {
            parent::reorderTable($order, $draggedRecordKey);
            foreach (Collection::whereKey($order)->get() as $book) {
                $book->recordChange();
            }
        });
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
