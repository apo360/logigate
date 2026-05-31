<?php

declare(strict_types=1);

namespace App\Domains\Processo\ValueObjects;

final readonly class CalculoTarifas
{
    public function __construct(
        public float $taxasAduaneiras = 0.0,
        public float $impostos = 0.0,
        public float $custosServico = 0.0,
        public float $outrosCustos = 0.0,
    ) {
    }

    public static function fromPercentuais(
        float $valorAduaneiro,
        float $percentualTaxas,
        float $percentualImpostos,
        float $custosServico = 0.0,
        float $outrosCustos = 0.0,
    ): self {
        return new self(
            taxasAduaneiras: round($valorAduaneiro * ($percentualTaxas / 100), 2),
            impostos: round($valorAduaneiro * ($percentualImpostos / 100), 2),
            custosServico: round($custosServico, 2),
            outrosCustos: round($outrosCustos, 2),
        );
    }

    public function total(): float
    {
        return round($this->taxasAduaneiras + $this->impostos + $this->custosServico + $this->outrosCustos, 2);
    }

    public function toArray(): array
    {
        return [
            'taxas_aduaneiras' => $this->taxasAduaneiras,
            'impostos' => $this->impostos,
            'custos_servico' => $this->custosServico,
            'outros_custos' => $this->outrosCustos,
            'total' => $this->total(),
        ];
    }
}
