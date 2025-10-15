<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CondicaoPagamento extends Model
{
    protected $table = 'condicao_pagamentos';

    protected $fillable = ['codigo', 'descricao'];

    public function processos()
    {
        return $this->hasMany(Processo::class, 'condicao_pagamento_id');
    }
}
