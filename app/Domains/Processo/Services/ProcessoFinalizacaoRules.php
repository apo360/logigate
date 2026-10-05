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

        if ((float) $processo->ValorAduaneiro <= 0) {
            $erros[] = 'O campo ValorAduaneiro é obrigatório.';
        }

        if ((float) $processo->cif <= 0) {
            $erros[] = 'O campo CIF é obrigatório.';
        }

        if ((float) $processo->Cambio <= 0) {
            $erros[] = 'O campo Cambio é obrigatório.';
        }
        if (! \Illuminate\Support\Facades\Schema::hasColumn('processos', 'cambio_confirmado')) {
            $erros[] = 'Atualize o schema para registar a confirmação, origem e data do câmbio antes de finalizar.';
        } elseif (! $processo->cambio_confirmado || ! $processo->cambio_origem || ! $processo->cambio_data) {
            $erros[] = 'O câmbio deve ser confirmado com origem e data antes da finalização.';
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
