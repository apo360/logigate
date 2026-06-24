<?php

namespace App\Domains\Customers\Repositories;

use App\Application\Customer\DTOs\UpdateCustomerDTO;
use App\Application\Customer\DTOs\CreateCustomerDTO;
use App\Domains\Customers\Exceptions\CustomerNotFoundException;
use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EloquentCustomerRepository implements CustomerRepositoryInterface
{
    public function create(CreateCustomerDTO $dto): Customer
    {
        return DB::transaction(function () use ($dto) {
            return Customer::query()->create($dto->toModelArray());
        });
    }

    public function update(int $id, UpdateCustomerDTO $dto): Customer
    {
        return DB::transaction(function () use ($id, $dto) {
            $customer = $this->findOrFail($id);

            $customer->fill($dto->data);
            $customer->save();

            return $customer->refresh();
        });
    }

    public function find(int $id): ?Customer
    {
        return Customer::query()->find($id);
    }

    public function findOrFail(int $id): Customer
    {
        $customer = $this->find($id);

        if (!$customer) {
            throw new CustomerNotFoundException();
        }

        return $customer;
    }

    public function findByTaxId(string $taxId): ?Customer
    {
        return Customer::query()
            ->where('CustomerTaxID', trim($taxId))
            ->first();
    }

    public function findByCustomerId(string $customerId): ?Customer
    {
        return Customer::query()
            ->where('CustomerID', trim($customerId))
            ->first();
    }

    public function paginateForEmpresa(int $empresaId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Customer::query()
            ->with('empresas')
            ->withCount([
                'processos',
                'licenciamento as licenciamentos_count',
                'documentosArquivos as documentos_count',
            ])
            ->forEmpresa($empresaId)
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->search($search))
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', function ($q) use ($filters) {
                $q->where('is_active', (bool) $filters['is_active']);
            })
            ->when($filters['tipo_cliente'] ?? null, fn ($q, $tipo) => $q->where('tipo_cliente', $tipo))
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function associateToEmpresa(int $customerId, int $empresaId, array $pivotData = []): void
    {
        $customer = $this->findOrFail($customerId);

        /**
         * Garante compatibilidade com o vínculo directo antigo.
         */
        if (empty($customer->empresa_id)) {
            $customer->forceFill([
                'empresa_id' => $empresaId,
            ])->save();
        }

        /**
         * Garante o vínculo real usado por $empresa->customers().
         * Não duplica registos na pivot.
         */
        $customer->empresas()->syncWithoutDetaching([
            $empresaId => array_merge([
                'status' => 'ativo',
            ], $pivotData),
        ]);
    }

    public function detachFromEmpresa(int $customerId, int $empresaId): void
    {
        $customer = $this->findOrFail($customerId);

        $customer->empresas()->detach($empresaId);
    }

    public function isAssociatedWithEmpresa(int $customerId, int $empresaId): bool
    {
        $customer = $this->findOrFail($customerId);

        return $customer->empresas()
            ->where('empresas.id', $empresaId)
            ->exists();
    }

    public function belongsToEmpresa(int $customerId, int $empresaId): bool
    {
        $customer = $this->findOrFail($customerId);

        if ((int) $customer->empresa_id === (int) $empresaId) {
            return true;
        }

        return $customer->empresas()
            ->where('empresas.id', $empresaId)
            ->exists();
    }

    public function activate(int $customerId): Customer
    {
        $customer = $this->findOrFail($customerId);
        $customer->update(['is_active' => true]);

        return $customer->refresh();
    }

    public function deactivate(int $customerId): Customer
    {
        $customer = $this->findOrFail($customerId);
        $customer->update(['is_active' => false]);

        return $customer->refresh();
    }

    public function delete(int $customerId): bool
    {
        $customer = $this->findOrFail($customerId);

        return (bool) $customer->delete();
    }
}