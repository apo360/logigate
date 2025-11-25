<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPriceLogs extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    // Use HasFactory e Audit table if needed
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    
    // Define the table associated with the model
    protected $table = 'product_price_logs';

    // Define fillable attributes
    protected $fillable = [
        'product_price_id',
        'old_price',
        'new_price',
        'old_tax',
        'new_tax',
        'changed_by',
        'change_reason',
        'change_origin',
    ];

    /* -------------- Relationships ----------------------- */
    
    // Relacionamento com ProductPrice
    public function productPrice()
    {
        return $this->belongsTo(ProductPrice::class, 'product_price_id');
    }

    // Relacionamento com o usuário que fez a alteração
    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // Timestamps are enabled by default
}
