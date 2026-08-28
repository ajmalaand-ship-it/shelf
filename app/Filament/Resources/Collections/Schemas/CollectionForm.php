<?php

namespace App\Filament\Resources\Collections\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CollectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')->required()->maxLength(255),
                TextInput::make('slug')->required()->alphaDash()->unique(ignoreRecord: true)->maxLength(255),
                TextInput::make('author')->maxLength(255),
                TextInput::make('subtitle')->maxLength(255),
                Textarea::make('description')->rows(4),
                Textarea::make('dedication')->rows(3)->extraInputAttributes(['dir' => 'rtl']),
                Textarea::make('introduction')->rows(12)->extraInputAttributes(['dir' => 'rtl']),
                TextInput::make('foreword_author')->maxLength(255),
                Textarea::make('foreword')->rows(12)->extraInputAttributes(['dir' => 'rtl']),
                Textarea::make('publication_info')->rows(7)->extraInputAttributes(['dir' => 'rtl']),
                FileUpload::make('cover_image')->disk('covers')->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(5120),
                TextInput::make('sort_order')->numeric()->minValue(0)->required()->default(0),
                Toggle::make('is_active')->default(false),
                TextInput::make('product_id')->maxLength(255),
            ]);
    }
}
