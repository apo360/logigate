<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TarifaDU extends Model
{
    use HasFactory;

    protected $table = "tarifa_du";

    protected $fillable = [
        'Fk_processo',
        'NrDU',
        'lmc',
        'navegacao',
        'viacao',
        'taxa_aeroportuaria',
        'caucao',
        'honorario',
        'honorario_iva',
        'frete',
        'carga_descarga',
        'orgaos_ofiais',
        'deslocacao',
        'guia_fiscal',
        'inerentes',
        'despesas',
        'selos'
    ];

    public function processo()
    {
        return $this->belongsTo(Processo::class, 'Fk_processo');
    }
}
