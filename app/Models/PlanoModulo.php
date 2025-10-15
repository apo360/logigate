<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanoModulo extends Model
{
    protected $table = 'plano_modulos';

    protected $fillable = ['plano_id', 'modulo_id'];

    public function plano()
    {
        return $this->belongsTo(Plano::class, 'plano_id');
    }

    public function modulo()
    {
        return $this->belongsTo(Module::class, 'modulo_id');
    }
}
