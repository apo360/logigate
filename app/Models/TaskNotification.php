<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TaskNotification extends Model
{
    use HasFactory;


    protected $table = 'task_notifications';


    protected $fillable = ['scheduled_task_id', 'notification_type', 'recipient', 'sent_at', 'status', 'payload'];


    protected $casts = [
        'recipient' => 'array',
        'sent_at' => 'datetime',
        'payload' => 'array',
    ];


    public function task()
    {
        return $this->belongsTo(ScheduledTask::class, 'scheduled_task_id');
    }
}
