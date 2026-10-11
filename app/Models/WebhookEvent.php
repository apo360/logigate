<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'provider',
        'event_id',
        'processed_at',
        'payload', 'status', 'attempts', 'last_error', 'received_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'payload' => 'array', 'received_at' => 'datetime', 'attempts' => 'integer',
    ];
}
