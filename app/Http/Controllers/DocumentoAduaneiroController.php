<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Http\Requests\DocumentoAduRequest;
use App\Models\DocumentosAduaneiros;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DocumentoAduaneiroController extends Controller
{
    public static function storeOrUpdate(DocumentoAduRequest $request, $ImportacaoID = null)
    {
        try {
            if ($ImportacaoID) {
                // Atualizar o documento existente se o ID for fornecido
                DocumentosAduaneiros::where('Fk_Importacao',$ImportacaoID)->update($request->validated());

            } else {
                // Criar um novo documento se o ID não for fornecido
                DocumentosAduaneiros::create($request->validated());
            }
            return true;
        } catch (QueryException $e) {
            return DatabaseErrorHandler::handle($e);
        }
    }
}
