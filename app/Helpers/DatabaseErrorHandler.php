<?php

namespace App\Helpers;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class DatabaseErrorHandler
{
    public static function handle(\Throwable $e, Request $request)
    {
        // Log do erro
        Log::error('Erro: ' . $e->getMessage());

        if ($request->ajax() || $request->wantsJson()) {
            // Resposta JSON para requisições AJAX
            if ($e instanceof QueryException) {
                return response()->json(['error' => 'Ocorreu um erro de banco de dados. Por favor, tente novamente mais tarde. ' . $e->getMessage()], 500);
            } else {
                return response()->json(['error' => 'Ocorreu um erro. Por favor, tente novamente mais tarde.'], 500);
            }
        } else {
            // Redirecionamento com mensagem de erro para requisições normais
            if ($e instanceof QueryException) {
                return redirect()->back()->with('error', 'Ocorreu um erro de banco de dados. Por favor, tente novamente mais tarde. '. $e->getMessage());
            } else {
                return redirect()->back()->with('error', 'Ocorreu um erro. Por favor, tente novamente mais tarde.');
            }
        }
    }
    
}
