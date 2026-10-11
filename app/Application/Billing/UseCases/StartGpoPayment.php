<?php

namespace App\Application\Billing\UseCases;

use App\Application\Billing\Contracts\GpoPaymentGateway;
use App\Application\Billing\DTOs\PaymentViewData;
use App\Domains\Billing\Enums\PaymentMethod;
use App\Domains\Billing\Enums\PaymentStatus;
use App\Domains\Billing\ValueObjects\MerchantTransactionId;
use App\Domains\Billing\ValueObjects\Money;
use App\Domains\Billing\ValueObjects\PhoneNumberAO;
use App\Models\Subscricao;

final class StartGpoPayment
{
    public function __construct(private readonly GpoPaymentGateway $client, private readonly FallbackToRefPayment $fallbackToRefPayment, private readonly BuildPaymentViewData $viewData) {}

    public function execute(Subscricao $subscription, string $phone): PaymentViewData
    {
        $phoneNumber = new PhoneNumberAO($phone);
        [$payment, $created] = app(ReservePayment::class)->execute($subscription, PaymentMethod::GPO, $phoneNumber->international());
        if (! $created) {
            return $this->viewData->fromSubscription($subscription->refresh(), $payment, 'Pagamento já iniciado.');
        }
        $result = $this->client->create(new Money($payment->amount, $payment->currency), new MerchantTransactionId($payment->merchant_transaction_id), 'Subscricao '.($subscription->commercial_snapshot['plan_name'] ?? $subscription->tipo_plano ?? 'LogiGate'), $phoneNumber);
        $payment = app(ApplyGatewayResult::class)->execute($payment, $result);
        if (in_array($payment->statusEnum(), [PaymentStatus::Failed, PaymentStatus::Expired], true)) {
            return $this->fallbackToRefPayment->execute($payment, $result->message ?? 'GPO recusado.');
        }

        return $this->viewData->fromSubscription($subscription->refresh(), $payment, $payment->failure_reason ?? $result->message ?? 'Pedido GPO enviado para autorização.');
    }
}
