<?php

namespace App\Domains\Customers\Queries;

use App\Models\Customer;

class ClienteTableQuery
{
    public function build(
        string $search = '',
        string $isActive = '',
        string $tipoCliente = '',
        ?int $empresaId = null
    ) {
        return Customer::query()
            ->withCount([
                'processos as processos_total_count',
                'licenciamento as licenciamentos_total_count',
            ])
            ->when($empresaId, function ($query) use ($empresaId) {
                $query->where(function ($tenantQuery) use ($empresaId) {
                    $tenantQuery->where('empresa_id', $empresaId)
                        ->orWhereHas('empresas', function ($empresaQuery) use ($empresaId) {
                            $empresaQuery->where('empresas.id', $empresaId);
                        });
                });
            })
            ->when(trim($search) !== '', function ($query) use ($search) {
                $search = trim($search);

                $query->where(function ($q) use ($search) {
                    $q->where('CompanyName', 'like', "%{$search}%")
                        ->orWhere('CustomerTaxID', 'like', "%{$search}%")
                        ->orWhere('Telephone', 'like', "%{$search}%")
                        ->orWhere('Email', 'like', "%{$search}%")
                        ->orWhere('CustomerID', 'like', "%{$search}%");
                });
            })
            ->when($isActive !== '', function ($query) use ($isActive) {
                $query->where('is_active', (bool) $isActive);
            })
            ->when($tipoCliente !== '', function ($query) use ($tipoCliente) {
                $query->where('tipo_cliente', strtolower($tipoCliente));
            });
    }
}