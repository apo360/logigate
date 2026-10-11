<?php

namespace App\Application\Billing\UseCases;

use App\Application\Billing\Contracts\RefPaymentGateway;
use App\Application\Billing\DTOs\PaymentViewData;
use App\Domains\Billing\Enums\PaymentMethod;
use App\Domains\Billing\Enums\PaymentStatus;
use App\Domains\Billing\ValueObjects\MerchantTransactionId;
use App\Domains\Billing\ValueObjects\Money;
use App\Models\Subscricao;
use Illuminate\Support\Facades\Auth;

final class StartRefPayment
{
    public function __construct(private readonly RefPaymentGateway $client, private readonly BuildPaymentViewData $viewData) {}

    public function execute(Subscricao $subscription, bool $fallbackApplied = false): PaymentViewData
    {
        [$payment, $created] = app(ReservePayment::class)->execute($subscription, PaymentMethod::REF);
        if (! $created) {
            return $this->viewData->fromSubscription($subscription->refresh(), $payment, 'Pagamento já iniciado.', null, $fallbackApplied);
        }
        $empresa = $subscription->empresa;
        $result = $this->client->create(new Money($payment->amount, $payment->currency), new MerchantTransactionId($payment->merchant_transaction_id), 'Subscricao '.($subscription->commercial_snapshot['plan_name'] ?? $subscription->tipo_plano ?? 'LogiGate'), [
            'name' => Auth::user()?->name ?? $empresa?->Empresa ?? 'Cliente Logigate',
            'email' => Auth::user()?->email ?? $empresa?->Email ?? '', 'telephone' => $empresa?->Contacto_movel ?? '',
            'smsNotification' => true, 'emailNotification' => true,
        ]);
        $payment = app(ApplyGatewayResult::class)->execute($payment, $result);
        $failed = in_array($payment->statusEnum(), [PaymentStatus::Failed, PaymentStatus::Expired], true);
        $message = $payment->failure_reason ?? $result->message ?? 'Referência Multicaixa gerada.';
        if (! $failed && $payment->status !== 'paid' && ! $payment->reference_number) {
            $message = 'Pedido registado. A referência ainda aguarda confirmação; não efectue um segundo pagamento.';
        }

        return $this->viewData->fromSubscription($subscription->refresh(), $payment, $message, $failed ? 'Não foi possível gerar a referência.' : null, $fallbackApplied);
    }
}
