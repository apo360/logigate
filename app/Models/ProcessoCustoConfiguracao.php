<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ProcessoCustoConfiguracao extends Model
{
    use BelongsToTenant;

    protected $table = 'processo_custo_configuracoes';

    protected $guarded = ['id'];
}
