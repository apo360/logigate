<?php

namespace App\Services;

use App\Models\ProductPrice;
use App\Models\Produto;
use Illuminate\Support\Facades\DB;

class ProdutoPriceService
{
    /**
     * Obtém o preço atual de um produto
     */
    public function getCurrentPrice($productId)
    {
        return ProductPrice::where('product_id', $productId)
            ->latest('created_at')
            ->first();
    }

    /**
     * Atualiza o preço mantendo histórico
     */
    public function updateProductPrice(Produto $product, array $data, string $reason = 'update')
    {
        return DB::transaction(function () use ($product, $data, $reason) {

            // Novos preços submetidos
            $newPrice = $data['venda'] ?? $data['preco_venda'] ?? null;

            if (!$newPrice) {
                throw new \Exception("O preço de venda é obrigatório para atualizar o preço.");
            }

            // Preço validado com cálculos automáticos
            $validatedPrice = $this->applyAutomaticPriceRules($newPrice, $product);

            return ProductPrice::create([
                'product_id' => $product->id,
                'price'      => $validatedPrice,
                'currency'   => $data['currency'] ?? 'AOA',
                'type'       => $reason, // update, promotion, admin-change, etc.
            ]);
        });
    }

    /**
     * Obtém histórico de preços
     */
    public function getPriceHistory($productId)
    {
        return ProductPrice::where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * createInitialPrice
     */
    public function createInitialPrice(Produto $product, array $data)
    {
        return DB::transaction(function () use ($product, $data) {

            $validatedPrice = $this->applyAutomaticPriceRules($data['venda']);

            $data['fk_product'] = $product->id;
            
            return ProductPrice::create($data);
        });
    }


    /**
     * Regras automáticas de cálculo aplicadas ao preço
     */
    private function applyAutomaticPriceRules(float $price, Produto $product = null)
    {
        $originalPrice = $price;

        /**
         * 🧮 REGRA 1 — Preço mínimo permitido
         * Ex: Nenhum preço pode ser menor que 100 AOA
         */
        $minPrice = 100;
        if ($price < $minPrice) {
            $price = $minPrice;
        }

        /**
         * 🧮 REGRA 2 — Se o produto tem custo, aplicar markup mínimo
         * Ex: lucro mínimo de 10%
         */
        if ($product && $product->cost_price > 0) {
            $minAllowed = $product->cost_price * 1.10; // 10% acima do custo

            if ($price < $minAllowed) {
                $price = $minAllowed;
            }
        }

        /**
         * 🧮 REGRA 3 — Evitar alteração brusca
         * Ex: não deixar alterar mais de ±40% num único update
         */
        if ($product) {
            $current = $this->getCurrentPrice($product->id);

            if ($current) {
                $maxIncrease = $current->price * 1.40;
                $maxDecrease = $current->price * 0.60;

                if ($price > $maxIncrease) $price = $maxIncrease;
                if ($price < $maxDecrease) $price = $maxDecrease;
            }
        }

        /**
         * 🧮 REGRA 4 — Preço sugerido automático
         *    Se o preço inserido é 0, gerar preço automático
         */
        if ($originalPrice == 0 && $product) {
            // Ex: custo + markup 20%
            $price = $product->cost_price * 1.20;
        }

        return round($price, 2);
    }
}
