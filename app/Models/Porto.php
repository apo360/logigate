<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Porto extends Model
{
    use HasFactory;

    protected $table = 'portos';

    protected $fillable = [
        'id',
        'continente',
        'pais',
        'porto',
        'link',
        'pais_id',
        'sigla',
    ];

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'pais_id', 'id');
    }

    public function processos()
    {
        return $this->hasMany(Processo::class, 'porto_desembarque_id', 'id');
    }
}
