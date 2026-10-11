<?php

namespace App\Models;

use App\Domains\Billing\Enums\BillingCycle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plano extends Model
{
    use SoftDeletes;

    protected $table = 'planos';

    protected $fillable = [
        'nome',
        'codigo',
        'descricao',
        'preco_mensal',
        'preco_trimestral',
        'preco_semestral',
        'preco_anual',
        'limite_utilizadores',
        'limite_armazenamento_gb',
        'limite_processos',
        'status',
        'ordem',
        'destaque', 'is_free', 'trial_days', 'is_popular',
    ];

    protected $casts = [
        'preco_mensal' => 'float',
        'preco_trimestral' => 'float',
        'preco_semestral' => 'float',
        'preco_anual' => 'float',
        'limite_utilizadores' => 'integer',
        'limite_armazenamento_gb' => 'integer',
        'limite_processos' => 'integer',
        'destaque' => 'boolean', 'is_free' => 'boolean',
    ];

    // Relacionamento com módulos (mantendo compatibilidade)
    public function modulos()
    {
        return $this->belongsToMany(Module::class, 'plano_modulos', 'plano_id', 'modulo_id')
            ->withTimestamps();
    }

    public function subscricoes()
    {
        return $this->hasMany(Subscricao::class, 'plano_id');
    }

    // Método para obter preço conforme modalidade
    public function getPreco($modalidade)
    {
        return BillingCycle::fromInput(strtolower($modalidade))->priceFrom($this);
    }

    public function itemplano()
    {
        return $this->hasMany(PlanoItem::class, 'plano_id');
    }

    // Verificar se plano é gratuito
    public function isGratuito()
    {
        return (bool) $this->is_free || ($this->preco_mensal !== null && $this->preco_mensal == 0 && $this->preco_trimestral == 0 && $this->preco_semestral == 0 && $this->preco_anual == 0);
    }

    /**
     * Constantes para modalidades de pagamento
     */
    public const MODALIDADES_PAGAMENTO = [
        'monthly' => 'Mensal',
        'trimestral' => 'Trimestral',
        'semestral' => 'Semestral',
        'annual' => 'Anual',
    ];
}
