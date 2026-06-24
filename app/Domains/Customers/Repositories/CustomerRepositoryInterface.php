<?php

namespace App\Domains\Customers\Repositories;

use App\Application\Customer\DTOs\UpdateCustomerDTO;
use App\Application\Customer\DTOs\CreateCustomerDTO;
use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CustomerRepositoryInterface
{
    public function create(CreateCustomerDTO $dto): Customer;

    public function update(int $id, UpdateCustomerDTO $dto): Customer;

    public function find(int $id): ?Customer;

    public function findOrFail(int $id): Customer;

    public function findByTaxId(string $taxId): ?Customer;

    public function findByCustomerId(string $customerId): ?Customer;

    public function paginateForEmpresa(int $empresaId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function associateToEmpresa(int $customerId, int $empresaId, array $pivotData = []): void;

    public function detachFromEmpresa(int $customerId, int $empresaId): void;

    public function isAssociatedWithEmpresa(int $customerId, int $empresaId): bool;

    public function belongsToEmpresa(int $customerId, int $empresaId): bool;

    public function activate(int $customerId): Customer;

    public function deactivate(int $customerId): Customer;

    public function delete(int $customerId): bool;
}