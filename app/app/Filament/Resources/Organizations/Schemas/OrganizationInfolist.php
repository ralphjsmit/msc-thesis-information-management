<?php

namespace App\Filament\Resources\Organizations\Schemas;

use App\Models\Organization;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Number;

class OrganizationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->columns(3)
                    ->schema([
                        TextEntry::make('login')
                            ->weight(FontWeight::SemiBold),
                        TextEntry::make('repositories_count')
                            ->label('Repositories')
                            ->state(fn (Organization $organization) => $organization->loadCount('repositories')->repositories_count)
                            ->formatStateUsing(fn (int $state) => Number::format($state)),
                        IconEntry::make('repositories_imported_at')
                            ->label('Repositories imported')
                            ->default(false)
                            ->boolean(),
                    ]),
                Section::make()
                    ->secondary()
                    ->schema([
                        TextEntry::make('sponsors')
                            ->formatStateUsing(function (array $state) {
                                return "{$state['name']} ({$state['login']})";
                            })
                            ->bulleted()
                            ->limitList(6)
                            ->expandableLimitedList(),
                    ]),
                Section::make()
                    ->secondary()
                    ->schema([
                        TextEntry::make('sponsorships_as_maintainer')
                            ->formatStateUsing(function (array $state) {
                                // array:7 [▼ // app/Filament/Resources/Organizations/Schemas/OrganizationInfolist.php:39
                                //  "id" => "S_kwHNbJzOAAHbxA"
                                //  "createdAt" => "2022-08-27T20:26:33Z"
                                //  "isActive" => false
                                //  "isOneTimePayment" => false
                                //  "isSponsorOptedIntoEmail" => null
                                //  "privacyLevel" => "PUBLIC"
                                //  "tierSelectedAt" => "2022-08-27T20:26:33Z"

                                return "{$state['createdAt']} " . ($state['isActive'] ? '' : '(inactive)');
                            })
                            ->bulleted()
                            ->limitList(6)
                            ->expandableLimitedList(),
                    ]),
            ]);
    }
}
