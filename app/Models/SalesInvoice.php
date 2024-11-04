<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SalesInvoice extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'sales_invoice';
    
    protected $fillable = [
        'invoice_no',
        'hash',
        'hash_control',
        'period',
        'invoice_date',
        'invoice_date_end',
        'invoice_type_id',
        'self_billing_indicator',
        'cash_vat_scheme_indicator',
        'third_parties_billing_indicator',
        'source_id',
        'system_entry_date',
        'transaction_id',
        'customer_id',
        'ship_to_id',
        'from_to_id',
        'movement_end_time',
        'movement_start_time',
        'imposto_retido',
        'motivo_retencao',
        'montante_retencao',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'invoice_date',
        'system_entry_date', // Assuming 'system_entry_date' is a date attribute
    ];

    public function getSystemEntryDate()
    {
        return Carbon::parse($this->attributes['system_entry_date'])->format('Y-m-d\TH:i:s');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'source_id');
    }

    public function empresa(){
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function invoiceType()
    {
        return $this->belongsTo(InvoiceType::class);
    }

    public function salesitem()
    {
        return $this->hasMany(SalesLine::class, 'documentoID');
    }

    public function salesdoctotal()
    {
        return $this->hasOne(SalesDocTotal::class, 'documentoID');
    }

    public function salesstatus(){
        return $this->hasMany(SalesStatus::class, 'documentoID');
    }

    public function getStatusAttribute($docID = null)
    {
        if ($docID) {
            $dataPagamento = $this->salesstatus->invoice_available_date->where('',$docID);
        }else{
            $dataPagamento = $this->salesstatus->invoice_available_date ?? '';
        }
        
        if ($dataPagamento) {
            // Fatura paga se houver data de pagamento
            return 'Pago';
        } elseif (Carbon::now()->gt($this->invoice_date)) {
            // Fatura em atraso se a data atual for posterior à data de vencimento
            return 'Vencido';
        } else {
            // Fatura por pagar se ainda não foi paga e não está em atraso
            return 'Por Pagar';
        }
    }

    // Eventos para realizar sempre que o controller ou model for usado no backend
}
