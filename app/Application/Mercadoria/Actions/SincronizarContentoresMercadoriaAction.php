<?php

declare(strict_types=1);

namespace App\Application\Mercadoria\Actions;

use App\Application\Mercadoria\DTOs\MercadoriaData;
use App\Models\Contentor;
use App\Models\Mercadoria;
use Illuminate\Auth\Access\AuthorizationException;
use App\Application\Mercadoria\Services\MercadoriaTenantAccessService;
use Illuminate\Support\Facades\Auth;

final class SincronizarContentoresMercadoriaAction
{
    public function __construct(private readonly MercadoriaTenantAccessService $tenantAccess)
    {
    }

    public function execute(Mercadoria $mercadoria, MercadoriaData $data): void
    {
        if ($data->context !== 'processo') {
            return;
        }

        $processo = $this->tenantAccess->authorizeContext(Auth::user(), 'processo', $data->parentId);
        if ((int) $mercadoria->Fk_Importacao !== $data->parentId) {
            throw new AuthorizationException('Mercadoria fora do processo informado.');
        }

        $ids = array_values(array_unique(array_filter($data->contentorIds, fn ($id) => $id > 0)));
        $contentores = Contentor::withoutGlobalScopes()
            ->where('processo_id', $data->parentId)
            ->where('empresa_id', $processo->empresa_id)
            ->whereIn('id', $ids)
            ->get(['id']);

        if ($contentores->count() !== count($ids)) {
            throw new AuthorizationException('Um ou mais contentores não pertencem ao processo/empresa autorizados.');
        }

        $mercadoria->contentores()->sync($ids);
    }
}
