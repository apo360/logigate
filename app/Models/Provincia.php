<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    use HasFactory;

    protected $fillable = ['Nome'];

    public function municipios()
    {
        return $this->hasMany(Municipio::class);
    }

    // Método para obter todos os municípios de uma província
    public function getAllMunicipios()
    {
        return $this->municipios()->get();
    }
}
