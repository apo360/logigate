<?php

namespace App\Application\Customer\Actions;

use App\Domains\Customers\Services\CustomerAccountStatementService;

final readonly class CalcularSaldoClienteAction
{
    public function __construct(
        private CustomerAccountStatementService $statementService,
    ) {
    }

    public function execute(int $customerId, ?int $empresaId = null): float
    {
        return $this->statementService->saldo($customerId, $empresaId);
    }
}
