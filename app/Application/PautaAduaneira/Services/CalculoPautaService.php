<?php

namespace App\Application\PautaAduaneira\Services;

use App\Application\PautaAduaneira\DTOs\CalculoPautaDTO;
use App\Application\PautaAduaneira\DTOs\ResultadoCalculoPautaDTO;
use App\Domains\PautaAduaneira\Repositories\PautaAduaneiraRepositoryInterface;
use App\Domains\PautaAduaneira\ValueObjects\TaxaPautal;

final class CalculoPautaService
{
    public function __construct(
        private readonly PautaAduaneiraRepositoryInterface $pautas,
    ) {
    }

    public function calcular(CalculoPautaDTO $dto): ResultadoCalculoPautaDTO
    {
        $pauta = $this->pautas->findOrFail($dto->pautaAduaneiraId);
        $taxaRegime = TaxaPautal::fromMixed($pauta->{$dto->regimeTaxa})->valueOrZero();
        $taxaIeq = TaxaPautal::fromMixed($pauta->ieq)->valueOrZero();
        $taxaIva = TaxaPautal::fromMixed($pauta->iva)->valueOrZero();

        $direitos = round($dto->valorAduaneiro * $taxaRegime / 100, 2);
        $ieq = $dto->incluirIeq ? round($dto->valorAduaneiro * $taxaIeq / 100, 2) : 0.0;
        $iva = $dto->incluirIva ? round(($dto->valorAduaneiro + $direitos + $ieq) * $taxaIva / 100, 2) : 0.0;
        $totalImpostos = round($direitos + $ieq + $iva, 2);

        return new ResultadoCalculoPautaDTO(
            valorAduaneiro: $dto->valorAduaneiro,
            taxaAplicada: $taxaRegime,
            direitosImportacao: $direitos,
            iva: $iva,
            ieq: $ieq,
            totalImpostos: $totalImpostos,
            totalEstimado: round($dto->valorAduaneiro + $totalImpostos, 2),
            breakdown: [
                'regime' => $dto->regimeTaxa,
                'taxas' => [
                    'regime' => $taxaRegime,
                    'iva' => $taxaIva,
                    'ieq' => $taxaIeq,
                ],
                'formulas' => [
                    'direitos_importacao' => 'valor_aduaneiro * taxa_regime / 100',
                    'ieq' => 'valor_aduaneiro * ieq / 100',
                    'iva' => '(valor_aduaneiro + direitos_importacao + ieq) * iva / 100',
                ],
            ],
        );
    }
}
