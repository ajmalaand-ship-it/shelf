<?php

namespace App\Filament\Resources\Collections\RelationManagers;

use App\Filament\Resources\Poems\PoemResource;
use App\Filament\Resources\Poems\Tables\PoemsTable;
use App\Support\BookContentOrder;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContentRelationManager extends RelationManager
{
    use HasWordImport;

    protected static string $relationship = 'poems';

    protected static ?string $title = 'Content';

    public function table(Table $table): Table
    {
        $label = $this->getOwnerRecord()->book_type === 'prose' ? 'Chapter' : 'Poem';

        return PoemsTable::configure($table)
            ->modelLabel($label)
            ->pluralModelLabel($label === 'Chapter' ? 'Chapters' : 'Poems')
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([SoftDeletingScope::class])->with('collection'))
            ->headerActions([
                $this->wordImportAction(),
                CreateAction::make()->label('Add '.$label)
                    ->url(fn (): string => PoemResource::getUrl('create', ['collection_id' => $this->getOwnerRecord()->getKey()])),
            ]);
    }

    public function reorderTable(array $order, int|string|null $draggedRecordKey = null): void
    {
        abort_unless($this->canReorder(), 403);
        BookContentOrder::reorder($this->getOwnerRecord()->getKey(), $order);
    }
}
