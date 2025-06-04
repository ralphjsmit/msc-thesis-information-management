<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class WebArchiveOrg
{
    public function __construct(
        public PendingRequest $pendingRequest
    ) {}

    public static function make(): static
    {
        $pendingRequest = Http::baseUrl('https://web.archive.org')
            ->throw();

        return new static($pendingRequest);
    }

    public function search(string $url): string
    {
        return $this
            ->pendingRequest
            ->get('cdx/search/cdx', [
                'url' => $url,
                'limit' => 1,
            ])
            ->body();
    }
}
