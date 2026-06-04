<?php

declare(strict_types=1);

namespace App\Domains\Processo\Services;

use App\Models\Processo;

final readonly class ProcessoFinalizacaoRules
{
    /**
     * @return array<int, string>
     */
    public function validar(Processo $processo): array
    {
        $erros = [];

        if (empty($processo->NrDU)) {
            $erros[] = 'O campo NrDU é obrigatório.';
        }

        if (empty($processo->BLC_Porte)) {
            $erros[] = 'O campo BLC_Porte é obrigatório.';
        }

        if (empty($processo->ValorAduaneiro)) {
            $erros[] = 'O campo ValorAduaneiro é obrigatório.';
        }

        if (empty($processo->cif)) {
            $erros[] = 'O campo CIF é obrigatório.';
        }

        if (empty($processo->Cambio)) {
            $erros[] = 'O campo Cambio é obrigatório.';
        }

        if ($processo->mercadorias->isEmpty()) {
            $erros[] = 'Deve haver pelo menos uma mercadoria associada ao processo.';
        }

        if (! $processo->emolumentoTarifa || $processo->emolumentoTarifa->honorario === null || $processo->emolumentoTarifa->honorario < 0) {
            $erros[] = 'Os campos Honorários e Emolumentos Tarifa não podem ser nulos ou negativos.';
        }

        return $erros;
    }
}
