<?php

namespace App\Application\Billing\UseCases;

use App\Application\Billing\DTOs\PaymentViewData;
use App\Application\Billing\DTOs\StartCheckoutData;
use App\Domains\Billing\Enums\PaymentMethod;
use App\Models\Subscricao;
use App\Services\BillingAccess;

final class StartCheckout
{
    public function __construct(private readonly StartGpoPayment $gpo, private readonly StartRefPayment $ref) {}

    public function execute(StartCheckoutData $data): PaymentViewData
    {
        app(BillingAccess::class)->authorize($data->empresaId, $data->userId);
        $sub = Subscricao::where('empresa_id', $data->empresaId)->pending()->latest('id')->firstOrFail();

        // Each client reserves and commits before contacting the provider.
        return match ($data->method) {
            PaymentMethod::GPO => $this->gpo->execute($sub, (string) $data->phone),
            PaymentMethod::REF => $this->ref->execute($sub),
        };
    }
}
