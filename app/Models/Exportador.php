<?php

namespace App\Models;

use Carbon\Carbon;
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


    protected static function boot()
    {
        parent::boot();

        // Evento executado antes de criar um novo registro
        static::creating(function ($exportador) {
            if (Auth::check()) {
                $exportador->user_id = Auth::user()->id;
                $exportador->empresa_id = $exportador->empresa_id ?? Auth::user()->empresas->first()->id;
                
                $exportador->ExportadorID = $exportador->generateExportadorID();
            }
        });
        

        // Evento(s) que executam antes de actualizar
        static::updating(function ($customer){

        });

        static::deleting( function ($customer){

        });
    }

    /**
     * Gera o ID do exportador baseado nas condições definidas.
     *
     * @return string
     */
    public function generateExportadorID()
    {
        $empresaId = Auth::user()->empresas->first()->id;
        $taxIdPart = $this->ExportadorTaxID ? $this->ExportadorTaxID : random_int(5, 1000);
        return 'exp' . $empresaId . $taxIdPart . Carbon::now()->format('y');
    }
}
