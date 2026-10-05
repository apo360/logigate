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
        return $this->createCustomerAction->execute(CreateCustomerDTO::fromArray(array_merge(
            $data->toArray(),
            [
                'empresa_id' => (int) $empresa->id,
                'user_id' => (int) \Illuminate\Support\Facades\Auth::id(),
                'endereco' => [
                    'AddressDetail' => $data->addressDetail,
                    'AddressType' => $data->addressType,
                    'City' => $data->city,
                    'Country' => $data->country,
                    'PostalCode' => $data->postalCode,
                    'Province' => $data->province,
                    'BuildingNumber' => $data->buildingNumber,
                    'StreetName' => $data->streetName,
                ],
            ],
        )));
    }
}
