<?php

namespace App\Application\PautaAduaneira\Actions;

use App\Application\PautaAduaneira\DTOs\CalculoPautaDTO;
use App\Application\PautaAduaneira\DTOs\ResultadoCalculoPautaDTO;
use App\Application\PautaAduaneira\Services\CalculoPautaService;

final class CalcularTaxasPautaAction
{
    public function __construct(
        private readonly CalculoPautaService $calculo,
    ) {
    }

    public function execute(CalculoPautaDTO $dto): ResultadoCalculoPautaDTO
    {
        return $this->calculo->calcular($dto);
    }
}
