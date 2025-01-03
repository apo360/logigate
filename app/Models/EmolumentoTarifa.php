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
        return $this->belongsTo(Processo::class);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Evento executado antes de criar o registro
        static::creating(function ($emolumentoTarifa) {
            // Adicionar lógica, se necessário
            // Exemplo: Garantir valores padrões
            if (is_null($emolumentoTarifa->direitos)) {
                $emolumentoTarifa->direitos = 0.00;
            }

            self::calcularValores($emolumentoTarifa);
        });

        // Evento executado antes de atualizar o registro
        static::updating(function ($emolumentoTarifa) {
            // Exemplo: Log para auditoria
            self::calcularValores($emolumentoTarifa); 
            logger()->info('Atualizando EmolumentoTarifa ID: ' . $emolumentoTarifa->id);
        });

        // Evento executado antes de excluir (soft delete) o registro
        static::deleting(function ($emolumentoTarifa) {
            // Exemplo: Impedir exclusão de registros específicos
            if ($emolumentoTarifa->honorario > 10000) {
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
}
