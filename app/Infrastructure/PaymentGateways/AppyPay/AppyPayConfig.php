<?php

namespace App\Infrastructure\PaymentGateways\AppyPay;

final readonly class AppyPayConfig
{
    public function __construct(
        public ?string $oauthUrl,
        public ?string $clientId,
        public ?string $clientSecret,
        public ?string $resource,
        public ?string $gatewayUrl,
        public ?string $gpoMethod,
        public ?string $refMethod,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            config('services.appypay.oauth_url'),
            config('services.appypay.client_id'),
            config('services.appypay.client_secret'),
            config('services.appypay.resource'),
            rtrim((string) config('services.appypay.gwy_url'), '/'),
            config('services.appypay.methods.gpo'),
            config('services.appypay.methods.ref'),
        );
    }

    public function chargeUrl(): string
    {
        if (blank($this->gatewayUrl)) {
            throw new \RuntimeException('AppyPay gateway URL nao configurada.');
        }

        return $this->gatewayUrl . '/v2.0/charges';
    }
}
