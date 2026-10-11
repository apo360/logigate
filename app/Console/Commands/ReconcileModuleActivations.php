<?php

namespace App\Console\Commands;

use App\Services\ModuleReconciliation;
use Illuminate\Console\Command;

class ReconcileModuleActivations extends Command
{
    protected $signature = 'modules:reconcile {empresa : Empresa a analisar} {--apply= : Manifest JSON previamente revisto}';
    protected $description = 'Simular vínculos legados; aplicar apenas correspondências comprovadas num manifest';

    public function handle(ModuleReconciliation $service): int
    {
        $empresa = filter_var($this->argument('empresa'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (! $empresa) {
            $this->error('Indique uma empresa válida.');
            return self::FAILURE;
        }
        if ($path = $this->option('apply')) {
            $manifest = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
            if (($manifest['empresa_id'] ?? null) !== $empresa) {
                $this->error('Empresa diferente no manifest.');
                return self::FAILURE;
            }
            $this->info('Vínculos aplicados: '.$service->apply($manifest));
        } else {
            $this->line(json_encode($service->preview($empresa), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        }

        return self::SUCCESS;
    }
}
