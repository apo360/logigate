<?php

namespace App\Http\Controllers;

use App\Imports\PautaAduaneiraImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\DatabaseErrorHandler;
use App\Models\PautaAduaneira;
use Illuminate\Database\QueryException;

class PautaAduaneiraController extends Controller
{
    // Exibir o formulário de consulta da Pauta Aduaneira
    public function consultarPauta(){
        return view('WebSite.consultar_pauta');
    }

    public function import_view(){

        return view('Master.pauta_aduaneira');
    }

    public function import(Request $request)
    {
        ini_set('max_execution_time', 300); // 5 minutos para processamento

        // Valida o arquivo
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240', // Limite de 10MB
        ]);

        try {
            // Faz a importação e processa em pacotes
            Excel::import(new PautaAduaneiraImport, $request->file('file'));

            return response()->json(['success' => true, 'message' => 'Importação concluída com sucesso!']);
        } catch (QueryException $e) {
            return DatabaseErrorHandler::handle($e, $request);
        }
        
    }

    function calcularTributacao($produto, $quantidade) {
        $pauta = PautaAduaneira::where('codigo_aduaneiro', $produto->codigo_aduaneiro)->first();
        
        if (!$pauta) {
            return response()->json(['error' => 'Produto não encontrado na pauta aduaneira'], 404);
        }
    
        $taxaImportacao = is_numeric($pauta->rg) ? floatval($pauta->rg) / 100 : 0;
        $iva = is_numeric($pauta->iva) ? floatval($pauta->iva) / 100 : 0;
        $ieq = is_numeric($pauta->ieq) ? floatval($pauta->ieq) / 100 : 0;
    
        $valorBase = $produto->preco_unitario * $quantidade;
    
        // Aplicar taxas
        $valorImpostoImportacao = $valorBase * $taxaImportacao;
        $valorIVA = ($valorBase + $valorImpostoImportacao) * $iva;
        $valorIEQ = $valorBase * $ieq;
    
        $totalTributacao = $valorImpostoImportacao + $valorIVA + $valorIEQ;
    
        return [
            'valor_base' => number_format($valorBase, 2, ',', '.'),
            'imposto_importacao' => number_format($valorImpostoImportacao, 2, ',', '.'),
            'iva' => number_format($valorIVA, 2, ',', '.'),
            'ieq' => number_format($valorIEQ, 2, ',', '.'),
            'total_tributos' => number_format($totalTributacao, 2, ',', '.')
        ];
    }
    
}

