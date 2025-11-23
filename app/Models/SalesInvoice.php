<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Facades\Auth;

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
        'detalhes_factura',
        'empresa_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'invoice_date',
        'system_entry_date', // Assuming 'system_entry_date' is a date attribute
    ];

    // function boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Set the system_entry_date to current date and time when creating a new record
            $model->system_entry_date = Carbon::now()->toDateTimeString();
            // Set the empresa_id to the current user's empresa_id
            $model->empresa_id = Auth::user()->empresas->first()->id ?? null;
            // Set the source_id to the current user's id
            if (Auth::check()) {
                $model->source_id = Auth::user()->id;
            }
            // Procedure to generate a unique invoice_no
        });

        static::updating(function ($model) {
            // Optionally, you can update the system_entry_date on updates as well
            // $model->system_entry_date = Carbon::now()->toDateTimeString();
        });

        static::deleting(function ($model) {
            // Actions to perform before deleting a record
            // For example, you might want to log this action or prevent deletion based on certain conditions
        });
    }

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
        return $this->hasMany(SalesLine::class, 'documentoID', 'id');
    }

    /**
     * Relacionamento com SalesDocTotal
     */
    public function salesdoctotal()
    {
        return $this->hasOne(SalesDocTotal::class, 'documentoID');
    }

    public function salesstatus(){
        return $this->hasOne(SalesStatus::class, 'documentoID');
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

    // Valor total
    public function getGrossTotalAttribute()
    {
        return $this->salesdoctotal?->gross_total ?? 0;
    }

    // Valor pago
    public function getPaidAmountAttribute()
    {
        return $this->salesdoctotal?->montante_pagamento ?? 0;
    }

    // Valor em dívida
    public function getDueAmountAttribute()
    {
        $dueAmount = ($this->salesdoctotal?->gross_total ?? 0) - ($this->salesdoctotal?->montante_pagamento ?? 0);
        return $dueAmount < 0 ? 0 : $dueAmount;
    }

    // Se já passou o prazo de pagamento
    public function getIsOverdueAttribute()
    {
        if (!$this->invoice_date_end) {
            return false; // sem prazo definido não é vencida
        }
        //return $this->due_amount > 0 && now()->gt(Carbon::parse($this->invoice_date_end));
        //return $this->getDueAmountAttribute > 0 && Carbon::now()->gt(Carbon::parse($this->invoice_date_end));
        return now()->greaterThan($this->invoice_date_end) && $this->due_amount > 0;
    }

    // Verificar se foi anulada
    public function getIsCancelledAttribute()
    {
        return $this->salesstatus?->invoice_status === 'A';
    }

    public function getReferenciaNoAttribute() 
    { 
        if ($this->invoiceType->Code === 'NC') 
        { 
            // Se for nota de crédito, retornar a referência da fatura associada 
            // $relatedInvoice = SalesInvoice::where('id', $this->source_id)->first(); 
            // return $relatedInvoice ? $relatedInvoice->invoice_no : 'N/A'; 
            return 'N/A';
        } 
    }

    // Status completo da fatura
    public function getPaymentStatusAttribute()
    {
        if ($this->is_cancelled) {
            return ['label' => 'Anulada', 'class' => 'bg-secondary', 'icon' => 'fa-ban'];
        }

        if ($this->due_amount <= 0) {
            return ['label' => 'Pago', 'class' => 'bg-success', 'icon' => 'fa-check-circle'];
        }

        if ($this->is_overdue) {
            return ['label' => 'Expirada', 'class' => 'bg-dark', 'icon' => 'fa-exclamation-triangle'];
        }

        if ($this->due_amount < $this->gross_total) {
            return ['label' => 'Pago Parcialmente', 'class' => 'bg-warning text-dark', 'icon' => 'fa-exclamation-circle'];
        }

        return ['label' => 'Em Dívida', 'class' => 'bg-danger', 'icon' => 'fa-times-circle'];
    }

    /**
     * Determina se esta fatura é de crédito (NC) ou débito (FT, FS, ND, etc.)
     */
    public function isDebit()
    {
        return in_array($this->invoiceType->Code, ['FT','FS','FR','ND']);
    }

    public function isCredit()
    {
        return in_array($this->invoiceType->Code, ['NC']);
    }

    /**
     * Valor total desta fatura (fallback 0 se não existir)
     */
    public function total()
    {
        return optional($this->salesdoctotal)->gross_total ?? 0;
    }

    /**
     * Soma global: TotalDebit em uma coleção de invoices
     */
    public static function sumDebit($collection)
    {
        return $collection->filter->isDebit()
            ->sum(fn($inv) => $inv->total());
    }

    /**
     * Soma global: TotalCredit em uma coleção de invoices
     */
    public static function sumCredit($collection)
    {
        return $collection->filter->isCredit()
            ->sum(fn($inv) => $inv->total());
    }

    /**
     * Formatar qualquer data em 'Y-m-d\TH:i:s' ou retornar data padrão
     */
    public static function formatDateTime($dateTime, $default = '2024-01-01T00:00:00')
    {
        if ($dateTime instanceof Carbon) {
            return $dateTime->format('Y-m-d\TH:i:s');
            //return $dateTime->toDateTimeString();
        } elseif (is_string($dateTime)) {
            try {
                return Carbon::parse($dateTime)->format('Y-m-d\TH:i:s');
            } catch (\Exception $e) {
                return $default;
            }
        }
        return $default;
        //return Carbon::now()->format('Y-m-d\TH:i:s');
    }

}
