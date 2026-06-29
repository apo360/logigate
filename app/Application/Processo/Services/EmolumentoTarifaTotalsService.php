<?php

namespace App\Application\Processo\Services;

use App\Models\EmolumentoTarifa;

final class EmolumentoTarifaTotalsService
{
    public function guiaFiscal(?EmolumentoTarifa $tarifa): float
    {
        if (! $tarifa) {
            return 0.0;
        }

        return array_sum([
            (float) ($tarifa->direitos ?? 0),
            (float) ($tarifa->emolumentos ?? 0),
            (float) ($tarifa->porto ?? 0),
            (float) ($tarifa->terminal ?? 0),
            (float) ($tarifa->lmc ?? 0),
            (float) ($tarifa->navegacao ?? 0),
            (float) ($tarifa->inerentes ?? 0),
            (float) ($tarifa->frete ?? 0),
            (float) ($tarifa->carga_descarga ?? 0),
            (float) ($tarifa->deslocacao ?? 0),
            (float) ($tarifa->selos ?? 0),
            (float) ($tarifa->iva_aduaneiro ?? 0),
            (float) ($tarifa->iec ?? 0),
            (float) ($tarifa->impostoEstatistico ?? 0),
            (float) ($tarifa->juros_mora ?? 0),
            (float) ($tarifa->multas ?? 0),
            (float) ($tarifa->caucao ?? 0),
            (float) ($tarifa->honorario ?? 0),
            (float) ($tarifa->honorario_iva ?? 0),
            (float) ($tarifa->orgaos_ofiais ?? 0),
        ]);
    }
}
