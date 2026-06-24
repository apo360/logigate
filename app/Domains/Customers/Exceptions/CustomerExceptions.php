<?php

namespace App\Domains\Customers\Exceptions;

use RuntimeException;

class CustomerExceptions{

    public static function customerAlreadyExists(): RuntimeException
    {
        return new RuntimeException('Cliente já existe.');
    }

    public static function CustomerJaAssociadoEmpresaException(): RuntimeException
    {
        return new RuntimeException('O cliente já está associado a esta empresa.');
    }

    public function CustomerHasActiveProcessesException(): RuntimeException
    {
        return new RuntimeException('O cliente possui processos ativos e não pode ser excluído.');
    }

    public function CustomerHasActiveLicenciamentosException(): RuntimeException
    {
        return new RuntimeException('O cliente possui licenciamentos ativos e não pode ser excluído.');
    }

    public function CustomerJaTemCredenciaisPortalException(): RuntimeException
    {
        return new RuntimeException('O cliente já possui credenciais para acesso ao portal.');
    }

    public function CustomerNaoTemCredenciaisPortalException(): RuntimeException
    {
        return new RuntimeException('O cliente não possui credenciais para acesso ao portal.');
    }

}