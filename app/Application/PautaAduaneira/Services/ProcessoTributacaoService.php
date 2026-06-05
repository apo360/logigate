<?php

namespace App\Application\PautaAduaneira\Services;

use App\Application\PautaAduaneira\Actions\CalcularTaxasPautaAction;
use App\Application\PautaAduaneira\DTOs\CalculoPautaDTO;
use App\Application\PautaAduaneira\DTOs\ProcessoTributacaoResultDTO;
use App\Models\Processo;

final class ProcessoTributacaoService
{
    public function __construct(
        private readonly CalcularTaxasPautaAction $calcularTaxas,
    ) {
    }

    public function calcular(
        Processo $processo,
        string $regimeTaxa = 'rg',
        bool $incluirIva = true,
        bool $incluirIeq = true,
        array $regimesPorMercadoria = [],
    ): ProcessoTributacaoResultDTO {
        $processo->loadMissing('mercadorias.pautaAduaneira');

        $mercadorias = $processo->mercadorias;
        $alertas = [];
        $items = [];
        $fob = (float) ($processo->fob_total ?? $mercadorias->sum('preco_total'));
        $frete = (float) ($processo->frete ?? 0);
        $seguro = (float) ($processo->seguro ?? 0);
        $cambio = (float) ($processo->Cambio ?? 0);

        if ($mercadorias->isEmpty()) {
            $alertas[] = 'Processo sem mercadorias.';
        }

        if ($fob <= 0) {
            $alertas[] = 'Processo sem FOB total válido.';
        }

        if ($cambio <= 0) {
            $alertas[] = 'Processo sem câmbio válido.';
        }

        foreach ($mercadorias as $mercadoria) {
            if (! $mercadoria->pauta_aduaneira_id) {
                $alertas[] = "Mercadoria {$mercadoria->id} sem código pautal associado.";
                continue;
            }

            if ($fob <= 0 || $cambio <= 0) {
                continue;
            }

            $fobItem = (float) ($mercadoria->preco_total ?? 0);
            $ratio = $fobItem > 0 ? $fobItem / $fob : 0;
            $freteRateado = round($frete * $ratio, 2);
            $seguroRateado = round($seguro * $ratio, 2);
            $valorAduaneiro = round(($fobItem + $freteRateado + $seguroRateado) * $cambio, 2);
            $regimeItem = $regimesPorMercadoria[$mercadoria->id] ?? $regimeTaxa;

            $calculo = $this->calcularTaxas->execute(new CalculoPautaDTO(
                pautaAduaneiraId: (int) $mercadoria->pauta_aduaneira_id,
                valorAduaneiro: $valorAduaneiro,
                regimeTaxa: $regimeItem,
                incluirIva: $incluirIva,
                incluirIeq: $incluirIeq,
            ))->toArray();

            $items[] = [
                'mercadoria_id' => $mercadoria->id,
                'codigo' => $mercadoria->codigo_aduaneiro,
                'descricao' => $mercadoria->Descricao,
                'regime' => $regimeItem,
                'fob' => $fobItem,
                'frete_rateado' => $freteRateado,
                'seguro_rateado' => $seguroRateado,
                'valor_aduaneiro' => $valorAduaneiro,
                'direitos_importacao' => $calculo['direitos_importacao'],
                'iva' => $calculo['iva'],
                'ieq' => $calculo['ieq'],
                'total_impostos' => $calculo['total_impostos'],
                'total_estimado' => $calculo['total_estimado'],
            ];
        }

        $totais = [
            'fob' => round($fob, 2),
            'frete' => round($frete, 2),
            'seguro' => round($seguro, 2),
            'cif' => round($fob + $frete + $seguro, 2),
            'valor_aduaneiro' => round(collect($items)->sum('valor_aduaneiro'), 2),
            'direitos_importacao' => round(collect($items)->sum('direitos_importacao'), 2),
            'iva' => round(collect($items)->sum('iva'), 2),
            'ieq' => round(collect($items)->sum('ieq'), 2),
            'total_impostos' => round(collect($items)->sum('total_impostos'), 2),
            'total_estimado' => round(collect($items)->sum('total_estimado'), 2),
        ];

        return new ProcessoTributacaoResultDTO($items, $totais, array_values(array_unique($alertas)));
    }
}
