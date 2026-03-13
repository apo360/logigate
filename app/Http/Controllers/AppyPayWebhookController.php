<?php

namespace App\Http\Controllers;

use App\Events\SubscriptionActivated;
use App\Models\PagamentoOnline;
use App\Models\Subscricao;
use App\Models\WebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppyPayWebhookController extends BaseController
{
    public function handle(Request $request)
    {
        if (! $request->isMethod('post')) {
            return response()->json(['error' => 'Method not allowed'], 405);
        }

        $payload = $request->getContent();
        $secret = config('services.appypay.webhook_secret');
        $signature = (string) $request->header('X-AppyPay-Signature', '');

        if (blank($secret) || blank($signature)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        // Verify the raw webhook body to ensure the sender is trusted.
        if (! hash_equals($signature, $expectedSignature)) {
            Log::warning('APPYPAY_INVALID_SIGNATURE', [
                'merchantTransactionId' => $request->input('merchantTransactionId'),
            ]);

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $gatewayId = $request->input('id');
        $status = $request->input('responseStatus.status');
        $merchantTransactionId = $request->input('merchantTransactionId');
        $eventId = $this->resolveEventId($request, $payload);

        if (! $gatewayId && ! $merchantTransactionId) {
            Log::warning('APPYPAY_INVALID_PAYLOAD', $request->all());
            return response()->json(['error' => 'invalid_payload'], 400);
        }

        try {
            $subscriptionToActivate = null;

            DB::transaction(function () use (
                $request,
                $gatewayId,
                $status,
                $merchantTransactionId,
                $eventId,
                &$subscriptionToActivate
            ) {
                // Persist the provider event id once so retried deliveries are harmless.
                $webhookEvent = WebhookEvent::firstOrCreate([
                    'provider' => 'appypay',
                    'event_id' => $eventId,
                ], [
                    'processed_at' => now(),
                ]);

                if (! $webhookEvent->wasRecentlyCreated) {
                    throw new \RuntimeException('duplicate_webhook_event');
                }

                $payment = PagamentoOnline::query()
                    ->when($merchantTransactionId, fn($q) =>
                        $q->where('merchant_transaction_id', $merchantTransactionId)
                    )
                    ->when(!$merchantTransactionId && $gatewayId, fn($q) =>
                        $q->where('gateway_id', $gatewayId)
                    )
                    ->lockForUpdate()
                    ->first();

                if (! $payment) {
                    Log::warning('APPYPAY_PAYMENT_NOT_FOUND', [
                        'merchantTransactionId' => $merchantTransactionId,
                        'gatewayId' => $gatewayId,
                    ]);

                    return;
                }

                if ($payment->status === 'paid') {
                    return;
                }

                $newStatus = match ($status) {
                    'Success' => 'paid',
                    'Pending' => 'waiting',
                    default => 'failed',
                };

                $payment->update([
                    'status' => $newStatus,
                    'gateway_id' => $gatewayId ?? $payment->gateway_id,
                    'raw_response' => $request->all(),
                    'paid_at' => $newStatus === 'paid' ? now() : null,
                ]);

                if ($newStatus === 'paid' && $payment->subscription) {
                    $subscriptionToActivate = $payment->subscription;
                }
            });

            if (
                $subscriptionToActivate &&
                Subscricao::normalizeStatus($subscriptionToActivate->status) !== Subscricao::STATUS_ATIVA
            ) {
                SubscriptionActivated::dispatch(
                    $subscriptionToActivate
                )->afterCommit();
            }

            return response()->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            if ($e->getMessage() === 'duplicate_webhook_event') {
                return response()->json(['status' => 'duplicate'], 202);
            }

            Log::error('APPYPAY_WEBHOOK_ERROR', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json(['error' => 'server_error'], 500);
        }
    }

    public function handleWebhook(Request $request)
    {
        return $this->handle($request);
    }

    private function resolveEventId(Request $request, string $payload): string
    {
        return (string) (
            $request->input('eventId')
            ?? $request->input('event_id')
            ?? $request->header('X-Event-Id')
            ?? hash('sha256', $payload)
        );
    }
}
