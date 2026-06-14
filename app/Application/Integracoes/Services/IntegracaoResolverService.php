<?php

namespace App\Application\Integracoes\Services;

use App\Domains\Integracoes\Enums\ProvedorIntegracaoEnum;
use App\Domains\Integracoes\Enums\TipoIntegracaoEnum;
use App\Domains\Integracoes\Exceptions\IntegracaoNaoConfiguradaException;
use App\Domains\Integracoes\Repositories\EmpresaIntegracaoRepositoryInterface;
use App\Models\Empresa;
use App\Models\EmpresaIntegracao;

final readonly class IntegracaoResolverService
{
    public function __construct(private EmpresaIntegracaoRepositoryInterface $integracoes)
    {
    }

    public function resolve(
        Empresa $empresa,
        TipoIntegracaoEnum $tipo,
        ?ProvedorIntegracaoEnum $provedor = null
    ): EmpresaIntegracao {
        $integracao = $this->integracoes->activeForEmpresa($empresa, $tipo, $provedor);

        if (! $integracao) {
            throw new IntegracaoNaoConfiguradaException("Integração activa não configurada para {$tipo->value}.");
        }

        return $integracao;
    }

    public function resolveForEmpresaId(
        int $empresaId,
        TipoIntegracaoEnum $tipo,
        ?ProvedorIntegracaoEnum $provedor = null
    ): EmpresaIntegracao {
        $empresa = new Empresa();
        $empresa->id = $empresaId;

        return $this->resolve($empresa, $tipo, $provedor);
    }
}
