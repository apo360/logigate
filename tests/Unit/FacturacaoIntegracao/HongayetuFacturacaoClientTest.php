<?php

namespace Tests\Unit\FacturacaoIntegracao;

use App\Application\FacturacaoIntegracao\Clients\HongayetuFacturacaoClient;
use App\Application\FacturacaoIntegracao\DTOs\SolicitarFacturaDTO;
use App\Application\Integracoes\Services\IntegracaoResolverService;
use App\Domains\FacturacaoIntegracao\Clients\HttpHongayetuFacturacaoClient;
use App\Domains\Integracoes\Enums\EstadoIntegracaoEnum;
use App\Domains\Integracoes\Enums\ProvedorIntegracaoEnum;
use App\Domains\Integracoes\Enums\TipoIntegracaoEnum;
use App\Domains\Integracoes\Exceptions\CredenciaisIntegracaoInvalidasException;
use App\Domains\Integracoes\Repositories\EmpresaIntegracaoRepositoryInterface;
use App\Models\Empresa;
use App\Models\EmpresaIntegracao;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HongayetuFacturacaoClientTest extends TestCase
{
    public function test_it_tests_connection_with_configured_api(): void
    {
        Http::fake([
            'https://facturacao.internal/api/integrations/health' => Http::response(['ok' => true], 200),
        ]);

        $result = (new HongayetuFacturacaoClient())->test($this->integration());

        $this->assertTrue($result->success);
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer secret-token'));
    }

    public function test_it_emits_invoice_with_idempotency_key(): void
    {
        Http::fake([
            'https://facturacao.internal/api/invoices' => Http::response([
                'id' => 'inv_123',
                'invoice_no' => 'FT 2026/1',
                'status' => 'issued',
                'issued_at' => '2026-06-09T12:00:00+00:00',
                'api_token' => 'must-not-leak',
            ], 201),
        ]);

        $dto = (new HongayetuFacturacaoClient())->emitirFactura(
            $this->integration(),
            new SolicitarFacturaDTO(1, 2, 'idem-123', ['lines' => []]),
        );

        $this->assertSame('inv_123', $dto->externalInvoiceId);
        $this->assertSame('FT 2026/1', $dto->externalInvoiceNo);
        $this->assertSame('***', $dto->rawResponseSanitized['api_token']);

        Http::assertSent(fn ($request) => $request->hasHeader('Idempotency-Key', 'idem-123'));
    }

    public function test_it_rejects_missing_credentials(): void
    {
        $this->expectException(CredenciaisIntegracaoInvalidasException::class);

        $integration = $this->integration(setCredentials: false);

        (new HongayetuFacturacaoClient())->test($integration);
    }

    public function test_domain_client_tests_connection_through_resolver(): void
    {
        Http::fake([
            'https://facturacao.internal/api/integrations/health' => Http::response(['ok' => true], 200),
        ]);

        $client = new HttpHongayetuFacturacaoClient(new IntegracaoResolverService($this->repository($this->integration())));

        $result = $client->testConnection(1);

        $this->assertTrue($result->success);
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer secret-token'));
    }

    public function test_domain_client_handles_failed_connection(): void
    {
        Http::fake([
            'https://facturacao.internal/api/integrations/health' => Http::response(['error' => 'down'], 500),
        ]);

        $client = new HttpHongayetuFacturacaoClient(new IntegracaoResolverService($this->repository($this->integration())));

        $result = $client->testConnection(1);

        $this->assertFalse($result->success);
        $this->assertSame(500, $result->context['status']);
    }

    private function integration(bool $setCredentials = true): EmpresaIntegracao
    {
        $integration = new EmpresaIntegracao([
            'empresa_id' => 1,
            'tipo' => TipoIntegracaoEnum::Facturacao,
            'provedor' => ProvedorIntegracaoEnum::HongayetuFacturacao,
            'estado' => EstadoIntegracaoEnum::Activo,
            'config' => [
                'api_url' => 'https://facturacao.internal',
                'timeout' => 5,
                'retry_attempts' => 0,
            ],
        ]);

        if ($setCredentials) {
            $integration->setCredentials(['api_token' => 'secret-token']);
        }

        return $integration;
    }

    private function repository(EmpresaIntegracao $integration): EmpresaIntegracaoRepositoryInterface
    {
        return new class($integration) implements EmpresaIntegracaoRepositoryInterface {
            public function __construct(private EmpresaIntegracao $integration)
            {
            }

            public function listForEmpresa(Empresa $empresa): Collection
            {
                return collect([$this->integration]);
            }

            public function findForEmpresa(Empresa $empresa, TipoIntegracaoEnum $tipo, ProvedorIntegracaoEnum $provedor): ?EmpresaIntegracao
            {
                return $this->integration;
            }

            public function activeForEmpresa(Empresa $empresa, TipoIntegracaoEnum $tipo, ?ProvedorIntegracaoEnum $provedor = null): ?EmpresaIntegracao
            {
                return (int) $empresa->id === (int) $this->integration->empresa_id ? $this->integration : null;
            }

            public function upsert(Empresa $empresa, array $attributes, array $credentials = []): EmpresaIntegracao
            {
                return $this->integration;
            }
        };
    }
}
