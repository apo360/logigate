<?php

namespace App\Models;

use App\Traits\SharedFieldsTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    use HasFactory, SoftDeletes;
    
    use SharedFieldsTrait;

    protected $table = 'customers';

    protected $primaryKey = 'id';
    
    protected $fillable = [
        'CustomerID',
        'AccountID',
        'CustomerTaxID',
        'CompanyName',
        'Telephone',
        'Email',
        'Website',
        'SelfBillingIndicator',
        'CustomerType',
        'is_active',
        'foto',
        'user_id',
        'nacionality',
        'doc_type',
        'doc_num',
        'validade_date_doc',
        'metodo_pagamento',
        'tipo_cliente',
        'tipo_mercadoria',
        'frequencia',
        'observacoes',
        'num_licenca',
        'validade_licenca',
        'moeda_operacao'
    ];

    protected $dates = [
        'validade_licenca',
        'validade_date_doc',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected static function boot()
    {
        parent::boot();

        // Evento executado antes de criar um novo registro
        static::creating(function ($customer) {

            if (Auth::check()) {
                $customer->user_id = Auth::user()->id;
            }

            $customer->CustomerID = 'cli'.Auth::user()->empresas->first()->id.$customer->CustomerTaxID.'/'. Carbon::now()->format('y');
            $customer->is_active = 1; 
            $customer->AccountID = 0;
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
         
        $codigo = 'cli'.str_pad($novoCodigo, 3,'0', STR_PAD_LEFT).'/'. Carbon::now()->format('y');
        
        return $codigo;
    }
    
    public function endereco(){
        return $this->hasOne(Endereco::class, 'customer_id');
    }
    
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

    // Licenciamentos do cliente
    public function licenciamento()
    {
        return $this->hasMany(Licenciamento::class, 'cliente_id', 'customer_id');
    }

    /**
     * Retorna apenas a primeira empresa associada ao cliente.
     */
    public function empresas()
    {
        return $this->belongsToMany(Empresa::class, 'customers_empresas')
                    ->withPivot(['codigo_cliente', 'status', 'customer_id', 'empresa_id', 'created_at', 'updated_at'])
                    ->withTimestamps();
    }

    public function contaCorrente(){
        return $this->belongsTo(ContaCorrente::class, 'cliente_id');
    }

    public function getSaldoAttribute()
    {
        $creditos = $this->contaCorrente()->where('tipo', 'credito')->sum('valor');
        $debitos  = $this->contaCorrente()->where('tipo', 'debito')->sum('valor');

        return $creditos - $debitos;
    }

    public function avencas(){
        return $this->hasMany(CustomerAvenca::class);
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scope para clientes ativos
    public function scopeAtivos($query)
    {
        return $query->where('is_active', 1);
    }

    // No Model Customer
    public function isAssociatedWithEmpresa($empresaId)
    {
        return $this->empresas()->where('empresa_id', $empresaId)->exists();
    }

    public function getAssociationsAttribute()
    {
        return $this->empresas()->withPivot('created_at', 'created_by')->get();
    }
}


