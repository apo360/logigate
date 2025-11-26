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
        'produto_id',
        'old_price',
        'new_price',
        'variacao',
        'motivo',
        'user_id',
        'ia_impacto',
        'ia_reavaliacao',
    ];

    protected $casts = [
        'ia_reavaliacao' => 'datetime',
    ];

    // -- Define relationships --
    public function produto()
    {
        return $this->belongsTo(ProductPrice::class, 'produto_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
