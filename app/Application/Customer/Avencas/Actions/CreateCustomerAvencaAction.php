<?php

namespace App\Application\Customer\Avencas\Actions;

use App\Application\Customer\Avencas\DTOs\CustomerAvencaData;
use App\Models\Customer;
use App\Models\CustomerAvenca;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final readonly class CreateCustomerAvencaAction
{
    public function execute(CustomerAvencaData $data): CustomerAvenca
    {
        Customer::query()
            ->forEmpresa($data->empresaId)
            ->findOrFail($data->customerId);

        return DB::transaction(function () use ($data): CustomerAvenca {
            return CustomerAvenca::query()->create($this->filterColumns($data->toArray()));
        });
    }

    private function filterColumns(array $data): array
    {
        return collect($data)
            ->filter(fn (mixed $value, string $column): bool => Schema::hasColumn('customer_avencas', $column))
            ->all();
    }
}
