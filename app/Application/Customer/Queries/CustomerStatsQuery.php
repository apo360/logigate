<?php

namespace App\Application\Customer\Queries;

use App\Application\Customer\Services\CustomerTenantAccessService;
use App\Models\Customer;

final class CustomerStatsQuery
{
    public function __construct(
        private readonly CustomerTenantAccessService $access,
    ) {
    }

    public function execute(): array
    {
        $empresaId = $this->access->currentEmpresaId();

        abort_if(!$empresaId, 403, 'Empresa actual não encontrada.');

        $base = Customer::query()->forEmpresa($empresaId);

        return [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('is_active', true)->count(),
            'inactive' => (clone $base)->where('is_active', false)->count(),
        ];
    }
}
