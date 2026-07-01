<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class CustomerAvenca extends Model
{
    use HasFactory;

    // Definir quais campos podem ser preenchidos em massa
    protected $fillable = [
        'empresa_id',
        'customer_id',
        'contrato_id',
        'titulo',
        'descricao',
        'valor',
        'periodicidade',
        'data_inicio',
        'data_fim',
        'dia_cobranca',
        'proxima_cobranca_em',
        'ultima_cobranca_em',
        'ultimo_movimento_id',
        'status',
        'ativo',
        'observacoes',
        'metadata',
        'created_by',
        'updated_by',
    ];

    // Cast automático para tipos
    protected $casts = [
        'data_inicio' => 'date',
        'data_fim'    => 'date',
        'proxima_cobranca_em' => 'date',
        'ultima_cobranca_em' => 'date',
        'ativo'       => 'boolean',
        'valor'       => 'decimal:2',
        'metadata' => 'array',
    ];

    // Relacionamento com o modelo de clientes
    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function criadoPor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function atualizadoPor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function movimentosContaCorrente()
    {
        return $this->hasMany(ContaCorrente::class, 'customer_avenca_id');
    }

    public function ultimoMovimento()
    {
        return $this->belongsTo(ContaCorrente::class, 'ultimo_movimento_id');
    }

    /** ------------------------
     *  ACESSORES / MUTATORS
     * ------------------------
     */

    // Verifica se a avença está dentro do período válido
    public function getEstaAtivaAttribute()
    {
        $hoje = Carbon::today();
        return $this->estado === 'ativa'
            && $this->data_inicio <= $hoje 
            && ($this->data_fim === null || $this->data_fim >= $hoje);
    }

    public function getEstadoAttribute(): string
    {
        if (!empty($this->attributes['status'])) {
            return (string) $this->attributes['status'];
        }

        if (($this->ativo ?? false) && $this->data_fim && $this->data_fim->isPast()) {
            return 'expirada';
        }

        return ($this->ativo ?? false) ? 'ativa' : 'rascunho';
    }

    public function getTituloExibicaoAttribute(): string
    {
        return $this->titulo ?: 'Avença de Cliente';
    }

    /** ------------------------
     *  SCOPES
     * ------------------------
     */

    // Apenas avenças ativas
    public function scopeAtivas($query)
    {
        $hoje = Carbon::today();
        return $query->where(function (Builder $query): void {
                        if (Schema::hasColumn('customer_avencas', 'status')) {
                            $query->where('status', 'ativa');
                        } else {
                            $query->where('ativo', true);
                        }
                    })
                     ->whereDate('data_inicio', '<=', $hoje)
                     ->where(function ($q) use ($hoje) {
                         $q->whereNull('data_fim')
                           ->orWhereDate('data_fim', '>=', $hoje);
                     });
    }

    public function scopeForEmpresa(Builder $query, int $empresaId): Builder
    {
        if (Schema::hasColumn($this->getTable(), 'empresa_id')) {
            return $query->where('empresa_id', $empresaId);
        }

        return $query->whereHas('customer', fn (Builder $customerQuery) => $customerQuery->forEmpresa($empresaId));
    }

    // Apenas avenças expiradas
    public function scopeExpiradas($query)
    {
        $hoje = Carbon::today();
        return $query->whereNotNull('data_fim')
                     ->whereDate('data_fim', '<', $hoje);
    }
    
}
