<?php

namespace App\Console\Commands;

use GraphQL\Client;
use GraphQL\InlineFragment;
use GraphQL\Query;
use GraphQL\RawObject;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Extraction2OrganizationsSponsorFieldsCommand extends Command
{
    protected $signature = 'extraction-2';

    public function handle(): int
    {
        foreach (Carbon::createFromFormat('Y', '2008')->toPeriod(now(), '1 year') as $year) {
            $this->extractOrganizations("type:org is:sponsorable created:{$year->startOfYear()->toDateString()}..{$year->endOfYear()->toDateString()}");
        }

        // foreach (range('A', 'Z') as $letter) {
        //     $this->extractOrganizations("type:org is:sponsorable {$letter}");
        // }

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
                                'name',
                                'login',
                            ]),
                    ]),
            ]);

        $client = $this->getClient();

        $responseBody = $client->runQuery($organizationsQuery)->getResults();

        $endCursor = null;

        $existingLogins = DB::table('extraction_2_organizations_sponsor_fields')->pluck('login');

        do {
            if ($endCursor) {
                $responseBody = $client
                    ->runQuery(
                        $organizationsQuery->setArguments([...invade($organizationsQuery)->arguments, 'after' => $endCursor])
                    )
                    ->getResults();
            }

            foreach ($responseBody->data->search->nodes as $node) {
                $count++;

                if ($existingLogins->contains($node->login)) {
                    // $this->info("[{$node->login}] already extracted");

                    continue;
                }

                $this->extractOrganization($node->login);
            }

            $endCursor = $responseBody->data->search->pageInfo->endCursor;
        } while ($responseBody->data->search->pageInfo->hasNextPage);

        $this->info("Counted {$count} organizations for [{$query}]");
    }

    protected function extractOrganization(string $login): void
    {
        $client = $this->getClient();

        $query = (new Query('organization'))
            ->setArguments(['login' => $login])
            ->setSelectionSet([
                'id',
                'name',
                'login',
                'hasSponsorsListing',
            ]);

        $organization = json_decode(json_encode($client->runQuery($query)->getData()), true)['organization'];

        $sponsoring = $this->extractOrganizationPaginated(
            $login,
            (new Query('sponsoring'))
                ->setSelectionSet([
                    (new Query('edges'))
                        ->setSelectionSet([
                            (new Query('node'))
                                ->setSelectionSet([
                                    (new InlineFragment('User'))
                                        ->setSelectionSet([
                                            'name',
                                            'login',
                                        ]),
                                    (new InlineFragment('Organization'))
                                        ->setSelectionSet([
                                            'name',
                                            'login',
                                        ]),
                                ]),
                        ]),
                    (new Query('pageInfo'))
                        ->setSelectionSet([
                            'startCursor',
                            'hasPreviousPage',
                            'endCursor',
                            'hasNextPage',
                        ]),
                ]),
        )['edges'];

        $sponsors = $this->extractOrganizationPaginated(
            $login,
            (new Query('sponsors'))
                ->setSelectionSet([
                    (new Query('edges'))
                        ->setSelectionSet([
                            (new Query('node'))
                                ->setSelectionSet([
                                    (new InlineFragment('User'))
                                        ->setSelectionSet([
                                            'name',
                                            'login',
                                        ]),
                                    (new InlineFragment('Organization'))
                                        ->setSelectionSet([
                                            'name',
                                            'login',
                                        ]),
                                ]),
                        ]),
                    (new Query('pageInfo'))
                        ->setSelectionSet([
                            'startCursor',
                            'hasPreviousPage',
                            'endCursor',
                            'hasNextPage',
                        ]),
                ]),
        )['edges'];

        $sponsorshipsAsMaintainer = $this->extractOrganizationPaginated(
            $login,
            (new Query('sponsorshipsAsMaintainer'))
                ->setArguments(['activeOnly' => false]) // See past sponsorships...
                ->setSelectionSet([
                    (new Query('nodes'))
                        ->setSelectionSet([
                            'id',
                            'createdAt',
                            'isActive',
                            'isOneTimePayment',
                            'isSponsorOptedIntoEmail',
                            'privacyLevel',
                            'tierSelectedAt',
                        ]),
                    (new Query('pageInfo'))
                        ->setSelectionSet([
                            'startCursor',
                            'hasPreviousPage',
                            'endCursor',
                            'hasNextPage',
                        ]),
                ]),
        )['nodes'];

        $sponsorshipsAsSponsor = $this->extractOrganizationPaginated(
            $login,
            (new Query('sponsorshipsAsSponsor'))
                ->setArguments(['activeOnly' => false])
                ->setSelectionSet([
                    (new Query('nodes'))
                        ->setSelectionSet([
                            'id',
                            'createdAt',
                            'isActive',
                            'isOneTimePayment',
                            'isSponsorOptedIntoEmail',
                            'privacyLevel',
                            'tierSelectedAt',
                        ]),
                    (new Query('pageInfo'))
                        ->setSelectionSet([
                            'startCursor',
                            'hasPreviousPage',
                            'endCursor',
                            'hasNextPage',
                        ]),
                ]),
        )['nodes'];

        DB::table('extraction_2_organizations_sponsor_fields')->updateOrInsert([
            'login' => $organization['login'],
        ], [
            'gitHubId' => $organization['id'],
            'name' => $organization['name'],
            'login' => $organization['login'],
            'hasSponsorsListing' => $organization['hasSponsorsListing'],
            'sponsoring' => json_encode($sponsoring),
            'sponsors' => json_encode($sponsors),
            'sponsorshipsAsMaintainer' => json_encode($sponsorshipsAsMaintainer),
            'sponsorshipsAsSponsor' => json_encode($sponsorshipsAsSponsor),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function extractOrganizationPaginated(string $login, Query $query, ?string $startCursor = null): array
    {
        $client = $this->getClient();

        $fieldName = invade($query)->fieldName;

        $paginationQuery = (new Query('organization'))
            ->setArguments(['login' => $login])
            ->setSelectionSet([
                $query->setArguments(
                    [...invade($query)->arguments, 'last' => 100, ...$startCursor ? ['before' => $startCursor] : []]
                ),
            ]);

        $responseBody = $client->runQuery($paginationQuery)->getResults();

        $data = json_decode(json_encode($responseBody->data->organization->{$fieldName}, true), true);

        if ($responseBody->data->organization->{$fieldName}->pageInfo->hasPreviousPage) {
            $pageInfoStartCursor = $responseBody->data->organization->{$fieldName}->pageInfo->startCursor;

            $data = array_merge_recursive(
                $data,
                $this->extractOrganizationPaginated($login, $query, $pageInfoStartCursor)
            );
        }

        return $data;
    }

    protected function getClient(): Client
    {
        return new Client(
            config('services.git_hub.endpoint'),
            ['Authorization' => 'Bearer ' . config('services.git_hub.personal_access_token')]
        );
    }
}
