<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepositoryMonth extends Model
{
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'issue_created_count' => 'integer',
            'issue_closed_count' => 'integer',
            'pull_request_created_count' => 'integer',
            'pull_request_merged_count' => 'integer',
            'pull_request_closed_count' => 'integer',
        ];
    }
}
