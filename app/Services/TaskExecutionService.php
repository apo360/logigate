<?php

namespace App\Services;

use App\Models\ScheduledTask;
use App\Models\TaskExecution;

class TaskExecutionService
{
public function startExecution(ScheduledTask $task, $executedBy = null, $workerId = null): TaskExecution
{
return TaskExecution::create([
'scheduled_task_id' => $task->id,
'attempt' => ($task->executions()->count() + 1),
'status' => TaskExecution::STATUS_RUNNING,
'started_at' => now(),
'executed_by' => $executedBy,
'worker_id' => $workerId,
]);
}


public function finishExecution(TaskExecution $execution, string $status, $result = null): TaskExecution
{
$execution->status = $status;
$execution->finished_at = now();
if ($result !== null) $execution->result = $result;
$execution->save();
return $execution;
}
}