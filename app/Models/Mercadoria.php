<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mercadoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'Fk_Importacao', 
        'Descricao',
        'NCM_HS',
        'NCM_HS_Numero',
        'Quantidade',
        'Qualificacao',
        'Unidade',
        'Peso',
        'preco_unitario',
        'preco_total',
        'codigo_aduaneiro',
    ];

    public function importacao()
    {
        return $this->belongsTo(Importacao::class, 'Fk_Importacao');
    }

    /**
     * Relacionamento com o Processo/Mercadoria.
     */
    public function procLicenMercadorias()
    {
        return $this->hasMany(ProcessoLicenciamentoMercadoria::class, 'processo_id');
    }

    // Função auxiliar para calcular o frete da mercadoria
    public static function calcularFreteMercadoria($precoTotal, $FOB, $Frete) {
        return ($precoTotal / $FOB) * $Frete;
    }

    // Função auxiliar para calcular o seguro da mercadoria
    public static function calcularSeguroMercadoria($precoTotal, $FOB, $Seguro) {
        return ($precoTotal / $FOB) * $Seguro;
    }

    // Função auxiliar para calcular o valor aduaneiro
    public static function calcularValorAduaneiro($precoTotal, $freteMercadoria, $seguroMercadoria) {
        return $precoTotal + $freteMercadoria + $seguroMercadoria;
    }
}
