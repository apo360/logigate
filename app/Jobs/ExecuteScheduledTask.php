<?php

namespace App\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\ScheduledTask;
use App\Models\TaskExecution;
use App\Services\ScheduledTaskService;
use App\Services\TaskExecutionService;
use Exception;
use Illuminate\Support\Facades\Auth;

class ExecuteScheduledTask implements ShouldQueue
{
    use \Illuminate\Foundation\Bus\Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use InteractsWithQueue, Queueable, SerializesModels;

    public $scheduledTaskId;

    public $tries = 3;
    public $backoff = [60, 300]; // seconds

    public function __construct($scheduledTaskId)
    {
        $this->scheduledTaskId = $scheduledTaskId;
    }

    public function handle(ScheduledTaskService $taskService, TaskExecutionService $executionService)
    {
        $task = ScheduledTask::find($this->scheduledTaskId);
        if (!$task) {
            Log::warning("ScheduledTask {$this->scheduledTaskId} not found");
            return;
        }

        // Start execution record
        $execution = $executionService->startExecution($task, Auth::user()?->id ?? null, $this->job?->getJobId() ?? null);

        try {
            // Delegate to service to perform the actual action
            $result = $taskService->performTask($task);

            $executionService->finishExecution($execution, TaskExecution::STATUS_SUCCESS, $result);

            // If recurrence, compute next run
            $task->computeNextRun();


            // Mark task as completed (unless recurrence wants pending)
            if ($task->recurrence === ScheduledTask::REC_NONE) {
                $task->status = ScheduledTask::STATUS_COMPLETED;
                $task->executed_at = now();
                $task->save();
            } else {
                // for recurring tasks: set status back to pending and set next_run_at
                $task->status = ScheduledTask::STATUS_PENDING;
                $task->save();
            }
        } catch (Exception $e) {
            Log::error('ExecuteScheduledTask failed: ' . $e->getMessage());

            $executionService->finishExecution($execution, TaskExecution::STATUS_FAILED, ['error' => $e->getMessage()]);

            $task->status = ScheduledTask::STATUS_FAILED;
            $task->save();

            // rethrow to let the queue attempt retry if configured
            throw $e;
        }
    }
}
