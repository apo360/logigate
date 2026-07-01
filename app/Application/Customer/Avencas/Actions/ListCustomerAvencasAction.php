<?php

namespace App\Application\Customer\Avencas\Actions;

use App\Models\CustomerAvenca;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

final readonly class ListCustomerAvencasAction
{
    public function execute(int $empresaId, int $customerId): Collection
    {
        $query = CustomerAvenca::query()
            ->where('customer_id', $customerId);

        if (Schema::hasColumn('customer_avencas', 'empresa_id')) {
            $query->where('empresa_id', $empresaId);
        }

        return $query->orderByDesc('created_at')->get();
    }
}
