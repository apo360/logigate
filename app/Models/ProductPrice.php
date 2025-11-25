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
        'validade_inicio',
        'validade_fim',
        'ativo',
        'motivo_alteracao',
        'alterado_por',
        'origem',
    ];

    /**
     * Relacionamento com Produto
     */
    public function produto()
    {
        return $this->belongsTo(Produto::class, 'fk_product', 'id');
    }

    // Relacionamento com a isenção de imposto
    public function exemptionReason()
    {
        return $this->belongsTo(ProductExemptionReason::class, 'reasonID', 'id');
    }
    
    // Relacionamento com a taxa de imposto
    public function taxa()
    {
        return $this->belongsTo(TaxTable::class, 'taxID', 'id');
    }

    // Log de alterações de preço (Histórico)
    public function priceHistory()
    {
        return $this->hasMany(ProductPriceLogs::class, 'fk_product_price', 'id');
    }
}
