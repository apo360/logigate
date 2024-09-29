<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcLicenFactura extends Model
{
    use HasFactory;

    protected $table = "proc_licen_sales";

    protected $fillable = [
        'empresa_id', 
        'licenciamento_id', 
        'processo_id', 
        'fatura_id', 
        'status_fatura'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function licenciamento()
    {
        return $this->belongsTo(Licenciamento::class);
    }

    public function processo()
    {
        return $this->belongsTo(Processo::class);
    }

    public function fatura()
    {
        return $this->belongsTo(SalesInvoice::class, 'fatura_id');
    }
}
