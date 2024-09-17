<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    use HasFactory;

    protected $table = 'product_prices';

    protected $fillable = [
        'fk_product',
        'unidade',
        'custo',
        'venda',
        'venda_sem_iva',
        'lucro',
        'taxID',
        'imposto',
        'reasonID',
        'taxAmount',
        'dedutivel_iva',
    ];

    /**
     * Relacionamento com Produto
     */
    public function produto()
    {
        return $this->belongsTo(Produto::class, 'fk_product');
    }

    // Relacionamento com a isenção de imposto
    public function exemptionReason()
    {
        return $this->belongsTo(ProductExemptionReason::class, 'reasonID', 'id');
    }
}
