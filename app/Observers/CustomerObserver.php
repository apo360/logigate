<?php

namespace App\Observers;

use App\Models\Customer;
use App\Support\ActorContext;
use App\Support\TenantContext;
use Carbon\Carbon;

class CustomerObserver
{
    public function creating(Customer $customer): void
    {
        if (empty($customer->user_id)) {
            $customer->user_id = ActorContext::id();
        }

        $empresaId = TenantContext::empresaId();

        if ($empresaId && empty($customer->CustomerID)) {
            $customer->CustomerID = 'cli' . $empresaId . $customer->CustomerTaxID . '/' . Carbon::now()->format('y');
        }

        $customer->is_active ??= 1;
        $customer->AccountID ??= 0;
    }
}
