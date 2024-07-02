<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodoPagamento extends Model
{
    use HasFactory;

    protected $fillable = ['metodo', 'descricao'];

    protected $table = "metodos_pagamento";

    public function pagamentos()
    {
        return $this->hasMany(Pagamento::class);
    }
}
