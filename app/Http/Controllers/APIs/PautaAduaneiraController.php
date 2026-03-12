<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use App\Models\PautaAduaneira;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PautaAduaneiraController 
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
            $query = PautaAduaneira::query();
            
            // Filtros
            if ($request->filled('codigo')) {
                $query->where('codigo', 'LIKE', $request->codigo . '%');
            }
            
            if ($request->filled('descricao')) {
                $query->where('descricao', 'ILIKE', '%' . $request->descricao . '%');
            }
            
            if ($request->filled('capitulo')) {
                $query->where('codigo', 'LIKE', $request->capitulo . '%');
            }
            
            if ($request->filled('posicao')) {
                $query->where('codigo', 'LIKE', $request->posicao . '%');
            }
            
            // Ordenação
            $query->orderBy('codigo', 'asc');
            
            // Paginação
            $perPage = $request->get('per_page', $this->perPage);
            
            return $query->paginate($perPage);
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
    public function show($codigo)
    {
        $cacheKey = 'pauta_show_' . $codigo;
        
        $item = Cache::remember($cacheKey, now()->addDay(), function () use ($codigo) {
            return PautaAduaneira::where('codigo', $codigo)->first();
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
            $query = PautaAduaneira::query();
            
            $termo = $request->q;
            $tipo = $request->get('tipo', 'ambos');
            
            if ($tipo === 'codigo' || $tipo === 'ambos') {
                $query->where('codigo', 'LIKE', '%' . $termo . '%');
            }
            
            if ($tipo === 'descricao' || $tipo === 'ambos') {
                if ($tipo === 'ambos') {
                    $query->orWhere('descricao', 'ILIKE', '%' . $termo . '%');
                } else {
                    $query->where('descricao', 'ILIKE', '%' . $termo . '%');
                }
            }
            
            $limit = $request->get('limit', 50);
            
            return $query->limit($limit)->get();
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
            return PautaAduaneira::where('codigo', 'LIKE', $termo . '%')
                ->orWhere('descricao', 'ILIKE', '%' . $termo . '%')
                ->limit(10)
                ->get(['codigo', 'descricao']);
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
        return $items->map(function($item) {
            return [
                'codigo' => $item->codigo,
                'descricao' => $item->descricao,
                'iva' => (float) $item->iva,
                'nivel' => $this->getNivel($item->codigo),
                'link' => url('/api/v1/pauta/' . $item->codigo)
            ];
        });
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