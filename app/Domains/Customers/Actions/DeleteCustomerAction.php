<?php

namespace App\Domains\Customers\Actions;

use App\Models\Customer;

final class DeleteCustomerAction
{
    public function execute(Customer $customer): void
    {
        $customer->delete();
    }
}
