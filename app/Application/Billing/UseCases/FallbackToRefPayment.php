<?php

namespace App\Application\Billing\UseCases;

use App\Application\Billing\DTOs\PaymentViewData;
use App\Models\PagamentoOnline;
use Illuminate\Support\Facades\Log;

final class FallbackToRefPayment
{
    public function __construct(private readonly StartRefPayment $startRefPayment) {}

    public function execute(PagamentoOnline $failedPayment, string $reason): PaymentViewData
    {
        $failedPayment->update([
            'status' => 'failed',
            'failure_reason' => $reason,
            'failed_at' => now(),
        ]);

        Log::warning('BILLING_GPO_FALLBACK_TO_REF', [
            'payment_id' => $failedPayment->id,
            'subscription_id' => $failedPayment->subscription_id,
            'merchant_transaction_id' => $failedPayment->merchant_transaction_id,
            'reason' => $reason,
        ]);

        return $this->startRefPayment->execute($failedPayment->subscription, true);
    }
}
