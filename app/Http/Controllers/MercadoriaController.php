<?php

namespace App\Http\Controllers;

use App\Application\Mercadoria\Actions\ReagruparMercadoriasAction;
use App\Application\Mercadoria\Services\PautaAduaneiraLookupService;

class MercadoriaController extends AuthenticatedController
{
    public function getCodigosAduaneiros(string $cod_pauta, PautaAduaneiraLookupService $lookup)
    {
        return response()->json($lookup->byPrefix($cod_pauta)->values());
    }

    public function reagrupar(int $licenciamentoId, ReagruparMercadoriasAction $action)
    {
        try {
            $action->execute($licenciamentoId);

            return redirect()->back()->with('success', 'Mercadorias agrupadas com sucesso!');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Erro ao agrupar mercadorias: ' . $e->getMessage());
        }
    }
}
