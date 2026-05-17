<?php

namespace App\Services;

use App\Models\RegiaoAduaneira;

class ProcessoFormService
{
    public function processTypeCode(?int $tipoProcessoId): ?string
    {
        if (! $tipoProcessoId) {
            return null;
        }

        return RegiaoAduaneira::query()
            ->whereKey($tipoProcessoId)
            ->value('codigo');
    }

    public function shouldShowCrudExport(?int $tipoProcessoId): bool
    {
        return in_array($this->processTypeCode($tipoProcessoId), ['CRUD_EXPORT', 'CRUD'], true);
    }
}
