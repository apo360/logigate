<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DocumentosAduaneiros extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'Fk_Importacao',
        'TipoDocumento',
        'NrDocumento',
        'DataEmissao',
        'Caminho'
    ];

    public function importacao()
    {
        return $this->belongsTo(Importacao::class, 'Fk_Importacao');
    }
}
