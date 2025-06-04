<?php

namespace App\Filament\Resources\Organizations\Tables;

use App\Jobs\UpdateOrCreateOrganization;
use App\Models\Organization;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class OrganizationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('git_hub_id')
                    ->label('GitHub node ID')
                    ->fontFamily(FontFamily::Mono)
                    ->grow(false),
                Tables\Columns\TextColumn::make('login')
                    ->grow()
                    ->weight(FontWeight::SemiBold)
                    ->searchable(),
                Tables\Columns\TextColumn::make('sponsors')
                    ->label('Sponsoring')
                    ->placeholder('...')
                    ->getStateUsing(fn (?Organization $organization) => $organization->sponsors?->count()),
                Tables\Columns\TextColumn::make('sponsorships_as_maintainer')
                    ->label('Sponsorships')
                    ->placeholder('...')
                    ->getStateUsing(fn (?Organization $organization) => $organization->sponsorships_as_maintainer?->count()),
                Tables\Columns\IconColumn::make('repositories_imported_at')
                    ->label('Repos imported')
                    ->default(false)
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('repositories_imported_at')
                    ->label('Repos imported')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('repositories_imported_at'),
                        false: fn (Builder $query) => $query->whereNull('repositories_imported_at'),
                    ),
            ])
            ->actions([
                ViewAction::make()
                    ->iconButton(),
                Action::make('import_organization')
                    ->label('Import organization')
                    ->icon(Heroicon::ArrowPath)
                    ->successNotificationTitle('Import triggered')
                    ->iconButton()
                    ->action(function (Organization $organization, Action $action) {
                        dispatch_sync(new UpdateOrCreateOrganization($organization));

                        $action->success();
                    }),
            ])
            ->bulkActions([
                BulkAction::make('update_or_create_organization')
                    ->label('Import organizations')
                    ->icon(Heroicon::ArrowPath)
                    ->successNotificationTitle('Import triggered')
                    ->requiresConfirmation()
                    ->action(function (Collection $records, BulkAction $action) {
                        $records->each(function (Organization $organization) {
                            dispatch(new UpdateOrCreateOrganization($organization));
                        });

                        $action->success();
                    }),
            ]);
    }
}
