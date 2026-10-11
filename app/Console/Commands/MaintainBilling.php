<?php

namespace App\Console\Commands;

use App\Services\SubscriptionMaintenance;
use Illuminate\Console\Command;

class MaintainBilling extends Command
{
    protected $signature = 'billing:maintain {--notices : Create in-app renewal notices}';

    protected $description = 'Recover signed events and paid activations, then mark expired subscriptions.';

    public function handle(SubscriptionMaintenance $maintenance): int
    {
        $result = $maintenance->reconcile();
        $result['expired'] = $maintenance->expire();
        if ($this->option('notices')) {
            $result['notices'] = $maintenance->renewalNotices();
        }
        $this->line(json_encode($result));

        return $result['failed'] ? self::FAILURE : self::SUCCESS;
    }
}
