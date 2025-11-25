<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    protected $table = "produtos";

    /**
     * Atributos que podem ser preenchidos em massa
     */
    protected $fillable = [
        'empresa_id',
        'ProductType',
        'ProductCode',
        'ProductGroup',
        'ProductDescription',
        'ProductNumberCode',
        'imagem_path',
        'status',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'discontinued_at',
    ];

    // Function boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            // Empresa ID
            $model->empresa_id = Auth::user()->empresas->first()->id;

            // Set the created_at timestamp to the current date and time
            $model->created_at = Carbon::now()->toDateTimeString();
        });

        static::updating(function ($model) {
            // Set the updated_at timestamp to the current date and time
            $model->updated_at = Carbon::now()->toDateTimeString();
        });
    }

    // Desabilitar timestamps se não estiver usando created_at e updated_at
    public $timestamps = true;

    /**
     * Relacionamento com ProductGroup (Grupos de Produtos)
     */
    public function grupo(){
        return $this->belongsTo(ProductGroup::class, 'ProductGroup');
    }

    /**
     * Relacionamento com ProductType (Tipos de Produtos)
     */
    public function tipo(){
        return $this->belongsTo(ProductType::class, 'ProductType', 'code');
    }

    /**
     * Relacionamento com Empresa (Empresa proprietária do produto)
     */
    public function empresa(){
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * Relacionamento com ProductPrice (Preços do Produto)
     */
    public function price()
    {
        return $this->hasOne(ProductPrice::class, 'fk_product')->latest();
    }

    /**
     * Relacionamento com ProductDiscount (Descontos aplicáveis ao Produto)
     */
    public function discounts()
    {
        return $this->hasMany(ProductDiscounst::class, 'product_id');
    }

    /**
     * Relacionamento com Vendas (SalesInvoiceLines)
     */
    public function salesLines()
    {
        return $this->hasMany(SalesLine::class, 'productID');
    }
}

