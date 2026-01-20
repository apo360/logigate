<?php

namespace App\Models;

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
        'destaque'
    ];

    protected $casts = [
        'preco_mensal' => 'float',
        'preco_trimestral' => 'float',
        'preco_semestral' => 'float',
        'preco_anual' => 'float',
        'limite_utilizadores' => 'integer',
        'limite_armazenamento_gb' => 'integer',
        'limite_processos' => 'integer',
        'destaque' => 'boolean'
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
        return match($modalidade) {
            'mensal' => $this->preco_mensal,
            'trimestral' => $this->preco_trimestral,
            'semestral' => $this->preco_semestral,
            'anual' => $this->preco_anual,
            default => $this->preco_mensal
        };
    }

    // Verificar se plano é gratuito
    public function isGratuito()
    {
        return $this->preco_mensal == 0;
    }
}