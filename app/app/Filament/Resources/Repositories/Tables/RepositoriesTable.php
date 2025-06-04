<?php

namespace App\Filament\Resources\Repositories\Tables;

use App\Jobs\ImportRepositoryMonths;
use App\Jobs\ImportRepositoryTags;
use App\Models\Repository;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Number;

class RepositoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->prefix(fn (Repository $repository) => $repository->organization->login . '/')
                    ->searchable()
                    ->grow(),
                TextColumn::make('stargazer_count')
                    ->label('Stars')
                    ->icon(Heroicon::Star)
                    ->formatStateUsing(fn (int $state) => Number::format($state))
                    ->sortable(),
                TextColumn::make('watcher_count')
                    ->label('Watchers')
                    ->icon(Heroicon::Eye)
                    ->formatStateUsing(fn (int $state) => Number::format($state))
                    ->sortable(),
                TextColumn::make('fork_count')
                    ->label('Forks')
                    ->icon(Heroicon::Share)
                    ->formatStateUsing(fn (int $state) => Number::format($state)),
                TextColumn::make('tags_count')
                    ->label('Releases')
                    ->icon(Heroicon::Tag)
                    ->counts('tags'),
                IconColumn::make('tags_imported_at')
                    ->label('Tags')
                    ->default(false)
                    ->boolean(),
                IconColumn::make('repository_months_imported_at')
                    ->label('RMs')
                    ->default(false)
                    ->boolean(),
                TextColumn::make('topics')
                    ->searchable()
                    ->bulleted()
                    ->limitList(1),
                TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make()
                    ->iconButton(),
                Action::make('import_tags')
                    ->tooltip('Import tags')
                    ->label('Import tags')
                    ->icon(Heroicon::ArrowPath)
                    ->successNotificationTitle('Import triggered')
                    ->iconButton()
                    ->action(function (Repository $repository, Action $action) {
                        dispatch_sync(new ImportRepositoryTags($repository));

                        $action->success();
                    }),
                Action::make('import_repository_months')
                    ->tooltip('Import months')
                    ->label('Import months')
                    ->icon(Heroicon::ArrowPath)
                    ->successNotificationTitle('Import triggered')
                    ->iconButton()
                    ->action(function (Repository $repository, Action $action) {
                        dispatch_sync(new ImportRepositoryMonths($repository));

                        $action->success();
                    }),
            ])
            ->bulkActions([
                //
            ]);
    }
}
