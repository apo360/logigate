<?php

namespace App\Models;

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
    ];

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
    public function prices()
    {
        return $this->hasMany(ProductPrice::class, 'fk_product');
    }

    /**
     * Relacionamento com ProductDiscount (Descontos aplicáveis ao Produto)
     */
    public function discounts()
    {
        return $this->hasMany(ProductDiscounst::class, 'product_id');
    }
}

