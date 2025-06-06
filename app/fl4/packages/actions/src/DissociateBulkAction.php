<?php

namespace Filament\Actions;

use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Throwable;

class DissociateBulkAction extends BulkAction
{
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'dissociate';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-actions::dissociate.multiple.label'));

        $this->modalHeading(fn (): string => __('filament-actions::dissociate.multiple.modal.heading', ['label' => $this->getTitleCasePluralModelLabel()]));

        $this->modalSubmitActionLabel(__('filament-actions::dissociate.multiple.modal.actions.dissociate.label'));

        $this->successNotificationTitle(__('filament-actions::dissociate.multiple.notifications.dissociated.title'));

        $this->color('danger');

        $this->icon(FilamentIcon::resolve('actions::dissociate-action') ?? Heroicon::XMark);

        $this->requiresConfirmation();

        $this->modalIcon(FilamentIcon::resolve('actions::dissociate-action.modal') ?? Heroicon::OutlinedXMark);

        $this->action(function (): void {
            $this->process(function (DissociateBulkAction $action, Collection $records, Table $table): void {
                $isFirstException = true;

                $records->each(function (Model $record) use ($action, &$isFirstException, $table): void {
                    try {
                        /** @var BelongsTo $inverseRelationship */
                        $inverseRelationship = $table->getInverseRelationshipFor($record);

                        $inverseRelationship->dissociate();
                        $record->save();
                    } catch (Throwable $exception) {
                        $action->reportBulkProcessingFailure();

                        if ($isFirstException) {
                            // Only report the first exception so as to not flood error logs. Even
                            // if Filament did not catch exceptions like this, only the first
                            // would be reported as the rest of the process would be halted.
                            report($exception);

                            $isFirstException = false;
                        }
                    }
                });
            });
        });

        $this->deselectRecordsAfterCompletion();
    }
}
