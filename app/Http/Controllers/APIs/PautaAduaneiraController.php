<?php

namespace App\Http\Controllers\APIs;

use App\Application\PautaAduaneira\Actions\ConsultarCodigoPautalAction;
use App\Application\PautaAduaneira\Services\PautaSearchService;
use App\Http\Controllers\BaseController;
use App\Models\PautaAduaneira;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class PautaAduaneiraController extends BaseController 
{
    /**
     * Número de itens por página
     */
    protected $perPage = 50;

    /**
     * Listar códigos com filtros
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'nullable|string|max:20',
            'descricao' => 'nullable|string|max:100',
            'capitulo' => 'nullable|string|size:2',
            'posicao' => 'nullable|string|size:4',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Cache key baseada nos parâmetros
        $cacheKey = 'pauta_index_' . md5(json_encode($request->all()));
        
        $data = Cache::remember($cacheKey, now()->addHours(6), function () use ($request) {
            return app(PautaSearchService::class)->search(
                $request->all(),
                (int) $request->get('per_page', $this->perPage)
            );
        });

        return response()->json([
            'success' => true,
            'data' => $this->formatCollection($data),
            'meta' => [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
            ]
        ]);
    }

    /**
     * Detalhe de um código específico
     *
     * @param string $codigo
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($codigo, ConsultarCodigoPautalAction $action)
    {
        $cacheKey = 'pauta_show_' . $codigo;
        
        $item = Cache::remember($cacheKey, now()->addDay(), function () use ($codigo, $action) {
            return $action->execute($codigo);
        });

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Código não encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatItem($item)
        ]);
    }

    /**
     * Busca avançada
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2|max:100',
            'tipo' => 'nullable|in:codigo,descricao,ambos',
            'limit' => 'nullable|integer|min:1|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $cacheKey = 'pauta_search_' . md5($request->q . $request->tipo);
        
        $results = Cache::remember($cacheKey, now()->addHours(2), function () use ($request) {
            return app(PautaSearchService::class)
                ->search([
                    'q' => $request->q,
                    'tipo' => $request->get('tipo', 'ambos'),
                ], (int) $request->get('limit', 50))
                ->getCollection();
        });

        return response()->json([
            'success' => true,
            'data' => $this->formatCollection($results),
            'meta' => [
                'total' => $results->count(),
                'termo' => $request->q
            ]
        ]);
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
                    'outros' => PautaAduaneira::whereNotIn('iva', [0,5,14])->count(),
                ]
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Sugestões de códigos (autocomplete)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function suggestions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'termo' => 'required|string|min:2|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $termo = $request->termo;
        
        $sugestoes = Cache::remember('pauta_suggest_' . $termo, now()->addHours(1), function () use ($termo) {
            return app(PautaSearchService::class)
                ->search(['q' => $termo], 10)
                ->getCollection();
        });

        return response()->json([
            'success' => true,
            'data' => $sugestoes->map(function($item) {
                return [
                    'codigo' => $item->codigo,
                    'descricao' => $item->descricao,
                    'display' => $item->codigo . ' - ' . $item->descricao
                ];
            })
        ]);
    }

    /**
     * Formatar item individual
     *
     * @param PautaAduaneira $item
     * @return array
     */
    private function formatItem($item)
    {
        return [
            'codigo' => $item->codigo,
            'descricao' => $item->descricao,
            'unidade' => $item->uq,
            'regime_geral' => $item->rg,
            'sadc' => $item->sadc,
            'ua' => $item->ua,
            'impostos' => [
                'iva' => (float) $item->iva,
                'ieq' => (float) $item->ieq,
            ],
            'requisitos' => $item->requisitos,
            'observacao' => $item->observacao,
            'nivel' => $this->getNivel($item->codigo),
            'links' => [
                'self' => url('/api/v1/pauta/' . $item->codigo)
            ]
        ];
    }

    /**
     * Formatar coleção
     *
     * @param \Illuminate\Support\Collection|\Illuminate\Pagination\LengthAwarePaginator $items
     * @return \Illuminate\Support\Collection
     */
    private function formatCollection($items)
    {
        $collection = method_exists($items, 'getCollection')
            ? $items->getCollection()
            : collect($items);

        return $collection->map(function($item) {
            return [
                'codigo' => $item->codigo,
                'descricao' => $item->descricao,
                'iva' => (float) $item->iva,
                'nivel' => $this->getNivel($item->codigo),
                'link' => url('/api/v1/pauta/' . $item->codigo)
            ];
        });
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
     * @param string $codigo
     * @return int
     */
    private function getNivel($codigo)
    {
        return substr_count($codigo, '.') + 1;
    }
}
