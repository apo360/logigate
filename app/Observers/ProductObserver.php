<?php

namespace App\Observers;

use App\Models\Produto;
use App\Models\ProductHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Carbon\Carbon;
// Usar AWS S3 para arquivar imagens
use aws\S3\S3Client;

class ProductObserver
{
    /**
     * Quando está a criar
     */
    public function creating(Produto $product)
    {
        // Se o ProductCode não vier do formulário → gerar automático
        if (empty($product->ProductCode)) {
            $product->ProductCode = $this->generateInternalCode($product);
        }

        // Se a categoria vier como "null" → converter para null real
        if ($product->ProductGroup === "null") {
            $product->ProductGroup = null;
        }

        // se o ProductNumberCode não estiver definido, definir igual ao ProductCode
        if (empty($product->ProductNumberCode)) {
            $product->ProductNumberCode = $product->ProductCode;
        }

        Log::info("Criando produto", [
            'user'  => Auth::id(),
            'dados' => $product->toArray(),
        ]);
    }

    /**
     * Depois de criar
     */
    public function created(Produto $product)
    {
        $this->saveHistory($product, 'created');
    }

    /**
     * Antes de atualizar
     */
    public function updating(Produto $product)
    {
        // Impedir alterar produtos descontinuados
        if ($product->getOriginal('status') == 0) {
            throw new \Exception("Este produto está desativado e não pode ser alterado.");
        }

        // Impedir alterar ProductCode associado a SalesInvoices (regra SAFT)
        if ($product->facturas()->exists() && $product->isDirty('ProductCode')) {
            throw new \Exception("Não é permitido alterar o produto: .".$product->ProductCode." já está associado a facturas.");
        }

        // 
        Log::info("Atualizando produto {$product->id}", [
            'user'   => Auth::id(),
            'antes'  => $product->getOriginal(),
            'depois' => $product->getDirty(),
        ]);
    }

    /**
     * Depois de atualizar
     */
    public function updated(Produto $product)
    {
        $this->saveHistory($product, 'updated');
    }

    /**
     * Bloquear deleção física
     */
    public function deleting(Produto $product)
    {
        throw new \Exception("Não é permitido eliminar produtos. Pode apenas marcar como descontinuado.");
    }

    // ------------------------------------------------------
    // ------------------- MÉTODOS AUXILIARES ---------------
    // ------------------------------------------------------


    /**
     * Gera código interno automático
     */
    private function generateInternalCode(Produto $product)
    {
        $nextId = (Produto::max('id') ?? 0) + 1;

        return 'P-' . date('Y') . '-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Guarda histórico
     */
    private function saveHistory(Produto $product, $action)
    {
        ProductHistory::create([
            'product_id' => $product->id,
            'user_id'    => Auth::id(),
            'action'     => $action,
            'changes'    => json_encode($product->getChanges()),
            'full_data'  => json_encode($product->toArray()),
        ]);
    }
}
