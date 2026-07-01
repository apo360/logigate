<?php

namespace App\Application\Customer\Actions;

use App\Application\Customer\DTOs\ContaCorrenteMovimentoDTO;
use App\Domains\Customers\Services\CustomerAccountStatementService;
use App\Models\ContaCorrente;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final readonly class RegistrarMovimentoContaCorrenteAction
{
    public function __construct(
        private CustomerAccountStatementService $statementService,
    ) {
    }

    public function execute(ContaCorrenteMovimentoDTO $dto): ContaCorrente
    {
        $this->assertSchemaSupportsTenantSafeWrites();

        $customer = Customer::query()
            ->forEmpresa($dto->empresaId)
            ->findOrFail($dto->customerId);

        return DB::transaction(function () use ($dto, $customer): ContaCorrente {
            $data = $this->filterColumns($dto->toPersistenceArray());
            $data['cliente_id'] = $customer->id;
            $data['customer_id'] = $customer->id;

            $saldoAnterior = $this->statementService->saldo((int) $customer->id, $dto->empresaId);
            $data['saldo_apos_movimento'] = $saldoAnterior + $this->statementService->valorAssinadoTipo($dto->tipo, $dto->valor);

            return ContaCorrente::query()->create($this->filterColumns($data));
        });
    }

    private function assertSchemaSupportsTenantSafeWrites(): void
    {
        if (!Schema::hasColumn('conta_correntes', 'empresa_id')) {
            throw new \RuntimeException('A tabela conta_correntes ainda não suporta empresa_id. Execute a migration evolutiva antes de criar movimentos.');
        }
    }

    private function filterColumns(array $data): array
    {
        return collect($data)
            ->filter(fn (mixed $value, string $column): bool => Schema::hasColumn('conta_correntes', $column))
            ->all();
    }
}
