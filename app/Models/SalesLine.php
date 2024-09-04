<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesLine extends Model
{
    use HasFactory;

    protected $table = 'sales_line';
    
    protected $fillable = [
        'line_number',
        'documentoID',
        'productID',
        'quantity',
        'unit_of_measure',
        'unit_price',
        'tax_point_date',
        'credit_amount',
        'debit_amount',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'invoice_date',
        'system_entry_date', // Assuming 'system_entry_date' is a date attribute
    ];

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'documentoID');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'productID');
    }

    // Getter para o campo tax_point_date
    public function getTaxPointDateAttribute($value)
    {
        // Converte o valor do banco de dados para um objeto Carbon
        $carbonDate = Carbon::parse($value);

        // Formata a data no formato desejado
        return $carbonDate->format('Y-m-d\TH:i:s');
    }

    // Setter para o campo tax_point_date
    public function setTaxPointDateAttribute($value)
    {
        // Converte o valor passado para um objeto Carbon
        $carbonDate = Carbon::parse($value);

        // Armazena no formato do banco de dados
        $this->attributes['tax_point_date'] = $carbonDate;
    }
}
