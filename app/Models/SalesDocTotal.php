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
}
