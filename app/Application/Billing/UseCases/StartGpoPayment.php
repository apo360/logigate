<?php

namespace App\Application\Billing\UseCases;

use App\Application\Billing\DTOs\PaymentViewData;
use App\Domains\Billing\Enums\BillingCycle;
use App\Domains\Billing\Enums\PaymentMethod;
use App\Domains\Billing\Enums\PaymentStatus;
use App\Domains\Billing\ValueObjects\MerchantTransactionId;
use App\Domains\Billing\ValueObjects\Money;
use App\Domains\Billing\ValueObjects\PhoneNumberAO;
use App\Infrastructure\PaymentGateways\AppyPay\AppyPayGpoClient;
use App\Models\PagamentoOnline;
use App\Models\Subscricao;

final class StartGpoPayment
{
    public function __construct(
        private readonly AppyPayGpoClient $client,
        private readonly FallbackToRefPayment $fallbackToRefPayment,
        private readonly BuildPaymentViewData $viewData,
    ) {}

    public function execute(Subscricao $subscription, string $phone): PaymentViewData
    {
        $phoneNumber = new PhoneNumberAO($phone);
        $plan = $subscription->plano;
        $cycle = BillingCycle::fromInput($subscription->modalidade_pagamento);
        $money = new Money(round($cycle->priceFrom($plan) * 1.14, 2));
        $merchantId = MerchantTransactionId::generate(PaymentMethod::GPO);

        $payment = PagamentoOnline::create([
            'empresa_id' => $subscription->empresa_id,
            'subscription_id' => $subscription->id,
            'method' => PaymentMethod::GPO->value,
            'amount' => $money->amount,
            'currency' => $money->currency,
            'status' => PaymentStatus::Processing->value,
            'merchant_transaction_id' => (string) $merchantId,
            'idempotency_key' => (string) $merchantId,
            'phone' => $phoneNumber->international(),
        ]);

        $result = $this->client->create($money, $merchantId, 'Subscricao ' . $plan->nome, $phoneNumber);

        $payment->update([
            'gateway_id' => $result->gatewayId,
            'status' => $result->success ? PaymentStatus::Processing->value : PaymentStatus::Failed->value,
            'failure_reason' => $result->success ? null : $result->message,
            'failed_at' => $result->success ? null : now(),
            'raw_response' => $result->rawResponse,
        ]);

        if (! $result->success) {
            return $this->fallbackToRefPayment->execute($payment->refresh(), $result->message ?? 'GPO falhou.');
        }

        return $this->viewData->fromSubscription(
            $subscription,
            $payment->refresh(),
            $result->message ?? 'Pedido GPO enviado para autorizacao.',
        );
    }
}
