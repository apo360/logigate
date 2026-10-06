<?php

namespace App\Http\Controllers\APIs;

use App\Application\PautaAduaneira\Actions\ConsultarCodigoPautalAction;
use App\Application\PautaAduaneira\Services\PautaSearchService;
use App\Application\PautaAduaneira\Services\PublicPautaCatalogue;
use App\Http\Controllers\BaseController;
use App\Models\PautaAduaneira;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PautaAduaneiraController extends BaseController
{
    /**
     * Número de itens por página
     */
    protected $perPage = 50;

    /**
     * Listar códigos com filtros
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $catalogue = app(PublicPautaCatalogue::class);
            $filters = $catalogue->filters($request);
            $legacy = $request->validate(['capitulo' => ['nullable', 'regex:/^[0-9]{2}$/'], 'posicao' => ['nullable', 'regex:/^[0-9]{4}$/']]);
            $filters = array_merge($filters, $legacy);
            $key = 'pauta_public_index_v2_'.md5(json_encode($filters));
            $data = Cache::remember($key, now()->addMinutes(5), fn () => app(PautaSearchService::class)->search($catalogue->searchFilters($filters), (int) ($filters['per_page'] ?? $this->perPage)));

            return response()->json($catalogue->listing($data));
        } catch (\Illuminate\Database\QueryException|\PDOException $exception) {
            report($exception);

            return response()->json(['success' => false, 'message' => 'A consulta está temporariamente indisponível. Tente novamente.'], 503);
        }
    }

    /**
     * Detalhe de um código específico
     *
     * @param  string  $codigo
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($codigo, ConsultarCodigoPautalAction $action)
    {
        try {
            $normalized = str_replace('.', '', $codigo);
            if (! preg_match('/^[0-9]+(?:\.[0-9]+)*$/', $codigo) || strlen($normalized) < 2) {
                return response()->json(['success' => false, 'message' => 'Código inválido.'], 422);
            }
            $items = PautaAduaneira::whereRaw("REPLACE(codigo, '.', '') = ?", [$normalized])->limit(2)->get();
            if ($items->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Código não encontrado.'], 404);
            }
            if ($items->count() !== 1) {
                return response()->json(['success' => false, 'message' => 'Código com várias identidades. Seleccione um resultado por ID.'], 409);
            }

            return response()->json(['success' => true, 'data' => app(PublicPautaCatalogue::class)->item($items->first())])->header('Cache-Control', 'no-store');
        } catch (\Illuminate\Database\QueryException|\PDOException $exception) {
            report($exception);

            return response()->json(['success' => false, 'message' => 'A consulta está temporariamente indisponível. Tente novamente.'], 503);
        }
    }

    public function details(Request $request, int $id)
    {
        try {
            $filters = $request->validate(['codigo' => ['nullable', 'string', 'max:50', 'regex:/^[0-9]+(?:\.[0-9]+)*$/']]);
            $item = PautaAduaneira::find($id);
            if (! $item) {
                return response()->json(['success' => false, 'message' => 'Mercadoria não encontrada.'], 404);
            }
            if (isset($filters['codigo']) && $filters['codigo'] !== (string) $item->codigo) {
                return response()->json(['success' => false, 'message' => 'A identidade pautal mudou. Seleccione novamente.'], 422);
            }

            return response()->json(['success' => true, 'data' => app(PublicPautaCatalogue::class)->item($item)])->header('Cache-Control', 'no-store');
        } catch (\Illuminate\Database\QueryException|\PDOException $exception) {
            report($exception);

            return response()->json(['success' => false, 'message' => 'A consulta está temporariamente indisponível. Tente novamente.'], 503);
        }
    }

    /**
     * Busca avançada
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try {
            $request->validate(['q' => 'required|string|min:2|max:100', 'limit' => 'nullable|integer|min:1|max:100']);
            $catalogue = app(PublicPautaCatalogue::class);
            $filters = $catalogue->filters($request);
            $size = (int) $request->get('per_page', $request->get('limit', 50));
            $filters['per_page'] = $size;
            $key = 'pauta_public_search_v2_'.md5(json_encode($filters));
            $results = Cache::remember($key, now()->addMinutes(5), fn () => app(PautaSearchService::class)->search($catalogue->searchFilters($filters), $size));
            $payload = $catalogue->listing($results);
            $payload['meta']['termo'] = $filters['q'];

            return response()->json($payload);
        } catch (\Illuminate\Database\QueryException|\PDOException $exception) {
            report($exception);

            return response()->json(['success' => false, 'message' => 'A consulta está temporariamente indisponível. Tente novamente.'], 503);
        }
    }

    /**
     * Estatísticas da pauta
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        $cacheKey = 'pauta_statistics';

        $stats = Cache::remember($cacheKey, now()->addDay(), function () {
            return [
                'total_codigos' => PautaAduaneira::count(),
                'total_capitulos' => PautaAduaneira::whereRaw('LENGTH(codigo) = 2')->count(),
                'total_posicoes' => PautaAduaneira::whereRaw('LENGTH(codigo) = 4')->count(),
                'total_subposicoes' => PautaAduaneira::whereRaw('LENGTH(codigo) = 7')->count(),
                'ultima_atualizacao' => PautaAduaneira::max('updated_at'),
                'distribuicao_iva' => [
                    '0%' => PautaAduaneira::where('iva', 0)->count(),
                    '5%' => PautaAduaneira::where('iva', 5)->count(),
                    '14%' => PautaAduaneira::where('iva', 14)->count(),
                    'outros' => PautaAduaneira::whereNotIn('iva', [0, 5, 14])->count(),
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);

    }

    /**
     * Sugestões de códigos (autocomplete)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function suggestions(Request $request)
    {
        try {
            $filters = $request->validate(['termo' => 'required|string|min:2|max:100', 'tipo' => 'nullable|in:auto,codigo,descricao,ambos']);
            $searchFilters = app(PublicPautaCatalogue::class)->searchFilters(['q' => $filters['termo'], 'tipo' => $filters['tipo'] ?? 'auto']);
            $searchFilters['page'] = 1;
            $items = Cache::remember('pauta_public_suggest_v2_'.md5(json_encode($searchFilters)), now()->addMinutes(5), fn () => app(PautaSearchService::class)->search($searchFilters, 8)->getCollection());

            return response()->json(['success' => true, 'data' => $items->map(fn ($item) => ['id' => $item->id, 'codigo' => (string) $item->codigo, 'descricao' => $item->descricao, 'display' => $item->codigo.' - '.$item->descricao])]);
        } catch (\Illuminate\Database\QueryException|\PDOException $exception) {
            report($exception);

            return response()->json(['success' => false, 'message' => 'A consulta está temporariamente indisponível. Tente novamente.'], 503);
        }
    }

    /**
     * Formatar item individual
     *
     * @param  PautaAduaneira  $item
     * @return array
     */
    private function formatItem($item)
    {
        return app(PublicPautaCatalogue::class)->item($item);
    }

    /**
     * Formatar coleção
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Pagination\LengthAwarePaginator  $items
     * @return \Illuminate\Support\Collection
     */
    private function formatCollection($items)
    {
        $collection = method_exists($items, 'getCollection') ? $items->getCollection() : collect($items);

        return $collection->map(fn ($item) => app(PublicPautaCatalogue::class)->item($item));
    }

    public function export(Request $request)
    {
        $results = app(PautaSearchService::class)
            ->search($request->all(), (int) $request->get('per_page', 100))
            ->getCollection();

        return response()->json([
            'success' => true,
            'data' => $this->formatCollection($results),
        ]);

    }

    /**
     * Determinar o nível do código baseado nos pontos
     *
     * @param  string  $codigo
     * @return int
     */
    private function getNivel($codigo)
    {
        return substr_count($codigo, '.') + 1;
    }
}
