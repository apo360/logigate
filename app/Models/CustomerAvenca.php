<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAvenca extends Model
{
    use HasFactory;

    // Definir quais campos podem ser preenchidos em massa
    protected $fillable = [
        'customer_id',
        'valor',
        'periodicidade',
        'data_inicio',
        'data_fim',
        'ativo',
    ];

    // Relacionamento com o modelo de clientes
    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
