<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarifas extends Model
{
    use HasFactory;

    protected $fillable = [
        'Fk_DAR',
        'Fk_DU',
        'Fk_Portuaria',
        'TotalDAR',
        'TotalDU',
        'TotalPortuaria'
    ];

    public function tarifaDAR()
    {
        return $this->belongsTo(TarifaDAR::class, 'Fk_DAR');
    }

    public function tarifaDU()
    {
        return $this->belongsTo(TarifaDU::class, 'Fk_DU');
    }

    public function tarifaPortuaria()
    {
        return $this->belongsTo(TarifaPortuaria::class, 'Fk_Portuaria');
    }
}
