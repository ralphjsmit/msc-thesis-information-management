<?php

namespace App\Console\Commands;

use App\Jobs\CalculatePanelData02052025Job;
use App\Models\Organization;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CalculatePanelData02052025Command extends Command
{
    protected $signature = 'calculate-panel-data-02';

    public function handle(): int
    {
        DB::disableQueryLog();

        $query = Organization::query()
            ->whereHas('repositoryMonths')
            ->whereHas('tags')
            ->whereDoesntHave('panelData02052025');

        $this->info('Calculating panel data for ' . $query->count() . ' organizations');

        $this->withProgressBar($query->lazyById(), function (Organization $organization) {
            dispatch(new CalculatePanelData02052025Job($organization));
        });

        return static::SUCCESS;
    }
}
