<?php

namespace App\Domains\Customers\Queries;

use App\Models\Customer;

final class CustomerDetailsQuery
{
    public function findForEmpresa(int $empresaId, int $customerId): ?Customer
    {
        return Customer::query()
            ->where('empresa_id', $empresaId)
            ->with(['endereco', 'empresas'])
            ->find($customerId);
    }
}
