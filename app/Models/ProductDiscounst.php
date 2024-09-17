<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDiscounst extends Model
{
    use HasFactory;

    protected $table = 'product_discounsts';

    protected $fillable = [
        'product_type_id',
        'discount_percentage',
        'discount_amount',
        'start_date',
        'end_date',
    ];

    /**
     * Relacionamento com Produto
     */
    public function produto()
    {
        return $this->belongsTo(Produto::class, 'product_type_id');
    }
}
