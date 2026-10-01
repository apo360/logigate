<?php

namespace App\Domains\Customers\Services;

use App\Application\Customer\Actions\CreateCustomerAction;
use App\Application\Customer\DTOs\CreateCustomerDTO;
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
        $attributes = $data->toArray();
        $attributes['empresa_id'] = (int) $empresa->id;
        $attributes['endereco'] = array_intersect_key($attributes, array_flip([
            'AddressDetail', 'AddressType', 'City', 'Country', 'PostalCode',
            'Province', 'BuildingNumber', 'StreetName',
        ]));

        return $this->createCustomerAction->execute(CreateCustomerDTO::fromArray($attributes));
    }
}
