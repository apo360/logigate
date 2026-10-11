<?php

namespace App\Console\Commands;

use App\Services\BillingPlanReview;
use Illuminate\Console\Command;

class ReviewBillingPlans extends Command
{
    protected $signature = 'billing:review-plans {--json}';

    protected $description = 'Read-only review of commercial plans, announced limits and module mappings.';

    public function handle(BillingPlanReview $review): int
    {
        $report = $review->report();
        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            $this->table(['Plano', 'Estado', 'Classificação', 'Módulos', 'Revisão'], array_map(fn ($plan) => [$plan['name'], $plan['status'], $plan['classification'], count($plan['module_ids']), implode(', ', $plan['issues'])], $report['plans']));
        }

        return self::SUCCESS;
    }
}
