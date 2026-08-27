<?php

namespace App\Filament\Resources\Poems;

use App\Filament\Resources\Poems\Pages\CreatePoem;
use App\Filament\Resources\Poems\Pages\EditPoem;
use App\Filament\Resources\Poems\Pages\ListPoems;
use App\Filament\Resources\Poems\Schemas\PoemForm;
use App\Filament\Resources\Poems\Tables\PoemsTable;
use App\Models\Poem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PoemResource extends Resource
{
    protected static ?string $model = Poem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PoemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PoemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPoems::route('/'),
            'create' => CreatePoem::route('/create'),
            'edit' => EditPoem::route('/{record}/edit'),
        ];
    }
}
