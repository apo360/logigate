<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportadorEmpresa extends Model
{
    protected $table = 'exportador_empresas';

    protected $fillable = [
        'exportador_id',
        'empresa_id',
        'codigo_exportador',
        'additional_info',
        'status',
        'data_associacao',
    ];

    public function exportador()
    {
        return $this->belongsTo(Exportador::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
