<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TaskExecution extends Model
{
    use HasFactory;


    public const STATUS_RUNNING = 'running';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';


    protected $table = 'task_executions';


    protected $fillable = [
        'scheduled_task_id',
        'attempt',
        'status',
        'started_at',
        'finished_at',
        'executed_by',
        'worker_id',
        'duration_ms',
        'result',
        'logs'
    ];


    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'result' => 'array',
    ];


    // Relations
    public function task()
    {
        return $this->belongsTo(ScheduledTask::class, 'scheduled_task_id');
    }


    public function executor()
    {
        return $this->belongsTo(User::class, 'executed_by');
    }
}
