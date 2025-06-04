<?php

namespace App\Jobs;

use App\Models\Organization;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Carbon;

class CalculatePanelData02052025Job extends Job
{
    public function __construct(
        public Organization $organization
    ) {}

    public function handle(): void
    {
        $repoTotas = $this
            ->organization
            ->repositories()
            ->select([
                new Expression('SUM(stargazer_count) as stargazer_count'),
                new Expression('SUM(watcher_count) as watcher_count'),
            ])
            ->first();

        foreach (Carbon::parse('2018-04-01')->startOfMonth()->toPeriod(Carbon::parse('2025-03-31')->endOfMonth(), '1 month') as $month) {
            $tagCount = $this
                ->organization
                ->tags()
                ->whereRaw("YEAR(target_committed_date) = {$month->year}")
                ->whereRaw("MONTH(target_committed_date) = {$month->month}")
                ->count();

            $repoMonthTotals = $this
                ->organization
                ->repositoryMonths()
                ->where('month', $month->month)
                ->where('year', $month->year)
                ->select([
                    new Expression('SUM(issue_created_count) as issue_created_count'),
                    new Expression('SUM(issue_closed_count) as issue_closed_count'),
                    new Expression('SUM(pull_request_created_count) as pull_request_created_count'),
                    new Expression('SUM(pull_request_merged_count) as pull_request_merged_count'),
                    new Expression('SUM(pull_request_closed_count) as pull_request_closed_count'),
                ])
                ->first();

            $total = $this->organization->panelData02052025()->create([
                'year' => $month->year,
                'month' => $month->month,
                'first_funding_date' => $this->organization->first_funding_date,
                'tag_count' => $tagCount,
                'stargazer_count' => $repoTotas->stargazer_count ?? 0,
                'watcher_count' => $repoTotas->watcher_count ?? 0,
                'issue_created_count' => $repoMonthTotals->issue_created_count ?? 0,
                'issue_closed_count' => $repoMonthTotals->issue_closed_count ?? 0,
                'pull_request_created_count' => $repoMonthTotals->pull_request_created_count ?? 0,
                'pull_request_merged_count' => $repoMonthTotals->pull_request_merged_count ?? 0,
                'pull_request_closed_count' => $repoMonthTotals->pull_request_closed_count ?? 0,
            ]);
        }
    }
}
