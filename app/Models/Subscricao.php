<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscricao extends Model
{
    use HasFactory;

    protected $fillable = ['empresa_id', 'modulo_id', 'data_subscricao', 'data_expiracao', 'status'];

    protected $table = "subscricoes";

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function modulo()
    {
        return $this->belongsTo(Module::class);
    }
}
