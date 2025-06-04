<?php

namespace App\Jobs;

use App\Console\Commands\CalculatePanelData05052025SingleCommand;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Carbon;

class CalculatePanelData05052025Job extends Job
{
    public function __construct(
        public Organization $organization,
        public ?CalculatePanelData05052025SingleCommand $command = null
    ) {}

    public function handle(): void
    {
        $repoTotas = $this
            ->organization
            ->repositories()
            ->select([
                new Expression('SUM(stargazer_count) as stargazer_count'),
                new Expression('SUM(watcher_count) as watcher_count'),
                new Expression('SUM(fork_count) as fork_count'),
                new Expression('COUNT(*) as repository_count'),
            ])
            ->first();

        $panelData = new Collection();

        foreach (Carbon::parse('2018-04-01')->startOfMonth()->toPeriod(Carbon::parse('2025-03-31')->endOfMonth(), '1 month') as $month) {
            if (
                $this
                    ->organization
                    ->panelData05052025()
                    ->where('year', $month->year)
                    ->where('month', $month->month)
                    ->exists()
            ) {
                $this->command?->info("Month already exists {$month->format('Y-m')}");

                continue;
            }

            $this->command?->info("Calculating for {$month->format('Y-m')}");

            $tagCount = $this
                ->organization
                ->tags()
                ->whereRaw("YEAR(target_committed_date) = {$month->year}")
                ->whereRaw("MONTH(target_committed_date) = {$month->month}")
                ->count();

            $repositoryCreatedCount = $this
                ->organization
                ->repositories()
                ->whereRaw("YEAR(created_at) = {$month->year}")
                ->whereRaw("MONTH(created_at) = {$month->month}")
                ->count();

            $repoMonthTotals = $this
                ->organization
                ->repositoryMonths()
                ->where('year', $month->year)
                ->where('month', $month->month)
                ->select([
                    new Expression('SUM(issue_created_count) as issue_created_count'),
                    new Expression('SUM(issue_closed_count) as issue_closed_count'),
                    new Expression('SUM(pull_request_created_count) as pull_request_created_count'),
                    new Expression('SUM(pull_request_merged_count) as pull_request_merged_count'),
                    new Expression('SUM(pull_request_closed_count) as pull_request_closed_count'),
                    new Expression('COUNT(DISTINCT repository_id) as repository_count'),
                ])
                ->first();

            $panelData[] = $this->organization->panelData05052025()->updateOrCreate([
                'year' => $month->year,
                'month' => $month->month,
            ], [
                'first_funding_date' => $this->organization->first_funding_date,
                'sponsorship_listing_date' => ($this->organization->wayback_machine_oldest_snapshot && $this->organization->first_funding_date)
                    ? ($this->organization->wayback_machine_oldest_snapshot->isAfter($this->organization->first_funding_date)
                        ? $this->organization->first_funding_date
                        : ($this->organization->wayback_machine_oldest_snapshot ?? $this->organization->first_funding_date)
                    )
                    : ($this->organization->wayback_machine_oldest_snapshot ?? $this->organization->first_funding_date),
                'new_funding_count' => $this
                    ->organization
                    ->sponsorships_as_maintainer
                    ->filter(function (array $sponsorship) use ($month) {
                        return Carbon::parse($sponsorship['createdAt'])->isSameMonth($month);
                    })
                    ->count(),
                'new_funding_onetime_count' => $this
                    ->organization
                    ->sponsorships_as_maintainer
                    ->filter(function (array $sponsorship) use ($month) {
                        return Carbon::parse($sponsorship['createdAt'])->isSameMonth($month) && $sponsorship['isOneTimePayment'];
                    })
                    ->count(),
                'new_funding_recurring_count' => $this
                    ->organization
                    ->sponsorships_as_maintainer
                    ->filter(function (array $sponsorship) use ($month) {
                        return Carbon::parse($sponsorship['createdAt'])->isSameMonth($month) && ! $sponsorship['isOneTimePayment'];
                    })
                    ->count(),
                'tag_count' => $tagCount,
                'stargazer_count' => $repoTotas->stargazer_count ?? 0,
                'watcher_count' => $repoTotas->watcher_count ?? 0,
                'fork_count' => $repoTotas->fork_count ?? 0,
                'repository_total_count' => $repoTotas->repository_count,
                'repository_count' => $repoMonthTotals->repository_count,
                'repository_created_count' => $repositoryCreatedCount,
                'issue_created_count' => $repoMonthTotals->issue_created_count ?? 0,
                'issue_closed_count' => $repoMonthTotals->issue_closed_count ?? 0,
                'pull_request_created_count' => $repoMonthTotals->pull_request_created_count ?? 0,
                'pull_request_merged_count' => $repoMonthTotals->pull_request_merged_count ?? 0,
                'pull_request_closed_count' => $repoMonthTotals->pull_request_closed_count ?? 0,
                //                'avg_activity_pre_treatment' => 999,
                //                'activity_relative_to_pre_treatment' => 999,
            ]);
        }
    }
}
