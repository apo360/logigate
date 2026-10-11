<?php

namespace App\Services;

use App\Models\ActivatedModule;
use App\Models\PlanoModulo;
use App\Models\Subscricao;
use Illuminate\Support\Facades\DB;
use LogicException;

class ModuloAtivacaoService
{
    public function ativarModulos(Subscricao|int $subscription, ?int $planoId = null): void
    {
        if (is_int($subscription)) {
            $matches = Subscricao::withoutGlobalScopes()->whereNull('deleted_at')->where('empresa_id', $subscription)->where('plano_id', $planoId)->active()->get()->filter(fn ($sub) => $sub->isAtiva());
            if ($matches->count() !== 1) {
                throw new LogicException('Indique a subscrição concreta para activar módulos.');
            }
            $subscription = $matches->first();
        }
        DB::transaction(function () use ($subscription) {
            $sub = Subscricao::withoutGlobalScopes()->whereNull('deleted_at')->lockForUpdate()->findOrFail($subscription->id);
            if ((int) $sub->empresa_id !== (int) $subscription->empresa_id || ! $sub->hasStatus(Subscricao::STATUS_ATIVA) || $sub->isExpirada()) {
                throw new LogicException('Subscrição inválida para esta activação.');
            }
            $modules = data_get($sub->commercial_snapshot, 'module_ids') ?? PlanoModulo::where('plano_id', $sub->plano_id)->distinct()->pluck('modulo_id')->all();
            foreach ($modules as $moduleId) {
                $rows = ActivatedModule::where('empresa_id', $sub->empresa_id)->where('module_id', $moduleId)->lockForUpdate()->get();
                // Preserve revocations and legacy rows; reconciliation is separate.
                if ($rows->contains(fn ($row) => ! $row->active)
                    || $rows->contains(fn ($row) => (int) $row->subscricao_id === (int) $sub->id)) {
                    continue;
                }
                ActivatedModule::create([
                    'empresa_id' => $sub->empresa_id, 'subscricao_id' => $sub->id,
                    'module_id' => $moduleId, 'menu_id' => null, 'active' => true,
                    'activation_date' => $sub->data_inicio ?? $sub->activated_at ?? now(), 'data_expiracao' => $sub->data_expiracao,
                ]);
            }
        });
    }
}
