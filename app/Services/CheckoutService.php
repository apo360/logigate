<?php

namespace App\Services;

use App\Application\Billing\DTOs\StartCheckoutData;
use App\Application\Billing\UseCases\StartCheckout;
use App\Domains\Billing\Enums\PaymentMethod;

/** Legacy adapter: there is one authenticated, tenant-aware payment path. */
class CheckoutService
{
    public function pay(array $data): array
    {
        $empresaId = (int) ($data['empresa']->id ?? 0);

        return app(StartCheckout::class)->execute(new StartCheckoutData(
            (int) auth()->id(), $empresaId, PaymentMethod::fromInput($data['method'] ?? ''), $data['phone'] ?? null
        ))->toArray();
    }
}
