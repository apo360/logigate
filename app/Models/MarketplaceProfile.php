<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceProfile extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'published' => 'boolean', 'service_provider' => 'boolean',
        'history_authorized' => 'boolean', 'history_reviewed_at' => 'datetime',
        'calculated_at' => 'datetime',
    ];
}
