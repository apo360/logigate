<?php

namespace App\Application\Customer\Avencas\Actions;

use App\Application\Customer\Avencas\DTOs\CustomerAvencaData;
use App\Models\CustomerAvenca;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final readonly class UpdateCustomerAvencaAction
{
    public function execute(CustomerAvenca $avenca, CustomerAvencaData $data): CustomerAvenca
    {
        $this->assertTenant($avenca, $data->empresaId, $data->customerId);

        return DB::transaction(function () use ($avenca, $data): CustomerAvenca {
            $payload = collect($data->toArray())
                ->except(['created_by'])
                ->filter(fn (mixed $value, string $column): bool => Schema::hasColumn('customer_avencas', $column))
                ->all();

            $avenca->update($payload);

            return $avenca->refresh();
        });
    }

    private function assertTenant(CustomerAvenca $avenca, int $empresaId, int $customerId): void
    {
        abort_unless((int) $avenca->customer_id === $customerId, 404);

        if (Schema::hasColumn('customer_avencas', 'empresa_id')) {
            abort_unless((int) $avenca->empresa_id === $empresaId, 404);
        }
    }
}
