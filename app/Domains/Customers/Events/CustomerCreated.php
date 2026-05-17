<?php

namespace App\Domains\Customers\Events;

use App\Models\Customer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CustomerCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Customer $customer)
    {
    }
}
