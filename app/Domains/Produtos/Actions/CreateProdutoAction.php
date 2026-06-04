<?php

namespace App\Domains\Produtos\Actions;

use App\Domains\Produtos\Data\ProdutoFormData;
use App\Domains\Produtos\Data\ProdutoPriceData;
use App\Domains\Produtos\Repositories\ProdutoRepositoryInterface;
use App\Models\Empresa;
use App\Models\Produto;
use Illuminate\Support\Facades\DB;

final class CreateProdutoAction
{
    public function __construct(
        private readonly ProdutoRepositoryInterface $produtos,
        private readonly CreateInitialProdutoPriceAction $createInitialPrice,
    ) {
    }

    public function execute(ProdutoFormData $produtoData, ProdutoPriceData $priceData, Empresa $empresa): Produto
    {
        return DB::transaction(function () use ($produtoData, $priceData, $empresa): Produto {
            $produto = $this->produtos->createForEmpresa($empresa, $produtoData->toArray());

            $this->createInitialPrice->execute($produto, $priceData);

            return $produto->refresh();
        });
    }
}
