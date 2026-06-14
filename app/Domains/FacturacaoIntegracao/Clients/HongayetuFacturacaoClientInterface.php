<?php

namespace App\Domains\FacturacaoIntegracao\Clients;

use App\Application\Integracoes\DTOs\ResultadoTesteIntegracaoDTO;

interface HongayetuFacturacaoClientInterface
{
    public function testConnection(int $empresaId): ResultadoTesteIntegracaoDTO;

    public function emitirFactura(array $payload): never;

    public function consultarFactura(string $referencia): never;

    public function cancelarFactura(string $referencia, string $motivo): never;
}
