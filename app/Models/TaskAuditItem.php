<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TaskAuditItem extends Model
{
    use HasFactory;

    protected $table = 'task_audit_items';
    protected $fillable = ['scheduled_task_id', 'execution_id', 'entity_type', 'entity_id', 'action', 'diff'];

    protected $casts = [
    'diff' => 'array',
    ];

    public function task()
    {
        return $this->belongsTo(ScheduledTask::class, 'scheduled_task_id');
    }

    public function execution()
    {
        return $this->belongsTo(TaskExecution::class, 'execution_id');
    }
}
