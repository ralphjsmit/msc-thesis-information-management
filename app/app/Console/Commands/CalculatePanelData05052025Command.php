<?php

namespace App\Console\Commands;

use App\Jobs\CalculatePanelData05052025Job;
use App\Models\Organization;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CalculatePanelData05052025Command extends Command
{
    protected $signature = 'calculate-panel-data-05';

    public function handle(): int
    {
        DB::disableQueryLog();

        $query = Organization::query()
            ->whereHas('repositories', function (Builder $query) {
                return $query->whereNotNull('repository_months_imported_at');
            });

        $this->info('Calculating panel data for ' . $query->count() . ' organizations');

        $this->withProgressBar($query->lazyById(), function (Organization $organization) {
            dispatch(new CalculatePanelData05052025Job($organization));
        });

        return static::SUCCESS;
    }
}
