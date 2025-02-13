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

    public function categoria_mercadoria(){

        $categoria = [
            'num' => 1, 'desc' => 'Animais Vivos e Produtos do Reino Animal',
            'num' => 2, 'desc' => 'Produtos do Reino Vegetal',
            'num' => 3, 'desc' => 'Gorduras e Óleos Animais, Vegetais ou de Origem Microbiana...',
            'num' => 4, 'desc' => 'Produtos das Indústrias Alimentares; Bebidas, Líquidos...',
            'num' => 5, 'desc' => 'Produtos Minerais',
            'num' => 6, 'desc' => 'Produtos das Indústrias Químicas ou das Indústrias Conexas',
        ];
    }

    public function mercadoria_seccao(){

        $seccao = [
            'num' => 01, 'desc' => 'Animais Vivos', 'categoria' => '1',
            'num' => 02, 'desc' => 'Carnes e miudezas, comestíveis', 'categoria' => '1',
            'num' => 03, 'desc' => 'Peixes e crustáceos, moluscos e outros invertebrados aquáticos', 'categoria' => '1',
            'num' => 04, 'desc' => 'Leite e laticínios; ovos de aves; mel natural; produtos comestíveis de origem animal', 'categoria' => '1',
            'num' => 05, 'desc' => 'Outros produtos de origem animal, não especificados nem compreendidos noutros Caítulos', 'categoria' => '1',
        ];
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
        $this->attributes['preco_unitario'] = round($value, 2); // Arredondar para 2 casas decimais
    }

    public function setFreteAttribute($value)
    {
        $this->attributes['frete'] = round($value, 2);
    }

    public function setSeguroAttribute($value)
    {
        $this->attributes['seguro'] = round($value, 2);
    }

    public function getValorTotalAttribute()
    {
        return $this->attributes['quantidade'] * $this->attributes['preco_unitario'];
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
