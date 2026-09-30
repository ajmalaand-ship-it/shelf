<?php

namespace App\Filament\Resources\Agreements;

use App\Models\AuthorShareAgreement;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Forms\Components\{Select, Repeater, TextInput, Textarea, DateTimePicker};
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AgreementResource extends Resource
{
    protected static ?string $model = AuthorShareAgreement::class;
    protected static ?string $navigationLabel = 'Author-share agreements';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static string|UnitEnum|null $navigationGroup = 'Sales and readers';
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('collection_id')->label('Book')->relationship('book', 'title')->searchable()->required(),
            Repeater::make('contributors')->label('Contributors')->defaultItems(0)->schema([
                Select::make('author_id')->label('Contributor')->options(fn () => \App\Models\Author::pluck('name', 'id'))->searchable()->required(),
                TextInput::make('percentage')->label('Percentage')->numeric()->minValue(0.0001)->maxValue(100)->required(),
            ]),
            Select::make('basis')->options(['gross' => 'Gross income', 'net' => 'Net income'])->required(),
            Textarea::make('deductions')->label('Allowed deductions')->required()->helperText('Enter the agreed deductions, or "none". Net figures remain unknown until fees/taxes are confirmed.'),
            Textarea::make('sharing_terms')->label('How contributors share')->required(),
            DateTimePicker::make('starts_at')->label('Effective from')->required()->minDate(now())->helperText('Creates a new version; never changes past earnings. No default percentage and no payouts.'),
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('book.title')->label('Book'), TextColumn::make('basis'),
            TextColumn::make('starts_at')->dateTime()->sortable(),
            TextColumn::make('contributors')->getStateUsing(fn ($record) => collect($record->contributors)->map(fn ($c) => (\App\Models\Author::find($c['author_id'])?->name ?? 'Contributor').' '.$c['percentage'].'%')->implode(', '))->wrap(),
            TextColumn::make('deductions')->wrap(), TextColumn::make('sharing_terms')->wrap(),
            TextColumn::make('created_by')->label('Owner ID'), TextColumn::make('created_at')->dateTime(),
        ])->defaultSort('starts_at', 'desc');
    }
    public static function getPages(): array { return ['index' => Pages\ListAgreements::route('/'), 'create' => Pages\CreateAgreement::route('/create')]; }
}
