<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskRecurrence extends Model
{
    use HasFactory;

    protected $table = 'task_recurrences';

    protected $fillable = ['scheduled_task_id', 'rrule', 'timezone', 'end_date'];

    protected $casts = [
        'end_date' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(ScheduledTask::class, 'scheduled_task_id');
    }
}
