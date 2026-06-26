<?php

namespace App\Models;

use App\Domains\Licenciamento\Services\EstadoLicenciamentoService;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Licenciamento extends Model
{
    use HasFactory, BelongsToTenant;

    //
    protected $table = "licenciamentos";

    // Definindo os campos que podem ser atribuídos em massa
    protected $fillable = [
        'codigo_licenciamento',
        'estancia_id',
        'cliente_id',
        'exportador_id',
        'empresa_id',
        'referencia_cliente',
        'factura_proforma',
        'descricao',
        'moeda',
        'tipo_declaracao',
        'tipo_transporte',
        'registo_transporte',
        'nacionalidade_transporte',
        'manifesto',
        'data_entrada',
        'porto_entrada',
        'peso_bruto',
        'adicoes',
        'metodo_avaliacao',
        'codigo_volume',
        'qntd_volume',
        'forma_pagamento',
        'codigo_banco',
        'fob_total',
        'frete',
        'seguro',
        'cif',
        'pais_origem',
        'porto_origem',
        'txt_gerado',
        'Nr_factura',
        'status_fatura',
    ];

    // Definindo casts para transformar automaticamente os tipos de dados
    protected $casts = [
        'data_entrada' => 'date',
        'peso_bruto' => 'decimal:2',
        'fob_total' => 'decimal:2',
        'frete' => 'decimal:2',
        'seguro' => 'decimal:2',
        'cif' => 'decimal:2',
    ];

    // Relacionamentos

    /**
     * Relacionamento com a Empresa (Despachante).
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Relacionamento com o Exportador.
     */
    public function exportador()
    {
        return $this->belongsTo(Exportador::class);
    }

    /**
     * Relacionamento com o Cliente.
     */
    public function cliente()
    {
        return $this->belongsTo(Customer::class);
    }

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'nacionalidade_transporte', 'id');
    }

    /**
     * Relacionamento com a Estância (Se for uma entidade separada da empresa).
     */
    public function estancia()
    {
        return $this->belongsTo(Estancia::class, 'estancia_id');
    }

    public function procLicenFaturas()
    {
        return $this->hasMany(ProcLicenFactura::class, 'licenciamento_id');
    }

    /**
     * Relacionamento com a Mercadorias.
     */
    public function procLicenMercadorias()
    {
        return $this->hasMany(ProcessoLicenciamentoMercadoria::class, 'processo_id');
    }


    public function mercadorias()
    {
        return $this->hasMany(Mercadoria::class, 'licenciamento_id');
    }

    public function mercadoriasAgrupadas()
    {
        return $this->hasMany(MercadoriaAgrupada::class, 'licenciamento_id');
    }

    public function documentosArquivos()
    {
        return $this->hasMany(DocumentoArquivo::class, 'licenciamento_id');
    }

    // Métodos auxiliares

    // Eventos do modelo não devem conter regras de negócio críticas.
    protected static function boot()
    {
        parent::boot();
    }

    public function getEstadoLicenciamentoAttribute() {
        return app(EstadoLicenciamentoService::class)->estado($this);
    }
    
    // Verifica se o licenciamento pode ser editado
    public function podeSerEditado()
    {
        // O licenciamento só pode ser editado se o status da fatura for "anulada" ou não houver .txt gerado
        return ($this->status_fatura == 'anulada') || !$this->txt_gerado;
    }

    // Método para verificar se a fatura está paga
    public function isFaturaPaga()
    {
        return $this->status_fatura === 'paga';
    }

    // Método para verificar se a fatura foi emitida
    public function isFaturaEmitida()
    {
        return $this->status_fatura === 'emitida';
    }
}
