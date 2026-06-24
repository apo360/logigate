<?php

namespace App\Domains\Customers\Actions;

use App\Models\Customer;

final readonly class ToggleCustomerStatusAction
{
    public function execute(Customer $customer, bool $active): Customer
    {
        $customer->is_active = $active ? 1 : 0;
        $customer->save();

        return $customer->refresh();
    }
}
