<?php

namespace App\Domains\Customers\Events;

use App\Models\Customer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CustomerUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Customer $customer)
    {
    }
}
