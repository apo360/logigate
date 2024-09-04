<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mercadoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'Fk_Importacao',
        'Descricao',
        'NCM_HS',
        'NCM_HS_Numero',
        'Quantidade',
        'Qualificacao',
        'Unidade',
        'Peso',
        'preco_unitario',
        'preco_total',
        'codigo_aduaneiro',
    ];

    public function importacao()
    {
        return $this->belongsTo(Importacao::class, 'Fk_Importacao');
    }
}
