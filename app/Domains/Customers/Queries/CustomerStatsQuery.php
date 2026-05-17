<?php

namespace App\Domains\Customers\Queries;

use App\Domains\Customers\Data\CustomerStatsData;
use App\Models\Customer;

final class CustomerStatsQuery
{
    public function forEmpresa(int $empresaId): CustomerStatsData
    {
        $query = Customer::query()->where('empresa_id', $empresaId);

        return new CustomerStatsData(
            total: (clone $query)->count(),
            ativos: (clone $query)->where('is_active', 1)->count(),
            importadores: (clone $query)->where('tipo_cliente', 'importador')->count(),
            empresas: (clone $query)->where('CustomerType', 'Empresa')->count(),
        );
    }
}
