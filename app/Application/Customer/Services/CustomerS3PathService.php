<?php

namespace App\Application\Customer\Services;

use App\Models\Customer;

class CustomerS3PathService
{
    public function customerRoot(Customer $customer, int $empresaId): string
    {
        return "empresa/{$empresaId}/clientes/{$customer->id}/";
    }

    public function folders(Customer $customer, int $empresaId): array
    {
        $root = $this->customerRoot($customer, $empresaId);

        return [
            $root,
            $root . 'processos/',
            $root . 'licenciamentos/',
            $root . 'facturas/',
            $root . 'comprovativos/',
            $root . 'geral/',
        ];
    }
}