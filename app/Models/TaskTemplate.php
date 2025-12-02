<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class TaskTemplate extends Model
{
    use HasFactory;


    protected $table = 'task_templates';


    protected $fillable = ['name', 'slug', 'description', 'type', 'default_payload', 'default_recurrence', 'created_by', 'active'];


    protected $casts = [
        'default_payload' => 'array',
        'active' => 'boolean',
    ];


    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }


    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    public function scheduledTasks()
    {
        return $this->hasMany(ScheduledTask::class, 'template_id');
    }
}
