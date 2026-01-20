<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscricao extends Model
{
    use SoftDeletes;

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
        'status',
        'referencia_pagamento',
        'renovacao_automatica',
        'dados_personalizados',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'valor_pago' => 'float',
        'data_subscricao' => 'datetime',
        'data_inicio' => 'datetime',
        'data_expiracao' => 'datetime',
        'renovacao_automatica' => 'boolean',
        'dados_personalizados' => 'array'
    ];

    // Status permitidos
    const STATUS_PENDENTE = 'pendente';
    const STATUS_ATIVA = 'ativa';
    const STATUS_EXPIRADA = 'expirada';
    const STATUS_CANCELADA = 'cancelada';
    const STATUS_SUSPENSA = 'suspensa';

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

    public function activatedModules()
    {
        return $this->hasMany(ActivatedModule::class, 'subscricao_id');
    }

    // Métodos de verificação
    public function isAtiva()
    {
        return $this->status === self::STATUS_ATIVA && !$this->isExpirada();
    }

    public function isExpirada()
    {
        if (!$this->data_expiracao) return false;
        return Carbon::now()->gt($this->data_expiracao);
    }

    public function diasRestantes()
    {
        if (!$this->data_expiracao) return null;
        return Carbon::now()->diffInDays($this->data_expiracao, false);
    }

    // Calcular data de expiração baseado na modalidade
    public function calcularDataExpiracao($dataInicio = null)
    {
        $dataInicio = $dataInicio ?? now();
        
        return match($this->modalidade_pagamento) {
            'mensal' => $dataInicio->copy()->addMonth(),
            'trimestral' => $dataInicio->copy()->addMonths(3),
            'semestral' => $dataInicio->copy()->addMonths(6),
            'anual' => $dataInicio->copy()->addYear(),
            default => $dataInicio->copy()->addMonth()
        };
    }

    // Ativar módulos da subscrição
    public function ativarModulos()
    {
        if (!$this->plano) return;

        $modulosParaAtivar = [];

        // Módulos do plano
        foreach ($this->plano->modulos()->wherePivot('incluido', true)->get() as $modulo) {
            $modulosParaAtivar[] = [
                'module_id' => $modulo->id,
                'limite' => $modulo->pivot->limite
            ];
        }

        // Módulos adicionais (do dados_personalizados)
        if (isset($this->dados_personalizados['modulos_extra'])) {
            foreach ($this->dados_personalizados['modulos_extra'] as $moduloId) {
                $modulosParaAtivar[] = [
                    'module_id' => $moduloId,
                    'limite' => null
                ];
            }
        }

        // Criar activated_modules
        foreach ($modulosParaAtivar as $modulo) {
            ActivatedModule::updateOrCreate(
                [
                    'empresa_id' => $this->empresa_id,
                    'module_id' => $modulo['module_id'],
                    'subscricao_id' => $this->id
                ],
                [
                    'activation_date' => now(),
                    'data_expiracao' => $this->data_expiracao,
                    'active' => true
                ]
            );
        }
    }
}