<?php

namespace App\Domains\FacturacaoIntegracao\Clients;

use App\Application\Integracoes\DTOs\ResultadoTesteIntegracaoDTO;
use App\Application\Integracoes\Services\IntegracaoResolverService;
use App\Domains\FacturacaoIntegracao\Exceptions\FacturacaoIntegracaoException;
use App\Domains\Integracoes\Enums\ProvedorIntegracaoEnum;
use App\Domains\Integracoes\Enums\TipoIntegracaoEnum;
use App\Infrastructure\FacturacaoIntegracao\Hongayetu\HttpHongayetuFacturacaoClient as OfficialHongayetuFacturacaoClient;

class HttpHongayetuFacturacaoClient implements HongayetuFacturacaoClientInterface
{
    private readonly OfficialHongayetuFacturacaoClient $client;

    public function __construct(
        private readonly IntegracaoResolverService $resolver,
        ?OfficialHongayetuFacturacaoClient $client = null,
    ) {
        $this->client = $client ?? new OfficialHongayetuFacturacaoClient();
    }

    public function testConnection(int $empresaId): ResultadoTesteIntegracaoDTO
    {
        $integracao = $this->resolver->resolveForEmpresaId(
            $empresaId,
            TipoIntegracaoEnum::Facturacao,
            ProvedorIntegracaoEnum::HongayetuFacturacao,
        );

        try {
            $result = $this->client->verificarCredenciais($integracao->config ?? [], $integracao->credentials());

            return ResultadoTesteIntegracaoDTO::success('Ligação com Hongayetu Facturação validada.', [
                'resultado' => $result,
            ]);
        } catch (FacturacaoIntegracaoException $exception) {
            return ResultadoTesteIntegracaoDTO::failure('Falha ao validar a ligação com Hongayetu Facturação.', [
                'status' => $exception->getCode(),
                'erro' => $exception->context(),
            ]);
        }
    }

    public function verificarCredenciais(array $config, array $credenciais): array
    {
        return $this->client->verificarCredenciais($config, $credenciais);
    }

    public function listarClientes(array $query = [], array $config = [], array $credenciais = []): array
    {
        return $this->client->listarClientes($query, $config, $credenciais);
    }

    public function criarCliente(array $payload, array $config = [], array $credenciais = []): array
    {
        return $this->client->criarCliente($payload, $config, $credenciais);
    }

    public function actualizarCliente(int $id, array $payload, array $config = [], array $credenciais = []): array
    {
        return $this->client->actualizarCliente($id, $payload, $config, $credenciais);
    }

    public function listarFacturas(array $query = [], array $config = [], array $credenciais = []): array
    {
        return $this->client->listarFacturas($query, $config, $credenciais);
    }

    public function consultarFactura(int $id, array $config = [], array $credenciais = []): array
    {
        return $this->client->consultarFactura($id, $config, $credenciais);
    }

    public function emitirFactura(array $payload, array $config = [], array $credenciais = []): array
    {
        return $this->client->emitirFactura($payload, $config, $credenciais);
    }

    public function obterPdfFactura(int $id, bool $base64 = true, array $config = [], array $credenciais = []): array|string
    {
        return $this->client->obterPdfFactura($id, $base64, $config, $credenciais);
    }

    public function anularFactura(int $id, array $payload, array $config = [], array $credenciais = []): array
    {
        return $this->client->anularFactura($id, $payload, $config, $credenciais);
    }

    public function listarBancos(array $query = [], array $config = [], array $credenciais = []): array
    {
        return $this->client->listarBancos($query, $config, $credenciais);
    }

    public function listarEstabelecimentos(array $query = [], array $config = [], array $credenciais = []): array
    {
        return $this->client->listarEstabelecimentos($query, $config, $credenciais);
    }
}
