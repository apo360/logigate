<?php

namespace App\Models;

use App\Traits\SharedFieldsTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
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

    protected static function boot()
    {
        parent::boot();

        // Evento executado antes de criar um novo registro
        static::creating(function ($customer) {

            if (Auth::check()) {
                $customer->user_id = Auth::user()->id;
            }

            // Definir automaticamente o empresa_id se ainda não estiver definido
            if (!$customer->empresa_id) {
                $customer->empresa_id = Auth::user()->empresas->first()->id /* Defina aqui o ID da empresa que deseja associar */;
            }

            $customer->CustomerID = self::generateNewCodeCustomer($customer->empresa_id);
        });

        // Evento(s) que executam antes de actualizar
        static::updating(function ($customer){

        });

        static::deleting( function ($customer){

        });
    }

    public static function generateNewCodeCustomer($empresaId)
    {
        $ultimoCliente = Customer::where('empresa_id', $empresaId)->orderBy('id', 'desc')->first();
        
        if ($ultimoCliente) {
            
            $ultimoCodigo = (int) substr($ultimoCliente->CustomerID, -4); // Exemplo: pega os 4 dígitos específicos
            
            $novoCodigo = $ultimoCodigo + 1;
        } else {
            // Caso seja o primeiro licenciamento da empresa
            $novoCodigo = 1;
        }
         
        $codigoCustos = 'cli'.str_pad($novoCodigo, 3,'0', STR_PAD_LEFT).'/'. Carbon::now()->format('y');
        
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

