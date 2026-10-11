<?php

namespace App\Application\Billing\Contracts;

use App\Application\Billing\DTOs\PaymentGatewayResult;
use App\Domains\Billing\ValueObjects\MerchantTransactionId;
use App\Domains\Billing\ValueObjects\Money;
use App\Domains\Billing\ValueObjects\PhoneNumberAO;

interface GpoPaymentGateway
{
    public function create(Money $money, MerchantTransactionId $merchantId, string $description, PhoneNumberAO $phone): PaymentGatewayResult;
}
