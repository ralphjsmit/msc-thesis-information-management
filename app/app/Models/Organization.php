<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Organization extends Model
{
    protected function casts(): array
    {
        return [
            'has_sponsors_listing' => 'boolean',
            'sponsors' => 'collection',
            'sponsorships_as_maintainer' => 'collection',
            'wayback_machine_oldest_snapshot' => 'datetime',
            'sponsors_imported_at' => 'datetime',
            'repositories_imported_at' => 'datetime',
            'wayback_machine_imported_at' => 'datetime',
            'first_funding_date' => 'immutable_datetime',
        ];
    }

    public function repositories(): HasMany
    {
        return $this->hasMany(Repository::class, 'git_hub_organization_id', 'git_hub_id');
    }

    public function repositoryMonths(): HasManyThrough
    {
        return $this->throughRepositories()->has('repositoryMonths');
    }

    public function tags(): HasManyThrough
    {
        return $this->throughRepositories()->has('tags');
    }

    public function panelData02052025(): HasMany
    {
        return $this->hasMany(PanelData02052025::class, 'organization_id', 'id');
    }

    public function panelData05052025(): HasMany
    {
        return $this->hasMany(PanelData05052025::class, 'organization_id', 'id');
    }
}
