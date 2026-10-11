<?php

namespace App\Application\Billing\Contracts;

use App\Application\Billing\DTOs\PaymentGatewayResult;
use App\Domains\Billing\ValueObjects\MerchantTransactionId;
use App\Domains\Billing\ValueObjects\Money;

interface RefPaymentGateway
{
    public function create(Money $money, MerchantTransactionId $merchantId, string $description, array $notify = []): PaymentGatewayResult;
}
