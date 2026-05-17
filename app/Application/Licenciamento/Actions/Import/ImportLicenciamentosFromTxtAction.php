<?php
// app/Application/Licenciamento/Actions/Import/ImportLicenciamentosFromTxtAction.php

namespace App\Application\Licenciamento\Actions\Import;

use App\Models\Licenciamento;
use App\Models\MercadoriaAgrupada;
use App\Models\Customer;
use App\Models\Exportador;
use App\Models\Estancia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportLicenciamentosFromTxtAction
{
    public function execute(UploadedFile $file, int $empresaId, int $userId): Licenciamento
    {
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
        if (!$handle) {
            throw new \RuntimeException('Não foi possível abrir o arquivo.');
        }

        $linha0 = null;
        $linha1 = null;
        $linhasAdicoes = [];

        // Primeira passagem: ler cabeçalhos
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if (empty($line)) continue;
            $fields = explode('|', $line);
            $tipo = $fields[0];
            if ($tipo == '0') {
                $linha0 = $fields;
            } elseif ($tipo == '1') {
                $linha1 = $fields;
                break; // encontramos linha1, podemos parar a primeira leitura
            }
        }
        if (!$linha0 || !$linha1) {
            throw new \InvalidArgumentException('Arquivo TXT inválido: faltam linhas de cabeçalho (0 e 1).');
        }

        // Validação empresa logada
        $empresaNome = $linha0[4] ?? null;
        $empresaCedula = $linha0[5] ?? null;
        $empresaLogada = \App\Models\Empresa::find($empresaId);
        if (!$empresaLogada || $empresaLogada->Empresa !== $empresaNome || $empresaLogada->Cedula !== $empresaCedula) {
            throw new \InvalidArgumentException('O arquivo não pertence à empresa logada.');
        }

        // Validação cliente
        $clienteNome = $linha0[3] ?? null;
        $clienteNIF = $linha1[3] ?? null;
        $cliente = Customer::where('CompanyName', $clienteNome)
            ->where('CustomerTaxID', $clienteNIF)
            ->where('empresa_id', $empresaId)
            ->first();
        if (!$cliente) {
            throw new \InvalidArgumentException("Cliente '{$clienteNome}' (NIF: {$clienteNIF}) não encontrado.");
        }

        // Validação exportador
        $exportadorNome = $linha1[2] ?? null;
        $exportadorNIF = $linha1[1] ?? null;
        $exportador = Exportador::where('Exportador', $exportadorNome)
            ->where('ExportadorTaxID', $exportadorNIF)
            ->where('empresa_id', $empresaId)
            ->first();
        if (!$exportador) {
            throw new \InvalidArgumentException("Exportador '{$exportadorNome}' (NIF: {$exportadorNIF}) não encontrado.");
        }

        // Validação estância
        $estanciaId = $linha0[2] ?? null;
        $estancia = Estancia::find($estanciaId);
        if (!$estancia) {
            throw new \InvalidArgumentException("Estância com ID {$estanciaId} não encontrada.");
        }

        // Segunda passagem: ler todas as linhas de adição
        rewind($handle);
        $inAdicoes = false;
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if (empty($line)) continue;
            $fields = explode('|', $line);
            if ($fields[0] == '2') {
                $linhasAdicoes[] = $fields;
            }
        }
        fclose($handle);

        if (empty($linhasAdicoes)) {
            throw new \RuntimeException('Nenhuma mercadoria (linha tipo 2) encontrada.');
        }

        // Criar licenciamento dentro de transação
        return DB::transaction(function () use ($linha0, $linha1, $linhasAdicoes, $cliente, $exportador, $estancia, $empresaId, $userId) {
            // Mapear campos da linha0 e linha1
            $data = [
                'estancia_id' => $estancia->id,
                'cliente_id' => $cliente->id,
                'exportador_id' => $exportador->id,
                'empresa_id' => $empresaId,
                'referencia_cliente' => $linha0[7] ?? null,
                'descricao' => $linha1[25] ?? null,
                'tipo_declaracao' => $linha1[13] ?? null,
                'tipo_transporte' => $linha1[6] ?? null,
                'registo_transporte' => $linha1[7] ?? null,
                'nacionalidade_transporte' => $linha1[8] ?? null,
                'manifesto' => $linha1[9] ?? null,
                'factura_proforma' => $linha1[10] ?? null,
                'porto_entrada' => $linha1[12] ?? null,
                'peso_bruto' => $linha1[15] ?? 0,
                'metodo_avaliacao' => $linha1[19] ?? 'GATT',
                'forma_pagamento' => $linha1[20] ?? 'RD',
                'codigo_banco' => $linha1[21] ?? null,
                'codigo_volume' => $linha1[22] ?? 'B',
                'qntd_volume' => $linha1[23] ?? 1,
                'moeda' => $linha1[4] ?? 'USD',
                'fob_total' => $linha1[3] ?? 0, // Precisa ver onde está FOB no TXT. Ajuste conforme especificação.
                'frete' => 0,
                'seguro' => 0,
                'cif' => 0,
                'porto_origem' => $linha1[30] ?? null,
                'pais_origem' => $linha1[32] ?? null,
            ];

            // Calcular FOB a partir das adições (soma dos preços totais)
            $fobTotal = array_sum(array_column($linhasAdicoes, 12));
            $data['fob_total'] = $fobTotal;
            $data['cif'] = $fobTotal; // pode adicionar frete/seguro se houver no TXT

            // Gerar código automaticamente
            $data['codigo_licenciamento'] = $this->gerarCodigo();

            $licenciamento = Licenciamento::create($data);

            // Inserir adições (MercadoriaAgrupada)
            foreach ($linhasAdicoes as $idx => $adicao) {
                MercadoriaAgrupada::create([
                    'licenciamento_id' => $licenciamento->id,
                    'codigo_aduaneiro' => $adicao[6] ?? null,
                    'quantidade_total' => $adicao[7] ?? 0,
                    'peso_total' => $adicao[10] ?? 0,
                    'moeda' => $adicao[11] ?? 'USD',
                    'preco_total' => $adicao[12] ?? 0,
                    'frete_mercadoria' => $adicao[13] ?? 0,
                    'seguro_mercadoria' => $adicao[14] ?? 0,
                    'cif_mercadoria' => $adicao[15] ?? 0,
                    'ordem' => $idx + 1,
                ]);
            }

            Log::info("Licenciamento importado via TXT", ['id' => $licenciamento->id, 'user' => $userId]);
            return $licenciamento;
        });
    }

    private function gerarCodigo(): string
    {
        $year = now()->format('Y');
        $last = Licenciamento::whereYear('created_at', $year)->max('codigo_licenciamento');
        $sequence = 1;
        if ($last) {
            $parts = explode('/', $last);
            $sequence = (int)end($parts) + 1;
        }
        return sprintf('%s/%04d', $year, $sequence);
    }
}