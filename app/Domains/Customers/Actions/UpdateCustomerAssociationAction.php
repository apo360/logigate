<?php

namespace App\Domains\Customers\Actions;

use App\Models\Customer;
use App\Models\Empresa;
use Illuminate\Support\Facades\Schema;

final readonly class UpdateCustomerAssociationAction
{
    public function execute(Customer $customer, Empresa $empresa, array $data): Customer
    {
        if (!Schema::hasTable('customers_empresas')) {
            return $customer->refresh();
        }

        $customer->empresas()->syncWithoutDetaching([
            $empresa->id => [
                'codigo_cliente' => $data['codigo_cliente'] ?? null,
                'additional_info' => $data['additional_info'] ?? null,
                'status' => $data['status'] ?? 'ATIVO',
                'data_associacao' => $data['data_associacao'] ?? now(),
            ],
        ]);

        return $customer->refresh()->load('empresas');
    }
}
