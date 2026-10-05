<?php

namespace App\Application\Mercadoria\Services;

use App\Models\Licenciamento;
use App\Models\Processo;

final class MercadoriaParentTotalsService
{
    /** Calculated values are projections; declared headers are never incremented by item writes. */
    public function calculatedTotals(Licenciamento|Processo $parent): array
    {
        $items = $parent->mercadorias()->selectRaw('COALESCE(SUM(preco_total), 0) AS fob, COALESCE(SUM(Peso), 0) AS peso')->first();
        $fob = round((float) $items->fob, 2);
        $cif = round($fob + (float) $parent->frete + (float) $parent->seguro, 2);
        $rate = $parent instanceof Processo ? (float) $parent->Cambio : 0;
        return ['fob' => $fob, 'peso' => round((float) $items->peso, 3), 'cif' => $cif,
            'valor_aduaneiro' => $rate > 0 ? round($cif * $rate, 2) : null];
    }
}
