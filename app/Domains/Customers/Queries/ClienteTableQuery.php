<?php

namespace App\Domains\Customers\Queries;

use App\Models\Customer;

class ClienteTableQuery
{
    public function build(string $search = '', string $isActive = '', string $tipoCliente = '', ?int $empresaId = null)
    {
        $query = Customer::query();

        if ($empresaId) {
            $query->where('empresa_id', $empresaId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('CompanyName', 'like', "%{$search}%")
                    ->orWhere('CustomerTaxID', 'like', "%{$search}%");
            });
        }

        if ($isActive !== '') {
            $query->where('is_active', $isActive);
        }

        if ($tipoCliente) {
            $query->where('tipo_cliente', strtolower($tipoCliente));
        }

        return $query;
    }
}
