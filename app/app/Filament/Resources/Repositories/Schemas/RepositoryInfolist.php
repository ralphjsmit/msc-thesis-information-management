<?php

namespace App\Filament\Resources\Repositories\Schemas;

use App\Models\Repository;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Number;

class RepositoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(6)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('stargazer_count')
                            ->label('Stars')
                            ->icon(Heroicon::Star)
                            ->formatStateUsing(fn (int $state) => Number::format($state)),
                        TextEntry::make('watcher_count')
                            ->label('Watchers')
                            ->icon(Heroicon::Eye)
                            ->formatStateUsing(fn (int $state) => Number::format($state)),
                        TextEntry::make('fork_count')
                            ->label('Forks')
                            ->icon(Heroicon::Share)
                            ->formatStateUsing(fn (int $state) => Number::format($state)),
                        TextEntry::make('repository_months_count')
                            ->label('Repository months')
                            ->icon(Heroicon::CalendarDateRange)
                            ->state(fn (Repository $repository) => $repository->loadCount('repositoryMonths')->repository_months_count),
                        TextEntry::make('tags_count')
                            ->label('Tags')
                            ->icon(Heroicon::Tag)
                            ->state(fn (Repository $repository) => $repository->loadCount('tags')->tags_count),
                    ]),
                Section::make()
                    ->columnSpanFull()
                    ->columns(6)
                    ->schema([
                        IconEntry::make('has_issues_enabled')
                            ->label('Issues')
                            ->boolean(),
                        IconEntry::make('has_projects_enabled')
                            ->label('Projects')
                            ->boolean(),
                        IconEntry::make('has_wiki_enabled')
                            ->label('Wiki')
                            ->boolean(),
                        IconEntry::make('has_discussions_enabled')
                            ->label('Discussions')
                            ->boolean(),
                        IconEntry::make('is_archived')
                            ->label('Archived')
                            ->boolean(),
                        IconEntry::make('is_disabled')
                            ->label('Disabled')
                            ->boolean(),
                    ]),
                Section::make()
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextEntry::make('languages'),
                        TextEntry::make('topics')
                            ->placeholder('No topics'),
                        TextEntry::make('created_at')
                            ->dateTime(),
                    ]),
            ]);
    }
}
