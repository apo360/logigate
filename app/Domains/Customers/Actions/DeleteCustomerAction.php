<?php

namespace App\Domains\Customers\Actions;

use App\Models\Customer;
use Illuminate\Support\Facades\Schema;

final class DeleteCustomerAction
{
    public function execute(Customer $customer): void
    {
        if (Schema::hasTable('processos') && $customer->processos()->exists()) {
            throw new \InvalidArgumentException('O cliente possui processos relacionados e não pode ser removido!');
        }

        if (Schema::hasTable('sales_invoices') && $customer->invoices()->exists()) {
            throw new \InvalidArgumentException('O cliente possui faturas relacionados e não pode ser removido!');
        }

        $customer->delete();
    }
}
