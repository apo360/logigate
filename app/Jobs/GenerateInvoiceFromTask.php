<?php

// GenerateInvoiceFromTask (example specialised job)
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ScheduledTask;
use App\Services\ScheduledTaskService;
use Illuminate\Support\Facades\Log;

class GenerateInvoiceFromTask implements ShouldQueue
{
    use \Illuminate\Foundation\Bus\Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use InteractsWithQueue, Queueable, SerializesModels;

    public $taskId;

    public function __construct($taskId)
    {
        $this->taskId = $taskId;
    }

    public function handle(ScheduledTaskService $service)
    {
        $task = ScheduledTask::find($this->taskId);
        if (!$task) return;

        // Delegate generation to service (which knows how to create invoices from payload)
        $service->generateInvoicesForTask($task);

        Log::info('GenerateInvoiceFromTask done for task ' . $this->taskId);
    }

    // Criar o dispatch
}
