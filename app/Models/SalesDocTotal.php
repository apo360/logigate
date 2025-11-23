<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesDocTotal extends Model
{
    use HasFactory;

    protected $table = 'sales_document_totals';
    public $timestamps = ['created_at', 'updated_at'];
    
    protected $fillable = [
        'descounts_total',
        'tax_payable',
        'net_total',
        'gross_total',
        'payment_mechanism_id',
        'montante_pagamento',
        'data_pagamento',
        'documentoID',
        'troco',
        'moeda',

    ];

    /**
     * Relacionamento com SalesInvoice
     */
    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'documentoID', 'id');
    }

    /**
     * Relacionamento com PaymentMechanism
     */
    public function paymentMechanism()
    {
        return $this->belongsTo(MetodoPagamento::class, 'payment_mechanism_id', 'id');
    }

    public function gross()
    {
        return $this->gross_total ?? 0;
    }

    public function net()
    {
        return $this->net_total ?? 0;
    }

    public function tax()
    {
        return $this->tax_payable ?? 0;
    }
}
