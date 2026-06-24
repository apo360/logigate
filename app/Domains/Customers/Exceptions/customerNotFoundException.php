<?php

namespace App\Domains\Customers\Exceptions;

use RuntimeException;

class CustomerNotFoundException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Cliente não encontrado.');
    }
}