<?php

namespace App\Domains\Customers\Exceptions;

use RuntimeException;

class CustomerNotAssociatedWithEmpresaException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('O cliente não está associado a esta empresa.');
    }
}