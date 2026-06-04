<?php

namespace App\Domains\Produtos\Services;

use App\Models\ProductPrice;
use App\Models\Produto;

final class ProdutoPriceRules
{
    public function normalizeVenda(float $price, Produto $produto, ?ProductPrice $currentPrice = null): float
    {
        $originalPrice = $price;
        $minPrice = 100.0;

        if ($price < $minPrice) {
            $price = $minPrice;
        }

        $custo = (float) ($produto->price?->custo ?? 0);

        if ($custo > 0) {
            $minAllowed = $custo * 1.10;

            if ($price < $minAllowed) {
                $price = $minAllowed;
            }
        }

        if ($currentPrice) {
            $maxIncrease = (float) $currentPrice->venda * 1.40;
            $maxDecrease = (float) $currentPrice->venda * 0.60;

            if ($price > $maxIncrease) {
                $price = $maxIncrease;
            }

            if ($price < $maxDecrease) {
                $price = $maxDecrease;
            }
        }

        if ($originalPrice == 0 && $custo > 0) {
            $price = $custo * 1.20;
        }

        return round($price, 2);
    }

    public function vendaSemIva(float $venda, mixed $imposto): float
    {
        $tax = (float) $imposto;

        if ($tax <= 0) {
            return $venda;
        }

        return round($venda / (1 + ($tax / 100)), 2);
    }
}
