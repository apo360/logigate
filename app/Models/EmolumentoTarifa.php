<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmolumentoTarifa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'emolumento_tarifas';

    protected $fillable = [
        'processo_id',
        'direitos',
        'emolumentos',
        'porto',
        'terminal',
        'lmc',
        'navegacao',
        'inerentes',
        'frete',
        'carga_descarga',
        'deslocacao',
        'selos',
        'iva_aduaneiro',
        'iec',
        'impostoEstatistico',
        'juros_mora',
        'multas',
        'caucao',
        'honorario',
        'honorario_iva',
        'orgaos_ofiais',
        'guia_fiscal',
    ];

    /**
     * Relação com o modelo Processo.
     */
    public function processo()
    {
        return $this->belongsTo(Processo::class, 'processo_id');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Evento antes de criar ou atualizar
        static::saving(function ($tarifa) {
            self::calcularValores($tarifa);
            $tarifa->guia_fiscal = $tarifa->calcularGuiaFiscal();
        });

        // Evento antes de excluir
        static::deleting(function ($tarifa) {
            if ($tarifa->honorario > 10000) {
                throw new \Exception('Não é permitido excluir tarifas com honorários acima de 10.000.');
            }
        });
    }

    /**
     * Calcula os valores dependentes do ValorAduaneiro e Honorário.
     *
     * @param EmolumentoTarifa $emolumentoTarifa
     */
    private static function calcularValores($emolumentoTarifa)
    {
        $processo = $emolumentoTarifa->processo()->first(); // Obtém o processo relacionado

        if ($processo && $processo->ValorAduaneiro) {
            $valorAduaneiro = $processo->ValorAduaneiro;

            $emolumentoTarifa->iva_aduaneiro = $valorAduaneiro * 0.14;
            $emolumentoTarifa->impostoEstatistico = $valorAduaneiro * 0.10;
            $emolumentoTarifa->emolumentos = $valorAduaneiro * 0.02;
        }

        if ($emolumentoTarifa->honorario) {
            $emolumentoTarifa->honorario_iva = $emolumentoTarifa->honorario * 0.14;
        }
    }

    // Método para calcular o total de guia_fiscal
    public function calcularGuiaFiscal()
    {
        return collect($this->attributes)
            ->except(['processo_id', 'guia_fiscal', 'created_at', 'updated_at', 'deleted_at'])
            ->map(fn($value) => (float) ($value ?? 0))
            ->sum();
    }

}
