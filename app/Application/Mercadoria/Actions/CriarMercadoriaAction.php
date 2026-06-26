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

final class CriarMercadoriaAction
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
            $this->tenantAccess->authorizeContext(Auth::user(), $data->context, $data->parentId, 'mercadorias.create');
            $this->rules->validate($data);
            $pauta = $this->consultarCodigoPautal->execute($data->codigoAduaneiro);

            $mercadoria = $this->mercadorias->create($data->toModelAttributes());
            $mercadoria = $this->associarPautaMercadoria->execute(
                mercadoriaId: $mercadoria->id,
                pautaAduaneiraId: $pauta->id,
                reason: $data->pautaChangeReason,
                source: $data->pautaChangeSource === 'ai_suggestion' ? 'ai_suggestion' : 'system',
            );

            $this->agrupamento->addOrUpdate($mercadoria);
            $this->parentTotals->applyCreate($mercadoria);

            return $mercadoria;
        });
    }
}
