<?php

namespace App\Domains\Customers\Actions;

use App\Models\Customer;
use App\Models\Empresa;

final readonly class UpdateCustomerAssociationAction
{
    public function execute(Customer $customer, Empresa $empresa, array $data): Customer
    {
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
