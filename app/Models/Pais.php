<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
    use HasFactory;

    protected $table = "paises";

    protected $fillable = [
        'id','codigo', 'pais', 'nacionalidade', 'moeda', 'capital', 'latitude', 'longitude', 'cambio', 'data_cambio'
    ];

    public function portos()
    {
        return $this->hasMany(Porto::class, 'pais_id', 'id');
    }

    // Função para obter o valor de um campo específico
    public static function getByField($field, $value, $output = null)
    {
        $result = self::where($field, $value)->first();
        return $output ? $result->{$output} : $result;
    }

    public static function getAllCountries()
    {
        return self::orderBy('pais')->get();
    }
}
