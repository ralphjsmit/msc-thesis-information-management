<?php

namespace App\Filament\Resources\Repositories\Pages;

use App\Filament\Resources\Repositories\RepositoryResource;
use App\Jobs\ImportRepositoryMonths;
use App\Jobs\ImportRepositoryTags;
use App\Models\Repository;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewRepository extends ViewRecord
{
    protected static string $resource = RepositoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import_tags')
                ->label('Import tags')
                ->icon(Heroicon::ArrowPath)
                ->successNotificationTitle('Imported')
                ->action(function (Repository $repository, Action $action) {
                    dispatch_sync(new ImportRepositoryTags($repository));

                    $action->success();
                }),
            Action::make('import_repository_months')
                ->label('Import months')
                ->icon(Heroicon::ArrowPath)
                ->successNotificationTitle('Imported')
                ->action(function (Repository $repository, Action $action) {
                    dispatch_sync(new ImportRepositoryMonths($repository));

                    $action->success();
                }),
        ];
    }
}
