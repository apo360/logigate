<?php

namespace App\Services;

use App\Models\ProductPrice;
use App\Models\ProductPriceLogs;
use App\Models\Produto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProdutoPriceService
{
    /**
     * Obtém o preço atual de um produto
     */
    public function getCurrentPrice($productId)
    {
        return ProductPrice::where('fk_product', $productId)
            ->latest('created_at')
            ->first();
    }

    /**
     * Atualiza o preço mantendo histórico e registrando log
     */
    public function updateProductPrice(Produto $product, array $data, string $reason = 'update')
    {
        return DB::transaction(function () use ($product, $data, $reason) {

            // 1. Obter o preço atual
            $currentPriceModel = $this->getCurrentPrice($product->id);
            $oldPrice = $currentPriceModel ? $currentPriceModel->venda : 0.00;

            // 2. Novos preços submetidos
            $newPrice = $data['new_price'] ?? $data['preco_venda'] ?? null;
            $motivo = $data['motivo'] ?? $reason;
            $notificar = $data['notificar'] ?? false;

            if (!$newPrice) {
                throw new \Exception("O preço de venda é obrigatório para atualizar o preço.");
            }

            // 3. Preço validado com cálculos automáticos
            $validatedPrice = $this->applyAutomaticPriceRules($newPrice, $product);

            // Novo valor sem taxa de acordo com a taxa atual (exemplo de 14%)
            $PriceWithoutTax = $validatedPrice / $this->getTaxMultiplier($product->price->imposto ?? null);

            // 4. Actualizar o novo registro de preço
            $newPriceModel = ProductPrice::where('fk_product', $product->id)
                ->update(
                    [
                        'venda' => $validatedPrice,
                        'venda_sem_iva' => $PriceWithoutTax,
                        'updated_at' => now(),
                    ]
                );

            // 5. Registrar o log detalhado
            $this->logPriceChange(
                $product,
                $oldPrice,
                $validatedPrice,
                $motivo,
                $notificar
            );

            return $newPriceModel;
        });
    }

    /**
     * Registra a alteração de preço no log detalhado.
     */
    private function logPriceChange(Produto $product, float $oldPrice, float $newPrice, string $motivo, bool $notificar)
    {
        $variacao = $oldPrice > 0 ? (($newPrice - $oldPrice) / $oldPrice) * 100 : 0.00;
        $userId = Auth::id();

        // 1. Classificação de Impacto Econômico (IA) e Agendamento de Reavaliação
        $iaData = $this->classifyAndScheduleIA($variacao);

        // 2. Criação do Log
        $log = ProductPriceLogs::create([
            'produto_id' => $product->id,
            'old_price' => $oldPrice,
            'new_price' => $newPrice,
            'variacao' => $variacao,
            'motivo' => $motivo,
            'user_id' => $userId,
            'ia_impacto' => $iaData['impacto'],
            'ia_reavaliacao' => $iaData['reavaliacao'],
        ]);

        // 3. Notificação Opcional
        if ($notificar) {
            // A lógica de notificação será implementada na Fase 7
            $this->notifyManager($log);
        }
    }

    /**
     * Classifica o impacto econômico e agenda a reavaliação (Lógica de IA).
     * Esta função será implementada na Fase 6.
     */
    private function classifyAndScheduleIA(float $variacao): array
    {
        // Lógica de IA (a ser implementada na Fase 6)
        return [
            'impacto' => 'Sem mudança', // Valor temporário
            'reavaliacao' => now()->addDays(30), // Valor temporário
        ];
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
         * 🧮 REGRA 1 — Preço mínimo absoluto
         */
        $minPrice = 100;
        if ($price < $minPrice) {
            $price = $minPrice;
        }

        /**
         * 🧮 REGRA 2 — Markup mínimo baseado no custo real (custo)
         */
        if ($product && $product->custo > 0) {

            // lucro mínimo de 10%
            $minAllowed = $product->custo * 1.10;

            if ($price < $minAllowed) {
                $price = $minAllowed;
            }
        }

        /**
         * 🧮 REGRA 3 — Evitar alteração brusca (>40% para cima ou baixo)
         */
        if ($product) {
            $current = $this->getCurrentPrice($product->id);

            if ($current) {
                $maxIncrease = $current->venda * 1.40;   // campo correto
                $maxDecrease = $current->venda * 0.60;

                if ($price > $maxIncrease) $price = $maxIncrease;
                if ($price < $maxDecrease) $price = $maxDecrease;
            }
        }

        /**
         * 🧮 REGRA 4 — Preço automático se preço inserido for 0
         */
        if ($originalPrice == 0 && $product && $product->custo > 0) {
            // custo + 20% lucro
            $price = $product->custo * 1.20;
        }

        return round($price, 2);
    }


    // Implementação da logica de notificação
    private function notifyManager($log)
    {
        // Lógica de notificação (a ser implementada na Fase 7)
    }

    /**
     * Obtém o multiplicador de taxa baseado no imposto
     */
    private function getTaxMultiplier($imposto)
    {
        // Exemplo simples: 14% de IVA
        if ($imposto) {
            return 1 + ($imposto / 100);
            // return 1 + (14 / 100);
        }
        return 1.0;
    }
}