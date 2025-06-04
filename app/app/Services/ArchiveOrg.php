<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class ArchiveOrg
{
    public function __construct(
        public PendingRequest $pendingRequest
    ) {}

    public static function make(): static
    {
        $pendingRequest = Http::baseUrl('https://archive.org')
            ->throw();

        return new static($pendingRequest);
    }

    public function getClosestSnapshot(string $url, Carbon $around): Collection
    {
        return $this
            ->pendingRequest
            ->get('wayback/available', [
                'url' => $url,
                'timestamp' => $around->format('Ymd'),
            ])
            ->collect();
    }
}
