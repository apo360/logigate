<?php

namespace App\Application\Processo\Services;

use App\Models\Processo;

final class ProcessoTxtService
{
    public function build(Processo $processo): string
    {
        $processo->loadMissing([
            'cliente',
            'exportador',
            'estancia',
            'paisOrigem',
            'paisDestino',
            'mercadoriasAgrupadas',
        ]);

        $lines = [
            implode('|', [
                '0',
                $processo->NrProcesso ?? '',
                optional($processo->cliente)->CompanyName ?? '',
                optional($processo->cliente)->CustomerTaxID ?? '',
                optional($processo->exportador)->Exportador ?? '',
                optional($processo->estancia)->cod_estancia ?? '',
                $processo->RefCliente ?? '',
                $processo->DataAbertura ?? '',
            ]),
            implode('|', [
                '1',
                $processo->Moeda ?? '',
                $processo->fob_total ?? '0.00',
                $processo->frete ?? '0.00',
                $processo->seguro ?? '0.00',
                $processo->cif ?? '0.00',
                $processo->ValorAduaneiro ?? '0.00',
                optional($processo->paisOrigem)->codigo ?? '',
                optional($processo->paisDestino)->codigo ?? '',
            ]),
        ];

        foreach ($processo->mercadoriasAgrupadas as $index => $mercadoria) {
            $lines[] = implode('|', [
                '2',
                $index + 1,
                $mercadoria->codigo_aduaneiro ?? '',
                $mercadoria->quantidade_total ?? 0,
                $mercadoria->peso_total ?? 0,
                $mercadoria->preco_total ?? 0,
            ]);
        }

        return implode(PHP_EOL, $lines);
    }
}
