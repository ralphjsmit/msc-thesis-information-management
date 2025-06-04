<?php

namespace App\Jobs;

use App\Jobs\Middleware\RateLimitGitHubApiRequests;
use App\Models\Repository;
use App\Services\GitHub;
use Carbon\CarbonImmutable;
use GraphQL\Query;
use GraphQL\RawObject;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ImportRepositoryMonths extends Job
{
    public int $backoff = 960; // 16 min

    public function __construct(
        public Repository $repository
    ) {}

    public function handle(): void
    {
        if ($this->repository->repository_months_imported_at) {
            return;
        }

        $selectionSet = new Collection();

        $start = $this->repository->created_at;

        if ($start->isBefore($startOf2018 = Carbon::parse('2018-01-01 00:00:00'))) {
            $start = $startOf2018;
        }

        foreach ($start->toImmutable()->startOfMonth()->toPeriod(now(), '1 month') as $month) {
            $selectionSet = $selectionSet->merge([
                $this->getQuery('issue_created_count', $month, 'is:issue', 'created'),
                $this->getQuery('issue_closed_count', $month, 'is:issue', 'closed'),
                // Note: we could exclude `-author:app/dependabot`, but that slows the search endpoint very much down and causes timeouts.
                $this->getQuery('pull_request_created_count', $month, 'is:pr', 'created'),
                $this->getQuery('pull_request_merged_count', $month, 'is:pr is:merged', 'merged'),
                $this->getQuery('pull_request_closed_count', $month, 'is:pr is:closed -is:merged', 'closed'),
            ]);
        }

        $repositoryMonths = [];

        // The oldest repos have about 900 months, so chunk by 200 means we get about 5 requests.
        $selectionSet->chunk(200)->each(function (Collection $selectionSetChunk) use (&$repositoryMonths) {
            $query = (new Query())->setSelectionSet($selectionSetChunk->all());

            $data = (array) GitHub::make()->query($query)->data;

            foreach ($data as $queryAlias => $issueCountObject) {
                [$columnName, $period] = explode('_PERIOD_', $queryAlias);

                $repositoryMonths[$period][$columnName] = $issueCountObject->issueCount;
            }
        });

        foreach ($repositoryMonths as $month => $columns) {
            [$year, $month] = explode('_', $month);

            $this->repository->repositoryMonths()->updateOrCreate([
                'year' => $year,
                'month' => $month,
            ], $columns);
        }

        $this->repository->touch('repository_months_imported_at');
    }

    protected function getQuery(string $prefix, CarbonImmutable $month, string $query, string $queryTimeProperty = 'created'): Query
    {
        $alias = "{$prefix}_PERIOD_{$month->year}_{$month->month}";

        return (new Query("{$alias}: search"))
            ->setArguments([
                'query' => "repo:{$this->repository->organization->login}/{$this->repository->name} {$query} {$queryTimeProperty}:{$month->startOfMonth()->toDateString()}..{$month->endOfMonth()->toDateString()}",
                'first' => 1,
                'type' => new RawObject('ISSUE'),
            ])
            ->setSelectionSet(['issueCount']);
    }

    public function middleware(): array
    {
        return [
            new RateLimitGitHubApiRequests(),
        ];
    }
}
