<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

abstract class Job implements ShouldQueue
{
    use Batchable;
    use Queueable;
    use SerializesModels;

    public const QUEUE_DEFAULT = 'default';

    public const QUEUE_REPOSITORY_TAGS = 'repository_tags';

    public const QUEUE_WAYBACK_MACHINE = 'wayback_machine';

    public function retryUntil(): Carbon
    {
        return now()->addWeek();
    }
}
