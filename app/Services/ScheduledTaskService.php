<?php

namespace App\Services;

use App\Models\ScheduledTask;
use App\Jobs\GenerateInvoiceFromTask;
use App\Jobs\SendTaskNotification;
use Illuminate\Support\Facades\Log;

class ScheduledTaskService
{
    public function createTask(array $data): ScheduledTask
    {
        $task = ScheduledTask::create($data);

        // compute next_run_at immediately for recurring tasks
        $task->computeNextRun();

        return $task;
    }

    public function updateTask(ScheduledTask $task, array $data): ScheduledTask
    {
        $task->fill($data);
        $task->save();
        $task->computeNextRun();
        return $task;
    }

    public function approveTask(ScheduledTask $task, $userId): ScheduledTask
    {
        $task->approved = true;
        $task->approved_by = $userId;
        $task->approved_at = now();
        $task->save();
        return $task;
    }

    public function performTask(ScheduledTask $task)
    {
        // dispatch specialised jobs based on type
        switch ($task->type) {
            case ScheduledTask::TYPE_INVOICE:
                // push a job to generate invoices
                GenerateInvoiceFromTask::dispatch($task->id)->onQueue('tasks');
                return ['dispatched' => 'generate_invoices'];


            case ScheduledTask::TYPE_ALERT:
                // create a notification record and dispatch sending
                $notification = $task->notifications()->create([
                    'notification_type' => 'dashboard',
                    'recipient' => ['user_id' => $task->created_by],
                    'payload' => ['message' => $task->title],
                ]);


                SendTaskNotification::dispatch($notification->id)->onQueue('notifications');


                return ['dispatched' => 'notification'];


            case ScheduledTask::TYPE_BACKUP:
                // call backup logic (not implemented here)
                Log::info('Backup task executed: ' . $task->id);
                return ['backup' => 'ok'];


            case ScheduledTask::TYPE_PAYMENT:
                // implement payment logic or call external service
                return ['payment' => 'scheduled'];


            default:
                Log::warning('Unknown task type: ' . $task->type);
                return ['unknown' => true];
        }
    }

    public function generateInvoicesForTask(ScheduledTask $task)
    {
        // Example implementation stub: iterate payload and create invoices
        $payload = $task->payload ?? [];
    }
}
