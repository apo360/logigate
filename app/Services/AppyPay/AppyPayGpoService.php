<?php
// app/Services/AppyPay/AppyPayGpoService.php

namespace App\Services\AppyPay;

use Illuminate\Support\Facades\Http;

class AppyPayGpoService
{
    public function create(array $data): array
    {
        $token = app(AppyPayAuthService::class)->getToken();

        return Http::withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post(
            config('services.appypay.gwy_url').'/v2.0/charges',
            [
                'amount' => $data['amount'],
                'currency' => 'AOA',
                'paymentMethod' => config('services.appypay.methods.gpo'),
                'description' => $data['description'],
                'merchantTransactionId' => $data['merchantTransactionId'],
                'paymentInfo' => [
                    'phoneNumber' => $data['phone'],
                ],
            ]
        )->json();
    }
}
