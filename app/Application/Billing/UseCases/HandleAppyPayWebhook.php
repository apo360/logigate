<?php

namespace App\Application\Billing\UseCases;

use App\Actions\Subscriptions\ActivateSubscriptionAction;
use App\Application\Billing\DTOs\WebhookPaymentResult;
use App\Domains\Billing\Enums\PaymentStatus;
use App\Models\Empresa;
use App\Models\PagamentoOnline;
use App\Models\Subscricao;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\DB;

final class HandleAppyPayWebhook
{
    public function execute(array $payload, string $rawPayload, ?string $eventId = null): WebhookPaymentResult
    {
        $gatewayId = $payload['id'] ?? null;
        $merchantId = $payload['merchantTransactionId'] ?? null;
        if (! $gatewayId && ! $merchantId) {
            return new WebhookPaymentResult('invalid_payload');
        }
        if (($gatewayId !== null && ! is_string($gatewayId)) || ($merchantId !== null && ! is_string($merchantId))) {
            return new WebhookPaymentResult('invalid_payload');
        }
        $id = (string) ($eventId ?: ($payload['eventId'] ?? $payload['event_id'] ?? hash('sha256', $rawPayload)));
        if (strlen($id) > 255) {
            return new WebhookPaymentResult('invalid_payload');
        }
        $event = WebhookEvent::firstOrCreate(['provider' => 'appypay', 'event_id' => $id], [
            'processed_at' => null, 'received_at' => now(), 'status' => 'received', 'payload' => $payload,
        ]);
        if ($event->payload !== null && $event->payload != $payload) {
            return new WebhookPaymentResult('invalid_payload');
        }
        try {
            return DB::transaction(function () use ($event, $merchantId, $gatewayId, $payload) {
                $event = WebhookEvent::whereKey($event->id)->lockForUpdate()->firstOrFail();
                if ($event->payload !== null && $event->payload != $payload) {
                    return new WebhookPaymentResult('invalid_payload');
                }
                if ($event->payload === null) {
                    $event->update(['payload' => $payload, 'processed_at' => null, 'received_at' => now(), 'status' => 'received']);
                }
                $paymentQuery = PagamentoOnline::query()
                    ->when($merchantId, fn ($q) => $q->where('merchant_transaction_id', $merchantId))
                    ->when(! $merchantId, fn ($q) => $q->where('gateway_id', $gatewayId));
                $empresaId = (clone $paymentQuery)->value('empresa_id');
                if ($empresaId) {
                    Empresa::whereKey($empresaId)->lockForUpdate()->firstOrFail();
                }
                $payment = $paymentQuery->lockForUpdate()->first();
                if (! $payment) {
                    $event->update(['status' => 'waiting_payment', 'attempts' => $event->attempts + 1]);

                    return new WebhookPaymentResult('payment_not_found');
                }
                $sub = Subscricao::withoutGlobalScopes()->whereNull('deleted_at')->whereKey($payment->subscription_id)->lockForUpdate()->first();
                if (! $sub || (int) $sub->empresa_id !== (int) $payment->empresa_id
                    || ($gatewayId && $payment->gateway_id && $gatewayId !== $payment->gateway_id)
                    || (isset($payload['amount']) && (! is_numeric($payload['amount']) || abs((float) $payload['amount'] - $payment->amount) > 0.005))
                    || (isset($payload['currency']) && strtoupper($payload['currency']) !== $payment->currency)) {
                    $event->update(['status' => 'rejected', 'last_error' => 'Payment correlation mismatch', 'attempts' => $event->attempts + 1]);

                    return new WebhookPaymentResult('invalid_payload');
                }
                $wasPaid = $payment->statusEnum() === PaymentStatus::Paid;
                $needsActivation = $wasPaid && $sub->activated_at === null;
                if ($event->processed_at && ! $needsActivation) {
                    return new WebhookPaymentResult('duplicate');
                }
                $status = $wasPaid ? PaymentStatus::Paid : PaymentStatus::fromGateway(data_get($payload, 'responseStatus.status'));
                if (! $wasPaid && in_array($payment->statusEnum(), [PaymentStatus::Failed, PaymentStatus::Expired], true) && $status->isOpen()) {
                    $status = $payment->statusEnum();
                }
                $payment->update([
                    'status' => $status->value, 'gateway_id' => $gatewayId ?: $payment->gateway_id,
                    'raw_response' => $payload, 'paid_at' => $status === PaymentStatus::Paid ? ($payment->paid_at ?? now()) : $payment->paid_at,
                    'failed_at' => in_array($status, [PaymentStatus::Failed, PaymentStatus::Expired], true) ? ($payment->failed_at ?? now()) : $payment->failed_at,
                ]);
                if ($status === PaymentStatus::Paid) {
                    app(ActivateSubscriptionAction::class)->execute($sub);
                }
                $event->update(['status' => 'processed', 'processed_at' => now(), 'last_error' => null, 'attempts' => $event->attempts + 1]);

                return new WebhookPaymentResult($wasPaid ? 'already_paid' : 'ok', $payment->id, $status, $status === PaymentStatus::Paid);
            }, 3);
        } catch (\Throwable $error) {
            WebhookEvent::whereKey($event->id)->update(['status' => 'failed', 'last_error' => mb_substr($error->getMessage(), 0, 1000), 'attempts' => DB::raw('attempts + 1')]);
            throw $error;
        }
    }
}
