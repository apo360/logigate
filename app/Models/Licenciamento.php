<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Licenciamento extends Model
{
    use HasFactory;

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
        return $this->belongsTo(Empresa::class, 'estancia_id');
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

    // Métodos auxiliares

    // Evento para gerar o código automaticamente
    protected static function boot()
    {
        parent::boot();

        // Gera o próximo código sequencial para a empresa
        static::creating(function ($licenciamento) {
            $licenciamento->codigo_licenciamento = self::generateCodigoLicenciamento($licenciamento->empresa_id);
        });

        // Impedir a alteração de moeda se houver uma fatura emitida ou paga
        static::updating(function ($licenciamento) {
            if ($licenciamento->procLicenFaturas()->whereIn('status_fatura', ['emitida', 'paga'])->exists()) {
                if ($licenciamento->isDirty('moeda')) {
                    throw new \Exception('Não é permitido alterar a moeda pois uma fatura já foi emitida ou paga.');
                }
            }
        });
    }

    public function getEstadoLicenciamentoAttribute() {
        if ($this->txt_gerado == 0) {
            return 'Por licenciar';
        } elseif ($this->txt_gerado == 1 && $this->procLicenFaturas->where('status_fatura', 'paga')->isNotEmpty()) {
            return 'Licenciado';
        } else {
            return 'Em licenciamento';
        }
    }
    
    // Função para gerar o código único e sequencial
    public static function generateCodigoLicenciamento($empresaId)
    {
        // Obtenha o último licenciamento dessa empresa
        $ultimoLicenciamento = Licenciamento::where('empresa_id', $empresaId)->orderBy('id', 'desc')->first();

        // Se houver um licenciamento anterior, incremente o número
        if ($ultimoLicenciamento) {
            $ultimoCodigo = (int) substr($ultimoLicenciamento->codigo_licenciamento, -4); // Exemplo: pega os últimos 4 dígitos
            $novoCodigo = $ultimoCodigo + 1;
        } else {
            // Caso seja o primeiro licenciamento da empresa
            $novoCodigo = 1;
        }

        // Formata o código (por exemplo, HYLC-001-0001, onde EMP001 é o código da empresa)
        $codigoEmpresa = 'HYLC-' . str_pad($empresaId, 3, '0', STR_PAD_LEFT);
        $codigoLicenciamento = $codigoEmpresa . '-' . str_pad($novoCodigo, 5, '0', STR_PAD_LEFT) .'/'. Carbon::now()->format('y');

        return $codigoLicenciamento;
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
