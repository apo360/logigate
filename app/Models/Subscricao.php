<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Services\ModuloAtivacaoService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscricao extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'subscricoes';

    // Mantendo campos existentes
    protected $fillable = [
        'empresa_id',
        'plano_id',
        'module_id', // Para compatibilidade com sistema antigo
        'tipo_plano',
        'modalidade_pagamento',
        'valor_pago',
        'data_subscricao',
        'data_inicio',
        'data_expiracao',
        'activated_at',
        'status',
        'referencia_pagamento',
        'renovacao_automatica',
        'dados_personalizados',
        'created_by',
        'updated_by', 'commercial_snapshot', 'renewal_notified_at',
    ];

    protected $casts = [
        'valor_pago' => 'float',
        'data_subscricao' => 'datetime',
        'data_inicio' => 'datetime',
        'data_expiracao' => 'datetime',
        'activated_at' => 'datetime',
        'renovacao_automatica' => 'boolean',
        'dados_personalizados' => 'array',
        'commercial_snapshot' => 'array',
        'renewal_notified_at' => 'datetime',
    ];

    // Status permitidos
    const STATUS_PENDENTE = 'pendente';

    const STATUS_ATIVA = 'ativa';

    const STATUS_EXPIRADA = 'expirada';

    const STATUS_CANCELADA = 'cancelada';

    const STATUS_SUSPENSA = 'suspensa';

    protected static function booted(): void
    {
        static::saving(function (self $subscription): void {
            // Keep persisted status values canonical even when older flows still
            // submit uppercase or mixed-case values.
            $subscription->status = self::normalizeStatus($subscription->status);
        });
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function plano()
    {
        return $this->belongsTo(Plano::class, 'plano_id');
    }

    // Para compatibilidade com sistema antigo
    public function modulo()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function pagamentos()
    {
        return $this->hasMany(Pagamento::class, 'subscricao_id');
    }

    public function pagamentoOnline()
    {
        return $this->hasMany(PagamentoOnline::class, 'subscription_id');
    }

    public function activatedModules()
    {
        return $this->hasMany(ActivatedModule::class, 'subscricao_id');
    }

    // Métodos de verificação
    public function isAtiva()
    {
        return $this->hasStatus(self::STATUS_ATIVA) && ! $this->isExpirada()
            && (! $this->data_inicio || $this->data_inicio->lte(now()));
    }

    public function isExpirada()
    {
        if (! $this->data_expiracao) {
            return false;
        }

        return Carbon::now()->gte($this->data_expiracao);
    }

    public function diasRestantes()
    {
        if (! $this->data_expiracao) {
            return null;
        }

        return Carbon::now()->diffInDays($this->data_expiracao, false);
    }

    public function hasStatus(string $status): bool
    {
        return self::normalizeStatus($this->status) === $status;
    }

    // Calcular data de expiração baseado na modalidade
    public function calcularDataExpiracao($dataInicio = null)
    {
        $dataInicio = $dataInicio ?? now();

        return match (strtolower((string) $this->modalidade_pagamento)) {
            'monthly',
            'mensal' => $dataInicio->copy()->addMonthNoOverflow(),
            'quarterly',
            'trimestral' => $dataInicio->copy()->addMonthsNoOverflow(3),
            'semestral' => $dataInicio->copy()->addMonthsNoOverflow(6),
            'annual',
            'yearly',
            'anual' => $dataInicio->copy()->addYearNoOverflow(),
            default => $dataInicio->copy()->addMonthNoOverflow()
        };
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', self::activeStatuses());
    }

    public function scopeEffective($query)
    {
        return $query->active()
            ->where(fn ($q) => $q->whereNull('data_inicio')->orWhere('data_inicio', '<=', now()))
            ->where(fn ($q) => $q->whereNull('data_expiracao')->orWhere('data_expiracao', '>', now()));
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', self::pendingStatuses());
    }

    public static function activeStatuses(): array
    {
        return [self::STATUS_ATIVA, 'ATIVA'];
    }

    public static function pendingStatuses(): array
    {
        return [self::STATUS_PENDENTE, 'PENDENTE'];
    }

    public static function normalizeStatus(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'ativa' => self::STATUS_ATIVA,
            'expirada' => self::STATUS_EXPIRADA,
            'cancelada' => self::STATUS_CANCELADA,
            'suspensa' => self::STATUS_SUSPENSA,
            'pendente' => self::STATUS_PENDENTE,
            default => self::STATUS_PENDENTE,
        };
    }

    // One implementation for current and legacy callers.
    public function ativarModulos()
    {
        app(ModuloAtivacaoService::class)->ativarModulos($this);
    }
}
