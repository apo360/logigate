<?php

// AIRecommenderTask (schedules AI suggestions)

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\AISchedulerService;

class AIRecommenderTask implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;


    public function handle(AISchedulerService $ai)
    {
        // Analyze system and create suggestions (they will be created as scheduled_tasks with executor_type = 'ai' and approved=false)
        $ai->analyzeAndSuggest();
    }
}
