<?php

// app/Services/AppyPay/AppyPayRefService.php

namespace App\Services\AppyPay;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AppyPayRefService
{
    public function create(array $data): array
    {
        $token = app(AppyPayAuthService::class)->getToken();

        try {
            $response = Http::withHeaders([ 'Authorization' => 'Bearer ' .$token, 'Accept' => 'application/json', 'Content-Type' => 'application/json',
            ])->post(
                config('services.appypay.gwy_url').'/v2.0/charges',
                [
                    'amount' => $data['amount'],
                    'currency' => 'AOA',
                    'paymentMethod' => config('services.appypay.methods.ref'),
                    'description' => $data['description'],
                    'merchantTransactionId' => $data['merchantTransactionId'],
                ]
            );

            if (! $response->successful()) {
                Log::error('AppyPay REF HTTP error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'HTTP_ERROR',
                    'response' => $response->json(),
                ];
            }

            return [
                'success' => true,
                'response' => $response->json(),
            ];

        } catch (\Throwable $e) {

            Log::error('AppyPay REF exception', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'error' => 'EXCEPTION',
                'message' => $e->getMessage(),
            ];
        }
    }

    /*public function formatReference(array $reference): array
    {
        return [
            'entity' => $reference['entity'] ?? '',
            'referenceNumber' => $reference['referenceNumber'] ?? '',
            'dueDate' => $reference['dueDate'] ?? '',
            'instructions' => [
                '1. Vá a um terminal Multicaixa',
                '2. Selecione "Pagamentos" > "Pagamento por Referência"',
                '3. Digite a referência acima',
                '4. Confirme o valor e efetue o pagamento'
            ]
        ];
    }*/
}

