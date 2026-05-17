<?php

namespace App\Domains\Customers\Data;

final class CustomerStatsData
{
    public function __construct(
        public readonly int $total = 0,
        public readonly int $ativos = 0,
        public readonly int $importadores = 0,
        public readonly int $empresas = 0,
    ) {
    }

    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'ativos' => $this->ativos,
            'importadores' => $this->importadores,
            'empresas' => $this->empresas,
        ];
    }
}
