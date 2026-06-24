<?php

namespace App\Application\Customer\Queries;

use App\Models\Customer;
use Illuminate\Support\Collection;

class CustomersPorEmpresaQuery
{
    public function execute(int $empresaId, bool $onlyActive = true): Collection
    {
        return Customer::query()
            ->forEmpresa($empresaId)
            ->when($onlyActive, fn ($q) => $q->where('is_active', true))
            ->orderBy('CompanyName')
            ->get();
    }
}