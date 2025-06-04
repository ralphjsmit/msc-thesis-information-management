<?php

namespace App\Console\Commands;

use App\Jobs\ImportWaybackMachineOldestSnapshot;
use App\Models\Organization;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportWaybackMachineOldestSnapshotCommand extends Command
{
    protected $signature = 'import-wayback-machine-oldest-snapshot';

    public function handle(): int
    {
        DB::disableQueryLog();

        $query = Organization::query()
            ->where('has_sponsors_listing', true)
            ->whereNull('wayback_machine_oldest_snapshot');

        $this->info('Importing Wayback Machine snapshot dates for ' . $query->count() . ' organizations');

        $this->withProgressBar($query->lazyById(), function (Organization $organization) {
            dispatch(new ImportWaybackMachineOldestSnapshot($organization));
        });

        return static::SUCCESS;
    }
}
