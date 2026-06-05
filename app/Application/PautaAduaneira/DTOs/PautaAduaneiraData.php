<?php

namespace App\Application\PautaAduaneira\DTOs;

use App\Models\PautaAduaneira;

final class PautaAduaneiraData
{
    public function __construct(
        public readonly int $id,
        public readonly string $codigo,
        public readonly string $descricao,
        public readonly ?string $uq,
        public readonly mixed $rg,
        public readonly mixed $sadc,
        public readonly mixed $ua,
        public readonly ?string $requisitos,
        public readonly ?string $observacao,
        public readonly mixed $iva,
        public readonly mixed $ieq,
    ) {
    }

    public static function fromModel(PautaAduaneira $pauta): self
    {
        return new self(
            id: (int) $pauta->id,
            codigo: (string) $pauta->codigo,
            descricao: (string) $pauta->descricao,
            uq: $pauta->uq,
            rg: $pauta->rg,
            sadc: $pauta->sadc,
            ua: $pauta->ua,
            requisitos: $pauta->requisitos,
            observacao: $pauta->observacao,
            iva: $pauta->iva,
            ieq: $pauta->ieq,
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
