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
        'marca',
        'modelo',
        'chassis',
        'ano_fabricacao',
        'potencia',
        'licenciamento_id',
        'subcategoria_id',
    ];

    public function processos()
    {
        return $this->belongsTo(Processo::class, 'Fk_Importacao');
    }

    public function licenciamento()
    {
        return $this->belongsTo(Licenciamento::class, 'licenciamento_id');
    }

    public function pautaAduaneira()
    {
        return $this->belongsTo(PautaAduaneira::class, 'codigo_aduaneiro', 'codigo');
    }

    public function getDescricaoAduaneiraAttribute()
    {
        if ($this->pautaAduaneira 
            && $this->codigo_aduaneiro === $this->pautaAduaneira->codigo_sem_pontos) {
            return $this->pautaAduaneira->descricao;
        }
        return 'N/D';
    }

    public function subcategoria()
    {
        return $this->belongsTo(Subcategoria::class, 'subcategoria_id');
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

    // Se necessário, podemos definir mutators ou acessors para formatar valores
    public function setValorUnitarioAttribute($value)
    {
        $this->attributes['preco_unitario'] = is_numeric($value)
            ? round((float) $value, 2)
            : 0.00; // Arredondar para 2 casas decimais com fallback seguro
    }

    public function setFreteAttribute($value)
    {
        $this->attributes['frete'] = is_numeric($value)
            ? round((float) $value, 2)
            : 0.00;
    }

    public function setSeguroAttribute($value)
    {
        $this->attributes['seguro'] = is_numeric($value)
            ? round((float) $value, 2)
            : 0.00;
    }

    public function getValorTotalAttribute()
    {
        // Accessors can run before every raw attribute is present on the model,
        // so guard missing values and fall back to the persisted total when set.
        $quantidade = (float) ($this->getAttributeFromArray('Quantidade') ?? 0);
        $precoUnitario = (float) ($this->getAttributeFromArray('preco_unitario') ?? 0);
        $precoTotal = $this->getAttributeFromArray('preco_total');

        if ($precoTotal !== null) {
            return (float) $precoTotal;
        }

        return round($quantidade * $precoUnitario, 2);
    }

    // Função auxiliar para calcular o direito aduaneiro
    public static function calcularDireito($valorAduaneiro, $rg) {
        if (is_numeric($rg)) {
            return $valorAduaneiro * ($rg / 100);
        }
        return $rg;
    }

    // Função auxiliar para calcular os emolumentos
    public static function calcularEmolumentos($valorAduaneiro, $taxaEmolumentos) {
        return $valorAduaneiro * $taxaEmolumentos;
    }

    // Função auxiliar para calcular o IVA aduaneiro
    public static function calcularIVA($valorAduaneiro, $emolumentos, $direito) {
        return ($valorAduaneiro + $emolumentos + $direito) * 0.14;
    }
}
