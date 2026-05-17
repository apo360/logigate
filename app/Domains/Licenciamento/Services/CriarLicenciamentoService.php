<?php

namespace App\Domains\Licenciamento\Services;

use App\Application\Licenciamento\DTOs\CriarLicenciamentoDTO;
use App\Models\Licenciamento;

class CriarLicenciamentoService
{
    public function execute(CriarLicenciamentoDTO $dto): Licenciamento
    {
        $licenciamento = new Licenciamento();

        $licenciamento->fill($dto->toArray());
        
        $licenciamento->save();

        return $licenciamento;
    }
}