<?php

namespace App\Domains\Integracoes\Repositories;

use App\Domains\Integracoes\Enums\ProvedorIntegracaoEnum;
use App\Domains\Integracoes\Enums\TipoIntegracaoEnum;
use App\Models\Empresa;
use App\Models\EmpresaIntegracao;
use Illuminate\Support\Collection;

interface EmpresaIntegracaoRepositoryInterface
{
    public function listForEmpresa(Empresa $empresa): Collection;

    public function findForEmpresa(
        Empresa $empresa,
        TipoIntegracaoEnum $tipo,
        ProvedorIntegracaoEnum $provedor
    ): ?EmpresaIntegracao;

    public function activeForEmpresa(
        Empresa $empresa,
        TipoIntegracaoEnum $tipo,
        ?ProvedorIntegracaoEnum $provedor = null
    ): ?EmpresaIntegracao;

    public function upsert(Empresa $empresa, array $attributes, array $credentials = []): EmpresaIntegracao;
}
