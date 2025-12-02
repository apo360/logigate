<?php

// =========================
// Scheduler Command + Kernel
// app/Console/Commands/DispatchDueTasks.php
// =========================

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScheduledTask;
use App\Jobs\ExecuteScheduledTask;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DispatchDueTasks extends Command
{
    protected $signature = 'tasks:dispatch-due';
    protected $description = 'Dispatch scheduled tasks that are due to the queue';

    public function handle()
    {
        $now = Carbon::now();

        $tasks = ScheduledTask::where('status', ScheduledTask::STATUS_PENDING)
            ->where(function ($q) use ($now) {
                $q->where('schedule_date', '<=', $now)
                    ->orWhereNotNull('next_run_at')->where('next_run_at', '<=', $now);
            })
            ->limit(200)
            ->get();

        foreach ($tasks as $task) {
            // mark as running optimistically to avoid double dispatch
            try {
                $task->markRunning();
                ExecuteScheduledTask::dispatch($task->id)->onQueue('tasks');
            } catch (\Exception $e) {
                Log::error('DispatchDueTasks error: ' . $e->getMessage());
            }
        }

        return 0;
    }
}
