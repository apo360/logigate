<?php

namespace App\Infrastructure\PaymentGateways\AppyPay;

use App\Application\Billing\DTOs\PaymentGatewayResult;
use App\Domains\Billing\ValueObjects\MerchantTransactionId;
use App\Domains\Billing\ValueObjects\Money;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class AppyPayRefClient
{
    public function __construct(
        private readonly AppyPayAuthClient $auth,
        private readonly AppyPayResponseMapper $mapper,
    ) {}

    public function create(Money $money, MerchantTransactionId $merchantId, string $description, array $notify = []): PaymentGatewayResult
    {
        $config = AppyPayConfig::fromConfig();

        try {
            $payload = [
                'amount' => $money->amount,
                'currency' => $money->currency,
                'paymentMethod' => $config->refMethod,
                'description' => $description,
                'merchantTransactionId' => (string) $merchantId,
            ];

            if ($notify !== []) {
                $payload['notify'] = $notify;
            }

            $response = Http::withToken($this->auth->getToken())
                ->acceptJson()
                ->asJson()
                ->post($config->chargeUrl(), $payload);

            $responsePayload = $response->json() ?? [];

            if (! $response->successful()) {
                Log::warning('APPYPAY_REF_HTTP_ERROR', [
                    'status' => $response->status(),
                    'merchantTransactionId' => (string) $merchantId,
                    'body' => $responsePayload,
                ]);

                return $this->mapper->failure('HTTP_ERROR', 'Falha ao gerar referencia.', $responsePayload);
            }

            return $this->mapper->success($responsePayload);
        } catch (\Throwable $exception) {
            Log::error('APPYPAY_REF_EXCEPTION', [
                'merchantTransactionId' => (string) $merchantId,
                'error' => $exception->getMessage(),
            ]);

            return $this->mapper->failure('EXCEPTION', $exception->getMessage());
        }
    }
}
