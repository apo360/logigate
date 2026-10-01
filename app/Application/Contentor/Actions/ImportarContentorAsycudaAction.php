<?php

declare(strict_types=1);

namespace App\Application\Contentor\Actions;

use App\Application\Mercadoria\Services\MercadoriaTenantAccessService;
use App\Models\Contentor;
use Illuminate\Support\Facades\Auth;

final class ImportarContentorAsycudaAction
{
    public function __construct(private readonly MercadoriaTenantAccessService $tenantAccess)
    {
    }

    public function execute(int $processoId, array $container): Contentor
    {
        $processo = $this->tenantAccess->authorizeContext(Auth::user(), 'processo', $processoId, 'mercadorias.create');

        return Contentor::query()->create([
            'empresa_id' => $processo->empresa_id,
            'processo_id' => $processo->id,
            'asycuda_id' => $container['external_id'] ?? null,
            'numero' => $container['numero'],
            'tipo' => $container['tipo'] ?? null,
            'descricao' => $container['descricao'] ?? null,
            'indicador_carga' => $container['indicador_carga'] ?? null,
            'peso_tara' => $container['peso_tara'] ?? null,
            'peso_bruto' => $container['peso_bruto'] ?? null,
            'volume_bruto' => $container['volume_bruto'] ?? null,
            'unidade_volume_bruto' => $container['unidade_volume_bruto'] ?? null,
            'numero_volumes' => $container['numero_volumes'] ?? null,
            'descarregado' => (bool) ($container['descarregado'] ?? false),
            'possui_selo' => (bool) ($container['possui_selo'] ?? false),
            'resselado' => (bool) ($container['resselado'] ?? false),
            'asycuda_declaration_item_id' => $container['declaration_item_id'] ?? null,
        ]);
    }
}
