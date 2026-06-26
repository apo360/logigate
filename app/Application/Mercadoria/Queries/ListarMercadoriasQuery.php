<?php

namespace App\Application\Mercadoria\Queries;

use App\Application\Mercadoria\Repositories\MercadoriaRepositoryInterface;
use App\Application\Mercadoria\Services\MercadoriaTenantAccessService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

final class ListarMercadoriasQuery
{
    public function __construct(
        private readonly MercadoriaRepositoryInterface $mercadorias,
        private readonly MercadoriaTenantAccessService $tenantAccess,
    ) {
    }

    public function execute(string $context, int $parentId, ?User $user = null): array
    {
        $this->tenantAccess->authorizeContext($user ?? Auth::user(), $context, $parentId, 'mercadorias.view');

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
