<?php

namespace App\Domains\FacturacaoIntegracao\Mappers;

use App\Domains\FacturacaoIntegracao\DTOs\MetodoPagamentoFacturaDTO;

class MetodoPagamentoFacturaMapper
{
    public static function map(MetodoPagamentoFacturaDTO $pagamento): array
     {
        return [
            'tipo' => $pagamento->tipo->value,
            'valor' => $pagamento->valor,
            'banco_id' => $pagamento->bancoId,
            'referencia' => $pagamento->referencia,
        ];
    }

    /**
     * Converte um array de DTOs para o formato de payload da API.
     */
    public static function toArray(array $dtos): array
    {
        return array_map(
            fn(MetodoPagamentoFacturaDTO $dto) => $dto->toArray(),
            $dtos
        );
    }
}