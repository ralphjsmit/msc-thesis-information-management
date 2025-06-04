<?php

namespace App\Console\Commands;

use App\Jobs\UpdateOrCreateOrganization;
use App\Models\Organization;
use App\Services\GitHub;
use GraphQL\InlineFragment;
use GraphQL\Query;
use GraphQL\RawObject;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ImportOrganizationsCommand extends Command
{
    protected $signature = 'import-organizations {start=2008}';

    protected Collection $organizations;

    public function handle(): int
    {
        DB::disableQueryLog();

        $this->organizations = new Collection();

        $periodStart = Carbon::createFromFormat('Y', $this->argument('start'));

        foreach ($periodStart->toPeriod(now(), '1 year') as $year) {
            $this->extractOrganizations("type:org is:sponsorable created:{$year->startOfYear()->toDateString()}..{$year->endOfYear()->toDateString()}");
        }

        $this->info('Extracted ' . count($this->organizations) . ' organizations with sponsorships in total');

        $this->organizations->each(function (Organization $organization) {
            dispatch(new UpdateOrCreateOrganization($organization));
        });

        $this->info('Dispatched all jobs');

        return static::SUCCESS;
    }

    protected function extractOrganizations(string $query): void
    {
        $this->info("Extracting organizations with query: {$query}");

        $count = 0;

        $organizationsQuery = (new Query('search'))
            ->setArguments([
                'query' => $query,
                'first' => 100,
                'type' => new RawObject('USER'),
                'after' => null,
            ])
            ->setSelectionSet([
                'userCount',
                (new Query('pageInfo'))
                    ->setSelectionSet([
                        'startCursor',
                        'hasNextPage',
                        'endCursor',
                    ]),
                (new Query('nodes'))
                    ->setSelectionSet([
                        (new InlineFragment('Organization'))
                            ->setSelectionSet([
                                'id',
                                'name',
                                'login',
                                'hasSponsorsListing',
                            ]),
                    ]),
            ]);

        $responseBody = GitHub::make()->query($organizationsQuery);

        $endCursor = null;

        do {
            if ($endCursor) {
                $responseBody = GitHub::make()->query(
                    $organizationsQuery->setArguments([...invade($organizationsQuery)->arguments, 'after' => $endCursor])
                );
            }

            foreach ($responseBody->data->search->nodes as $node) {
                $count++;

                if ($this->organizations->pluck('login')->contains($node->login)) {
                    continue;
                }

                $organization = Organization::updateOrCreate([
                    'git_hub_id' => $node->id,
                    'login' => $node->login,
                ], [
                    'has_sponsors_listing' => $node->hasSponsorsListing,
                ]);

                $this->organizations[] = $organization;
            }

            $endCursor = $responseBody->data->search->pageInfo->endCursor;
        } while ($responseBody->data->search->pageInfo->hasNextPage);

        $this->info("Dispatched {$count} jobs for [{$query}]");
    }
}
