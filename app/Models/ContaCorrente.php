<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\TipoMovimentoEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class ContaCorrente extends Model
{
    use HasFactory;

    protected $table = 'conta_correntes';
    protected $primaryKey = 'id';

    protected $fillable = [
        'empresa_id',
        'customer_id',
        'cliente_id', 
        'customer_avenca_id',
        'processo_id',
        'licenciamento_id',
        'origem_tipo',
        'origem_id',
        'referencia',
        'documento_id',
        'valor', 
        'tipo' /**Tipo do Movimento (Factura, Pagamento, Crédito, Debito, Ajuste ... etc) */, 
        'descricao', 
        'data' /**Data do Movimento */, 
        'data_movimento',
        'saldo_apos_movimento',
        'saldo_contabilistico' /**Saldo Contabilistico */,
        'observacoes',
        'created_by',
        'estornado_movimento_id',
        'metadata',
    ];

    protected $casts = [
        'data' => 'date',
        'data_movimento' => 'date',
        'valor' => 'decimal:2',
        'saldo' => 'decimal:2',
        'saldo_apos_movimento' => 'decimal:2',
        'metadata' => 'array',
    ];

     /* ===============================
       SCOPES
    =============================== */

    public function scopeFacturas(Builder $query): Builder
    {
        return $query->where('tipo', TipoMovimentoEnum::FACTURA->value);
    }

    public function scopePagamentos(Builder $query): Builder
    {
        return $query->where('tipo', TipoMovimentoEnum::PAGAMENTO->value);
    }

    public function scopeAjustes(Builder $query): Builder
    {
        return $query->where('tipo', TipoMovimentoEnum::AJUSTE->value);
    }

    /* ===============================
       RELAÇÕES
    =============================== */

    public function documento()
    {
        return $this->belongsTo(Documento::class, 'documento_id');
    }

    public function criadoPor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cliente()
    {
        return $this->belongsTo(Customer::class, 'cliente_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'cliente_id');
    }

    public function customerAvenca()
    {
        return $this->belongsTo(CustomerAvenca::class, 'customer_avenca_id');
    }

    public function origem()
    {
        return $this->morphTo(__FUNCTION__, 'origem_tipo', 'origem_id');
    }

    public function movimentoEstornado()
    {
        return $this->belongsTo(self::class, 'estornado_movimento_id');
    }

    public function getOrigemDescricaoAttribute(): string
    {
        if ($this->customerAvenca) {
            return 'Avença: ' . $this->customerAvenca->titulo_exibicao;
        }

        if ($this->origem_tipo === CustomerAvenca::class) {
            return 'Avença';
        }

        if ($this->origem_tipo) {
            return class_basename($this->origem_tipo);
        }

        return 'Manual';
    }

    public function scopeForCliente(Builder $query, int $customerId): Builder
    {
        return $query->where('cliente_id', $customerId);
    }

    public function scopeForEmpresa(Builder $query, int $empresaId): Builder
    {
        if (Schema::hasColumn($this->getTable(), 'empresa_id')) {
            $query->where('empresa_id', $empresaId);
        }

        return $query;
    }
    
    public function calcularSaldoAtual($customerId)
    {
        return app(\App\Domains\Customers\Services\CustomerAccountStatementService::class)
            ->saldo((int) $customerId);
    }
}
