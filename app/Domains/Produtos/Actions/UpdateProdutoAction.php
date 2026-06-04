<?php

namespace App\Domains\Produtos\Actions;

use App\Domains\Produtos\Data\ProdutoFormData;
use App\Domains\Produtos\Repositories\ProdutoRepositoryInterface;
use App\Models\Empresa;
use App\Models\Produto;

final class UpdateProdutoAction
{
    public function __construct(
        private readonly ProdutoRepositoryInterface $produtos,
    ) {
    }

    public function execute(Produto $produto, ProdutoFormData $data, Empresa $empresa): Produto
    {
        $produto = $this->produtos->findForEmpresa($produto->id, $empresa);

        if ($this->produtos->hasSales($produto) && $produto->ProductCode !== $data->productCode) {
            throw new \RuntimeException('Não é permitido alterar o código de produto já associado a faturação.');
        }

        return $this->produtos->update($produto, $data->toArray());
    }
}
