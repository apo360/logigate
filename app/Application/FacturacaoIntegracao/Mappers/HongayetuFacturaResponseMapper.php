<?php

namespace App\Application\FacturacaoIntegracao\Mappers;

use App\Application\FacturacaoIntegracao\DTOs\FacturaExternaEmitidaDTO;

class HongayetuFacturaResponseMapper
{
    public function toFacturaExternaEmitidaDTO(array $response): FacturaExternaEmitidaDTO
    {
        return new FacturaExternaEmitidaDTO(
            externalInvoiceId: $this->extractExternalInvoiceId($response),
            externalInvoiceNumber: $this->extractExternalInvoiceNumber($response),
            total: $this->extractTotal($response),
            apiEstado: $this->extractApiEstado($response),
            rawResponse: $response,
        );
    }

    public function extractExternalInvoiceId(array $response): ?int
    {
        return $this->intFrom($response, [
            'data.factura.id',
            'data.id',
            'factura.id',
            'id',
        ]);
    }

    public function extractExternalInvoiceNumber(array $response): ?string
    {
        return $this->stringFrom($response, [
            'data.factura.codigo_formatado',
            'data.factura.numero',
            'data.codigo_formatado',
            'codigo_formatado',
            'invoice_no',
        ]);
    }

    public function extractTotal(array $response): ?float
    {
        $value = data_get($response, 'data.factura.total')
            ?? data_get($response, 'data.total')
            ?? data_get($response, 'total');

        return is_numeric($value) ? (float) $value : null;
    }

    public function extractApiEstado(array $response): ?int
    {
        return $this->intFrom($response, [
            'data.factura.estado',
            'data.estado',
            'estado',
        ]);
    }

    private function intFrom(array $payload, array $paths): ?int
    {
        foreach ($paths as $path) {
            $value = data_get($payload, $path);

            if (is_numeric($value)) {
                return (int) $value;
            }
        }

        return null;
    }

    private function stringFrom(array $payload, array $paths): ?string
    {
        foreach ($paths as $path) {
            $value = data_get($payload, $path);

            if (is_scalar($value) && (string) $value !== '') {
                return (string) $value;
            }
        }

        return null;
    }
}
