<?php
// app/Services/AppyPay/AppyPayAuthService.php

namespace App\Services\AppyPay;

use Illuminate\Support\Facades\Http;

class AppyPayAuthService
{
    public function getToken(): string
    {
        $response = Http::asForm()->post(
            config('services.appypay.oauth_url'),
            [
                'grant_type'    => 'client_credentials',
                'client_id'     => config('services.appypay.client_id'),
                'client_secret' => config('services.appypay.client_secret'),
                'resource'      => config('services.appypay.resource'),
            ]
        );

        if (! $response->ok()) {
            throw new \Exception(
                'Erro OAuth AppyPay: '.$response->body()
            );
        }

        $json = $response->json();

        if (! isset($json['access_token'])) {
            throw new \Exception(
                'OAuth AppyPay não retornou access_token: '.$response->body()
            );
        }

        return $json['access_token'];
    }
}
