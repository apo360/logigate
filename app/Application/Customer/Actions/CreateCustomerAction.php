<?php

namespace App\Application\Customer\Actions;

use App\Application\Customer\DTOs\CreateCustomerDTO;
use App\Application\Customer\Services\CustomerCodeGenerator;
use App\Domains\Customers\Repositories\CustomerRepositoryInterface;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CreateCustomerAction
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
        private readonly CustomerCodeGenerator $codeGenerator,
    ) {
    }

    public function execute(CreateCustomerDTO $dto): Customer
    {
        return DB::transaction(function () use ($dto) {
            $existing = $this->customers->findByTaxId($dto->CustomerTaxID);

            if ($existing) {
                $this->customers->associateToEmpresa($existing->id, $dto->empresa_id);

                return $existing->refresh();
            }

            $data = $dto->toModelArray();

            if (empty($data['CustomerID'])) {
                $data['CustomerID'] = $this->codeGenerator->generate($dto->empresa_id, $dto->CustomerTaxID);
            }

            $newDto = CreateCustomerDTO::fromArray(array_merge($data, ['endereco' => $dto->endereco,]));

            $customer = $this->customers->create($newDto);

            $this->customers->associateToEmpresa($customer->id, $dto->empresa_id);

            return $customer->refresh();
        });
    }
}
