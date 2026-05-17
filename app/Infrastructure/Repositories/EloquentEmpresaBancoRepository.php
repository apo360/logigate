<?php
// app/Infrastructure/Repositories/EloquentEmpresaBancoRepository.php
namespace App\Infrastructure\Repositories;

use App\Domains\Banco\Repositories\EmpresaBancoRepositoryInterface;
use App\Application\Banco\DTOs\EmpresaBancoDTO;
use App\Models\EmpresaBanco;

class EloquentEmpresaBancoRepository implements EmpresaBancoRepositoryInterface
{
    public function create(EmpresaBancoDTO $dto): EmpresaBanco
    {
        return EmpresaBanco::create($dto->toArray());
    }
}