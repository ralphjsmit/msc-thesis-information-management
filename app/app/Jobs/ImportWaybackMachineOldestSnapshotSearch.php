<?php

namespace App\Jobs;

use App\Models\Organization;
use App\Services\WebArchiveOrg;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ImportWaybackMachineOldestSnapshotSearch extends Job
{
    public function __construct(
        public Organization $organization
    ) {
        $this->onQueue(static::QUEUE_WAYBACK_MACHINE);
    }

    public function handle(): void
    {
        if ($this->organization->wayback_machine_imported_at && $this->organization->wayback_machine_oldest_snapshot) {
            return;
        }

        $result = str(WebArchiveOrg::make()->search("github.com/sponsors/{$this->organization->login}"));

        if ($result->isEmpty()) {
            $this->organization->update([
                'wayback_machine_oldest_snapshot' => null,
                'wayback_machine_imported_at' => now(),
            ]);

            return;
        }

        $timestamp = $result
            ->lower()
            ->after(Str::lower("{$this->organization->login} "))
            ->before(' http');

        $oldestSnapshot = Carbon::parse($timestamp->toString());

        $this->organization->update([
            'wayback_machine_oldest_snapshot' => $oldestSnapshot,
            'wayback_machine_imported_at' => now(),
        ]);
    }
}
