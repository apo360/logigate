<?php

namespace App\Services;

use App\Domains\Billing\Enums\BillingCycle;
use App\Models\Plano;
use App\Models\Subscricao;
use Illuminate\Validation\ValidationException;

final class SubscriptionQuote
{
    public function fromPlan(Plano $plan, BillingCycle $cycle): array
    {
        $field = match ($cycle->value) {
            'monthly' => 'preco_mensal', 'trimestral' => 'preco_trimestral', 'semestral' => 'preco_semestral', 'annual' => 'preco_anual'
        };
        $base = $plan->{$field};
        $modules = $plan->modulos()->pluck('modules.id')->unique()->map(fn ($id) => (int) $id)->values()->all();
        if ($plan->status !== 'activo' || $base === null || $base < 0
            || ($base <= 0 && ! $plan->isGratuito()) || (! $modules && ! config('billing.allow_empty_plans'))) {
            throw ValidationException::withMessages(['plano_id' => 'Este plano ou modalidade ainda não está configurado para contratação. Contacte a equipa LogiGate.']);
        }
        $rate = (float) config('billing.vat_rate');
        if ($rate < 0 || $rate > 1) {
            throw new \LogicException('Taxa de cobrança inválida.');
        }

        return [
            'version' => 1, 'plan_name' => $plan->nome, 'plan_id' => (int) $plan->id, 'cycle' => $cycle->value,
            'base_amount' => round($base, 2), 'vat_rate' => $rate, 'vat_amount' => round($base * $rate, 2),
            'total' => round($base + round($base * $rate, 2), 2), 'currency' => config('billing.currency'),
            'module_ids' => $modules, 'is_free' => $plan->isGratuito() && $base == 0,
            'limits' => ['users' => $plan->limite_utilizadores, 'processes' => $plan->limite_processos, 'storage_gb' => $plan->limite_armazenamento_gb],
            'quoted_at' => now()->toIso8601String(),
        ];
    }

    public function forSubscription(Subscricao $sub): array
    {
        if ($sub->commercial_snapshot) {
            return $sub->commercial_snapshot;
        }
        // Legacy pending contracts are frozen once, before the first payment reservation.
        $plan = $sub->plano;
        if (! $plan) {
            throw ValidationException::withMessages(['plano_id' => 'O plano desta subscrição já não está disponível.']);
        }
        $quote = $this->fromPlan($plan, BillingCycle::fromInput($sub->modalidade_pagamento));
        if ($sub->valor_pago > 0) {
            $quote['base_amount'] = round($sub->valor_pago, 2);
            $quote['vat_amount'] = round($quote['base_amount'] * $quote['vat_rate'], 2);
            $quote['total'] = round($quote['base_amount'] + $quote['vat_amount'], 2);
            $quote['is_free'] = false;
        }
        $sub->update(['commercial_snapshot' => $quote]);

        return $quote;
    }
}
