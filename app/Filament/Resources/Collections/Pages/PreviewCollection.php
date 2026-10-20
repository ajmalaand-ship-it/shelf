<?php

namespace App\Filament\Resources\Collections\Pages;

use App\Filament\Resources\Collections\CollectionResource;
use App\Http\Middleware\PrivateAccountResponse;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Livewire\Attributes\Locked;
use Livewire\WithPagination;

class PreviewCollection extends Page
{
    use InteractsWithRecord;
    use WithPagination;

    protected static string $resource = CollectionResource::class;
    protected string $view = 'filament.books.preview';
    protected static string|array $routeMiddleware = [PrivateAccountResponse::class];

    #[Locked]
    public bool $sampleOnly = false;

    public function mount(int|string $record): void
    {
        abort_unless(auth()->user()?->is_owner, 403);
        $this->record = $this->resolveRecord($record);
        $this->sampleOnly = request()->boolean('sample');
    }

    protected function getViewData(): array
    {
        abort_unless(auth()->user()?->is_owner, 403);
        $book = $this->getRecord();
        $items = $book->poems();
        if ($this->sampleOnly) {
            $items->where('is_active', true)->whereIn('sample_mode', ['full', 'partial']);
        }

        return ['book' => $book->loadMissing('credits.author'), 'items' => $items->paginate(10),
            'editUrl' => CollectionResource::getUrl('edit', ['record' => $book]),
            'fullUrl' => CollectionResource::getUrl('preview', ['record' => $book]),
            'sampleUrl' => CollectionResource::getUrl('preview', ['record' => $book, 'sample' => 1])];
    }
}
