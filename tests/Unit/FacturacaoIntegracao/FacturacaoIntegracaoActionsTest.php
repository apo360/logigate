<?php

namespace Tests\Unit\FacturacaoIntegracao;

use App\Application\FacturacaoIntegracao\Actions\ConsultarFacturaExternaAction;
use App\Application\FacturacaoIntegracao\Actions\EmitirFacturaExternaAction;
use App\Application\FacturacaoIntegracao\Actions\SincronizarClienteFacturacaoAction;
use App\Application\FacturacaoIntegracao\DTOs\EmitirFacturaExternaDTO;
use App\Application\FacturacaoIntegracao\DTOs\EmitirFacturaLinhaDTO;
use App\Application\FacturacaoIntegracao\DTOs\SincronizarClienteFacturacaoDTO;
use App\Domains\FacturacaoIntegracao\Clients\HongayetuFacturacaoClientInterface;
use App\Domains\FacturacaoIntegracao\Exceptions\ClienteExternoNaoSincronizadoException;
use App\Domains\FacturacaoIntegracao\Exceptions\FacturaExternaFalhouException;
use App\Domains\FacturacaoIntegracao\Exceptions\FacturaExternaInvalidaException;
use App\Domains\FacturacaoIntegracao\Exceptions\FacturacaoIntegracaoException;
use App\Domains\Integracoes\Enums\EstadoIntegracaoEnum;
use App\Domains\Integracoes\Enums\ProvedorIntegracaoEnum;
use App\Domains\Integracoes\Enums\TipoIntegracaoEnum;
use App\Models\Customer;
use App\Models\Empresa;
use App\Models\EmpresaIntegracao;
use App\Models\ExternalApiLog;
use App\Models\ExternalCustomerMapping;
use App\Models\ExternalInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PDO;
use PDOException;
use Tests\TestCase;

class FacturacaoIntegracaoActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        $this->skipUnlessDatabaseEnvironmentIsSafe();

        parent::setUp();
    }

    private function skipUnlessDatabaseEnvironmentIsSafe(): void
    {
        $appEnv = $this->envValue('APP_ENV');
        $connection = $this->envValue('DB_CONNECTION');
        $database = $this->envValue('DB_DATABASE');
        $filesystem = $this->envValue('FILESYSTEM_DISK');
        $awsBucket = $this->envValue('AWS_BUCKET');

        if ($appEnv !== 'testing') {
            $this->markTestSkipped("Ambiente inseguro: APP_ENV deve ser testing, recebido [{$appEnv}].");
        }

        if ($filesystem !== 'local') {
            $this->markTestSkipped("Ambiente inseguro: FILESYSTEM_DISK deve ser local, recebido [{$filesystem}].");
        }

        if ($awsBucket !== null && trim($awsBucket) !== '') {
            $this->markTestSkipped('Ambiente inseguro: AWS_BUCKET deve estar vazio para estes testes.');
        }

        if ($connection === 'sqlite') {
            $this->skipUnlessSqliteIsSafe($database);
            return;
        }

        if ($connection === 'mysql') {
            $this->skipUnlessMysqlIsSafe($database);
            return;
        }

        $this->markTestSkipped("Ambiente inseguro: DB_CONNECTION deve ser sqlite ou mysql, recebido [{$connection}].");
    }

    private function skipUnlessSqliteIsSafe(?string $database): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite não está disponível para SQLite em memória.');
        }

        if ($database === ':memory:') {
            return;
        }

        $normalized = strtolower((string) $database);

        if ($normalized === '' || ! str_contains($normalized, 'test') || $this->containsForbiddenDatabaseToken($normalized)) {
            $this->markTestSkipped("Ambiente inseguro: DB_DATABASE sqlite deve ser :memory: ou base temporária de teste, recebido [{$database}].");
        }
    }

    private function skipUnlessMysqlIsSafe(?string $database): void
    {
        if (! extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('pdo_mysql não está disponível para testar com MySQL isolado.');
        }

        if ($database !== 'logigate_testing') {
            $this->markTestSkipped("Ambiente inseguro: DB_DATABASE mysql deve ser exactamente logigate_testing, recebido [{$database}].");
        }

        if ($database === 'logigatetb_teste' || $this->containsForbiddenDatabaseToken(strtolower($database))) {
            $this->markTestSkipped("Ambiente inseguro: DB_DATABASE mysql proibido [{$database}].");
        }

        $this->skipUnlessMysqlConnectionIsAvailable($database);
    }

    private function skipUnlessMysqlConnectionIsAvailable(string $database): void
    {
        $host = $this->envValue('DB_HOST') ?: '127.0.0.1';
        $port = $this->envValue('DB_PORT') ?: '3306';
        $username = $this->envValue('DB_USERNAME') ?: '';
        $password = $this->envValue('DB_PASSWORD') ?: '';

        try {
            new PDO(
                "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 3,
                ],
            );
        } catch (PDOException $exception) {
            $this->markTestSkipped("Base MySQL isolada [{$database}] indisponível ou credenciais inválidas: {$exception->getMessage()}");
        }
    }

    private function containsForbiddenDatabaseToken(string $database): bool
    {
        foreach (['prod', 'production', 'real', 'live'] as $token) {
            if (str_contains($database, $token)) {
                return true;
            }
        }

        return false;
    }

    private function envValue(string $key): ?string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        return $value === false ? null : (string) $value;
    }

    public function test_sincronizar_cliente_cria_mapping_quando_nao_existe(): void
    {
        [$empresa, $customer, $integracao] = $this->integrationFixture();
        $client = Mockery::mock(HongayetuFacturacaoClientInterface::class);
        $client->shouldReceive('criarCliente')
            ->once()
            ->andReturn([
                'estado' => 'ok',
                'data' => [
                    'id' => 535,
                    'nome' => 'Cliente Teste',
                    'nif' => '123456789',
                ],
            ]);
        $this->app->instance(HongayetuFacturacaoClientInterface::class, $client);

        $mapping = app(SincronizarClienteFacturacaoAction::class)->execute(new SincronizarClienteFacturacaoDTO(
            empresaId: $empresa->id,
            empresaIntegracaoId: $integracao->id,
            customerId: $customer->id,
            nome: 'Cliente Teste',
            tipo: 1,
            nif: '123456789',
            telefone: '923000000',
            email: 'cliente@example.test',
            endereco: 'Luanda',
        ));

        $this->assertSame(535, $mapping->external_customer_id);
        $this->assertDatabaseHas('external_customer_mappings', [
            'customer_id' => $customer->id,
            'external_customer_id' => 535,
        ]);
        $this->assertDatabaseHas('external_api_logs', [
            'empresa_integracao_id' => $integracao->id,
            'endpoint' => '/facturacao/clientes',
            'success' => true,
        ]);
    }

    public function test_sincronizar_cliente_reutiliza_mapping_existente(): void
    {
        [$empresa, $customer, $integracao] = $this->integrationFixture();
        $existing = ExternalCustomerMapping::query()->create([
            'empresa_id' => $empresa->id,
            'empresa_integracao_id' => $integracao->id,
            'customer_id' => $customer->id,
            'external_customer_id' => 535,
        ]);
        $client = Mockery::mock(HongayetuFacturacaoClientInterface::class);
        $client->shouldReceive('criarCliente')->never();
        $this->app->instance(HongayetuFacturacaoClientInterface::class, $client);

        $mapping = app(SincronizarClienteFacturacaoAction::class)->execute(new SincronizarClienteFacturacaoDTO(
            empresaId: $empresa->id,
            empresaIntegracaoId: $integracao->id,
            customerId: $customer->id,
            nome: 'Cliente Teste',
            tipo: 1,
        ));

        $this->assertTrue($existing->is($mapping));
    }

    public function test_sincronizar_cliente_extrai_id_externo_de_data_numero(): void
    {
        [$empresa, $customer, $integracao] = $this->integrationFixture();
        $client = Mockery::mock(HongayetuFacturacaoClientInterface::class);
        $client->shouldReceive('criarCliente')
            ->once()
            ->andReturn([
                'estado' => 'ok',
                'data' => [
                    'numero' => 88,
                    'nome' => 'Cliente Teste',
                    'nif' => '123456789',
                ],
            ]);
        $this->app->instance(HongayetuFacturacaoClientInterface::class, $client);

        $mapping = app(SincronizarClienteFacturacaoAction::class)->execute(new SincronizarClienteFacturacaoDTO(
            empresaId: $empresa->id,
            empresaIntegracaoId: $integracao->id,
            customerId: $customer->id,
            nome: 'Cliente Teste',
            tipo: 1,
            nif: '123456789',
        ));

        $this->assertSame(88, $mapping->external_customer_id);
    }

    public function test_sincronizar_cliente_sem_id_externo_lanca_exception_e_nao_cria_mapping(): void
    {
        [$empresa, $customer, $integracao] = $this->integrationFixture();
        $client = Mockery::mock(HongayetuFacturacaoClientInterface::class);
        $client->shouldReceive('criarCliente')
            ->once()
            ->andReturn([
                'estado' => 'ok',
                'data' => [
                    'nome' => 'Cliente Teste',
                    'nif' => '123456789',
                ],
            ]);
        $this->app->instance(HongayetuFacturacaoClientInterface::class, $client);

        $this->expectException(FacturaExternaFalhouException::class);
        $this->expectExceptionMessage('Não foi possível identificar o ID externo do cliente retornado pela Facturação Hongayetu.');

        try {
            app(SincronizarClienteFacturacaoAction::class)->execute(new SincronizarClienteFacturacaoDTO(
                empresaId: $empresa->id,
                empresaIntegracaoId: $integracao->id,
                customerId: $customer->id,
                nome: 'Cliente Teste',
                tipo: 1,
                nif: '123456789',
            ));
        } finally {
            $this->assertDatabaseMissing('external_customer_mappings', [
                'customer_id' => $customer->id,
            ]);
            $this->assertDatabaseHas('external_api_logs', [
                'empresa_integracao_id' => $integracao->id,
                'endpoint' => '/facturacao/clientes',
                'success' => false,
                'error_code' => 'FacturaExternaFalhouException',
            ]);
        }
    }

    public function test_sincronizar_cliente_grava_log_falha_quando_client_falha(): void
    {
        [$empresa, $customer, $integracao] = $this->integrationFixture();
        $client = Mockery::mock(HongayetuFacturacaoClientInterface::class);
        $client->shouldReceive('criarCliente')
            ->once()
            ->andThrow(new FacturacaoIntegracaoException('Falha fake', ['erro' => 'fake'], 422));
        $this->app->instance(HongayetuFacturacaoClientInterface::class, $client);

        $this->expectException(FacturacaoIntegracaoException::class);

        try {
            app(SincronizarClienteFacturacaoAction::class)->execute(new SincronizarClienteFacturacaoDTO(
                empresaId: $empresa->id,
                empresaIntegracaoId: $integracao->id,
                customerId: $customer->id,
                nome: 'Cliente Teste',
                tipo: 1,
            ));
        } finally {
            $this->assertDatabaseHas('external_api_logs', [
                'empresa_integracao_id' => $integracao->id,
                'endpoint' => '/facturacao/clientes',
                'success' => false,
                'error_code' => 'FacturacaoIntegracaoException',
            ]);
        }
    }

    public function test_emitir_ft_simples_cria_invoice_e_marca_issued(): void
    {
        [$empresa, $customer, $integracao] = $this->integrationFixture();
        ExternalCustomerMapping::query()->create([
            'empresa_id' => $empresa->id,
            'empresa_integracao_id' => $integracao->id,
            'customer_id' => $customer->id,
            'external_customer_id' => 535,
        ]);

        $client = Mockery::mock(HongayetuFacturacaoClientInterface::class);
        $client->shouldReceive('emitirFactura')
            ->once()
            ->withArgs(function (array $payload) use ($customer): bool {
                $this->assertSame(1, ExternalInvoice::query()->where('customer_id', $customer->id)->where('status', ExternalInvoice::STATUS_PENDING)->count());
                $this->assertSame(535, $payload['cliente_id']);
                return true;
            })
            ->andReturn([
                'estado' => 'ok',
                'data' => [
                    'estado' => 'ok',
                    'texto' => 'Factura emitida com sucesso',
                    'factura' => [
                        'id' => 50,
                        'codigo_formatado' => 'FT 050/2026',
                        'total' => 57000,
                    ],
                    'id' => 50,
                ],
            ]);
        $this->app->instance(HongayetuFacturacaoClientInterface::class, $client);

        $result = app(EmitirFacturaExternaAction::class)->execute($this->invoiceDto($empresa, $customer, $integracao));

        $this->assertSame(50, $result->externalInvoiceId);
        $this->assertSame('FT 050/2026', $result->externalInvoiceNumber);
        $this->assertDatabaseHas('external_invoices', [
            'customer_id' => $customer->id,
            'status' => ExternalInvoice::STATUS_ISSUED,
            'external_invoice_id' => 50,
            'external_invoice_number' => 'FT 050/2026',
        ]);
        $this->assertSame(1, ExternalInvoice::query()->firstOrFail()->lines()->count());
        $this->assertDatabaseHas('external_api_logs', [
            'empresa_integracao_id' => $integracao->id,
            'endpoint' => '/facturacao/facturas/emitir',
            'success' => true,
        ]);
    }

    public function test_emitir_ft_falha_marca_invoice_failed(): void
    {
        [$empresa, $customer, $integracao] = $this->integrationFixture();
        ExternalCustomerMapping::query()->create([
            'empresa_id' => $empresa->id,
            'empresa_integracao_id' => $integracao->id,
            'customer_id' => $customer->id,
            'external_customer_id' => 535,
        ]);
        $client = Mockery::mock(HongayetuFacturacaoClientInterface::class);
        $client->shouldReceive('emitirFactura')
            ->once()
            ->andThrow(new FacturaExternaFalhouException('Falha ao emitir', ['erro' => 'fake'], 500));
        $this->app->instance(HongayetuFacturacaoClientInterface::class, $client);

        $this->expectException(FacturaExternaFalhouException::class);

        try {
            app(EmitirFacturaExternaAction::class)->execute($this->invoiceDto($empresa, $customer, $integracao));
        } finally {
            $invoice = ExternalInvoice::query()->firstOrFail();
            $this->assertSame(ExternalInvoice::STATUS_FAILED, $invoice->status);
            $this->assertNotEmpty($invoice->last_error);
            $this->assertDatabaseHas('external_api_logs', [
                'external_invoice_id' => $invoice->id,
                'success' => false,
                'error_code' => 'FacturaExternaFalhouException',
            ]);
        }
    }

    public function test_emitir_factura_sem_cliente_sincronizado_lanca_exception(): void
    {
        [$empresa, $customer, $integracao] = $this->integrationFixture();
        $client = Mockery::mock(HongayetuFacturacaoClientInterface::class);
        $client->shouldReceive('emitirFactura')->never();
        $this->app->instance(HongayetuFacturacaoClientInterface::class, $client);

        $this->expectException(ClienteExternoNaoSincronizadoException::class);

        app(EmitirFacturaExternaAction::class)->execute($this->invoiceDto($empresa, $customer, $integracao));
    }

    public function test_emitir_factura_recusa_tipo_diferente_de_ft_nesta_fase(): void
    {
        [$empresa, $customer, $integracao] = $this->integrationFixture();
        $client = Mockery::mock(HongayetuFacturacaoClientInterface::class);
        $client->shouldReceive('emitirFactura')->never();
        $this->app->instance(HongayetuFacturacaoClientInterface::class, $client);

        $this->expectException(FacturaExternaInvalidaException::class);

        app(EmitirFacturaExternaAction::class)->execute(new EmitirFacturaExternaDTO(
            empresaId: $empresa->id,
            empresaIntegracaoId: $integracao->id,
            customerId: $customer->id,
            documentType: ExternalInvoice::DOC_TYPE_FR,
            apiTipo: ExternalInvoice::API_TIPO_FR,
            linhas: [
                new EmitirFacturaLinhaDTO(10, 'Servico', 1, 57000),
            ],
        ));
    }

    public function test_consultar_factura_externa_actualiza_response_payload(): void
    {
        [$empresa, $customer, $integracao] = $this->integrationFixture();
        $invoice = ExternalInvoice::query()->create([
            'empresa_id' => $empresa->id,
            'empresa_integracao_id' => $integracao->id,
            'customer_id' => $customer->id,
            'status' => ExternalInvoice::STATUS_ISSUED,
            'external_invoice_id' => 50,
        ]);
        $client = Mockery::mock(HongayetuFacturacaoClientInterface::class);
        $client->shouldReceive('consultarFactura')
            ->once()
            ->with(50, Mockery::type('array'), Mockery::type('array'))
            ->andReturn([
                'estado' => 'ok',
                'data' => [
                    'factura' => [
                        'id' => 50,
                        'estado' => 1,
                    ],
                ],
            ]);
        $this->app->instance(HongayetuFacturacaoClientInterface::class, $client);

        app(ConsultarFacturaExternaAction::class)->execute($invoice);

        $this->assertEquals(['estado' => 'ok', 'data' => ['factura' => ['id' => 50, 'estado' => 1]]], $invoice->fresh()->response_payload);
        $this->assertSame(1, $invoice->fresh()->api_estado);
        $this->assertDatabaseHas('external_api_logs', [
            'external_invoice_id' => $invoice->id,
            'endpoint' => '/facturacao/facturas/50',
            'success' => true,
        ]);
    }

    private function invoiceDto(Empresa $empresa, Customer $customer, EmpresaIntegracao $integracao): EmitirFacturaExternaDTO
    {
        return new EmitirFacturaExternaDTO(
            empresaId: $empresa->id,
            empresaIntegracaoId: $integracao->id,
            customerId: $customer->id,
            localReference: 'LOCAL-001',
            linhas: [
                new EmitirFacturaLinhaDTO(
                    externalArtigoId: 10,
                    description: 'Servico aduaneiro',
                    quantity: 1,
                    unitPrice: 57000,
                    discountAmount: 0,
                    taxPercentage: 0,
                    type: 'service',
                ),
            ],
        );
    }

    /**
     * @return array{0: Empresa, 1: Customer, 2: EmpresaIntegracao}
     */
    private function integrationFixture(): array
    {
        $user = User::factory()->create();
        $empresa = Empresa::withoutEvents(fn () => Empresa::query()->create([
            'Empresa' => 'Empresa Teste',
            'NIF' => '500000000',
            'Endereco_completo' => 'Luanda',
            'conta' => 'EMP-TEST',
        ]));
        $customer = Customer::query()->create([
            'CustomerID' => 'CUST-TEST-' . uniqid(),
            'AccountID' => 'ACC-TEST-' . uniqid(),
            'CustomerTaxID' => '123456789',
            'CompanyName' => 'Cliente Teste',
            'Telephone' => '923000000',
            'Email' => 'cliente@example.test',
            'SelfBillingIndicator' => 0,
            'user_id' => $user->id,
            'empresa_id' => $empresa->id,
        ]);
        $integracao = EmpresaIntegracao::query()->create([
            'empresa_id' => $empresa->id,
            'tipo' => TipoIntegracaoEnum::Facturacao,
            'provedor' => ProvedorIntegracaoEnum::HongayetuFacturacao,
            'estado' => EstadoIntegracaoEnum::Activo,
            'config' => [
                'api_url' => 'https://api.test/logigate/v1',
                'timeout' => 1,
                'retry_attempts' => 0,
            ],
        ]);
        $integracao->setCredentials(['api_token' => 'fake-token']);
        $integracao->save();

        return [$empresa, $customer, $integracao->fresh()];
    }
}
