<?php

namespace App\Domains\Produtos\Actions;

use App\Domains\Produtos\Data\ProdutoPriceData;
use App\Domains\Produtos\Repositories\ProdutoRepositoryInterface;
use App\Domains\Produtos\Services\ProdutoPriceRules;
use App\Models\Produto;

final class CreateInitialProdutoPriceAction
{
    public function __construct(
        private readonly ProdutoRepositoryInterface $produtos,
        private readonly ProdutoPriceRules $rules,
    ) {
    }

    public function execute(Produto $produto, ProdutoPriceData $data): void
    {
        $venda = $this->rules->normalizeVenda($data->venda, $produto);
        $vendaSemIva = $data->vendaSemIva ?? $this->rules->vendaSemIva($venda, $data->imposto);

        $payload = $data->toCreateArray($produto->id);
        $payload['venda'] = $venda;
        $payload['venda_sem_iva'] = $vendaSemIva;

        $this->produtos->savePrice($produto, $payload);
    }
}
