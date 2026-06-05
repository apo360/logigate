<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MercadoriaPautaAudit extends Model
{
    protected $fillable = [
        'mercadoria_id',
        'processo_id',
        'licenciamento_id',
        'old_pauta_aduaneira_id',
        'new_pauta_aduaneira_id',
        'old_codigo',
        'new_codigo',
        'old_snapshot',
        'new_snapshot',
        'changed_by',
        'reason',
        'source',
    ];

    protected $casts = [
        'old_snapshot' => 'array',
        'new_snapshot' => 'array',
    ];

    public function mercadoria()
    {
        return $this->belongsTo(Mercadoria::class);
    }
}
