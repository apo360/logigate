<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ProcessoCustoSimulacao extends Model
{
    use BelongsToTenant;

    protected $table = 'processo_custo_simulacoes';

    protected $guarded = ['id'];

    protected $casts = ['parametros' => 'array', 'entradas' => 'array', 'resultado' => 'array', 'completa' => 'boolean'];
}
