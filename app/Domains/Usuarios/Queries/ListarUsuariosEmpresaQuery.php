<?php

namespace App\Domains\Usuarios\Queries;

use App\Domains\Usuarios\Repositories\UsuarioRepositoryInterface;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Collection;

final class ListarUsuariosEmpresaQuery
{
    public function __construct(
        private readonly UsuarioRepositoryInterface $usuarios,
    ) {
    }

    public function execute(Empresa $empresa, ?string $search = null): Collection
    {
        return $this->usuarios->listForEmpresa($empresa, $search);
    }
}
