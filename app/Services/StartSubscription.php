<?php

namespace App\Services;

use App\Actions\Subscriptions\ActivateSubscriptionAction;
use App\Domains\Billing\Enums\BillingCycle;
use App\Models\Empresa;
use App\Models\Plano;
use App\Models\Subscricao;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class StartSubscription
{
    public function execute(int $empresaId, int $planId, BillingCycle $cycle, int $userId, bool $renew = false): Subscricao
    {
        app(BillingAccess::class)->authorize($empresaId, $userId);

        return DB::transaction(function () use ($empresaId, $planId, $cycle, $userId, $renew) {
            Empresa::whereKey($empresaId)->lockForUpdate()->firstOrFail();
            $plan = Plano::whereKey($planId)->lockForUpdate()->firstOrFail();
            $quote = app(SubscriptionQuote::class)->fromPlan($plan, $cycle);
            $active = Subscricao::withoutGlobalScopes()->whereNull('deleted_at')->where('empresa_id', $empresaId)->effective()->latest('id')->lockForUpdate()->first();
            if ($active && ! $renew) {
                throw ValidationException::withMessages(['plano_id' => 'A empresa já possui um plano activo. Escolha renovar ou alterar para o próximo período.']);
            }
            $future = Subscricao::withoutGlobalScopes()->whereNull('deleted_at')->where('empresa_id', $empresaId)->active()->where('data_inicio', '>', now())->exists();
            if ($future) {
                throw ValidationException::withMessages(['plano_id' => 'Já existe uma subscrição confirmada para o próximo período.']);
            }
            $pending = Subscricao::withoutGlobalScopes()->whereNull('deleted_at')->where('empresa_id', $empresaId)->pending()->latest('id')->lockForUpdate()->first();
            if ($pending) {
                // Never activate a different pending contract based on newly submitted prices.
                if ((int) $pending->plano_id !== $planId || BillingCycle::fromInput($pending->modalidade_pagamento) !== $cycle) {
                    throw ValidationException::withMessages(['plano_id' => 'Existe outra subscrição pendente. Continue ou cancele essa contratação antes de escolher outro plano.']);
                }
                $pendingQuote = app(SubscriptionQuote::class)->forSubscription($pending);
                if ($pendingQuote['is_free'] && $pendingQuote['total'] == 0) {
                    app(ActivateSubscriptionAction::class)->execute($pending);
                }

                return $pending->refresh();
            }
            if ($active) {
                if (! $active->data_expiracao) {
                    throw ValidationException::withMessages(['plano_id' => 'Este contrato não tem data de renovação. Contacte o suporte.']);
                }
                $quote['starts_at'] = $active->data_expiracao->toIso8601String();
                $quote['previous_subscription_id'] = $active->id;
            }
            $sub = Subscricao::create([
                'empresa_id' => $empresaId, 'plano_id' => $planId, 'tipo_plano' => $plan->nome,
                'modalidade_pagamento' => $cycle->value, 'valor_pago' => $quote['base_amount'],
                'data_subscricao' => now(), 'status' => 'pendente', 'created_by' => $userId,
                'commercial_snapshot' => $quote,
            ]);
            if ($quote['is_free']) {
                app(ActivateSubscriptionAction::class)->execute($sub);
            }

            return $sub->refresh();
        }, 3);
    }
}
