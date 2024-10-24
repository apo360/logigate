<?php

namespace App\Http\Controllers;

use App\Imports\PautaAduaneiraImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\DatabaseErrorHandler;

class PautaAduaneiraController extends Controller
{
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
}

