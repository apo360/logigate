<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MercadoriaLocalizacao extends Model
{
    protected $table = 'mercadoria_localizacaos';

    protected $fillable = [
        'codigo',
        'descricao',
    ];

    public function __toString()
    {
        return $this->descricao ?? $this->codigo;
    }

    public function processos()
    {
        return $this->hasMany(Processo::class, 'localizacao_mercadoria_id');
    }
}
