<?php

namespace Tests\Feature\Integracoes;

use App\Application\Integracoes\Services\IntegracaoResolverService;
use App\Domains\FacturacaoIntegracao\Clients\HongayetuFacturacaoClientInterface;
use App\Domains\FacturacaoIntegracao\Exceptions\FacturaExternaFalhouException;
use App\Domains\Integracoes\Enums\EstadoIntegracaoEnum;
use App\Domains\Integracoes\Enums\ProvedorIntegracaoEnum;
use App\Domains\Integracoes\Enums\TipoIntegracaoEnum;
use App\Livewire\Customers\CustomerShow;
use App\Livewire\Customers\Form as CustomerForm;
use App\Livewire\Integracoes\FacturacaoHongayetuEmitirFt;
use App\Livewire\Integracoes\FacturacaoHongayetuFacturas;
use App\Livewire\Integracoes\FacturacaoHongayetu;
use App\Models\Customer;
use App\Models\Empresa;
use App\Models\EmpresaIntegracao;
use App\Models\ExternalCustomerMapping;
use App\Models\ExternalInvoice;
use App\Models\ProductPrice;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Mockery;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FacturacaoHongayetuTest extends TestCase
{
    use DatabaseTransactions;

    private Empresa $empresa;

    private User $actor;

    protected function setUp(): void
    {
        $this->skipUnlessDatabaseEnvironmentIsSafe();

        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->empresa = Empresa::withoutEvents(fn () => Empresa::query()->create([
            'Empresa' => 'Empresa Hongayetu Teste',
            'NIF' => '500000000',
            'Endereco_completo' => 'Luanda',
            'conta' => 'EMP-HONGAYETU-TEST',
        ]));
        $this->actor = $this->adminForEmpresa($this->empresa);
        $this->actingAs($this->actor);

        Config::set('hongayetu_facturacao.api_url', 'https://fake.test/logigate/v1');
        Config::set('hongayetu_facturacao.api_token', 'fake-token-testing');
        Config::set('hongayetu_facturacao.api_key', null);
        Config::set('hongayetu_facturacao.timeout', 30);
        Config::set('hongayetu_facturacao.retry', 1);
        Config::set('hongayetu_facturacao.environment', 'testing');
    }

    public function test_renderiza_guarda_testa_activa_e_desactiva_integracao(): void
    {
        Livewire::test(FacturacaoHongayetu::class)
            ->assertOk()
            ->assertSee('Facturação Hongayetu')
            ->assertSee('Configuração interna da aplicação')
            ->assertSee('Configurada internamente')
            ->assertSee('Configurado internamente')
            ->assertDontSee('form.credentials.api_token', false)
            ->assertDontSee('form.credentials.api_key', false)
            ->assertDontSee('form.config.api_url', false)
            ->set('form.config.nif', '500000000')
            ->set('form.config.estabelecimento_id_padrao', '1')
            ->call('save')
            ->assertHasNoErrors()
            ->call('activate')
            ->assertHasErrors(['activation']);

        $integracao = $this->integration();

        $this->assertSame(EstadoIntegracaoEnum::EmConfiguracao, $integracao->estado);
        $this->assertSame([], $integracao->credentials());
        $this->assertSame('1', $integracao->config['estabelecimento_id_padrao'] ?? null);
        $this->assertArrayNotHasKey('api_url', $integracao->config);
        $this->assertArrayNotHasKey('api_token', $integracao->config);

        $client = Mockery::mock(HongayetuFacturacaoClientInterface::class);
        $client->shouldReceive('verificarCredenciais')
            ->once()
            ->with([], [])
            ->andReturn([
                'estado' => 'ok',
                'data' => [
                    'empresa' => [
                        'id' => 10,
                        'nome' => 'Empresa Fiscal Remota',
                        'nif' => '500000000',
                    ],
                    'permissions' => ['facturas:read'],
                ],
            ]);
        $client->shouldReceive('emitirFactura')->never();
        $this->app->instance(HongayetuFacturacaoClientInterface::class, $client);

        Livewire::test(FacturacaoHongayetu::class)
            ->call('testCredentials')
            ->assertHasNoErrors()
            ->call('activate')
            ->assertHasNoErrors();

        $integracao = $this->integration()->fresh();

        $this->assertSame('sucesso', $integracao->ultimo_teste_status);
        $this->assertSame('Empresa Fiscal Remota', $integracao->config['remote_empresa_nome'] ?? null);
        $this->assertSame(EstadoIntegracaoEnum::Activo, $integracao->estado);
        $this->assertTrue(app(IntegracaoResolverService::class)->isFacturacaoHongayetuActiva($this->empresa->id));

        Livewire::test(FacturacaoHongayetu::class)
            ->call('deactivate')
            ->assertHasNoErrors();

        $this->assertSame(EstadoIntegracaoEnum::Inactivo, $this->integration()->fresh()->estado);
        $this->assertFalse(app(IntegracaoResolverService::class)->isFacturacaoHongayetuActiva($this->empresa->id));
    }

    public function test_rota_renderiza_com_layout_do_projecto(): void
    {
        $this->get(route('integracoes.facturacao-hongayetu'))
            ->assertOk()
            ->assertSee('Facturação Hongayetu')
            ->assertSee('Configurado internamente');
    }

    public function test_ui_indica_token_nao_configurado_e_nao_chama_client(): void
    {
        Config::set('hongayetu_facturacao.api_token', null);

        $client = Mockery::mock(HongayetuFacturacaoClientInterface::class);
        $client->shouldReceive('verificarCredenciais')->never();
        $this->app->instance(HongayetuFacturacaoClientInterface::class, $client);

        Livewire::test(FacturacaoHongayetu::class)
            ->assertSee('Não configurado')
            ->call('testCredentials')
            ->assertHasErrors(['test'])
            ->assertSee('Token interno da Facturação Hongayetu não configurado. Contacte o suporte técnico.');

        $this->assertSame('falha', $this->integration()->ultimo_teste_status);
    }

    public function test_teste_credenciais_sem_api_url_interna_nao_chama_client(): void
    {
        Config::set('hongayetu_facturacao.api_url', null);

        $client = Mockery::mock(HongayetuFacturacaoClientInterface::class);
        $client->shouldReceive('verificarCredenciais')->never();
        $this->app->instance(HongayetuFacturacaoClientInterface::class, $client);

        Livewire::test(FacturacaoHongayetu::class)
            ->call('testCredentials')
            ->assertHasErrors(['test'])
            ->assertSee('API URL interna da Facturação Hongayetu não configurada. Contacte o suporte técnico.');

        $this->assertSame('falha', $this->integration()->ultimo_teste_status);
    }

    public function test_activation_rejeita_nif_remoto_diferente(): void
    {
        EmpresaIntegracao::query()->create([
            'empresa_id' => $this->empresa->id,
            'tipo' => TipoIntegracaoEnum::Facturacao,
            'provedor' => ProvedorIntegracaoEnum::HongayetuFacturacao,
            'estado' => EstadoIntegracaoEnum::EmConfiguracao,
            'config' => [
                'remote_empresa_nif' => '999999999',
            ],
            'ultimo_teste_em' => now(),
            'ultimo_teste_status' => 'sucesso',
            'created_by' => $this->actor->id,
            'updated_by' => $this->actor->id,
        ]);

        Livewire::test(FacturacaoHongayetu::class)
            ->call('activate')
            ->assertHasErrors(['activation']);

        $this->assertFalse(app(IntegracaoResolverService::class)->isFacturacaoHongayetuActiva($this->empresa->id));
    }

    public function test_menu_condiciona_facturacao_hongayetu_pelo_estado_da_integracao(): void
    {
        $inactiveMenu = view('components.menu-aside')->render();
        $this->assertStringNotContainsString('Facturação Hongayetu', $inactiveMenu);

        $this->activeIntegration();
        $this->assertTrue(app(IntegracaoResolverService::class)->isFacturacaoHongayetuActiva($this->empresa->id));

        $activeMenu = view('components.menu-aside')->render();
        $this->assertStringContainsString('Facturação Hongayetu', $activeMenu);
        $this->assertStringContainsString(route('integracoes.facturacao-hongayetu.facturas'), $activeMenu);
        $this->assertStringContainsString(route('integracoes.facturacao-hongayetu.emitir-ft'), $activeMenu);
    }

    public function test_rotas_operacionais_bloqueiam_quando_integracao_inactiva(): void
    {
        $this->get(route('integracoes.facturacao-hongayetu.facturas'))
            ->assertRedirect(route('integracoes.facturacao-hongayetu'))
            ->assertSessionHas('status', 'A integração de Facturação Hongayetu precisa estar activa para aceder a esta área.');

        $this->get(route('integracoes.facturacao-hongayetu.emitir-ft'))
            ->assertRedirect(route('integracoes.facturacao-hongayetu'));
    }

    public function test_rotas_operacionais_permitidas_quando_integracao_activa(): void
    {
        $this->activeIntegration();

        $this->get(route('integracoes.facturacao-hongayetu.facturas'))
            ->assertOk()
            ->assertSee('Facturas externas');

        $this->get(route('integracoes.facturacao-hongayetu.emitir-ft'))
            ->assertOk()
            ->assertSee('Emitir FT Manual');
    }

    public function test_criar_cliente_com_integracao_inactiva_nao_chama_sincronizacao_hongayetu(): void
    {
        $client = Mockery::mock(HongayetuFacturacaoClientInterface::class);
        $client->shouldReceive('criarCliente')->never();
        $client->shouldReceive('emitirFactura')->never();
        $this->app->instance(HongayetuFacturacaoClientInterface::class, $client);

        Livewire::test(CustomerForm::class)
            ->set('form.CustomerTaxID', '501' . random_int(100000, 999999))
            ->set('form.CustomerType', 'Empresa')
            ->set('form.CompanyName', 'Cliente Sem Hongayetu')
            ->set('form.Email', 'cliente-sem-hongayetu-' . uniqid() . '@example.test')
            ->set('form.Telephone', '923000000')
            ->set('form.Province', 'Luanda')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('customers', [
            'CompanyName' => 'Cliente Sem Hongayetu',
            'empresa_id' => $this->empresa->id,
        ]);
        $this->assertSame(0, ExternalCustomerMapping::query()->count());
    }

    public function test_criar_cliente_com_integracao_activa_sincroniza_e_grava_mapping(): void
    {
        $integracao = $this->activeIntegration();
        $client = Mockery::mock(HongayetuFacturacaoClientInterface::class);
        $client->shouldReceive('criarCliente')
            ->once()
            ->withArgs(function (array $payload) {
                $this->assertSame('Cliente Com Hongayetu', $payload['nome']);
                $this->assertSame(1, $payload['tipo']);
                $this->assertSame('923000000', $payload['telefone']);

                return true;
            })
            ->andReturn([
                'estado' => 'ok',
                'data' => [
                    'id' => 11819,
                    'nome' => 'Cliente Com Hongayetu',
                    'nif' => '502123456',
                ],
            ]);
        $client->shouldReceive('emitirFactura')->never();
        $this->app->instance(HongayetuFacturacaoClientInterface::class, $client);

        Livewire::test(CustomerForm::class)
            ->set('form.CustomerTaxID', '502123456')
            ->set('form.CustomerType', 'Empresa')
            ->set('form.CompanyName', 'Cliente Com Hongayetu')
            ->set('form.Email', 'cliente-com-hongayetu-' . uniqid() . '@example.test')
            ->set('form.Telephone', '923000000')
            ->set('form.Province', 'Luanda')
            ->call('save')
            ->assertHasNoErrors();

        $customer = Customer::query()->where('CompanyName', 'Cliente Com Hongayetu')->firstOrFail();

        $this->assertDatabaseHas('external_customer_mappings', [
            'empresa_id' => $this->empresa->id,
            'empresa_integracao_id' => $integracao->id,
            'customer_id' => $customer->id,
            'provider' => ExternalCustomerMapping::PROVIDER_HONGAYETU_FACTURACAO,
            'external_customer_id' => 11819,
        ]);
    }

    public function test_falha_na_sincronizacao_nao_apaga_cliente_local(): void
    {
        $this->activeIntegration();
        $client = Mockery::mock(HongayetuFacturacaoClientInterface::class);
        $client->shouldReceive('criarCliente')
            ->once()
            ->andThrow(new FacturaExternaFalhouException('Falha fake Hongayetu'));
        $client->shouldReceive('emitirFactura')->never();
        $this->app->instance(HongayetuFacturacaoClientInterface::class, $client);

        Livewire::test(CustomerForm::class)
            ->set('form.CustomerTaxID', '503123456')
            ->set('form.CustomerType', 'Empresa')
            ->set('form.CompanyName', 'Cliente Falha Hongayetu')
            ->set('form.Email', 'cliente-falha-hongayetu-' . uniqid() . '@example.test')
            ->set('form.Telephone', '923000000')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSessionHas('warning', 'Cliente criado localmente, mas não foi possível sincronizar com a Facturação Hongayetu.');

        $customer = Customer::query()->where('CompanyName', 'Cliente Falha Hongayetu')->firstOrFail();

        $this->assertDatabaseMissing('external_customer_mappings', [
            'customer_id' => $customer->id,
        ]);
    }

    public function test_customer_show_expõe_estado_para_botao_sincronizar_quando_sem_mapping(): void
    {
        $this->activeIntegration();
        $customer = $this->customer('Cliente Show Sem Mapping');

        $component = app(CustomerShow::class);
        $component->mount($customer, app(\App\Application\Customer\Queries\CustomerDetailsQuery::class));

        $this->assertTrue($component->hongayetuActiva);
        $this->assertNull($component->hongayetuMapping);
        $this->assertStringContainsString(
            'Sincronizar com Hongayetu',
            file_get_contents(resource_path('views/livewire/customers/customer-show.blade.php'))
        );
    }

    public function test_customer_show_expõe_estado_sincronizado_quando_mapping_existe(): void
    {
        $integracao = $this->activeIntegration();
        $customer = $this->customer('Cliente Show Mapeado');
        ExternalCustomerMapping::query()->create([
            'empresa_id' => $this->empresa->id,
            'empresa_integracao_id' => $integracao->id,
            'customer_id' => $customer->id,
            'provider' => ExternalCustomerMapping::PROVIDER_HONGAYETU_FACTURACAO,
            'external_customer_id' => 11819,
            'synced_at' => now(),
        ]);

        $component = app(CustomerShow::class);
        $component->mount($customer, app(\App\Application\Customer\Queries\CustomerDetailsQuery::class));

        $this->assertTrue($component->hongayetuActiva);
        $this->assertSame(11819, $component->hongayetuMapping?->external_customer_id);
    }

    public function test_customer_show_sincroniza_cliente_manual_sem_mapping(): void
    {
        $integracao = $this->activeIntegration();
        $customer = $this->customer('Cliente Show Manual');
        $client = Mockery::mock(HongayetuFacturacaoClientInterface::class);
        $client->shouldReceive('criarCliente')
            ->once()
            ->andReturn([
                'estado' => 'ok',
                'data' => [
                    'id' => 11820,
                    'nome' => 'Cliente Show Manual',
                    'nif' => $customer->CustomerTaxID,
                ],
            ]);
        $client->shouldReceive('emitirFactura')->never();
        $this->app->instance(HongayetuFacturacaoClientInterface::class, $client);

        $component = app(CustomerShow::class);
        $component->mount($customer, app(\App\Application\Customer\Queries\CustomerDetailsQuery::class));
        app()->call([$component, 'sincronizarHongayetu']);

        $this->assertSame('Cliente sincronizado com a Facturação Hongayetu.', $component->hongayetuSyncMessage);
        $this->assertDatabaseHas('external_customer_mappings', [
            'empresa_id' => $this->empresa->id,
            'empresa_integracao_id' => $integracao->id,
            'customer_id' => $customer->id,
            'external_customer_id' => 11820,
        ]);
    }

    public function test_listagem_mostra_apenas_facturas_da_empresa_actual(): void
    {
        $integracao = $this->activeIntegration();
        $customer = $this->customer('Cliente Listado');
        $otherEmpresa = Empresa::withoutEvents(fn () => Empresa::query()->create([
            'Empresa' => 'Empresa Outra',
            'NIF' => '600000000',
            'Endereco_completo' => 'Luanda',
            'conta' => 'EMP-OUTRA-TEST',
        ]));

        ExternalInvoice::query()->create([
            'empresa_id' => $this->empresa->id,
            'empresa_integracao_id' => $integracao->id,
            'customer_id' => $customer->id,
            'provider' => ExternalInvoice::PROVIDER_HONGAYETU_FACTURACAO,
            'external_invoice_number' => 'FT 001/2026',
            'document_type' => ExternalInvoice::DOC_TYPE_FT,
            'api_tipo' => ExternalInvoice::API_TIPO_FT,
            'status' => ExternalInvoice::STATUS_ISSUED,
            'currency' => 'AOA',
            'gross_total' => 1500,
            'issue_date' => now(),
            'local_reference' => 'LOCAL-001',
        ]);

        ExternalInvoice::query()->create([
            'empresa_id' => $otherEmpresa->id,
            'customer_id' => $customer->id,
            'provider' => ExternalInvoice::PROVIDER_HONGAYETU_FACTURACAO,
            'external_invoice_number' => 'FT OUTRA/2026',
            'document_type' => ExternalInvoice::DOC_TYPE_FT,
            'api_tipo' => ExternalInvoice::API_TIPO_FT,
            'status' => ExternalInvoice::STATUS_ISSUED,
            'currency' => 'AOA',
            'gross_total' => 2500,
            'issue_date' => now(),
        ]);

        Livewire::test(FacturacaoHongayetuFacturas::class)
            ->assertSee('FT 001/2026')
            ->assertDontSee('FT OUTRA/2026')
            ->set('numero', 'LOCAL-001')
            ->assertSee('FT 001/2026');
    }

    public function test_tela_emitir_ft_carrega_clientes_locais_e_preenche_cliente_sem_mapping(): void
    {
        $this->activeIntegration();
        $customer = $this->customer('Cliente Local Sem Mapping');

        Livewire::test(FacturacaoHongayetuEmitirFt::class)
            ->assertSee('Cliente Local Sem Mapping')
            ->set('form.customer_id', $customer->id)
            ->assertSet('form.cliente_nome', 'Cliente Local Sem Mapping')
            ->assertSet('form.cliente_nif', $customer->CustomerTaxID)
            ->assertSet('form.external_customer_id', '')
            ->assertSee('Cliente será enviado por nome/NIF nesta emissão.');
    }

    public function test_tela_emitir_ft_preenche_external_customer_id_quando_cliente_tem_mapping(): void
    {
        $integracao = $this->activeIntegration();
        $customer = $this->customer('Cliente Com Mapping');

        ExternalCustomerMapping::query()->create([
            'empresa_id' => $this->empresa->id,
            'empresa_integracao_id' => $integracao->id,
            'customer_id' => $customer->id,
            'provider' => ExternalCustomerMapping::PROVIDER_HONGAYETU_FACTURACAO,
            'external_customer_id' => 535,
            'external_customer_name' => 'Cliente Com Mapping',
            'external_customer_nif' => $customer->CustomerTaxID,
            'synced_at' => now(),
        ]);

        Livewire::test(FacturacaoHongayetuEmitirFt::class)
            ->set('form.customer_id', $customer->id)
            ->assertSet('form.external_customer_id', '535')
            ->assertSee('Cliente já mapeado com Hongayetu.');
    }

    public function test_produto_local_preenche_linha_e_artigo_externo_continua_obrigatorio(): void
    {
        $this->activeIntegration();
        $produto = $this->produto('Serviço Local', 2500, null);

        Livewire::test(FacturacaoHongayetuEmitirFt::class)
            ->assertSee('Serviço Local')
            ->call('selectProdutoServico', 0, 'product:' . $produto->id)
            ->assertSet('form.linhas.0.descricao', 'Serviço Local')
            ->assertSet('form.linhas.0.preco', 2500.0)
            ->assertSet('form.linhas.0.external_artigo_id', '')
            ->assertSee('Este produto/serviço ainda não tem artigo externo mapeado.');
    }

    public function test_emitir_ft_exige_confirmacao_e_linha_com_artigo_externo(): void
    {
        $this->activeIntegration();
        $customer = $this->customer('Cliente Sem Mapping');

        Livewire::test(FacturacaoHongayetuEmitirFt::class)
            ->set('form.customer_id', $customer->id)
            ->set('form.linhas.0.external_artigo_id', '')
            ->set('form.linhas.0.descricao', 'Serviço')
            ->set('form.linhas.0.quantidade', 1)
            ->set('form.linhas.0.preco', 1000)
            ->set('form.confirmarEmissao', true)
            ->call('emitir')
            ->assertHasErrors(['form.linhas.0.external_artigo_id']);

        Livewire::test(FacturacaoHongayetuEmitirFt::class)
            ->set('form.customer_id', $customer->id)
            ->set('form.linhas.0.external_artigo_id', 10)
            ->set('form.linhas.0.descricao', 'Serviço')
            ->set('form.linhas.0.quantidade', 1)
            ->set('form.linhas.0.preco', 1000)
            ->call('emitir')
            ->assertHasErrors(['form.confirmarEmissao']);
    }

    public function test_emitir_ft_manual_sem_mapping_usa_cliente_nome_nif_e_client_mockado(): void
    {
        $integracao = $this->activeIntegration();
        $customer = $this->customer('Cliente Sem Mapping Emitido');

        $client = Mockery::mock(HongayetuFacturacaoClientInterface::class);
        $client->shouldReceive('emitirFactura')
            ->once()
            ->withArgs(function (array $payload, array $config = [], array $credentials = []): bool {
                $this->assertArrayNotHasKey('cliente_id', $payload);
                $this->assertSame('Cliente Sem Mapping Emitido', $payload['cliente_nome']);
                $this->assertNotEmpty($payload['cliente_nif']);
                $this->assertSame(1, $payload['tipo']);
                $this->assertSame(0, $payload['moeda']);
                $this->assertSame(10, $payload['artigos'][0]['artigo_id']);

                return true;
            })
            ->andReturn([
                'estado' => 'ok',
                'data' => [
                    'factura' => [
                        'id' => 50,
                        'codigo_formatado' => 'FT 050/2026',
                        'total' => 1000,
                    ],
                ],
            ]);
        $this->app->instance(HongayetuFacturacaoClientInterface::class, $client);

        Livewire::test(FacturacaoHongayetuEmitirFt::class)
            ->set('form.customer_id', $customer->id)
            ->set('form.estabelecimento_id', 1)
            ->set('form.nossa_referencia', 'MANUAL-001')
            ->set('form.linhas.0.external_artigo_id', 10)
            ->set('form.linhas.0.descricao', 'Serviço manual')
            ->set('form.linhas.0.quantidade', 1)
            ->set('form.linhas.0.preco', 1000)
            ->set('form.confirmarEmissao', true)
            ->call('emitir')
            ->assertHasNoErrors()
            ->assertSee('Factura Comercial emitida com sucesso.')
            ->assertSee('FT 050/2026');

        $this->assertDatabaseHas('external_invoices', [
            'empresa_id' => $this->empresa->id,
            'customer_id' => $customer->id,
            'document_type' => ExternalInvoice::DOC_TYPE_FT,
            'api_tipo' => ExternalInvoice::API_TIPO_FT,
            'status' => ExternalInvoice::STATUS_ISSUED,
            'external_invoice_number' => 'FT 050/2026',
        ]);
    }

    public function test_emitir_ft_manual_com_mapping_continua_a_usar_cliente_id_externo(): void
    {
        $integracao = $this->activeIntegration();
        $customer = $this->customer('Cliente Mapeado');

        ExternalCustomerMapping::query()->create([
            'empresa_id' => $this->empresa->id,
            'empresa_integracao_id' => $integracao->id,
            'customer_id' => $customer->id,
            'provider' => ExternalCustomerMapping::PROVIDER_HONGAYETU_FACTURACAO,
            'external_customer_id' => 535,
            'external_customer_name' => 'Cliente Mapeado',
            'external_customer_nif' => $customer->CustomerTaxID,
            'synced_at' => now(),
        ]);

        $client = Mockery::mock(HongayetuFacturacaoClientInterface::class);
        $client->shouldReceive('emitirFactura')
            ->once()
            ->withArgs(function (array $payload): bool {
                $this->assertSame(535, $payload['cliente_id']);
                $this->assertArrayNotHasKey('cliente_nome', $payload);

                return true;
            })
            ->andReturn([
                'estado' => 'ok',
                'data' => [
                    'factura' => [
                        'id' => 51,
                        'codigo_formatado' => 'FT 051/2026',
                        'total' => 1000,
                    ],
                ],
            ]);
        $this->app->instance(HongayetuFacturacaoClientInterface::class, $client);

        Livewire::test(FacturacaoHongayetuEmitirFt::class)
            ->set('form.customer_id', $customer->id)
            ->set('form.linhas.0.external_artigo_id', 10)
            ->set('form.linhas.0.descricao', 'Serviço manual')
            ->set('form.linhas.0.quantidade', 1)
            ->set('form.linhas.0.preco', 1000)
            ->set('form.confirmarEmissao', true)
            ->call('emitir')
            ->assertHasNoErrors()
            ->assertSee('FT 051/2026');
    }

    private function integration(): EmpresaIntegracao
    {
        return EmpresaIntegracao::query()
            ->where('empresa_id', $this->empresa->id)
            ->where('tipo', TipoIntegracaoEnum::Facturacao->value)
            ->where('provedor', ProvedorIntegracaoEnum::HongayetuFacturacao->value)
            ->firstOrFail();
    }

    private function activeIntegration(): EmpresaIntegracao
    {
        return EmpresaIntegracao::query()->updateOrCreate([
            'empresa_id' => $this->empresa->id,
            'tipo' => TipoIntegracaoEnum::Facturacao->value,
            'provedor' => ProvedorIntegracaoEnum::HongayetuFacturacao->value,
        ], [
            'estado' => EstadoIntegracaoEnum::Activo->value,
            'config' => [
                'remote_empresa_nif' => $this->empresa->NIF,
            ],
            'ultimo_teste_em' => now(),
            'ultimo_teste_status' => 'sucesso',
            'created_by' => $this->actor->id,
            'updated_by' => $this->actor->id,
        ])->refresh();
    }

    private function customer(string $name): Customer
    {
        return Customer::query()->create([
            'CustomerID' => 'CUST-' . uniqid(),
            'AccountID' => 'ACC-' . uniqid(),
            'CustomerTaxID' => (string) random_int(100000000, 999999999),
            'CompanyName' => $name,
            'Telephone' => '923000000',
            'Email' => (string) str($name)->slug() . '-' . uniqid() . '@example.test',
            'SelfBillingIndicator' => 0,
            'user_id' => $this->actor->id,
            'empresa_id' => $this->empresa->id,
        ]);
    }

    private function produto(string $name, float $price, ?int $externalArtigoId): Produto
    {
        $produto = Produto::query()->create([
            'empresa_id' => $this->empresa->id,
            'ProductType' => 'S',
            'ProductCode' => 'PROD-' . uniqid(),
            'ProductGroup' => 1,
            'ProductDescription' => $name,
            'ProductNumberCode' => $externalArtigoId ? (string) $externalArtigoId : 'LOCAL-' . uniqid(),
            'status' => 0,
        ]);

        ProductPrice::query()->create([
            'fk_product' => $produto->id,
            'unidade' => 'UN',
            'custo' => 0,
            'venda' => $price,
            'venda_sem_iva' => $price,
            'lucro' => $price,
            'taxID' => '0',
            'imposto' => 0,
            'reasonID' => 1,
            'taxAmount' => 0,
            'ativo' => true,
        ]);

        return $produto->refresh();
    }

    private function adminForEmpresa(Empresa $empresa): User
    {
        $user = User::factory()->create([
            'email' => 'admin-hongayetu-' . uniqid() . '@example.test',
            'is_active' => true,
            'is_blocked' => false,
        ]);

        $empresa->users()->attach($user->id, ['conta' => $empresa->conta]);
        $user->assignRole($this->role('Administrador'));

        return $user->refresh();
    }

    private function role(string $name): Role
    {
        return Role::query()->firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
        ]);
    }

    private function skipUnlessDatabaseEnvironmentIsSafe(): void
    {
        $appEnv = $this->envValue('APP_ENV');
        $connection = $this->envValue('DB_CONNECTION');
        $database = $this->envValue('DB_DATABASE');
        $filesystem = $this->envValue('FILESYSTEM_DISK');
        $awsBucket = $this->envValue('AWS_BUCKET');

        if ($appEnv !== 'testing'
            || $connection !== 'mysql'
            || $database !== 'logigate_testing'
            || $filesystem !== 'local'
            || ($awsBucket !== null && trim($awsBucket) !== '')
        ) {
            $this->markTestSkipped('Ambiente de teste inseguro para gravar dados Livewire de integração Hongayetu.');
        }
    }

    private function envValue(string $key): ?string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        return $value === false ? null : (string) $value;
    }
}
