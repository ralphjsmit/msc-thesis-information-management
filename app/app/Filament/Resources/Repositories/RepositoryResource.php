<?php

namespace App\Filament\Resources\Repositories;

use App\Filament\Resources\Repositories\Pages\ListRepositories;
use App\Filament\Resources\Repositories\Pages\ViewRepository;
use App\Filament\Resources\Repositories\Schemas\RepositoryForm;
use App\Filament\Resources\Repositories\Schemas\RepositoryInfolist;
use App\Filament\Resources\Repositories\Tables\RepositoriesTable;
use App\Models\Repository;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RepositoryResource extends Resource
{
    protected static ?string $model = Repository::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::Cube;

    public static function form(Schema $schema): Schema
    {
        return RepositoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RepositoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RepositoriesTable::configure($table);
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
            'index' => ListRepositories::route('/'),
            'view' => ViewRepository::route('/{record}'),
        ];
    }
}
