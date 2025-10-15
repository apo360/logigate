<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoRecibo extends Model
{
    //
    protected $table = 'PaymentType';
    protected $fillable = ['Code', 'Descriptions'];

    public function recibos()
    {
        return $this->hasMany(Recibo::class, 'tipo_reciboID', 'Id');
    }
}
