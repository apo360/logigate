<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxTable extends Model
{
    use HasFactory;

    // Nome da tabela no banco de dados
    protected $table = 'tax_tables';

    // Atributos que podem ser preenchidos
    protected $fillable = [
        'TaxType',
        'TaxCode',
        'TaxCountryRegion',
        'Description',
        'TaxExpirationDate',
        'TaxPercentage',
        'TaxAmount',
    ];

    // Se você não quiser usar timestamps automáticos
    public $timestamps = true;
}
