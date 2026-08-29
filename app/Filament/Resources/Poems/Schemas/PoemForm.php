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
                TextInput::make('title')->maxLength(255)
                    ->helperText('Leave blank when the source poem has no authored title.'),
                Textarea::make('body')->required()->rows(18)->extraInputAttributes(['dir' => 'rtl']),
                Textarea::make('excerpt')->required()->rows(5)->extraInputAttributes(['dir' => 'rtl']),
                Select::make('work_type')->options([
                    'ORIGINAL' => 'Original work',
                    'TRANSLATION' => 'Translation',
                ])->required()->default('ORIGINAL'),
                TextInput::make('original_author')->maxLength(255),
                TextInput::make('translator')->maxLength(255),
                TextInput::make('source_date_place')->maxLength(255)
                    ->helperText('Preserve the authored date/place exactly; do not convert calendars.'),
                Textarea::make('source_note')->rows(3)->extraInputAttributes(['dir' => 'rtl']),
                FileUpload::make('audio_path')->disk('audio')->visibility('private')
                    ->acceptedFileTypes(['audio/mpeg', 'audio/mp4', 'audio/x-m4a', 'audio/wav', 'audio/x-wav'])
                    ->maxSize(51200)
                    ->helperText('Owner recording only. Upload M4A, MP3, or WAV up to 50 MB. Replacing or removing audio changes the public content version; old private files remain available to the backup process.'),
                TextInput::make('audio_duration_seconds')->numeric()->minValue(0)
                    ->disabled()->dehydrated(false)
                    ->helperText('Detected automatically after upload when ffprobe can read the file.'),
                TextInput::make('sort_order')->numeric()->minValue(0)->required()->default(0),
                Toggle::make('is_free_sample')->default(false),
                Toggle::make('is_active')->default(false),
            ]);
    }
}
