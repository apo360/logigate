<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaBanco extends Model
{
    use HasFactory;

    protected $table = 'empresa_banco';

    protected $fillable = [
        'empresa_id',
        'code_banco',
        'iban',
        'conta'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
