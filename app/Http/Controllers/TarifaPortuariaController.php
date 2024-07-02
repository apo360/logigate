<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Http\Requests\PortuariaRequest;
use App\Models\Processo;
use App\Models\TarifaPortuaria;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TarifaPortuariaController extends Controller
{
    public static function storeOrUpdate(PortuariaRequest $request, $processoID)
    {
        try {
            // Obter o processo associado
            $processo = Processo::where('id', $processoID)->first();

            // Verificar se já existe uma TarifaPortuaria associada ao processo
            if ($processo->portuaria) {
                // Atualizar os dados da TarifaPortuaria
                $tarifaPortuaria = TarifaPortuaria::where('Fk_processo', $processoID)->update($request->validated());
            } else {
                // Criar uma nova TarifaPortuaria
                $tarifaPortuaria = TarifaPortuaria::create([
                    'Fk_processo' => $processo->id,
                    'ep14' => $request->ep14,
                    'ep17' => $request->ep17,
                    'terminal' => $request->terminal,
                ]);
            }

            return $tarifaPortuaria;

        } catch (QueryException $e) {
            // Lidar com erros de consulta, se necessário
            Log::error('Erro ao salvar TarifaPortuaria: ' . $e->getMessage());
            return DatabaseErrorHandler::handle($e, $request);
        }
    }
}
