<?php

namespace Tests\Unit\Integracoes;

use App\Application\Integracoes\Services\IntegracaoResolverService;
use App\Domains\Integracoes\Enums\EstadoIntegracaoEnum;
use App\Domains\Integracoes\Enums\ProvedorIntegracaoEnum;
use App\Domains\Integracoes\Enums\TipoIntegracaoEnum;
use App\Domains\Integracoes\Exceptions\IntegracaoNaoConfiguradaException;
use App\Domains\Integracoes\Repositories\EmpresaIntegracaoRepositoryInterface;
use App\Models\Empresa;
use App\Models\EmpresaIntegracao;
use Illuminate\Support\Collection;
use Tests\TestCase;

class IntegracoesDomainTest extends TestCase
{
    public function test_provider_declares_its_integration_type(): void
    {
        $this->assertSame(TipoIntegracaoEnum::Facturacao, TipoIntegracaoEnum::FACTURACAO);
        $this->assertSame(ProvedorIntegracaoEnum::HongayetuFacturacao, ProvedorIntegracaoEnum::HONGAYETU_FACTURACAO);
        $this->assertSame(EstadoIntegracaoEnum::EmConfiguracao, EstadoIntegracaoEnum::EM_CONFIGURACAO);
        $this->assertSame(TipoIntegracaoEnum::Facturacao, ProvedorIntegracaoEnum::HongayetuFacturacao->tipo());
        $this->assertSame(TipoIntegracaoEnum::WhatsApp, ProvedorIntegracaoEnum::MetaWhatsApp->tipo());
        $this->assertSame(TipoIntegracaoEnum::Sms, ProvedorIntegracaoEnum::GenericSms->tipo());
        $this->assertSame('blue', TipoIntegracaoEnum::Facturacao->color());
        $this->assertSame('green', ProvedorIntegracaoEnum::MetaWhatsApp->color());
        $this->assertSame('slate', EstadoIntegracaoEnum::EmConfiguracao->color());
    }

    public function test_credentials_are_encrypted_and_masked(): void
    {
        $integration = new EmpresaIntegracao([
            'empresa_id' => 1,
            'tipo' => TipoIntegracaoEnum::Facturacao,
            'provedor' => ProvedorIntegracaoEnum::HongayetuFacturacao,
            'estado' => EstadoIntegracaoEnum::EmConfiguracao,
            'config' => ['api_url' => 'https://facturacao.internal'],
        ]);

        $integration->setCredentials(['api_token' => 'tok_1234567890_secret']);

        $this->assertNotSame('tok_1234567890_secret', $integration->getAttributes()['credentials_encrypted']);
        $this->assertSame('tok_1234567890_secret', $integration->credentials()['api_token']);
        $this->assertSame('tok_********cret', $integration->maskedCredentials()['api_token']);
        $this->assertArrayNotHasKey('credentials_encrypted', $integration->toArray());
    }

    public function test_resolver_returns_active_integration(): void
    {
        $empresa = new Empresa(['Empresa' => 'Demo']);
        $empresa->id = 10;

        $integration = new EmpresaIntegracao([
            'empresa_id' => 10,
            'tipo' => TipoIntegracaoEnum::Facturacao,
            'provedor' => ProvedorIntegracaoEnum::HongayetuFacturacao,
            'estado' => EstadoIntegracaoEnum::Activo,
        ]);

        $resolver = new IntegracaoResolverService(new class($integration) implements EmpresaIntegracaoRepositoryInterface {
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
                return $this->integration;
            }

            public function upsert(Empresa $empresa, array $attributes, array $credentials = []): EmpresaIntegracao
            {
                return $this->integration;
            }
        });

        $this->assertSame($integration, $resolver->resolve($empresa, TipoIntegracaoEnum::Facturacao, ProvedorIntegracaoEnum::HongayetuFacturacao));
    }

    public function test_resolver_throws_when_integration_is_missing(): void
    {
        $this->expectException(IntegracaoNaoConfiguradaException::class);

        $empresa = new Empresa(['Empresa' => 'Demo']);
        $empresa->id = 10;

        $resolver = new IntegracaoResolverService(new class implements EmpresaIntegracaoRepositoryInterface {
            public function listForEmpresa(Empresa $empresa): Collection
            {
                return collect();
            }

            public function findForEmpresa(Empresa $empresa, TipoIntegracaoEnum $tipo, ProvedorIntegracaoEnum $provedor): ?EmpresaIntegracao
            {
                return null;
            }

            public function activeForEmpresa(Empresa $empresa, TipoIntegracaoEnum $tipo, ?ProvedorIntegracaoEnum $provedor = null): ?EmpresaIntegracao
            {
                return null;
            }

            public function upsert(Empresa $empresa, array $attributes, array $credentials = []): EmpresaIntegracao
            {
                throw new \RuntimeException('Not used.');
            }
        });

        $resolver->resolve($empresa, TipoIntegracaoEnum::Facturacao);
    }
}
