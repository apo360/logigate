<?php

namespace App\Domains\Produtos\Queries;

use App\Domains\Produtos\Repositories\ProdutoRepositoryInterface;
use App\Models\Empresa;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ProdutoTableQuery
{
    public function __construct(
        private readonly ProdutoRepositoryInterface $produtos,
    ) {
    }

    public function paginate(Empresa $empresa, array $filters = []): LengthAwarePaginator
    {
        return $this->produtos->paginateForEmpresa($empresa, $filters);
    }
}
