<?php

declare(strict_types=1);

namespace App\Application\Processo\DTOs;

use App\Models\Processo;

final readonly class ProcessoData
{
    public function __construct(
        public int $id,
        public string $numero,
        public string $cliente,
        public string $estado,
        public ?string $data_abertura,
        public ?string $data_finalizacao,
    ) {
    }

    public static function fromModel(Processo $processo): self
    {
        return new self(
            id: (int) $processo->id,
            numero: (string) $processo->NrProcesso,
            cliente: (string) ($processo->cliente?->CompanyName ?? 'N/D'),
            estado: (string) $processo->Estado,
            data_abertura: $processo->DataAbertura ? (string) $processo->DataAbertura : null,
            data_finalizacao: $processo->DataFecho ? (string) $processo->DataFecho : null,
        );
    }
}
