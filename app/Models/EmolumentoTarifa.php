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

    public function atualizarValores()
    {
        $processo = $this->processo()->first();

        if ($processo && $processo->ValorAduaneiro) {
            $valorAduaneiro = $processo->ValorAduaneiro;

            $this->iva_aduaneiro = $valorAduaneiro * 0.14;
            $this->impostoEstatistico = $valorAduaneiro * 0.10;
            $this->emolumentos = $valorAduaneiro * 0.02;
        }

        if ($this->honorario) {
            $this->honorario_iva = $this->honorario * 0.14;
        }

        $this->guia_fiscal = $this->calcularGuiaFiscal();
    }

    // 
    private function calcularGuiaFiscal(): float
    {
        return array_sum([
            (float) $this->direitos,
            (float) $this->emolumentos,
            (float) $this->porto,
            (float) $this->terminal,
            (float) $this->lmc,
            (float) $this->navegacao,
            (float) $this->inerentes,
            (float) $this->frete,
            (float) $this->carga_descarga,
            (float) $this->deslocacao,
            (float) $this->selos,
            (float) $this->iva_aduaneiro,
            (float) $this->iec,
            (float) $this->impostoEstatistico,
            (float) $this->juros_mora,
            (float) $this->multas,
            (float) $this->caucao,
            (float) $this->honorario,
            (float) $this->honorario_iva,
            (float) $this->orgaos_ofiais,
        ]);
    }
}
