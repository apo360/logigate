<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ProcessoCustoRegisto extends Model
{
    use BelongsToTenant;

    protected $table = 'processo_custo_registos';

    protected $guarded = ['id'];

    protected $casts = ['contexto' => 'array', 'valor' => 'decimal:2', 'data' => 'date', 'anulado_at' => 'datetime'];

    public function factura()
    {
        return $this->belongsTo(ExternalInvoice::class, 'external_invoice_id');
    }
}
