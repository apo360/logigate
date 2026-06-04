<?php

namespace App\Domains\Produtos\Actions;

use App\Domains\Produtos\Data\ProdutoPriceData;
use App\Domains\Produtos\Repositories\ProdutoRepositoryInterface;
use App\Domains\Produtos\Services\ProdutoPriceRules;
use App\Models\Empresa;
use App\Models\Produto;
use Illuminate\Support\Facades\DB;

final class UpdateProdutoPriceAction
{
    public function __construct(
        private readonly ProdutoRepositoryInterface $produtos,
        private readonly ProdutoPriceRules $rules,
    ) {
    }

    public function execute(Produto $produto, ProdutoPriceData $data, Empresa $empresa): void
    {
        DB::transaction(function () use ($produto, $data, $empresa): void {
            $produto = $this->produtos->findForEmpresa($produto->id, $empresa);
            $currentPrice = $this->produtos->currentPrice($produto);
            $newPrice = $this->rules->normalizeVenda($data->venda, $produto, $currentPrice);
            $vendaSemIva = $data->vendaSemIva ?? $this->rules->vendaSemIva($newPrice, $currentPrice?->imposto ?? $data->imposto);

            $this->produtos->savePrice($produto, $data->toUpdateArray($newPrice, $vendaSemIva));
        });
    }
}
