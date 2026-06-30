<?php

namespace App\Domains\Empresa\Actions;

use App\Domains\Empresa\Data\EmpresaData;
use App\Domains\Empresa\Repositories\EmpresaRepositoryInterface;
use App\Application\Arquivo\Actions\CriarPastaEmpresaAction;
use App\Models\Empresa;
use Illuminate\Support\Facades\DB;

final class CriarEmpresaAction
{
    public function __construct(
        private readonly EmpresaRepositoryInterface $empresas,
        private readonly GerarCodigoContaEmpresaAction $gerarConta,
        private readonly CriarPastaEmpresaAction $criarPastaEmpresa,
    ) {
    }

    public function execute(EmpresaData $data): Empresa
    {
        return DB::transaction(function () use ($data): Empresa {
            $attributes = $data->toAttributes();
            $attributes['conta'] ??= $this->gerarConta->execute();

            $empresa = $this->empresas->create($attributes);

            DB::afterCommit(fn () => $this->criarPastaEmpresa->execute($empresa));

            return $empresa;
        });
    }
}
