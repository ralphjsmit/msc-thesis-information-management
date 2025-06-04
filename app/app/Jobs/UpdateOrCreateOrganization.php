<?php

namespace App\Jobs;

use App\Jobs\Middleware\RateLimitGitHubApiRequests;
use App\Models\Organization;
use App\Services\GitHub;
use Arr;
use GraphQL\InlineFragment;
use GraphQL\Query;

class UpdateOrCreateOrganization extends Job
{
    public int $backoff = 65; // 65 seconds

    public function __construct(
        public Organization $organization,
    ) {}

    public function handle(): void
    {
        $this->updateOrCreateOrganization();
        $this->updateOrCreateRepositories();
    }

    protected function updateOrCreateOrganization(): void
    {
        if ($this->organization->sponsors_imported_at) {
            return;
        }

        $sponsors = GitHub::make()->paginatedQueryFromParent(
            'organization',
            ['login' => $this->organization->login],
            (new Query('sponsors'))
                ->setSelectionSet([
                    (new Query('nodes'))
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
        )['nodes'];

        $sponsorshipsAsMaintainer = GitHub::make()->paginatedQueryFromParent(
            'organization',
            ['login' => $this->organization->login],
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
                ]),
        )['nodes'];

        $this->organization->sponsors = $sponsors;
        $this->organization->sponsorships_as_maintainer = $sponsorshipsAsMaintainer;
        $this->organization->sponsors_imported_at = now();
        $this->organization->save();
    }

    protected function updateOrCreateRepositories(): void
    {
        if ($this->organization->repositories_imported_at) {
            return;
        }

        $repositories = GitHub::make()->paginatedQueryFromParent(
            'organization',
            ['login' => $this->organization->login],
            (new Query('repositories'))
                ->setSelectionSet([
                    (new Query('nodes'))
                        ->setSelectionSet([
                            'id',
                            'name',
                            'stargazerCount',
                            (new Query('languages'))
                                ->setArguments(['first' => 100])
                                ->setSelectionSet([
                                    (new Query('nodes'))
                                        ->setSelectionSet([
                                            'name',
                                        ]),
                                ]),
                            'hasIssuesEnabled',
                            'hasProjectsEnabled',
                            'hasWikiEnabled',
                            'hasDiscussionsEnabled',
                            'forkCount',
                            'isArchived',
                            'isDisabled',
                            'createdAt',
                            (new Query('watchers'))
                                ->setSelectionSet([
                                    'totalCount',
                                ]),
                            (new Query('repositoryTopics'))
                                ->setArguments(['first' => 100])
                                ->setSelectionSet([
                                    (new Query('nodes'))
                                        ->setSelectionSet([
                                            (new Query('topic'))
                                                ->setSelectionSet([
                                                    'name',
                                                ]),
                                        ]),
                                ]),
                        ]),
                ]),
        );

        foreach ($repositories['nodes'] as $repository) {
            $repository = $this->organization->repositories()->updateOrCreate([
                'git_hub_id' => $repository['id'],
            ], [
                'name' => $repository['name'],
                'stargazer_count' => $repository['stargazerCount'],
                'watcher_count' => $repository['watchers']['totalCount'],
                'languages' => Arr::pluck($repository['languages']['nodes'], 'name'),
                'has_issues_enabled' => $repository['hasIssuesEnabled'],
                'has_projects_enabled' => $repository['hasProjectsEnabled'],
                'has_wiki_enabled' => $repository['hasWikiEnabled'],
                'has_discussions_enabled' => $repository['hasDiscussionsEnabled'],
                'fork_count' => $repository['forkCount'],
                'is_archived' => $repository['isArchived'],
                'is_disabled' => $repository['isDisabled'],
                'topics' => Arr::pluck($repository['repositoryTopics']['nodes'], 'topic.name'),
                'created_at' => $repository['createdAt'],
            ]);
        }

        $this->organization->touch('repositories_imported_at');
    }

    //    public function uniqueId(): string
    //    {
    //        return $this->organization->login;
    //    }

    public function middleware(): array
    {
        return [
            new RateLimitGitHubApiRequests(),
        ];
    }
}
