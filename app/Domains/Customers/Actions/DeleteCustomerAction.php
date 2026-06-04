<?php

namespace App\Domains\Customers\Actions;

use App\Models\Customer;

final class DeleteCustomerAction
{
    public function execute(Customer $customer): void
    {
        if ($customer->processos()->exists()) {
            throw new \InvalidArgumentException('O cliente possui processos relacionados e não pode ser removido!');
        }

        if ($customer->invoices()->exists()) {
            throw new \InvalidArgumentException('O cliente possui faturas relacionados e não pode ser removido!');
        }

        $customer->delete();
    }
}
