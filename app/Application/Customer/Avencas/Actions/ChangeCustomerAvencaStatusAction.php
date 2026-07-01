<?php

namespace App\Application\Customer\Avencas\Actions;

use App\Application\Customer\Avencas\DTOs\CustomerAvencaData;
use App\Models\CustomerAvenca;
use Illuminate\Support\Facades\Schema;

final readonly class ChangeCustomerAvencaStatusAction
{
    public function execute(CustomerAvenca $avenca, int $empresaId, string $status, ?int $updatedBy = null): CustomerAvenca
    {
        if (Schema::hasColumn('customer_avencas', 'empresa_id')) {
            abort_unless((int) $avenca->empresa_id === $empresaId, 404);
        }

        $status = CustomerAvencaData::normalizeStatus($status);

        $payload = [
            'status' => $status,
            'ativo' => $status === 'ativa',
            'updated_by' => $updatedBy,
        ];

        $avenca->update(
            collect($payload)
                ->filter(fn (mixed $value, string $column): bool => Schema::hasColumn('customer_avencas', $column))
                ->all()
        );

        return $avenca->refresh();
    }
}
