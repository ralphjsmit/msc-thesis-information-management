<?php

namespace App\Services;

use App\Models\GitHubApiRequest;
use GraphQL\Client;
use GraphQL\Query;

class GitHub
{
    public function __construct(
        public Client $client
    ) {}

    public static function make(): static
    {
        $client = new Client(
            config('services.git_hub.endpoint'),
            ['Authorization' => 'Bearer ' . config('services.git_hub.personal_access_token')]
        );

        return new static($client);
    }

    public function query(Query $query): array | object
    {
        $result = $this->client->runQuery($query)->getResults();

        GitHubApiRequest::create();

        return $result;
    }

    public function paginatedQueryFromParent(string $parentFieldName, array $parentFieldArguments, Query $query, ?string $startCursor = null): array | object
    {
        $fieldName = invade($query)->fieldName;

        $paginationQuery = (new Query($parentFieldName))
            ->setArguments($parentFieldArguments)
            ->setSelectionSet([
                $query
                    ->setArguments([
                        ...invade($query)->arguments,
                        'last' => 100,
                        ...$startCursor ? ['before' => $startCursor] : [],
                    ])
                    ->setSelectionSet([
                        ...invade($query)->selectionSet,
                        (new Query('pageInfo'))
                            ->setSelectionSet([
                                'startCursor',
                                'hasPreviousPage',
                                'endCursor',
                                'hasNextPage',
                            ]),
                    ]),
            ]);

        $responseBody = $this->query($paginationQuery);

        $data = json_decode(json_encode($responseBody->data->{$parentFieldName}->{$fieldName}, true), true);

        if ($responseBody->data->{$parentFieldName}->{$fieldName}->pageInfo->hasPreviousPage) {
            $pageInfoStartCursor = $responseBody->data->{$parentFieldName}->{$fieldName}->pageInfo->startCursor;

            $data = array_merge_recursive(
                $data,
                $this->paginatedQueryFromParent($parentFieldName, $parentFieldArguments, $query, $pageInfoStartCursor)
            );
        }

        return $data;
    }
}
