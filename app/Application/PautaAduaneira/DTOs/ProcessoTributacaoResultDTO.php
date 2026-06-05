<?php

namespace App\Application\PautaAduaneira\DTOs;

final class ProcessoTributacaoResultDTO
{
    public function __construct(
        public readonly array $items,
        public readonly array $totais,
        public readonly array $alertas,
    ) {
    }

    public function toArray(): array
    {
        return [
            'items' => $this->items,
            'totais' => $this->totais,
            'alertas' => $this->alertas,
        ];
    }
}
