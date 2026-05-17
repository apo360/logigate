<?php

namespace App\Domains\Banco\Services;
// app/Domains/Banco/Services/EmpresaBancoService.php

use App\Domains\Banco\Repositories\EmpresaBancoRepositoryInterface;
use App\Application\Banco\DTOs\EmpresaBancoDTO;

class EmpresaBancoService
{
    public function __construct(
        private EmpresaBancoRepositoryInterface $repository
    ) {}

    public function criarConta(EmpresaBancoDTO $dto)
    {
        return $this->repository->create($dto);
    }
}