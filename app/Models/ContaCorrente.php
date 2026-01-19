<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\TipoMovimentoEnum;
use Illuminate\Database\Eloquent\Builder;

class ContaCorrente extends Model
{
    use HasFactory;

    protected $table = 'conta_correntes';
    protected $primaryKey = 'id';

    protected $fillable = [
        'cliente_id', 
        'referencia',
        'documento_id',
        'valor', 
        'tipo' /**Tipo do Movimento (Factura, Pagamento, Crédito, Debito, Ajuste ... etc) */, 
        'descricao', 
        'data' /**Data do Movimento */, 
        'saldo_contabilistico' /**Saldo Contabilistico */,
        'observacoes',
        'created_by'
    ];

    protected $casts = [
        'data' => 'date',
        'valor' => 'decimal:2',
        'saldo' => 'decimal:2'
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
        return $this->belongsTo(Customer::class);
    }
    
    // Calcular saldo atual
    public function calcularSaldoAtual($customerId)
    {
        return self::where('customer_id', $customerId)
                  ->orderBy('data_movimento', 'desc')
                  ->orderBy('created_at', 'desc')
                  ->value('saldo') ?? 0;
    }
}
