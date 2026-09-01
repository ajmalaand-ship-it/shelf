<?php

namespace App\Filament\Resources\Poems\Schemas;

use App\Models\Poem;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PoemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Poem / شعر')
                    ->description('Choose the book, preserve the original title truthfully, and enter the poem exactly as authored.')
                    ->columns(2)
                    ->schema([
                        Select::make('collection_id')->label('Collection / Book')
                            ->relationship('collection', 'title')->searchable()->preload()->required()
                            ->default(fn (): ?int => request()->integer('collection_id') ?: null),
                        TextInput::make('title')->label('Original poem title')->maxLength(255)
                            ->helperText('Optional. Leave blank when the poem has no original title; the first line is never saved as a title.'),
                        Textarea::make('body')->label('Poem text')->required()->rows(20)
                            ->extraInputAttributes(['dir' => 'rtl'])->columnSpanFull(),
                    ]),
                Section::make('Source / Literary metadata')
                    ->columns(2)
                    ->schema([
                        TextInput::make('source_date_place')->label('Date / place as written')->maxLength(255)
                            ->helperText('Optional. Preserve the exact stored wording and calendar; do not infer missing information.')
                            ->columnSpanFull(),
                        Textarea::make('source_note')->label('Source note')->rows(3)
                            ->extraInputAttributes(['dir' => 'rtl'])->columnSpanFull(),
                    ]),
                Section::make('Presentation / ښودنه')
                    ->columns(2)
                    ->schema([
                        Select::make('layout_mode')->label('Poetry layout')->options([
                            Poem::LAYOUT_SOURCE => 'SOURCE — preserve authored spacing',
                            Poem::LAYOUT_COUPLET => 'COUPLET — gap after every 2 lines',
                            Poem::LAYOUT_FOUR_LINES => 'FOUR_LINES — gap after every 4 lines',
                        ])->required()->default(Poem::LAYOUT_SOURCE)
                            ->helperText('Display only. This never changes the stored poem text.'),
                        TextInput::make('sort_order')->label('Order in book')->numeric()->minValue(0)->required()->default(0),
                    ]),
                Section::make('Access / Publication')
                    ->description('Draft/published and free/locked are separate decisions.')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_active')->label('Published')->helperText('Draft poems remain unavailable through the public API.')->default(false),
                        Toggle::make('is_free_sample')->label('Free sample')->helperText('Off means the poem is locked and requires entitlement when published.')->default(false),
                        Textarea::make('excerpt')->label('Public excerpt')->required()->rows(5)
                            ->helperText('Shown when a locked poem is listed or opened without entitlement. Do not rewrite the poem.')
                            ->extraInputAttributes(['dir' => 'rtl'])->columnSpanFull(),
                    ]),
                Section::make('Audio / غږ')
                    ->description('Ajmal’s original recording only.')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('audio_path')->label('Poem recording')->disk('audio')->visibility('private')
                            ->acceptedFileTypes(['audio/mpeg', 'audio/mp4', 'audio/x-m4a', 'audio/wav', 'audio/x-wav'])
                            ->maxSize(51200)
                            ->helperText('M4A, MP3, or WAV up to 50 MB. Replaced files remain available to the protected backup process.')
                            ->columnSpanFull(),
                        TextInput::make('audio_duration_seconds')->label('Detected duration (seconds)')->numeric()->minValue(0)
                            ->disabled()->dehydrated(false)
                            ->helperText('Read automatically after upload when the audio can be inspected.'),
                    ])
                    ->collapsible(),
                Section::make('Translation / ژباړه')
                    ->description('Use Translation only when the poem is translated, and preserve complete truthful attribution.')
                    ->columns(2)
                    ->schema([
                        Select::make('work_type')->label('Work type')->options([
                            'ORIGINAL' => 'Original work',
                            'TRANSLATION' => 'Translation',
                        ])->required()->default('ORIGINAL')->live(),
                        TextInput::make('original_author')->label('Original author')->maxLength(255)
                            ->required(fn (Get $get): bool => $get('work_type') === 'TRANSLATION'),
                        TextInput::make('translator')->label('Pashto translator')->maxLength(255)
                            ->required(fn (Get $get): bool => $get('work_type') === 'TRANSLATION'),
                    ]),
            ]);
    }
}
