<?php

namespace App\Infrastructure\PaymentGateways\AppyPay;

use App\Application\Billing\DTOs\PaymentGatewayResult;
use App\Domains\Billing\ValueObjects\MerchantTransactionId;
use App\Domains\Billing\ValueObjects\Money;
use App\Domains\Billing\ValueObjects\PhoneNumberAO;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class AppyPayGpoClient
{
    public function __construct(
        private readonly AppyPayAuthClient $auth,
        private readonly AppyPayResponseMapper $mapper,
    ) {}

    public function create(Money $money, MerchantTransactionId $merchantId, string $description, PhoneNumberAO $phone): PaymentGatewayResult
    {
        $config = AppyPayConfig::fromConfig();

        try {
            $response = Http::withToken($this->auth->getToken())
                ->acceptJson()
                ->asJson()
                ->post($config->chargeUrl(), [
                    'amount' => $money->amount,
                    'currency' => $money->currency,
                    'paymentMethod' => $config->gpoMethod,
                    'description' => $description,
                    'merchantTransactionId' => (string) $merchantId,
                    'paymentInfo' => [
                        'phoneNumber' => $phone->international(),
                    ],
                ]);

            $payload = $response->json() ?? [];

            if (! $response->successful()) {
                Log::warning('APPYPAY_GPO_HTTP_ERROR', [
                    'status' => $response->status(),
                    'merchantTransactionId' => (string) $merchantId,
                    'body' => $payload,
                ]);

                return $this->mapper->failure('HTTP_ERROR', 'Falha ao iniciar GPO.', $payload);
            }

            return $this->mapper->success($payload);
        } catch (\Throwable $exception) {
            Log::error('APPYPAY_GPO_EXCEPTION', [
                'merchantTransactionId' => (string) $merchantId,
                'error' => $exception->getMessage(),
            ]);

            return $this->mapper->failure('EXCEPTION', $exception->getMessage());
        }
    }
}
