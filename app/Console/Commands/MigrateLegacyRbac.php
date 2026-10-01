<?php

namespace App\Console\Commands;

use App\Application\Empresa\Actions\ConsolidarLegacyRbacAction;
use Illuminate\Console\Command;

class MigrateLegacyRbac extends Command
{
    protected $signature = 'security:migrate-legacy-rbac';
    protected $description = 'Consolidate unambiguous legacy RBAC on the isolated testing database.';

    public function handle(ConsolidarLegacyRbacAction $action): int
    {
        try {
            $counts = $action->execute();
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
        foreach ($counts as $label => $count) $this->line("{$label}: {$count}");
        return $counts['Errors'] === 0 ? self::SUCCESS : self::FAILURE;
    }
}
