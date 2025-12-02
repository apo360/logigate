<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskEvent extends Model
{
    use HasFactory;

    protected $table = 'task_events';

    protected $fillable = ['name', 'description', 'payload_schema'];

    protected $casts = [
        'payload_schema' => 'array',
    ];
}
