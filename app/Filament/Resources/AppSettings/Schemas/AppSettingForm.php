<?php

namespace App\Filament\Resources\AppSettings\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AppSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')->required()->alphaDash()->unique(ignoreRecord: true)->maxLength(255),
                Textarea::make('value')->required()->rows(3),
            ]);
    }
}
