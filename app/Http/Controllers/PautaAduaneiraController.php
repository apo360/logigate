<?php

namespace App\Http\Controllers;

use App\Application\PautaAduaneira\Actions\CalcularTaxasPautaAction;
use App\Application\PautaAduaneira\DTOs\CalculoPautaDTO;
use App\Application\PautaAduaneira\Services\PautaSearchService;
use App\Domains\PautaAduaneira\Repositories\PautaAduaneiraRepositoryInterface;
use App\Helpers\DatabaseErrorHandler;
use App\Imports\PautaAduaneiraImport;
use App\Models\Mercadoria;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PautaAduaneiraController extends Controller
{
    public function index(Request $request, PautaSearchService $search)
    {
        if ($request->expectsJson() || $request->query()) {
            $results = $search->search($request->all(), (int) $request->get('per_page', 15));

            return response()->json($results);
        }

        return view('pauta-aduaneira.index');
    }

    public function consultar(Request $request, PautaSearchService $search)
    {
        return $this->index($request, $search);
    }

    public function show(int $id, PautaAduaneiraRepositoryInterface $pautas)
    {
        $pautas->findOrFail($id);

        return view('pauta-aduaneira.show', [
            'pautaId' => $id,
        ]);
    }

    public function simulador()
    {
        return view('pauta-aduaneira.simulador');
    }

    public function import(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('livewire.import-pauta-aduaneira');
        }

        ini_set('max_execution_time', 300);

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            Excel::import(new PautaAduaneiraImport, $request->file('file'));

            return response()->json(['success' => true, 'message' => 'Importação concluída com sucesso!']);
        } catch (QueryException $e) {
            return DatabaseErrorHandler::handle($e, $request);
        }
    }

    public function calcularTributacao(Mercadoria $produto, int|float $quantidade, CalcularTaxasPautaAction $action)
    {
        $pautaId = $produto->pauta_aduaneira_id;

        if (! $pautaId) {
            return response()->json(['error' => 'Produto sem código pautal associado.'], 404);
        }

        $resultado = $action->execute(new CalculoPautaDTO(
            pautaAduaneiraId: (int) $pautaId,
            valorAduaneiro: (float) $produto->preco_unitario * (float) $quantidade,
            regimeTaxa: 'rg',
            incluirIva: true,
            incluirIeq: true,
        ));

        return $resultado->toArray();
    }
}
