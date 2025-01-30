<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;

class ProcessoDraft extends Model  implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    // Nome da tabela no banco de dados
    protected $table = 'processos_drafts';

    // Campos que podem ser preenchidos
    protected $fillable = [
        'RefCliente',
        'Descricao',
        'DataAbertura',
        'DataFecho',
        'TipoProcesso',
        'Estado',
        'customer_id',
        'user_id',
        'empresa_id',
        'exportador_id',
        'estancia_id',
        'NrDU',
        'N_Dar',
        'MarcaFiscal',
        'BLC_Porte',
        'Pais_origem',
        'Pais_destino',
        'PortoOrigem',
        'DataChegada',
        'TipoTransporte',
        'registo_transporte',
        'nacionalidade_transporte',
        'forma_pagamento',
        'codigo_banco',
        'Moeda',
        'Cambio',
        'ValorTotal',
        'ValorAduaneiro',
        'fob_total',
        'frete',
        'seguro',
        'cif',
        'peso_bruto',
        'quantidade_barris',
        'data_carregamento',
        'valor_barril_usd',
        'num_deslocacoes',
        'rsm_num',
        'certificado_origem',
        'guia_exportacao',
    ];

    protected static function boot()
    {
        parent::boot();

        // Evento executado antes de criar um novo registro
        static::creating(function ($processo) {

            if (Auth::check()) {
                $processo->user_id = Auth::user()->id;
            }

            // Definir automaticamente o empresa_id se ainda não estiver definido
            if (!$processo->empresa_id) {
                $processo->empresa_id = Auth::user()->empresas->first()->id/* Defina aqui o ID da empresa que deseja associar */;
            }

        });
        
    }

    public function tipoTransporte()
    {
        return $this->belongsTo(TipoTransporte::class, 'TipoTransporte');
    }

    // Relacionamento com a tabela Exportador
    public function exportador()
    {
        return $this->belongsTo(Exportador::class, 'exportador_id');
    }

    /**
     * Obtém o cliente associado a este processo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cliente()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function estancia()
    {
        return $this->belongsTo(Estancia::class, 'estancia_id');
    }

    public function tipoProcesso()
    {
        return $this->belongsTo(RegiaoAduaneira::class, 'TipoProcesso');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
