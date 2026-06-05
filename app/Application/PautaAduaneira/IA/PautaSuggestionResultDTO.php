<?php

namespace App\Application\PautaAduaneira\IA;

final class PautaSuggestionResultDTO
{
    public function __construct(
        public readonly int $pautaAduaneiraId,
        public readonly string $codigo,
        public readonly string $descricao,
        public readonly int $confidence,
        public readonly string $reason,
        public readonly string $source,
    ) {
    }

    public function toArray(): array
    {
        return [
            'pauta_aduaneira_id' => $this->pautaAduaneiraId,
            'codigo' => $this->codigo,
            'descricao' => $this->descricao,
            'confidence' => max(0, min($this->confidence, 100)),
            'reason' => $this->reason,
            'source' => $this->source,
        ];
    }
}
