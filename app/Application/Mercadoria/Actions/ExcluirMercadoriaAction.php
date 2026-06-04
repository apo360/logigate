<?php

namespace App\Application\Mercadoria\Actions;

use App\Application\Mercadoria\Repositories\MercadoriaRepositoryInterface;
use App\Application\Mercadoria\Services\MercadoriaAgrupamentoService;
use App\Application\Mercadoria\Services\MercadoriaParentTotalsService;
use Illuminate\Support\Facades\DB;

final class ExcluirMercadoriaAction
{
    public function __construct(
        private readonly MercadoriaRepositoryInterface $mercadorias,
        private readonly MercadoriaAgrupamentoService $agrupamento,
        private readonly MercadoriaParentTotalsService $parentTotals,
    ) {
    }

    public function execute(int $id, string $context, int $parentId): void
    {
        DB::transaction(function () use ($id, $context, $parentId): void {
            $mercadoria = $this->mercadorias->findInContext($id, $context, $parentId);

            $this->agrupamento->remove($mercadoria);
            $this->parentTotals->applyDelete($mercadoria);
            $this->mercadorias->delete($mercadoria);
        });
    }
}
