<?php

namespace App\Domains\Customers\Actions;

use App\Domains\Customers\Data\CustomerFormData;
use App\Models\Customer;
use App\Models\Empresa;

final class CreateCustomerAction
{
    public function __construct(private readonly CreateOrAssociateCustomerAction $createOrAssociateCustomerAction)
    {
    }

    public function execute(CustomerFormData $data, Empresa $empresa): Customer
    {
        return $this->createOrAssociateCustomerAction->execute($data, $empresa);
    }
}
