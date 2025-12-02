<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;


class ScheduledTask extends Model
{
    use HasFactory;


    public const EXECUTOR_USER = 'user';
    public const EXECUTOR_AI = 'ai';
    public const EXECUTOR_SYSTEM = 'system';


    public const TYPE_INVOICE = 'invoice';
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_ALERT = 'alert';
    public const TYPE_BACKUP = 'backup';
    public const TYPE_CUSTOM = 'custom';


    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';


    public const REC_NONE = 'none';
    public const REC_DAILY = 'daily';
    public const REC_WEEKLY = 'weekly';
    public const REC_MONTHLY = 'monthly';
    public const REC_YEARLY = 'yearly';


    protected $table = 'scheduled_tasks';


    protected $fillable = [
        'uuid',
        'title',
        'description',
        'executor_type',
        'type',
        'status',
        'schedule_date',
        'next_run_at',
        'recurrence',
        'recurrence_rule',
        'payload',
        'metadata',
        'created_by',
        'approved',
        'approved_by',
        'approved_at',
    ];


    protected $casts = [
        'schedule_date' => 'datetime',
        'next_run_at' => 'datetime',
        'approved_at' => 'datetime',
        'payload' => 'array',
        'metadata' => 'array',
        'approved' => 'boolean',
    ];


    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->status)) {
                $model->status = self::STATUS_PENDING;
            }
        });
    }


    // Relations
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
