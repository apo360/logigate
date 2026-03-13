<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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
        'empresa_id',
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

}
