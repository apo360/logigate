<?php

namespace App\Observers;

use App\Models\Exportador;
use App\Support\TenantContext;

class ExportadorObserver
{
    public function creating(Exportador $exportador): void
    {
        if (! empty($exportador->ExportadorID)) {
            return;
        }

        $empresaId = TenantContext::empresaId();

        if (! $empresaId) {
            return;
        }

        $exportador->ExportadorID = $exportador->generateExportadorID($empresaId);
    }
}
