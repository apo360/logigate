<?php

namespace App\Domains\Customers\Services;

use App\Domains\Customers\Queries\CustomerStatsQuery;

final class CustomerAnalyticsService
{
    public function __construct(
        private readonly CustomerStatsQuery $customerStatsQuery,
    ) {
    }

    public function statsForEmpresa(int $empresaId): array
    {
        return $this->customerStatsQuery->forEmpresa($empresaId)->toArray();
    }
}
