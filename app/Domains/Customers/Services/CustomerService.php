<?php

namespace App\Domains\Customers\Services;

use App\Domains\Customers\Actions\CreateCustomerAction;
use App\Domains\Customers\Data\CustomerFormData;
use App\Models\Customer;
use App\Models\Empresa;

final class CustomerService
{
    public function __construct(
        private readonly CreateCustomerAction $createCustomerAction,
    ) {
    }

    public function create(CustomerFormData $data, Empresa $empresa): Customer
    {
        return $this->createCustomerAction->execute($data, $empresa);
    }
}
