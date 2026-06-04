<?php

namespace App\Application\Mercadoria\Queries;

use App\Application\Mercadoria\Repositories\MercadoriaRepositoryInterface;

final class ListarMercadoriasQuery
{
    public function __construct(
        private readonly MercadoriaRepositoryInterface $mercadorias,
    ) {
    }

    public function execute(string $context, int $parentId): array
    {
        $mercadorias = $this->mercadorias->listForContext($context, $parentId);
        $agrupadas = $this->mercadorias->groupedForContext($context, $parentId);

        return [
            'mercadorias' => $mercadorias->toArray(),
            'agrupadas' => $agrupadas->toArray(),
            'totais' => [
                'quantidade' => (float) $mercadorias->sum('Quantidade'),
                'peso' => (float) $mercadorias->sum('Peso'),
                'fob' => (float) $mercadorias->sum('preco_total'),
                'fob_aplicado' => (float) $mercadorias->sum('preco_total'),
            ],
        ];
    }
}
