<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
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

    protected static function boot()
    {
        parent::boot();

        // Evento executado antes de criar um novo registro
        static::creating(function ($exportador) {

            if (Auth::check()) {
                $exportador->user_id = Auth::user()->id;
            }

            // Definir automaticamente o empresa_id se ainda não estiver definido
            if (!$exportador->empresa_id) {
                $exportador->empresa_id = Auth::user()->empresas->first()->id /* Defina aqui o ID da empresa que deseja associar */;
            }

            $exportador->ExportadorID = self::generateNewCode();
        });

        // Evento(s) que executam antes de actualizar
        static::updating(function ($customer){

        });

        static::deleting( function ($customer){

        });
    }
}
