<?php

namespace App\Console\Commands;

use App\Models\Organization;
use Illuminate\Console\Command;

class CalculateFirstFundingDate extends Command
{
    protected $signature = 'calculate-first-funding-date';

    public function handle(): int
    {
        $query = Organization::query()
            ->whereJsonLength('sponsorships_as_maintainer', '>', 0);

        $this->withProgressBar($query->get(), function (Organization $organization) {
            $firstFundingDate = $organization
                ->sponsorships_as_maintainer
                ->map(fn (array $sponsorship) => $sponsorship['createdAt'])
                ->sort();

            $organization->update([
                'first_funding_date' => $firstFundingDate->first(),
            ]);
        });

        return static::SUCCESS;
    }
}
