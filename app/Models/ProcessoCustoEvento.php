<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ProcessoCustoEvento extends Model
{
    use BelongsToTenant;

    protected $table = 'processo_custo_eventos';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = ['detalhes' => 'array', 'created_at' => 'datetime'];
}
