<?php

namespace App\Application\PautaAduaneira\Actions;

use App\Application\PautaAduaneira\DTOs\ProcessoTributacaoResultDTO;
use App\Application\PautaAduaneira\Services\ProcessoTributacaoService;
use App\Models\Processo;

final class CalcularTributacaoProcessoAction
{
    public function __construct(
        private readonly ProcessoTributacaoService $service,
    ) {
    }

    public function execute(
        Processo $processo,
        string $regimeTaxa = 'rg',
        bool $incluirIva = true,
        bool $incluirIeq = true,
        array $regimesPorMercadoria = [],
    ): ProcessoTributacaoResultDTO {
        return $this->service->calcular($processo, $regimeTaxa, $incluirIva, $incluirIeq, $regimesPorMercadoria);
    }
}
