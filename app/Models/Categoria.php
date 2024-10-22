<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    protected $table = "categoria_aduaneira";

    protected $fillable = ['nome'];

    public function subcategorias()
    {
        return $this->hasMany(Subcategoria::class);
    }
}
