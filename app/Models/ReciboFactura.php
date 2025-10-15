<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReciboFactura extends Model
{
    use HasFactory;

    protected $table = 'recibo_facturas';

    protected $fillable = [
        'reciboID',
        'linha_number',
        'documentoID',
        'desconto_documento',
        'valor_debito',
        'valor_credito'
    ];

    /**
     * Relacionamento com ReciboEmitido
     */
    public function recibo()
    {
        return $this->belongsTo(Recibo::class, 'reciboID');
    }

    /**
     * Relacionamento com Documento (Invoice)
     */
    public function documento()
    {
        return $this->belongsTo(SalesInvoice::class, 'documentoID');
    }
}
