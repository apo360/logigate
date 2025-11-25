<?php

namespace App\Observers;

use App\Models\ProductPrice;
use App\Models\ProductPriceLogs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProductPriceObserver
{
    /**
     * Antes de criar um novo preço.
     */
    public function creating(ProductPrice $price)
    {
        // Cálculo automático
        $this->calculateFields($price);

        // Garantir apenas 1 preço ativo
        // $this->deactivateOtherPrices($price);

        // Log
        Log::info("Criando novo preço para o produto {$price->fk_product}", [
            'user' => Auth::id(),
            'dados' => $price->toArray(),
        ]);
    }

    /**
     * Após criar: salvar histórico
     */
    public function created(ProductPrice $price)
    {
        $this->saveHistory($price, 'created');
    }

    /**
     * Antes de atualizar
     */
    public function updating(ProductPrice $price)
    {
        // Se preços mudaram, recalcular
        $this->calculateFields($price);

        // Garantir apenas 1 preço ativo
        $this->deactivateOtherPrices($price);

        // Log de antes/depois
        Log::info("Atualizando preço do produto {$price->fk_product}", [
            'user' => Auth::id(),
            'antes' => $price->getOriginal(),
            'depois' => $price->getDirty(),
        ]);
    }

    /**
     * Após atualizar → salvar histórico
     */
    public function updated(ProductPrice $price)
    {
        $this->saveHistory($price, 'updated');
    }

    /**
     * Cálculo automático de IVA, preço sem IVA, lucro etc.
     */
    private function calculateFields(ProductPrice $price)
    {
        // imposto = venda * (taxID/100)
        $price->taxAmount = ($price->venda * ($price->imposto / 100));

        // venda sem iva
        $price->venda_sem_iva = $price->venda - $price->taxAmount;

        // lucro = venda - custo
        $price->lucro = $price->venda - $price->custo;

        // Se for IVA dedutível
        if ($price->dedutivel_iva === null) {
            $price->dedutivel_iva = 0;
        }
    }

    /**
     * Garantir apenas 1 preço ativo
     * Aqui assumimos que "status = 1" significa ativo
     */
    private function deactivateOtherPrices(ProductPrice $price)
    {
        if ($price->status ?? 1) { // se ativo
            ProductPrice::where('fk_product', $price->fk_product)
                ->where('id', '!=', $price->id ?? 0)
                ->update(['status' => 0]);
        }
    }

    /**
     * Após deletar → salvar histórico
     */
    public function deleted(ProductPrice $price)
    {
        $this->saveHistory($price, 'deleted');
    }

    /**
     * Histórico de alterações de Preços
     */
    private function saveHistory(ProductPrice $price, $action)
    {
        ProductPriceLogs::create([
            'product_price_id' => $price->id,
            'fk_product'       => $price->fk_product,
            'user_id'          => Auth::id(),
            'action'           => $action,
            'changes'          => json_encode($price->getChanges()),
            'full_data'        => json_encode($price->toArray()),
            'reasonID'         => $price->reasonID,
        ]);
    }

    /**
     * Cálculo dos preços em modo servidor
     */
    private function calculatePrices(ProductPrice $product)
    {
        $custo = floatval($product->custo);
        $margem = floatval($product->lucro);
        $venda = floatval($product->venda);
        $taxType = $product->taxa_iva;

        // Calcular venda a partir do custo e margem se não estiver preenchido
        if ($custo > 0 && $margem > 0) {
            $venda = $custo + ($custo * ($margem / 100));
            $product->venda = $venda;
        }

        // Buscar percentagem da taxa de IVA associada
        if ($product->relationLoaded('tax')) {
            $iva = floatval($product->tax->TaxPercentage ?? 0);
        } else {
            $iva = floatval(optional($product->tax)->TaxPercentage ?? 0);
        }

        // Preço sem IVA
        if ($venda > 0 && $iva >= 0) {
            $product->venda_sem_iva = $venda / (1 + $iva / 100);
        }
    }
}
