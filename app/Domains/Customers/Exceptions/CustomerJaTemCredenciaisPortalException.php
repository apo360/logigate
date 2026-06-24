<?php

namespace App\Domains\Customers\Exceptions;

use RuntimeException;

class CustomerJaTemCredenciaisPortalException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('O cliente já possui credenciais para acesso ao portal.');
    }
}