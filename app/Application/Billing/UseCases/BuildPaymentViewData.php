<?php

namespace App\Application\Billing\UseCases;

use App\Application\Billing\DTOs\PaymentViewData;
use App\Domains\Billing\Enums\BillingCycle;
use App\Domains\Billing\Enums\PaymentMethod;
use App\Domains\Billing\Enums\PaymentStatus;
use App\Domains\Billing\ValueObjects\PaymentReference;
use App\Models\PagamentoOnline;
use App\Models\Subscricao;

final class BuildPaymentViewData
{
    public function fromSubscription(Subscricao $subscription, ?PagamentoOnline $payment = null, ?string $message = null, ?string $error = null, bool $fallback = false): PaymentViewData
    {
        $plan = $subscription->plano;
        $cycle = BillingCycle::fromInput($subscription->modalidade_pagamento);
        $snapshot = $subscription->commercial_snapshot ?? [];
        $rate = (float) ($snapshot['vat_rate'] ?? config('billing.vat_rate'));
        $base = (float) ($snapshot['base_amount'] ?? ($subscription->valor_pago > 0 ? $subscription->valor_pago : ($plan ? $cycle->priceFrom($plan) : 0)));
        $amount = $payment?->amount ?? ($snapshot['total'] ?? round($base * (1 + $rate), 2));
        if ($payment && ! $snapshot) {
            $base = round($payment->amount / (1 + $rate), 2);
        }
        $method = $payment ? PaymentMethod::fromInput($payment->method) : PaymentMethod::GPO;
        $status = $payment ? PaymentStatus::fromPersisted($payment->status) : PaymentStatus::Pending;

        $reference = null;
        if ($payment && ($payment->reference_entity || $payment->reference_number || $payment->reference_due_date)) {
            $reference = new PaymentReference(
                $payment->reference_entity,
                $payment->reference_number,
                $payment->reference_due_date,
            );
        }

        return new PaymentViewData(
            $payment?->id,
            $method,
            $status,
            (float) $amount,
            $payment?->currency ?? 'AOA',
            (string) ($snapshot['plan_name'] ?? $plan?->nome ?? $subscription->tipo_plano ?? 'Plano'),
            $cycle,
            $payment?->merchant_transaction_id,
            $payment?->phone,
            $reference,
            $message,
            $error,
            $fallback,
            $base,
            $rate,
            $payment?->status === 'paid' && $subscription->activated_at === null,
            $subscription->data_inicio?->toIso8601String(),
        );
    }
}
