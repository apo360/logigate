<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pagamento extends Model
{
    use HasFactory;

    protected $fillable = ['empresa_id', 'metodo_pagamento_id', 'valor', 'data_pagamento'];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function metodoPagamento()
    {
        return $this->belongsTo(MetodoPagamento::class);
    }
}
