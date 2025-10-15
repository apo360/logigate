<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Recibo extends Model
{
    //
    protected $table = 'recibos';

    protected $fillable = [
        'debito_total', 
        'credito_total', 
        'recibo_no', 
        'periodo_contabil', 
        'transacaoID', 
        'data_emissao_recibo', 
        'tipo_reciboID', 
        'descricao_pagamento', 
        'systemID', 
        'estado_pagamento', 
        'data_hora_estado', 
        'motivo_alterar_estado', 
        'sourceID', 
        'origem_recibo', 
        'meio_pagamento', 
        'montante_pagamento', 
        'data_pagamento', 
        'customer_id', 
        'tipo_imposto_retido', 
        'motivo_retencao', 
        'montante_retencao'
    ];

    public function facturas()
    {
        return $this->hasMany(ReciboFactura::class, 'reciboID');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function tipoRecibo()
    {
        return $this->belongsTo(TipoRecibo::class, 'tipo_reciboID');
    }

    /**
     * The "boot" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($recibo) {
            // Lógica a ser executada antes da criação do recibo
            $recibo->data_hora_estado = Carbon::now();
            $recibo->estado_pagamento = 'N'; // Estado inicial
            $recibo->sourceID = Auth::user()->id; // Usuário autenticado
            $recibo->systemID = 1; // Definir conforme necessário
            $recibo->periodo_contabil = now()->format('Y-m'); // Exemplo de período contabil
        });

        static::deleting(function ($recibo) {
            // Deletar todas as associações na tabela pivô
            $recibo->facturas()->delete();
        });
    }
}
