<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TarifaDAR extends Model
{
    use HasFactory;

    protected $table = "tarifa_dar";

    protected $fillable = [
        'Fk_processo',
        'N_Dar',
        'DataEntrada',
        'direitos',
        'emolumentos',
        'iva_aduaneiro',
        'iec',
        'impostoEstatistico',
        'juros_mora',
        'multas',
        'subtotal'
    ];

    public function processo()
    {
        return $this->belongsTo(Processo::class, 'Fk_processo');
    }
}
