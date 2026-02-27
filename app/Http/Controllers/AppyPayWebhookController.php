<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\PagamentoOnline;
use App\Events\SubscriptionActivated;

class AppyPayWebhookController
{
    public function handle(Request $request)
    {
        Log::info('APPYPAY_WEBHOOK_RECEIVED', [
            'method' => $request->method(),
        ]);

        if ($request->isMethod('get')) {
            return response()->json(['status' => 'webhook_online']);
        }

        $gatewayId = $request->input('id');
        $status = $request->input('responseStatus.status');
        $merchantTransactionId = $request->input('merchantTransactionId');

        if (! $gatewayId && ! $merchantTransactionId) {
            Log::warning('APPYPAY_INVALID_PAYLOAD', $request->all());
            return response()->json(['error'=>'invalid_payload'],400);
        }

        try {

            $subscriptionToActivate = null;

            DB::transaction(function () use (
                $request,
                $gatewayId,
                $status,
                $merchantTransactionId,
                &$subscriptionToActivate
            ) {

                // 🔎 Localizar pagamento com lock
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
                        'merchantTransactionId'=>$merchantTransactionId,
                        'gatewayId'=>$gatewayId
                    ]);
                    return;
                }

                // 🛡 Idempotência nível pagamento
                if ($payment->status === 'paid') {
                    return;
                }

                // 🎯 Mapear status
                $newStatus = match ($status) {
                    'Success' => 'paid',
                    'Pending' => 'waiting',
                    default => 'failed'
                };

                $payment->update([
                    'status' => $newStatus,
                    'gateway_id' => $gatewayId ?? $payment->gateway_id,
                    'raw_response' => $request->all(),
                    'paid_at' => $newStatus === 'paid' ? now() : null,
                ]);

                // 🚀 Apenas marcar para ativação
                if ($newStatus === 'paid' && $payment->subscription) {
                    $subscriptionToActivate = $payment->subscription;
                }
            });

            // 🔔 Disparar evento APÓS commit
            if ($subscriptionToActivate &&
                $subscriptionToActivate->status !== 'ATIVA') {

                SubscriptionActivated::dispatch(
                    $subscriptionToActivate
                )->afterCommit();
            }

            return response()->json(['status'=>'ok']);

        } catch (\Throwable $e) {

            Log::error('APPYPAY_WEBHOOK_ERROR',[
                'error'=>$e->getMessage(),
                'payload'=>$request->all()
            ]);

            return response()->json(['error'=>'server_error'],500);
        }
    }

    public function handleWebhook(Request $request)
    {
        return $this->handle($request);
    }
}