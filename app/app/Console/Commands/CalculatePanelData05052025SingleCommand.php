<?php

namespace App\Console\Commands;

use App\Jobs\CalculatePanelData05052025Job;
use App\Models\Organization;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CalculatePanelData05052025SingleCommand extends Command
{
    protected $signature = 'calculate-panel-data-05-missing';

    public function handle(): int
    {
        DB::disableQueryLog();

        $ids = [
            8405,
            8355,
            7912,
            7818,
            7802,
            7583,
            7580,
            7579,
            7573,
            7568,
            7555,
            7452,
            7349,
            7290,
            7260,
            7247,
            7199,
            7179,
            7107,
            7085,
            6911,
            6810,
            6767,
            6591,
            6504,
            6250,
            6244,
            6231,
            6047,
            6039,
            5977,
            5973,
            5956,
            5951,
            5945,
            5687,
            5673,
            5661,
            5653,
            5384,
            602,
            599,
        ];

        $organizations = Organization::findMany(array_reverse($ids));

        $this->info('Calculating panel data for ' . $organizations->count() . ' organizations');

        $this->withProgressBar($organizations, function (Organization $organization) {
            $job = new CalculatePanelData05052025Job($organization, $this);

            $job->handle();
        });

        return static::SUCCESS;
    }
}
