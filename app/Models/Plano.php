<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plano extends Model
{
    protected $fillable = ['nome', 'descricao', 'preco_mensal', 'duracao_padrao', 'status'];

    public function modulos()
    {
        return $this->belongsToMany(Module::class, 'plano_modulos', 'plano_id', 'modulo_id');
    }

    /* public function assinaturas()
    {
        return $this->hasMany(Assinatura::class);
    } */

}
