<?php

namespace App\Domains\Customers\Actions;

use App\Domains\Customers\Data\CustomerFormData;
use App\Domains\Customers\Events\CustomerUpdated;
use App\Models\Customer;

final class UpdateCustomerAction
{
    public function __construct(private readonly UpdateCustomerProfileAction $updateCustomerProfileAction)
    {
    }

    public function execute(Customer $customer, CustomerFormData $data): Customer
    {
        $customer = $this->updateCustomerProfileAction->execute($customer, $data);

        event(new CustomerUpdated($customer));

        return $customer;
    }
}
