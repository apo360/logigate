<?php

namespace App\Domains\Empresa\Actions;

use App\Domains\Empresa\Services\GeradorCodigoContaEmpresaService;

final class GerarCodigoContaEmpresaAction
{
    public function __construct(
        private readonly GeradorCodigoContaEmpresaService $service,
    ) {
    }

    public function execute(): string
    {
        return $this->service->gerar();
    }
}
