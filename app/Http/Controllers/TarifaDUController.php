<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Http\Requests\TarifaDURequest;
use App\Models\Processo;
use App\Models\TarifaDU;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class TarifaDUController extends Controller
{
    public static function storeOrUpdate(TarifaDURequest $request, $processoID)
    {
        try {
            
            $processo = Processo::where('id', $processoID)->first();
            
            if($processo->du){
                // Actualizar a TarifaDU
                TarifaDU::where('Fk_processo', $processo->id)->update($request->validated());
            }else{
                // Inserir a TarifaDU
                TarifaDU::create($request->validated());
            }

        } catch (QueryException $e) {
            // Lidar com erros de consulta, se necessário
            Log::error('Erro ao salvar TarifaDU: ' . $e->getMessage());
            return DatabaseErrorHandler::handle($e, $request);
        }
    }
}
