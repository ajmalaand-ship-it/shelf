<?php

namespace App\Filament\Resources\Poems\Schemas;

use App\Models\Collection;
use App\Models\Poem;
use App\Support\SampleText;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class PoemForm
{
    public static function contentLabel(Get $get): string
    {
        return Collection::find($get('collection_id'))?->book_type === 'prose' ? 'Chapter' : 'Poem';
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(fn (Get $get): string => self::contentLabel($get))
                    ->description('Preserve the original title truthfully, and enter the content exactly as authored.')
                    ->columns(2)
                    ->schema([
                        Select::make('collection_id')->label('Book')
                            ->relationship('collection', 'title', fn ($query) => $query->with('credits.author'))->getOptionLabelFromRecordUsing(fn ($record): string => $record->selector_label)->disabled()->dehydrated(false)
                            ->default(fn ($livewire): int => $livewire->contentBookId()),
                        TextInput::make('title')->label(fn (Get $get): string => self::contentLabel($get).' title')->maxLength(255)
                            ->helperText('Optional. Leave blank when the item has no original title; the first line is never saved as a title.'),
                        Textarea::make('body')->label(fn (Get $get): string => self::contentLabel($get).' text')->required()->rows(20)
                            ->extraInputAttributes(['dir' => 'rtl'])->columnSpanFull(),
                        TextInput::make('slug')->label('Web identifier (slug)')->required(fn (string $operation): bool => $operation === 'edit')->alphaDash()->maxLength(255)->unique(ignoreRecord: true)
                            ->helperText('Generated from the title on creation; untitled content receives a neutral identifier.'),
                        TextInput::make('sort_order')->label('Order in book')->disabled()->dehydrated(false)
                            ->helperText('New content is added at the end. Use the book content list to reorder.'),
                        Select::make('layout_mode')->label('Poetry layout')->options([
                            'SOURCE' => 'Source layout', 'COUPLET' => 'Couplets', 'FOUR_LINES' => 'Four lines',
                        ])->default('SOURCE')->rules([Rule::in(Poem::LAYOUT_MODES)])
                            ->visible(fn (Get $get): bool => filled($get('collection_id')) && self::contentLabel($get) === 'Poem'),
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
                Section::make('Access / Publication')
                    ->description('Visibility and sample approval are separate decisions. Only approved sample text is public.')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_active')->label('Visible')->helperText('Hidden items are never available through the public API.')->default(false),
                        Select::make('sample_mode')->label('Free sample')->options(Poem::SAMPLE_MODES)->default('none')->required()->live()->rules([Rule::in(array_keys(Poem::SAMPLE_MODES))]),
                        Select::make('sample_unit')->label('Count by')->options(['lines' => 'Lines', 'paragraphs' => 'Paragraphs'])
                            ->visible(fn (Get $get): bool => $get('sample_mode') === 'partial')->required(fn (Get $get): bool => $get('sample_mode') === 'partial')->live()
                            ->helperText('Lines end at a line break. Paragraphs are blocks separated by a blank line. Full audio and artwork stay private for partial samples.'),
                        TextInput::make('sample_count')->label('Number of free lines / paragraphs')->integer()->minValue(1)
                            ->visible(fn (Get $get): bool => $get('sample_mode') === 'partial')->required(fn (Get $get): bool => $get('sample_mode') === 'partial')
                            ->rules([fn (Get $get): \Closure => function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                                if ($get('sample_mode') === 'partial' && blank(SampleText::prefix($get('body') ?? '', $get('sample_unit') ?? '', (int) $value))) {
                                    $fail('Choose a count that leaves some text for the full book.');
                                }
                            }]),
                        Textarea::make('excerpt')->label('Legacy excerpt (private)')->default('')->dehydrateStateUsing(fn (?string $state): string => $state ?? '')->rows(5)
                            ->helperText('Retained for the owner only. Public excerpts come only from the approved sample; this field grants no access.')
                            ->extraInputAttributes(['dir' => 'rtl'])->columnSpanFull(),
                    ]),
                Section::make('Audio / غږ')
                    ->description('Audio recording')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('audio_path')->label('Audio recording')->disk('audio')->visibility('private')
                            ->acceptedFileTypes(['audio/mpeg', 'audio/mp4', 'audio/x-m4a', 'audio/wav', 'audio/x-wav'])
                            ->maxSize(51200)
                            ->helperText('M4A, MP3, or WAV up to 50 MB. Replaced files remain available to the protected backup process.')
                            ->columnSpanFull(),
                        TextInput::make('audio_duration_seconds')->label('Detected duration (seconds)')->numeric()->minValue(0)
                            ->disabled()->dehydrated(false)
                            ->helperText('Read automatically after upload when the audio can be inspected.'),
                    ])
                    ->collapsible(),
                Section::make('Artwork / انځور')
                    ->description('One original illustration associated with this item. It remains protected by the content access rules.')
                    ->schema([
                        FileUpload::make('artwork_path')->label('Artwork / انځور')->disk('artwork')->visibility('private')
                            ->image()
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                            ->maxSize(20480)
                            ->helperText('PNG, JPEG, or WebP up to 20 MB. Replaced files remain available to the protected backup process.')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
                Section::make('Translation / ژباړه')
                    ->description('Use Translation only when the item is translated, and preserve complete truthful attribution.')
                    ->columns(2)
                    ->schema([
                        Select::make('work_type')->label('Work type')->options([
                            'ORIGINAL' => 'Original work',
                            'TRANSLATION' => 'Translation',
                        ])->required()->default('ORIGINAL')->live(),
                        TextInput::make('original_author')->label('Original author')->maxLength(255)
                            ->required(fn (Get $get): bool => $get('work_type') === 'TRANSLATION'),
                        TextInput::make('translator')->label('Translator')->maxLength(255)
                            ->required(fn (Get $get): bool => $get('work_type') === 'TRANSLATION'),
                    ]),
            ]);
    }
}
