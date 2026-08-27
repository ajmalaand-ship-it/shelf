<?php

namespace App\Filament\Resources\Poems\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PoemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('collection_id')->relationship('collection', 'title')->required(),
                TextInput::make('title')->required()->maxLength(255),
                Textarea::make('body')->required()->rows(18)->extraInputAttributes(['dir' => 'rtl']),
                Textarea::make('excerpt')->required()->rows(5)->extraInputAttributes(['dir' => 'rtl']),
                FileUpload::make('audio_path')->disk('audio')->visibility('private')
                    ->acceptedFileTypes(['audio/mpeg', 'audio/mp4', 'audio/x-m4a', 'audio/wav', 'audio/x-wav'])
                    ->maxSize(51200),
                TextInput::make('audio_duration_seconds')->numeric()->minValue(0),
                TextInput::make('sort_order')->numeric()->minValue(0)->required()->default(0),
                Toggle::make('is_free_sample')->default(false),
                Toggle::make('is_active')->default(false),
            ]);
    }
}
