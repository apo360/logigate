<?php
// app/Domains/Banco/Repositories/EmpresaBancoRepositoryInterface.php
namespace App\Domains\Banco\Repositories;

use App\Models\EmpresaBanco;
use App\Application\Banco\DTOs\EmpresaBancoDTO;

interface EmpresaBancoRepositoryInterface
{
    public function create(EmpresaBancoDTO $dto): EmpresaBanco;
}