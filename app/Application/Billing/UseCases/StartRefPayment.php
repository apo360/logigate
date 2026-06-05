<?php

namespace App\Application\Billing\UseCases;

use App\Application\Billing\DTOs\PaymentViewData;
use App\Domains\Billing\Enums\BillingCycle;
use App\Domains\Billing\Enums\PaymentMethod;
use App\Domains\Billing\Enums\PaymentStatus;
use App\Domains\Billing\ValueObjects\MerchantTransactionId;
use App\Domains\Billing\ValueObjects\Money;
use App\Infrastructure\PaymentGateways\AppyPay\AppyPayRefClient;
use App\Models\PagamentoOnline;
use App\Models\Subscricao;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

final class StartRefPayment
{
    public function __construct(
        private readonly AppyPayRefClient $client,
        private readonly BuildPaymentViewData $viewData,
    ) {}

    public function execute(Subscricao $subscription, bool $fallbackApplied = false): PaymentViewData
    {
        $plan = $subscription->plano;
        $cycle = BillingCycle::fromInput($subscription->modalidade_pagamento);
        $money = new Money(round($cycle->priceFrom($plan) * 1.14, 2));
        $merchantId = MerchantTransactionId::generate(PaymentMethod::REF);
        $empresa = $subscription->empresa;
        $user = Auth::user();

        $payment = PagamentoOnline::create([
            'empresa_id' => $subscription->empresa_id,
            'subscription_id' => $subscription->id,
            'method' => PaymentMethod::REF->value,
            'amount' => $money->amount,
            'currency' => $money->currency,
            'status' => PaymentStatus::Pending->value,
            'merchant_transaction_id' => (string) $merchantId,
            'idempotency_key' => (string) $merchantId,
        ]);

        $result = $this->client->create(
            $money,
            $merchantId,
            'Subscricao ' . $plan->nome,
            [
                'name' => $user?->name ?? $empresa?->Empresa ?? 'Cliente Logigate',
                'email' => $user?->email ?? $empresa?->Email ?? '',
                'telephone' => $empresa?->Contacto_movel ?? '',
                'smsNotification' => true,
                'emailNotification' => true,
            ],
        );

        $reference = $result->reference;

        $payment->update([
            'gateway_id' => $result->gatewayId,
            'status' => $result->success ? PaymentStatus::Pending->value : PaymentStatus::Failed->value,
            'reference_entity' => $reference?->entity,
            'reference_number' => $reference?->referenceNumber,
            'reference_due_date' => $reference?->dueDate,
            'failure_reason' => $result->success ? null : $result->message,
            'failed_at' => $result->success ? null : now(),
            'raw_response' => $result->rawResponse,
        ]);

        if (! $result->success) {
            Log::error('BILLING_REF_FAILED', [
                'payment_id' => $payment->id,
                'subscription_id' => $subscription->id,
                'error_code' => $result->errorCode,
                'message' => $result->message,
            ]);
        }

        return $this->viewData->fromSubscription(
            $subscription,
            $payment->refresh(),
            $result->message ?? 'Referencia Multicaixa gerada.',
            $result->success ? null : 'Nao foi possivel gerar a referencia.',
            $fallbackApplied,
        );
    }
}
