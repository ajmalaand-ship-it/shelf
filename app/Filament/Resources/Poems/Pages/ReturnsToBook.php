<?php

namespace App\Filament\Resources\Poems\Pages;

use App\Filament\Resources\Collections\CollectionResource;
use Filament\Actions\Action;

trait ReturnsToBook
{
    abstract public function contentBookId(): int;

    public function bookUrl(): string
    {
        return CollectionResource::getUrl('edit', ['record' => $this->contentBookId()]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->bookUrl();
    }

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')->label('Cancel')->color('gray')->url($this->bookUrl());
    }

    public function getBreadcrumbs(): array
    {
        return [CollectionResource::getUrl() => 'Books', $this->bookUrl() => 'Content', $this->getTitle()];
    }
}
