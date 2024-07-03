<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Http\Requests\DARRequest;
use App\Models\TarifaDAR;
use App\Models\Processo;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class TarifaDARController extends Controller
{
    public static function storeOrUpdate(DARRequest $request, $processoID)
    {
        try {
            $processo = Processo::where('id', $processoID)->first();
            
            if($processo->dar){
                TarifaDAR::where('Fk_processo', $processoID)->update($request->validated());
            }
            else{
                TarifaDAR::create($request->validated());
            }

        } catch (QueryException $e) {
            // Lidar com erros de consulta, se necessário
            Log::error('Erro ao salvar TarifaDAR: ' . $e->getMessage());
            return DatabaseErrorHandler::handle($e);
        }
    }
}
