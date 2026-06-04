<?php

namespace App\Domains\Produtos\Actions;

use App\Domains\Produtos\Repositories\ProdutoRepositoryInterface;
use App\Models\Empresa;
use App\Models\Produto;

final class ToggleProdutoStatusAction
{
    public function __construct(
        private readonly ProdutoRepositoryInterface $produtos,
    ) {
    }

    public function execute(Produto $produto, Empresa $empresa): Produto
    {
        $produto = $this->produtos->findForEmpresa($produto->id, $empresa);
        $newStatus = (int) ! ((bool) $produto->status);

        return $this->produtos->setStatus($produto, $newStatus);
    }
}
