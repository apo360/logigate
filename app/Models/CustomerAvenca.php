<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAvenca extends Model
{
    use HasFactory;

    // Definir quais campos podem ser preenchidos em massa
    protected $fillable = [
        'customer_id',
        'valor',
        'periodicidade',
        'data_inicio',
        'data_fim',
        'ativo',
    ];

    // Cast automático para tipos
    protected $casts = [
        'data_inicio' => 'date',
        'data_fim'    => 'date',
        'ativo'       => 'boolean',
        'valor'       => 'decimal:2',
    ];

    // Relacionamento com o modelo de clientes
    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /** ------------------------
     *  ACESSORES / MUTATORS
     * ------------------------
     */

    // Verifica se a avença está dentro do período válido
    public function getEstaAtivaAttribute()
    {
        $hoje = Carbon::today();
        return $this->ativo 
            && $this->data_inicio <= $hoje 
            && ($this->data_fim === null || $this->data_fim >= $hoje);
    }

    /** ------------------------
     *  SCOPES
     * ------------------------
     */

    // Apenas avenças ativas
    public function scopeAtivas($query)
    {
        $hoje = Carbon::today();
        return $query->where('ativo', true)
                     ->whereDate('data_inicio', '<=', $hoje)
                     ->where(function ($q) use ($hoje) {
                         $q->whereNull('data_fim')
                           ->orWhereDate('data_fim', '>=', $hoje);
                     });
    }

    // Apenas avenças expiradas
    public function scopeExpiradas($query)
    {
        $hoje = Carbon::today();
        return $query->whereNotNull('data_fim')
                     ->whereDate('data_fim', '<', $hoje);
    }
    
}
