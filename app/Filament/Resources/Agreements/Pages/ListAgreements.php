<?php
namespace App\Filament\Resources\Agreements\Pages;
class ListAgreements extends \Filament\Resources\Pages\ListRecords
{
    protected static string $resource = \App\Filament\Resources\Agreements\AgreementResource::class;
    protected function getHeaderActions(): array { return [\Filament\Actions\CreateAction::make()->label('New agreement version')]; }
}
