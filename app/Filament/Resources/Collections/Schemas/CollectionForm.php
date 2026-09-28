<?php

namespace App\Filament\Resources\Collections\Schemas;

use App\Models\BookCredit;
use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class CollectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Book')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')->label('Book title')->required()->maxLength(255),
                        TextInput::make('subtitle')->label('Subtitle')->maxLength(255),
                        Select::make('book_type')->label('Book type')->options(['poetry' => 'Poetry', 'prose' => 'Prose'])->required()->default('poetry')->rules([Rule::in(['poetry', 'prose'])]),
                        Select::make('categories')->relationship('categories', 'name')->multiple()->searchable()->preload(),
                        Select::make('language')->options(fn () => config('books.languages'))
                            ->rules([Rule::in(array_keys(config('books.languages')))])
                            ->required(fn (Get $get): bool => (bool) $get('is_active')),
                        Repeater::make('credits')->label('Book credits')->relationship()
                            ->orderColumn('position')->defaultItems(0)->columnSpanFull()
                            ->required(fn (Get $get): bool => (bool) $get('is_active'))
                            ->schema([
                                Select::make('author_id')->label('Author / person')
                                    ->relationship('author', 'name')->searchable(['name', 'name_latin'])
                                    ->required()->exists('authors', 'id'),
                                Select::make('role')->options(BookCredit::ROLES)->required()
                                    ->rules([Rule::in(array_keys(BookCredit::ROLES))])->default('author'),
                            ])->columns(2)
                            ->rules([fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                                $credits = collect($value ?? []);
                                if ($get('is_active') && ! $credits->contains(fn ($credit) => ($credit['role'] ?? null) === 'author' && filled($credit['author_id'] ?? null))) {
                                    $fail('Add at least one author before publishing.');
                                }
                                $identities = $credits->map(fn ($credit) => ($credit['author_id'] ?? '').':'.($credit['role'] ?? ''));
                                if ($identities->unique()->count() !== $identities->count()) {
                                    $fail('Each person can have a role only once per book.');
                                }
                            }]),
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
                        Toggle::make('is_active')->label('Published')->helperText('Draft books and their content remain outside the public catalogue.')->default(false)->live(),
                    ]),
                Section::make('Advanced')
                    ->description('Rarely changed technical and commercial identifiers.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('slug')->label('Web identifier (slug)')->required(fn (string $operation): bool => $operation === 'edit')->alphaDash()
                            ->unique(ignoreRecord: true)->maxLength(255)
                            ->helperText('Generated from the title when blank on a new book. Title edits never change it. Change an existing identifier only deliberately.'),
                        TextInput::make('product_id')->label('Store product mapping')->maxLength(255)
                            ->helperText('Leave unchanged unless configuring an approved store product.'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
