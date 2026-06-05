<?php

namespace App\Application\Billing\UseCases;

use App\Application\Billing\DTOs\WebhookPaymentResult;
use App\Domains\Billing\Enums\PaymentStatus;
use App\Events\SubscriptionActivated;
use App\Models\PagamentoOnline;
use App\Models\Subscricao;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class HandleAppyPayWebhook
{
    public function execute(array $payload, string $rawPayload, ?string $eventId = null): WebhookPaymentResult
    {
        $gatewayId = $payload['id'] ?? null;
        $status = data_get($payload, 'responseStatus.status');
        $merchantTransactionId = $payload['merchantTransactionId'] ?? null;
        $resolvedEventId = $eventId ?: $this->resolveEventId($payload, $rawPayload);

        if (! $gatewayId && ! $merchantTransactionId) {
            Log::warning('APPYPAY_INVALID_PAYLOAD', $payload);

            return new WebhookPaymentResult('invalid_payload');
        }

        $subscriptionToActivate = null;
        $paymentResult = null;

        try {
            DB::transaction(function () use (
                $payload,
                $gatewayId,
                $status,
                $merchantTransactionId,
                $resolvedEventId,
                &$subscriptionToActivate,
                &$paymentResult
            ) {
                $webhookEvent = WebhookEvent::firstOrCreate([
                    'provider' => 'appypay',
                    'event_id' => $resolvedEventId,
                ], [
                    'processed_at' => now(),
                ]);

                if (! $webhookEvent->wasRecentlyCreated) {
                    $paymentResult = new WebhookPaymentResult('duplicate');
                    return;
                }

                $payment = PagamentoOnline::query()
                    ->when($merchantTransactionId, fn ($query) => $query->where('merchant_transaction_id', $merchantTransactionId))
                    ->when(! $merchantTransactionId && $gatewayId, fn ($query) => $query->where('gateway_id', $gatewayId))
                    ->lockForUpdate()
                    ->first();

                if (! $payment) {
                    Log::warning('APPYPAY_PAYMENT_NOT_FOUND', [
                        'merchantTransactionId' => $merchantTransactionId,
                        'gatewayId' => $gatewayId,
                    ]);

                    $paymentResult = new WebhookPaymentResult('payment_not_found');
                    return;
                }

                if ($payment->status === PaymentStatus::Paid->value) {
                    $paymentResult = new WebhookPaymentResult('already_paid', $payment->id, PaymentStatus::Paid);
                    return;
                }

                $newStatus = PaymentStatus::fromGateway($status);

                $payment->update([
                    'status' => $newStatus->value,
                    'gateway_id' => $gatewayId ?? $payment->gateway_id,
                    'raw_response' => $payload,
                    'paid_at' => $newStatus === PaymentStatus::Paid ? now() : $payment->paid_at,
                    'failed_at' => in_array($newStatus, [PaymentStatus::Failed, PaymentStatus::Expired], true) ? now() : $payment->failed_at,
                ]);

                if (
                    $newStatus === PaymentStatus::Paid
                    && $payment->subscription
                    && Subscricao::normalizeStatus($payment->subscription->status) !== Subscricao::STATUS_ATIVA
                ) {
                    $subscriptionToActivate = $payment->subscription;
                }

                $paymentResult = new WebhookPaymentResult('ok', $payment->id, $newStatus, $subscriptionToActivate !== null);
            });
        } catch (\Throwable $exception) {
            Log::error('APPYPAY_WEBHOOK_ERROR', [
                'error' => $exception->getMessage(),
                'merchantTransactionId' => $merchantTransactionId,
                'gatewayId' => $gatewayId,
            ]);

            throw $exception;
        }

        if ($subscriptionToActivate) {
            SubscriptionActivated::dispatch($subscriptionToActivate)->afterCommit();
        }

        return $paymentResult ?? new WebhookPaymentResult('ok');
    }

    private function resolveEventId(array $payload, string $rawPayload): string
    {
        return (string) (
            $payload['eventId']
            ?? $payload['event_id']
            ?? hash('sha256', $rawPayload)
        );
    }
}
