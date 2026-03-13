<?php

namespace App\Services;

use App\Models\Licenciamento;
use App\Models\LicenciamentoRascunho;
use App\Models\Mercadoria;
use App\Models\MercadoriaAgrupada;
use App\Models\Pais;
use App\Models\PautaAduaneira;
use App\Models\Porto;
use App\Models\Processo;
use App\Models\ProcLicenFactura;
use Illuminate\Support\Facades\DB;

class LicenciamentoService
{
    public function create(array $data, int $empresaId): Licenciamento
    {
        return DB::transaction(function () use ($data, $empresaId) {
            $payload = $this->preparePayload($data, $empresaId);
            $payload['adicoes'] = $payload['adicoes'] ?? 0;

            return Licenciamento::create($payload);
        });
    }

    public function createDraft(array $data, int $empresaId): LicenciamentoRascunho
    {
        return DB::transaction(function () use ($data, $empresaId) {
            $data['empresa_id'] = $empresaId;

            return LicenciamentoRascunho::create($data);
        });
    }

    public function update(Licenciamento $licenciamento, array $data, int $empresaId): Licenciamento
    {
        return DB::transaction(function () use ($licenciamento, $data, $empresaId) {
            $licenciamento->update($this->preparePayload($data, $empresaId));

            return $licenciamento->refresh();
        });
    }

    public function constituirProcesso(Licenciamento $licenca, ?int $userId): Processo
    {
        return DB::transaction(function () use ($licenca, $userId) {
            $processo = Processo::create([
                'ContaDespacho' => $licenca->referencia_cliente,
                'RefCliente' => $licenca->referencia_cliente,
                'estancia_id' => $licenca->estancia_id,
                'Descricao' => $licenca->descricao,
                'DataAbertura' => now(),
                'TipoProcesso' => $licenca->tipo_declaracao,
                'Estado' => 'Aberto',
                'customer_id' => $licenca->cliente_id,
                'user_id' => $userId,
                'empresa_id' => $licenca->empresa_id,
                'exportador_id' => $licenca->exportador_id,
                'forma_pagamento' => $licenca->forma_pagamento,
                'fob_total' => $licenca->fob_total,
                'frete' => $licenca->frete,
                'seguro' => $licenca->seguro,
                'codigo_banco' => $licenca->codigo_banco,
                'peso_bruto' => $licenca->peso_bruto,
                'TipoTransporte' => $licenca->tipo_transporte,
                'registo_transporte' => $licenca->registo_transporte,
                'nacionalidade_transporte' => $licenca->nacionalidade_transporte,
                'DataChegada' => $licenca->data_entrada,
                'Moeda' => $licenca->moeda,
                'Cambio' => 1.0,
                'ValorTotal' => $licenca->cif,
                'cif' => $licenca->cif,
                'ValorAduaneiro' => $licenca->cif + $licenca->frete + $licenca->seguro,
            ]);

            ProcLicenFactura::updateOrCreate(
                ['licenciamento_id' => $licenca->id],
                ['processo_id' => $processo->id]
            );

            Mercadoria::where('licenciamento_id', $licenca->id)
                ->update(['Fk_Importacao' => $processo->id]);

            return $processo;
        });
    }

    public function generateTxtDownload(Licenciamento $licenciamento): array
    {
        if (is_null($licenciamento->frete) || is_null($licenciamento->seguro) || $licenciamento->frete === 0 || $licenciamento->seguro === 0) {
            throw new \InvalidArgumentException('Os campos Frete e Seguro precisam estar preenchidos e diferentes de zero antes de gerar o licenciamento.');
        }

        $mercadoriaAgrupada = MercadoriaAgrupada::where('licenciamento_id', $licenciamento->id)->get();
        if ($mercadoriaAgrupada->isEmpty()) {
            throw new \RuntimeException('Nenhuma mercadoria agrupada encontrada para este licenciamento.');
        }

        $porto = Porto::where('sigla', $licenciamento->porto_origem)->first();
        if (! $porto || ! $porto->pais) {
            throw new \RuntimeException('Porto de origem não encontrado ou sem país associado.');
        }

        $pais = Pais::findOrFail($porto->pais_id);
        $FOB = $licenciamento->fob_total;
        $frete = $licenciamento->frete;
        $seguro = $licenciamento->seguro;

        $linha0 = "0|" . count($mercadoriaAgrupada) . "|{$licenciamento->estancia_id}|{$licenciamento->cliente->CompanyName}|{$licenciamento->empresa->Empresa}|{$licenciamento->empresa->Cedula}|{$licenciamento->empresa->Email}|{$licenciamento->referencia_cliente}|||||||||||||||||||||||||||||";
        $linha1 = "1|{$licenciamento->exportador->ExportadorTaxID}|{$licenciamento->exportador->Exportador}|{$licenciamento->cliente->CustomerTaxID}||{$licenciamento->empresa->Cedula}|{$licenciamento->tipo_transporte}|{$licenciamento->registo_transporte}|{$licenciamento->pais->codigo}|{$licenciamento->manifesto}|{$licenciamento->factura_proforma}|//|{$licenciamento->porto_entrada}|{$licenciamento->tipo_declaracao}|{$licenciamento->estancia_id}|" . count($mercadoriaAgrupada) . "|{$licenciamento->peso_bruto}||||{$licenciamento->metodo_avaliacao}|{$licenciamento->forma_pagamento}|{$licenciamento->codigo_banco}|{$licenciamento->codigo_volume}|{$licenciamento->qntd_volume}|{$licenciamento->descricao}||||{$pais->codigo}{$porto->sigla}||{$pais->codigo}|AO||||";

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

        $licenciamento->txt_gerado = 1;
        $licenciamento->save();

        return [
            'content' => $linha0 . "\n" . $linha1 . "\n" . implode("\n", $adicoes),
            'filename' => 'licenciamento_' . $licenciamento->codigo_licenciamento . '.txt',
        ];
    }

    private function preparePayload(array $data, int $empresaId): array
    {
        $data['empresa_id'] = $empresaId;

        if (! empty($data['porto_origem'])) {
            $porto = Porto::where('sigla', $data['porto_origem'])->first();
            $data['pais_origem'] = $porto?->pais_id;
        }

        return $data;
    }
}
