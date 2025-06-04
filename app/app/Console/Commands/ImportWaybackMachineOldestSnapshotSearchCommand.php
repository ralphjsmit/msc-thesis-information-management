<?php

namespace App\Console\Commands;

use App\Jobs\ImportWaybackMachineOldestSnapshotSearch;
use App\Models\Organization;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportWaybackMachineOldestSnapshotSearchCommand extends Command
{
    protected $signature = 'import-wayback-machine-oldest-snapshot-search';

    public function handle(): int
    {
        DB::disableQueryLog();

        $query = Organization::query()
            ->whereNull('wayback_machine_oldest_snapshot');

        $this->info('Importing Wayback Machine snapshot dates for ' . $query->count() . ' organizations');

        $this->withProgressBar($query->lazyById(), function (Organization $organization) {
            dispatch(new ImportWaybackMachineOldestSnapshotSearch($organization));
        });

        return static::SUCCESS;
    }
}
