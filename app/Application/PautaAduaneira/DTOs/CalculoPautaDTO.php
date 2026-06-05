<?php

namespace App\Application\PautaAduaneira\DTOs;

final class CalculoPautaDTO
{
    public function __construct(
        public readonly int $pautaAduaneiraId,
        public readonly float $valorAduaneiro,
        public readonly string $regimeTaxa = 'rg',
        public readonly bool $incluirIva = true,
        public readonly bool $incluirIeq = true,
    ) {
        if (! in_array($this->regimeTaxa, ['rg', 'sadc', 'ua'], true)) {
            throw new \InvalidArgumentException('Regime de taxa inválido.');
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            pautaAduaneiraId: (int) ($data['pauta_aduaneira_id'] ?? $data['pautaAduaneiraId'] ?? 0),
            valorAduaneiro: (float) ($data['valor_aduaneiro'] ?? $data['valorAduaneiro'] ?? 0),
            regimeTaxa: (string) ($data['regime_taxa'] ?? $data['regimeTaxa'] ?? 'rg'),
            incluirIva: filter_var($data['incluir_iva'] ?? $data['incluirIva'] ?? true, FILTER_VALIDATE_BOOL),
            incluirIeq: filter_var($data['incluir_ieq'] ?? $data['incluirIeq'] ?? true, FILTER_VALIDATE_BOOL),
        );
    }
}
