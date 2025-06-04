<?php

namespace App\Filament\Resources\Organizations\Pages;

use App\Console\Commands\ImportOrganizationsCommand;
use App\Filament\Resources\Organizations\OrganizationResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;

class ListOrganizations extends ListRecords
{
    protected static string $resource = OrganizationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import_organizations')
                ->label('Import')
                ->successNotificationTitle('Jobs dispatched')
                ->color('gray')
                ->icon(Heroicon::ArrowPath)
                ->action(function (Action $action) {
                    Artisan::call(ImportOrganizationsCommand::class);

                    $action->success();
                }),
        ];
    }
}
