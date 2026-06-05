<?php

namespace App\Http\Controllers;

use App\Application\Billing\UseCases\HandleAppyPayWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AppyPayWebhookController extends BaseController
{
    public function handle(Request $request, HandleAppyPayWebhook $handleWebhook)
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

        try {
            $result = $handleWebhook->execute($request->all(), $payload, $request->header('X-Event-Id'));

            return match ($result->result) {
                'duplicate' => response()->json(['status' => 'duplicate'], 202),
                'invalid_payload' => response()->json(['error' => 'invalid_payload'], 400),
                'payment_not_found' => response()->json(['status' => 'ok']),
                default => response()->json(['status' => $result->result]),
            };
        } catch (\Throwable $e) {
            Log::error('APPYPAY_WEBHOOK_ERROR', [
                'error' => $e->getMessage(),
                'merchantTransactionId' => $request->input('merchantTransactionId'),
            ]);

            return response()->json(['error' => 'server_error'], 500);
        }
    }

    public function handleWebhook(Request $request)
    {
        return $this->handle($request, app(HandleAppyPayWebhook::class));
    }
}
