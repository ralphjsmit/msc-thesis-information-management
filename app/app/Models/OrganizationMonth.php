<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationMonth extends Model
{
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'is_treated' => 'boolean',
            'is_post_event' => 'boolean',
            'first_funding_date' => 'date',
            'issue_created_count' => 'integer',
            'issue_closed_count' => 'integer',
            'pull_request_created_count' => 'integer',
            'pull_request_merged_count' => 'integer',
            'pull_request_closed_count' => 'integer',
            'tag_count' => 'integer',
        ];
    }
}
