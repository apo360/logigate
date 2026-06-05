<?php

namespace App\Application\PautaAduaneira\DTOs;

final class ResultadoCalculoPautaDTO
{
    public function __construct(
        public readonly float $valorAduaneiro,
        public readonly float $taxaAplicada,
        public readonly float $direitosImportacao,
        public readonly float $iva,
        public readonly float $ieq,
        public readonly float $totalImpostos,
        public readonly float $totalEstimado,
        public readonly array $breakdown,
    ) {
    }

    public function toArray(): array
    {
        return [
            'valor_aduaneiro' => $this->valorAduaneiro,
            'taxa_aplicada' => $this->taxaAplicada,
            'direitos_importacao' => $this->direitosImportacao,
            'iva' => $this->iva,
            'ieq' => $this->ieq,
            'total_impostos' => $this->totalImpostos,
            'total_estimado' => $this->totalEstimado,
            'breakdown' => $this->breakdown,
        ];
    }
}
