<?php

namespace App\Application\Billing\UseCases;

use App\Actions\Subscriptions\ActivateSubscriptionAction;
use App\Application\Billing\DTOs\PaymentGatewayResult;
use App\Domains\Billing\Enums\PaymentStatus;
use App\Models\PagamentoOnline;
use App\Models\Subscricao;
use Illuminate\Support\Facades\DB;

final class ApplyGatewayResult
{
    public function execute(PagamentoOnline $payment, PaymentGatewayResult $result): PagamentoOnline
    {
        $payment = DB::transaction(function () use ($payment, $result) {
            $locked = PagamentoOnline::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            // A callback can finish before the HTTP request returns.
            if ($locked->statusEnum() === PaymentStatus::Paid) {
                return $locked;
            }
            $uncertain = in_array($result->errorCode, ['EXCEPTION', 'REQUEST_UNCERTAIN'], true);
            $status = $uncertain ? PaymentStatus::Processing : ($result->success ? $result->status : PaymentStatus::Failed);
            if (in_array($locked->statusEnum(), [PaymentStatus::Failed, PaymentStatus::Expired], true) && $status->isOpen()) {
                $status = $locked->statusEnum();
            }
            $reference = $result->reference;
            $locked->update([
                'gateway_id' => $result->gatewayId ?? $locked->gateway_id, 'status' => $status->value,
                'reference_entity' => $reference?->entity ?? $locked->reference_entity,
                'reference_number' => $reference?->referenceNumber ?? $locked->reference_number,
                'reference_due_date' => $reference?->dueDate ?? $locked->reference_due_date,
                'failure_reason' => $uncertain ? 'Pedido sem confirmação. Aguarde a reconciliação antes de tentar novamente.' : ($status === PaymentStatus::Failed ? $result->message : null),
                'failed_at' => in_array($status, [PaymentStatus::Failed, PaymentStatus::Expired], true) ? now() : $locked->failed_at,
                'paid_at' => $status === PaymentStatus::Paid ? now() : $locked->paid_at, 'raw_response' => $result->rawResponse,
            ]);

            return $locked;
        }, 3);
        if ($payment->statusEnum() === PaymentStatus::Paid) {
            $sub = Subscricao::withoutGlobalScopes()->whereNull('deleted_at')->whereKey($payment->subscription_id)->where('empresa_id', $payment->empresa_id)->firstOrFail();
            app(ActivateSubscriptionAction::class)->execute($sub);
        }

        return $payment->refresh();
    }
}
