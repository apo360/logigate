<?php

namespace App\Application\Billing\UseCases;

use App\Domains\Billing\Enums\PaymentMethod;
use App\Domains\Billing\Enums\PaymentStatus;
use App\Domains\Billing\ValueObjects\MerchantTransactionId;
use App\Models\Empresa;
use App\Models\PagamentoOnline;
use App\Models\Subscricao;
use App\Services\BillingAccess;
use App\Services\SubscriptionQuote;
use Illuminate\Support\Facades\DB;

final class ReservePayment
{
    public function execute(Subscricao $subscription, PaymentMethod $method, ?string $phone = null): array
    {
        app(BillingAccess::class)->authorize((int) $subscription->empresa_id);

        return DB::transaction(function () use ($subscription, $method, $phone) {
            Empresa::whereKey($subscription->empresa_id)->lockForUpdate()->firstOrFail();
            $sub = Subscricao::withoutGlobalScopes()->whereNull('deleted_at')->whereKey($subscription->id)
                ->where('empresa_id', $subscription->empresa_id)->lockForUpdate()->firstOrFail();
            $existing = $sub->pagamentoOnline()->whereIn('status', ['pending', 'processing', 'paid'])->latest('id')->lockForUpdate()->first();
            if ($existing) {
                return [$existing, false];
            }
            if (! $sub->hasStatus('pendente')) {
                throw new \LogicException('Esta subscrição já não está pendente.');
            }
            $quote = app(SubscriptionQuote::class)->forSubscription($sub);
            if ($quote['total'] <= 0) {
                throw new \InvalidArgumentException('Um plano gratuito não requer pagamento.');
            }
            $id = (string) MerchantTransactionId::generate($method);
            $payment = PagamentoOnline::create([
                'empresa_id' => $sub->empresa_id, 'subscription_id' => $sub->id, 'method' => $method->value,
                'amount' => $quote['total'], 'currency' => $quote['currency'], 'status' => PaymentStatus::Processing->value,
                'merchant_transaction_id' => $id, 'idempotency_key' => $id, 'phone' => $phone,
            ]);

            return [$payment, true];
        }, 3);
    }
}
