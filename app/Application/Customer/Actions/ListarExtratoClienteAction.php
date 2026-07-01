<?php

namespace App\Application\Customer\Actions;

use App\Domains\Customers\Services\CustomerAccountStatementService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class ListarExtratoClienteAction
{
    public function __construct(
        private CustomerAccountStatementService $statementService,
    ) {
    }

    public function execute(
        int $customerId,
        ?int $empresaId = null,
        ?string $tipo = null,
        ?string $dataInicio = null,
        ?string $dataFim = null,
        ?string $search = null,
        int $perPage = 10,
    ): LengthAwarePaginator {
        return $this->statementService
            ->extratoQuery($customerId, $empresaId, $tipo, $dataInicio, $dataFim, $search)
            ->paginate($perPage);
    }
}
