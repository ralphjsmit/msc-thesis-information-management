<?php

namespace App\Jobs;

use App\Models\Organization;
use App\Services\ArchiveOrg;
use Illuminate\Support\Carbon;

class ImportWaybackMachineOldestSnapshot extends Job
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

        $urls = [
            "https://github.com/sponsors/{$this->organization->login}",
            "http://github.com/sponsors/{$this->organization->login}",
        ];

        $oldestSnapshot = null;

        foreach ($urls as $url) {
            if ($oldestSnapshot) {
                continue;
            }

            $closestSnapshot = ArchiveOrg::make()->getClosestSnapshot(
                url: $url,
                around: Carbon::parse('2020-01-01')
            );

            if (isset($closestSnapshot['archived_snapshots']['closest'])) {
                $oldestSnapshot = Carbon::parse($closestSnapshot['archived_snapshots']['closest']['timestamp']);
            }
        }

        $this->organization->update([
            'wayback_machine_oldest_snapshot' => $oldestSnapshot,
            'wayback_machine_imported_at' => now(),
        ]);
    }
}
