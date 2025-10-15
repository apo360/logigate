<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PautaAduaneira extends Model
{
    use HasFactory;

    protected $table = 'pauta_aduaneira';

    protected $fillable = [
        'codigo',  // O código na base dados está com pontos Ex: 0203.11.00
        'descricao',
        'uq',
        'rg',
        'sadc',
        'ua',
        'requisitos',
        'observacao',
        'iva',
        'ieq'
    ];

    public function toSearchableArray()
    {
        $array = $this->toArray();
        // Customize the array as needed
        return $array;
    }

    /**
     * Relação Mercadorias com a Pauta Aduaneira
     */
    public function mercadorias()
    {
        return $this->hasMany(Mercadoria::class, 'codigo_aduaneiro', 'codigo');
    }
}
