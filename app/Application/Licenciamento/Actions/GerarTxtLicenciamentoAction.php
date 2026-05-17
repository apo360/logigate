<?php

namespace App\Application\Licenciamento\Actions;

use App\Models\Licenciamento;
use App\Models\MercadoriaAgrupada;
use App\Models\Porto;
use App\Models\Pais;
use App\Models\PautaAduaneira;
use App\Models\Mercadoria;
use Illuminate\Support\Facades\DB;

class GerarTxtLicenciamentoAction
{
    /**
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function execute(Licenciamento $licenciamento): array
    {
        // Validação original: frete e seguro diferentes de zero
        if (is_null($licenciamento->frete) || is_null($licenciamento->seguro) || $licenciamento->frete == 0 || $licenciamento->seguro == 0) {
            throw new \InvalidArgumentException('Os campos Frete e Seguro precisam estar preenchidos e diferentes de zero antes de gerar o licenciamento.');
        }

        $mercadoriaAgrupada = MercadoriaAgrupada::where('licenciamento_id', $licenciamento->id)->get();
        if ($mercadoriaAgrupada->isEmpty()) {
            throw new \RuntimeException('Nenhuma mercadoria agrupada encontrada para este licenciamento.');
        }

        $porto = Porto::where('sigla', $licenciamento->porto_origem)->first();
        if (!$porto || !$porto->pais) {
            throw new \RuntimeException('Porto de origem não encontrado ou sem país associado.');
        }

        $pais = Pais::findOrFail($porto->pais_id);
        $FOB = $licenciamento->fob_total;
        $frete = $licenciamento->frete;
        $seguro = $licenciamento->seguro;

        // Linha 0
        $linha0 = "0|" . count($mercadoriaAgrupada) . "|{$licenciamento->estancia_id}|{$licenciamento->cliente->CompanyName}|{$licenciamento->empresa->Empresa}|{$licenciamento->empresa->Cedula}|{$licenciamento->empresa->Email}|{$licenciamento->referencia_cliente}|||||||||||||||||||||||||||||";

        // Linha 1
        $linha1 = "1|{$licenciamento->exportador->ExportadorTaxID}|{$licenciamento->exportador->Exportador}|{$licenciamento->cliente->CustomerTaxID}||{$licenciamento->empresa->Cedula}|{$licenciamento->tipo_transporte}|{$licenciamento->registo_transporte}|{$licenciamento->pais->codigo}|{$licenciamento->manifesto}|{$licenciamento->factura_proforma}|//|{$licenciamento->porto_entrada}|{$licenciamento->tipo_declaracao}|{$licenciamento->estancia_id}|" . count($mercadoriaAgrupada) . "|{$licenciamento->peso_bruto}||||{$licenciamento->metodo_avaliacao}|{$licenciamento->forma_pagamento}|{$licenciamento->codigo_banco}|{$licenciamento->codigo_volume}|{$licenciamento->qntd_volume}|{$licenciamento->descricao}||||{$pais->codigo}{$porto->sigla}||{$pais->codigo}|AO||||";

        // Linhas de adições
        $adicoes = [];
        foreach ($mercadoriaAgrupada as $key => $adicao) {
            $pautaAduaneira = PautaAduaneira::where(DB::raw("REPLACE(codigo, '.', '')"), $adicao->codigo_aduaneiro)->first();
            $ordem = $key + 1;

            $freteSeguro = Mercadoria::calcularFreteMercadoria($adicao->preco_total, $FOB, $frete)
                + Mercadoria::calcularSeguroMercadoria($adicao->preco_total, $FOB, $seguro);
            $CIF = $freteSeguro + $adicao->preco_total;
            $peso = $adicao->peso_total == 0
                ? $licenciamento->peso_bruto / count($mercadoriaAgrupada)
                : $adicao->peso_total;

            $adicoes[] = sprintf(
                "2|%d|||||%s|%d||%s|%s|%s|%s|%s|%s|||%s|||||||||||||||||||",
                $ordem,
                $adicao->codigo_aduaneiro ?? 'N/A',
                $adicao->quantidade_total ?? 0,
                $pais->codigo ?? 'N/A',
                $peso ?? '0.00',
                $licenciamento->moeda ?? 'N/A',
                $adicao->preco_total ?? '0.00',
                $freteSeguro ?? '0.00',
                $CIF ?? '0.00',
                $pautaAduaneira->uq ?? 'N/A'
            );
        }

        // Marcar como TXT gerado
        $licenciamento->txt_gerado = 1;
        $licenciamento->save();

        return [
            'content' => $linha0 . "\n" . $linha1 . "\n" . implode("\n", $adicoes),
            'filename' => 'licenciamento_' . str_replace('/', '-', $licenciamento->codigo_licenciamento) . '_' . now()->format('Ymd_His') . '.txt',
        ];
    }
}