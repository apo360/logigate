<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscricao extends Model
{
    use HasFactory;

    protected $table = "subscricoes";

    protected $fillable = [
        'empresa_id', 
        'modulo_id', 
        'data_subscricao', 
        'data_expiracao', 
        'status', '
        tipo_plano', 
        'modalidade_pagamento', 
        'valor_pago', 
        'status', 
        'plano_id'];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function modulo()
    {
        return $this->belongsTo(Module::class, 'modulo_id');
    }

    public function plano()
    {
        return $this->belongsTo(Plano::class, 'plano_id');
    }

    /**
     * Get the tipo_plano attribute ENUM('Teste','Básico', 'Profissional', 'Empresarial').
     */
    public function tipoPlano()
    {
        return $this->tipo_plano;
    }

    /**
     * Get the modalidade_pagamento attribute ENUM('Mensal','Semestral','Trimestral','Anual').
    */
    public function modalidadePagamento()
    {
        return $this->modalidade_pagamento;
    }

    public function isExpired()
    {
        return Carbon::now()->gt(Carbon::parse($this->data_fim));
    }
}
