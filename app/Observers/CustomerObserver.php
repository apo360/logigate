<?php

namespace App\Observers;

use App\Models\Customer;
use App\Support\ActorContext;

class CustomerObserver
{
    public function creating(Customer $customer): void
    {
        if (empty($customer->user_id)) {
            $customer->user_id = ActorContext::id();
        }

        $customer->is_active ??= 1;
        $customer->AccountID ??= 0;
    }
}
