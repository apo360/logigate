<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Exportador extends Model
{
    use HasFactory;

    protected $fillable = [
        'ExportadorID',
        'ExportadorTaxID',
        'AccountID',
        'Exportador',
        'Telefone',
        'Email',
        'Pais',
        'Website',
        'user_id',
        'empresa_id',
    ];


    public static function generateNewCode()
    {
        return DB::select('CALL ExportadorNewCod()')[0]->codigoExportador;
    }
}
