<?php

namespace App\Jobs\Middleware;

use App\Jobs\Job;
use App\Models\GitHubApiRequest;
use Closure;

class RateLimitGitHubApiRequests
{
    public function handle(Job $job, Closure $next): void
    {
        $count = GitHubApiRequest::where('created_at', '>', now()->subHour())->count();

        if ($count > 4900) {
            $job->release(now()->addMinutes(16));
        } else {
            $next($job);
        }
    }
}
