<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected function casts(): array
    {
        return [
            'target_committed_date' => 'immutable_datetime',
        ];
    }
}
