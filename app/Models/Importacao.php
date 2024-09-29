<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Importacao extends Model
{
    use HasFactory;
    
    protected $table = 'importacao'; // Nome da tabela

    protected $fillable = [
        'processo_id',
        'Fk_pais_origem',
        'Fk_pais_destino',
        'PortoOrigem',
        'TipoTransporte',
        'NomeTransporte',
        'DataChegada',
        'MarcaFiscal',
        'BLC_Porte',
        'Moeda',
        'Cambio',
        'ValorAduaneiro',
        'ValorTotal', // CIF
        'FOB', 
        'Freight', //Frete
        'Insurance', // Seguro
    ];

    protected $dates = [
        'DataChegada',
        'created_at',
        'updated_at'
    ];

    public static function getLastInsertedId()
    {
        $ultimoImporte = self::latest()->first();

        if ($ultimoImporte) {
            return $ultimoImporte->Id;
        }

        return null;
    }

    // Relacionamento com a tabela Processos
    public function processo()
    {
        return $this->belongsTo(Processo::class);
    }

    // Relacionamento com a tabela DocumentosAduaneiros (se necessário)
    public function documentosAduaneiros()
    {
        return $this->hasMany(DocumentosAduaneiros::class, 'Fk_Importacao', 'Id');
    }

    public function origem()
    {
        return $this->belongsTo(Pais::class, 'Fk_pais_origem');
    }

    public function destino() 
    {
        return $this->belongsTo(Pais::class, 'Fk_pais_destino');
    }

    public function mercadorias()
    {
        return $this->hasMany(Mercadoria::class, 'Fk_Importacao');
    }
}
