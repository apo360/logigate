<?php

namespace App\Application\FacturacaoIntegracao\Clients;

use App\Application\FacturacaoIntegracao\DTOs\FacturaEmitidaDTO;
use App\Application\FacturacaoIntegracao\DTOs\SolicitarFacturaDTO;
use App\Application\Integracoes\DTOs\ResultadoTesteIntegracaoDTO;
use App\Domains\FacturacaoIntegracao\Clients\HongayetuFacturacaoClientInterface;
use App\Domains\FacturacaoIntegracao\Exceptions\CredenciaisFacturacaoInvalidasException;
use App\Domains\FacturacaoIntegracao\Exceptions\FacturacaoIntegracaoException;
use App\Models\EmpresaIntegracao;
use App\Infrastructure\FacturacaoIntegracao\Hongayetu\HttpHongayetuFacturacaoClient as OfficialHongayetuFacturacaoClient;

class HongayetuFacturacaoClient
{
    public function __construct(private ?HongayetuFacturacaoClientInterface $client = null)
    {
    }

    public function test(EmpresaIntegracao $integracao): ResultadoTesteIntegracaoDTO
    {
        try {
            $result = $this->client()->verificarCredenciais($integracao->config ?? [], $integracao->credentials());

            return ResultadoTesteIntegracaoDTO::success('Ligação com Hongayetu Facturação validada.', [
                'resultado' => $result,
            ]);
        } catch (CredenciaisFacturacaoInvalidasException $exception) {
            throw $exception;
        } catch (FacturacaoIntegracaoException $exception) {
            return ResultadoTesteIntegracaoDTO::failure('Falha ao validar a ligação com Hongayetu Facturação.', [
                'erro' => $exception->context(),
            ]);
        }
    }

    public function emitirFactura(EmpresaIntegracao $integracao, SolicitarFacturaDTO $data): FacturaEmitidaDTO
    {
        $result = $this->client()->emitirFactura([
            'empresa_id' => $data->empresaId,
            'source_user_id' => $data->sourceUserId,
            'idempotency_key' => $data->idempotencyKey,
            'payload' => $data->payload,
        ], $integracao->config ?? [], $integracao->credentials());

        return FacturaEmitidaDTO::fromArray($result['data'] ?? $result, $integracao->provedor->value);
    }

    private function client(): HongayetuFacturacaoClientInterface
    {
        if ($this->client) {
            return $this->client;
        }

        $this->client = app()->bound(HongayetuFacturacaoClientInterface::class)
            ? app(HongayetuFacturacaoClientInterface::class)
            : new OfficialHongayetuFacturacaoClient();

        return $this->client;
    }
}
