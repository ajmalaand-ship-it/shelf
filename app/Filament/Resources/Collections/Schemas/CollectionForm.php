<?php

namespace App\Filament\Resources\Collections\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CollectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Collection / Book')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')->label('Book title')->required()->maxLength(255),
                        TextInput::make('subtitle')->label('Subtitle')->maxLength(255),
                        TextInput::make('author')->label('Author')->maxLength(255),
                        Textarea::make('description')->label('Description')->rows(4)
                            ->extraInputAttributes(['dir' => 'rtl'])->columnSpanFull(),
                        FileUpload::make('cover_image')->label('Book cover')->disk('covers')->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(5120)
                            ->helperText('JPEG, PNG, or WebP up to 5 MB.'),
                    ]),
                Section::make('Front matter / د کتاب پیل')
                    ->schema([
                        Textarea::make('dedication')->label('Dedication')->rows(3)->extraInputAttributes(['dir' => 'rtl']),
                        Textarea::make('introduction')->label('Introduction')->rows(12)->extraInputAttributes(['dir' => 'rtl']),
                        TextInput::make('foreword_author')->label('Foreword author')->maxLength(255),
                        Textarea::make('foreword')->label('Foreword')->rows(12)->extraInputAttributes(['dir' => 'rtl']),
                        Textarea::make('publication_info')->label('Publication information')->rows(7)->extraInputAttributes(['dir' => 'rtl']),
                    ])
                    ->collapsible(),
                Section::make('Order / Publication')
                    ->columns(2)
                    ->schema([
                        TextInput::make('sort_order')->label('Book order')->numeric()->minValue(0)->required()->default(0),
                        Toggle::make('is_active')->label('Published')->helperText('Draft collections and their poems remain outside the public catalogue.')->default(false),
                    ]),
                Section::make('Advanced')
                    ->description('Rarely changed technical and commercial identifiers.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('slug')->label('Web identifier (slug)')->required()->alphaDash()
                            ->unique(ignoreRecord: true)->maxLength(255)
                            ->helperText('Required by existing API links. Change only deliberately.'),
                        TextInput::make('product_id')->label('Store product mapping')->maxLength(255)
                            ->helperText('Leave unchanged unless configuring an approved store product.'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
