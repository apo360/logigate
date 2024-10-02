<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class EmpresaBanco extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

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
