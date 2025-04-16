<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RecentSearch extends Model
{
    protected $fillable = [
        'user_id', 'field', 'value', 'model',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function searchable(): MorphTo
    {
        return $this->morphTo();
    }
}
