<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NifVerificationController extends Controller
{
    /**
     * Verifica o NIF do Cliente.
     */
    public function verifyCliente($nif)
    {
        return $this->verifyNif($nif, 'cliente');
    }

    /**
     * Verifica o NIF do Exportador.
     */
    public function verifyExportador($nif)
    {
        return $this->verifyNif($nif, 'exportador');
    }

    /**
     * Método interno de verificação.
     */
    private function verifyNif($nif, $tipo)
    {
        // ⚠️ Validação básica do formato do NIF
        if (!preg_match('/^\d{9}$/', $nif)) {
            return response()->json([
                'success' => false,
                'tipo' => $tipo,
                'message' => 'Formato de NIF inválido',
            ], 400);
        }

        // 🔍 Exemplo 1: consulta numa base interna (ex: tabela customers)
        $table = $tipo === 'cliente' ? 'customers' : 'exportadores';

        $coluna = 'cliente' === $tipo ? 'CustomerTaxID' : 'ExportadorTaxID';

        $exists = DB::table($table)->where($coluna, $nif)->first();

        if ($exists) {
            return response()->json([
                'success' => true,
                'tipo' => $tipo,
                'message' => 'NIF encontrado na base de dados',
                'data' => [
                    'name' => $exists->CompanyName ?? $exists->Exportador ?? '—',
                    'nif' => $exists->$coluna,
                    'telefone' => $exists->Telephone ?? $exists->Telefone ?? '—',
                    'email' => $exists->Email ?? '—',
                ],
            ]);
        }

        // 🔍 Exemplo 2 (opcional): consulta à API pública da AGT (se disponível)
        /*
        $response = Http::get("https://api.agt.ao/nif/{$nif}");
        if ($response->successful()) {
            return response()->json([
                'success' => true,
                'tipo' => $tipo,
                'message' => 'NIF válido (verificado na AGT)',
                'data' => $response->json(),
            ]);
        }
        */

        //Se não for encontrado
        return response()->json([
            'success' => false,
            'tipo' => $tipo,
            'message' => 'NIF não encontrado',
        ], 404);
    }
}
