<?php

namespace App\Jobs;

use App\Jobs\Middleware\RateLimitGitHubApiRequests;
use App\Models\Repository;
use App\Services\GitHub;
use GraphQL\InlineFragment;
use GraphQL\Query;

class ImportRepositoryTags extends Job
{
    public int $backoff = 960; // 16 min

    public function __construct(
        public Repository $repository,
    ) {
        $this->onQueue(static::QUEUE_REPOSITORY_TAGS);
    }

    public function handle(): void
    {
        if ($this->repository->tags_imported_at) {
            return;
        }

        $tags = GitHub::make()->paginatedQueryFromParent(
            'repository',
            ['owner' => $this->repository->organization->login, 'name' => $this->repository->name],
            (new Query('refs'))
                ->setArguments([
                    'refPrefix' => 'refs/tags/',
                ])
                ->setSelectionSet([
                    (new Query('nodes'))
                        ->setSelectionSet([
                            'name',
                            (new Query('target'))
                                ->setSelectionSet([
                                    (new InlineFragment('Commit'))
                                        ->setSelectionSet([
                                            'committedDate',
                                            'oid',
                                        ]),
                                ]),
                        ]),
                ])
        )['nodes'];

        foreach ($tags as $tag) {
            if (! $tag['target']) {
                continue;
            }

            $this->repository->tags()->updateOrCreate([
                'name' => $tag['name'],
            ], [
                'target_committed_date' => $tag['target']['committedDate'],
                'target_oid' => $tag['target']['oid'],
            ]);
        }

        $this->repository->touch('tags_imported_at');
    }

    public function middleware(): array
    {
        return [
            new RateLimitGitHubApiRequests(),
        ];
    }
}
