<?php

namespace App\Application\Billing\UseCases;

use App\Application\Billing\DTOs\PaymentViewData;
use App\Application\Billing\DTOs\StartCheckoutData;
use App\Domains\Billing\Enums\PaymentMethod;
use App\Domains\Billing\Enums\PaymentStatus;
use App\Models\Empresa;
use Illuminate\Support\Facades\DB;

final class StartCheckout
{
    public function __construct(
        private readonly StartGpoPayment $startGpoPayment,
        private readonly StartRefPayment $startRefPayment,
        private readonly BuildPaymentViewData $viewData,
    ) {}

    public function execute(StartCheckoutData $data): PaymentViewData
    {
        return DB::transaction(function () use ($data) {
            $empresa = Empresa::with(['subscricoes.plano', 'subscricoes.pagamentoOnline'])
                ->whereKey($data->empresaId)
                ->lockForUpdate()
                ->firstOrFail();

            $subscription = $empresa->subscricoes()
                ->with(['plano', 'pagamentoOnline'])
                ->pending()
                ->latest('id')
                ->firstOrFail();

            $existing = $subscription->pagamentoOnline()
                ->whereIn('method', [PaymentMethod::GPO->value, PaymentMethod::REF->value])
                ->whereIn('status', [PaymentStatus::Pending->value, PaymentStatus::Processing->value, PaymentStatus::Paid->value])
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $this->viewData->fromSubscription($subscription, $existing, 'Pagamento ja iniciado.');
            }

            return match ($data->method) {
                PaymentMethod::GPO => $this->startGpoPayment->execute($subscription, (string) $data->phone),
                PaymentMethod::REF => $this->startRefPayment->execute($subscription),
            };
        });
    }
}
