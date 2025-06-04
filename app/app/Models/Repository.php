<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Repository extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'stargazer_count' => 'integer',
            'watcher_count' => 'integer',
            'languages' => 'collection',
            'has_issues_enabled' => 'boolean',
            'has_projects_enabled' => 'boolean',
            'has_wiki_enabled' => 'boolean',
            'has_discussions_enabled' => 'boolean',
            'fork_count' => 'integer',
            'is_archived' => 'boolean',
            'is_disabled' => 'boolean',
            'topics' => 'collection',
            'tags_imported_at' => 'immutable_datetime',
            'repository_months_imported_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'git_hub_organization_id', 'git_hub_id');
    }

    public function repositoryMonths(): HasMany
    {
        return $this->hasMany(RepositoryMonth::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }
}
