<?php

namespace App\Application\FacturacaoIntegracao\Mappers;

use App\Application\FacturacaoIntegracao\DTOs\SincronizarClienteFacturacaoDTO;

class CustomerToHongayetuPayloadMapper
{
    public function map(SincronizarClienteFacturacaoDTO $dto): array
    {
        return $this->withoutNulls([
            'nome' => $dto->nome,
            'tipo' => $dto->tipo,
            'nif' => $dto->nif,
            'telefone' => $dto->telefone,
            'email' => $dto->email,
            'endereco' => $dto->endereco,
            'provincia_id' => $dto->provinciaId,
            'grupo_id' => $dto->grupoId,
        ]);
    }

    private function withoutNulls(array $payload): array
    {
        return array_filter($payload, fn ($value) => $value !== null);
    }
}
