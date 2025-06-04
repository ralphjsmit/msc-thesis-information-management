<?php

namespace App\Filament\Resources\Organizations\Pages;

use App\Filament\Resources\Organizations\OrganizationResource;
use App\Jobs\UpdateOrCreateOrganization;
use App\Models\Organization;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewOrganization extends ViewRecord
{
    protected static string $resource = OrganizationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import_organization')
                ->label('Import organization')
                ->icon(Heroicon::ArrowPath)
                ->successNotificationTitle('Import triggered')
                ->action(function (Organization $organization, Action $action, self $livewire) {
                    dispatch_sync(new UpdateOrCreateOrganization($organization));

                    $action->success();

                    $livewire->redirect($livewire->getResourceUrl('view', ['record' => $organization]));
                }),
        ];
    }
}
