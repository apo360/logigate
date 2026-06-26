<?php

namespace App\Application\Mercadoria\Actions;

use App\Application\Mercadoria\DTOs\MercadoriaData;
use App\Application\Mercadoria\Repositories\MercadoriaRepositoryInterface;
use App\Application\Mercadoria\Services\MercadoriaAgrupamentoService;
use App\Application\Mercadoria\Services\MercadoriaParentTotalsService;
use App\Application\Mercadoria\Services\MercadoriaRules;
use App\Application\Mercadoria\Services\MercadoriaTenantAccessService;
use App\Application\PautaAduaneira\Actions\AssociarPautaMercadoriaAction;
use App\Application\PautaAduaneira\Actions\ConsultarCodigoPautalAction;
use App\Models\Mercadoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

final class AtualizarMercadoriaAction
{
    public function __construct(
        private readonly MercadoriaRepositoryInterface $mercadorias,
        private readonly MercadoriaRules $rules,
        private readonly MercadoriaAgrupamentoService $agrupamento,
        private readonly MercadoriaParentTotalsService $parentTotals,
        private readonly ConsultarCodigoPautalAction $consultarCodigoPautal,
        private readonly AssociarPautaMercadoriaAction $associarPautaMercadoria,
        private readonly MercadoriaTenantAccessService $tenantAccess,
    ) {
    }

    public function execute(MercadoriaData $data): Mercadoria
    {
        return DB::transaction(function () use ($data): Mercadoria {
            $this->rules->validate($data);

            if (! $data->id) {
                throw new \InvalidArgumentException('ID da mercadoria é obrigatório para atualização.');
            }

            $this->tenantAccess->authorizeMercadoria(Auth::user(), $data->id, $data->context, $data->parentId, 'mercadorias.update');
            $pauta = $this->consultarCodigoPautal->execute($data->codigoAduaneiro);
            $mercadoria = $this->mercadorias->findInContext($data->id, $data->context, $data->parentId);
            $before = clone $mercadoria;

            $this->agrupamento->remove($mercadoria);

            $updated = $this->mercadorias->update($mercadoria, $data->toModelAttributes());
            $updated = $this->associarPautaMercadoria->execute(
                mercadoriaId: $updated->id,
                pautaAduaneiraId: $pauta->id,
                reason: $data->pautaChangeReason,
                source: $data->pautaChangeSource,
            );

            $this->agrupamento->addOrUpdate($updated);
            $this->parentTotals->applyUpdate($before, $updated);

            return $updated;
        });
    }
}
