<?php

namespace App\Models;

use App\Traits\SharedFieldsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    use HasFactory;
    
    use SharedFieldsTrait;
    
    protected $fillable = [
        'CustomerID',
        'AccountID',
        'CustomerTaxID',
        'CompanyName',
        'Contact',
        'BillingAddress_StreetName',
        'BillingAddress_BuildingNumber',
        'BillingAddress_AddressDetail',
        'City',
        'PostalCode',
        'Province',
        'Country',
        'Telephone',
        'Fax',
        'Email',
        'Website',
        'SelfBillingIndicator',
        'user_id',
        'empresa_id'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public static function generateNewCode()
    {
        return DB::select('CALL ClienteNewCod()')[0]->codigoCliente;
    }
/*
    public function endereco(){
        return $this->hasOne(BillingAddress::class, 'CustomerID');
    }
*/
    /**
     * Define the "invoices" relationship. Each customer can have multiple invoices.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    
    public function invoices()
    {
        return $this->hasMany(SalesInvoice::class, 'customer_id');
    }

    /**
     * Define the "processes" relationship. Each customer can have multiple processes.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function processos()
    {
        return $this->hasMany(Processo::class, 'customer_id');
    }

    public function empresa(){
        return $this->hasMany(Empresa::class, 'empresa_id');
    }

    public function contaCorrente(){
        return $this->belongsTo(ContaCorrente::class, 'cliente_id');
    }

    public function avencas(){
        return $this->hasMany(CustomerAvenca::class);
    }


}

