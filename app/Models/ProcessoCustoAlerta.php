<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ProcessoCustoAlerta extends Model
{
    use BelongsToTenant;

    protected $table = 'processo_custo_alertas';

    protected $guarded = ['id'];

    protected $casts = ['detalhes' => 'array', 'detectado' => 'boolean', 'prazo' => 'date'];

    public function processo()
    {
        return $this->belongsTo(Processo::class);
    }
}
