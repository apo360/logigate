<?php

namespace App\Domains\Produtos\Actions;

use App\Domains\Produtos\Repositories\ProdutoRepositoryInterface;
use App\Models\Empresa;
use App\Models\Produto;

final class DeleteProdutoAction
{
    public function __construct(
        private readonly ProdutoRepositoryInterface $produtos,
    ) {
    }

    public function execute(Produto $produto, Empresa $empresa): Produto
    {
        $produto = $this->produtos->findForEmpresa($produto->id, $empresa);

        if ($this->produtos->hasSales($produto)) {
            throw new \RuntimeException('Não pode apagar, já existe faturação ligada.');
        }

        return $this->produtos->setStatus($produto, 0);
    }
}
