<?php

namespace App\Infrastructure\PaymentGateways\AppyPay;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class AppyPayAuthClient
{
    public function getToken(): string
    {
        $config = AppyPayConfig::fromConfig();

        $response = Http::asForm()->post($config->oauthUrl, [
            'grant_type' => 'client_credentials',
            'client_id' => $config->clientId,
            'client_secret' => $config->clientSecret,
            'resource' => $config->resource,
        ]);

        if (! $response->ok()) {
            Log::error('APPYPAY_OAUTH_HTTP_ERROR', [
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);

            throw new \RuntimeException('Erro OAuth AppyPay.');
        }

        $json = $response->json();

        if (! isset($json['access_token'])) {
            Log::error('APPYPAY_OAUTH_TOKEN_MISSING', [
                'status' => $response->status(),
                'body' => $json,
            ]);

            throw new \RuntimeException('OAuth AppyPay nao retornou access_token.');
        }

        return (string) $json['access_token'];
    }

}
