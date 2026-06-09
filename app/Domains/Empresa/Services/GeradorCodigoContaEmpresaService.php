<?php

namespace App\Domains\Empresa\Services;

use App\Models\Empresa;

final class GeradorCodigoContaEmpresaService
{
    public function gerar(): string
    {
        $total = Empresa::query()->count() + 1;

        return 'HYLGA' . str_pad((string) $total, 5, '0', STR_PAD_LEFT) . now()->year;
    }
}
