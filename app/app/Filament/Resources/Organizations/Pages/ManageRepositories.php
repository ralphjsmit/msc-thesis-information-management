<?php

namespace App\Filament\Resources\Organizations\Pages;

use App\Filament\Resources\Organizations\OrganizationResource;
use App\Filament\Resources\Repositories\RepositoryResource;
use BackedEnum;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Icons\Heroicon;

class ManageRepositories extends ManageRelatedRecords
{
    protected static string $resource = OrganizationResource::class;

    protected static string $relationship = 'repositories';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::Cube;

    protected static ?string $relatedResource = RepositoryResource::class;
}
