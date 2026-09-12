<?php

namespace App\Application\FacturacaoIntegracao\Mappers;

class HongayetuClienteResponseMapper
{
    public function extractExternalCustomerId(array $response): ?int
    {
        foreach ([
            'data.id',
            'data.cliente.id',
            'data.customer.id',
            'data.numero',
            'data.CustomerID',
            'cliente.id',
            'customer.id',
            'id',
        ] as $path) {
            $value = data_get($response, $path);

            if (is_numeric($value) && (int) $value > 0) {
                return (int) $value;
            }
        }

        return null;
    }

    public function extractName(array $response): ?string
    {
        foreach (['data.nome', 'data.cliente.nome', 'data.customer.nome', 'nome'] as $path) {
            $value = data_get($response, $path);

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    public function extractNif(array $response): ?string
    {
        foreach (['data.nif', 'data.cliente.nif', 'data.customer.nif', 'nif'] as $path) {
            $value = data_get($response, $path);

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }
}
