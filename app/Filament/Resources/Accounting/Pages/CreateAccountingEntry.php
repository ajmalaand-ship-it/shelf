<?php

namespace App\Filament\Resources\Accounting\Pages;

use App\Filament\Resources\Accounting\AccountingEntryResource;
use App\Services\Accounting\AccountingService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAccountingEntry extends CreateRecord
{
    protected static string $resource = AccountingEntryResource::class;

    protected static ?string $title = 'Record financial entry';

    protected function handleRecordCreation(array $data): Model
    {
        return app(AccountingService::class)->record(auth()->user(), $data);
    }

    protected function getRedirectUrl(): string
    {
        return static::$resource::getUrl('index');
    }
}
