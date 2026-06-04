<?php

namespace App\Domains\Produtos\Repositories;

use App\Models\Empresa;
use App\Models\ProductPrice;
use App\Models\Produto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProdutoRepositoryInterface
{
    public function findForEmpresa(int $id, Empresa $empresa): Produto;

    public function createForEmpresa(Empresa $empresa, array $attributes): Produto;

    public function update(Produto $produto, array $attributes): Produto;

    public function savePrice(Produto $produto, array $attributes): ProductPrice;

    public function currentPrice(Produto $produto): ?ProductPrice;

    public function setStatus(Produto $produto, int $status): Produto;

    public function hasSales(Produto $produto): bool;

    public function paginateForEmpresa(Empresa $empresa, array $filters = []): LengthAwarePaginator;
}
