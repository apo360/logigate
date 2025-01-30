<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PautaAduaneira extends Model
{
    use HasFactory;

    protected $table = 'pauta_aduaneira';

    protected $fillable = [
        'codigo',
        'descricao',
        'uq',
        'rg',
        'sadc',
        'ua',
        'requisitos',
        'observacao',
    ];

    public function toSearchableArray()
    {
        $array = $this->toArray();
        // Customize the array as needed
        return $array;
    }

    // Definir um Accessor para remover os pontos do código automaticamente
    public function getCodigoSemPontosAttribute()
    {
        return str_replace('.', '', $this->codigo);
    }
}
