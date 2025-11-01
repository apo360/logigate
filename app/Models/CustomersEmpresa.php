<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomersEmpresa extends Model
{
    //
    protected $table = 'customers_empresas';

    protected $fillable = [
        'customer_id',
        'empresa_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
