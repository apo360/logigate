<?php

namespace App\Http\Controllers\WebPage;

use App\Application\Marketplace\MarketplaceExperience;
use App\Application\PautaAduaneira\Services\PautaSearchService;
use App\Application\PautaAduaneira\Services\PublicPautaCatalogue;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PublicPautaController extends Controller
{
    public function index(Request $request, PublicPautaCatalogue $catalogue, PautaSearchService $search, MarketplaceExperience $experience)
    {
        try {
            $filters = $catalogue->filters($request);
            $selected = $experience->selection(isset($filters['pauta_id']) ? (int) $filters['pauta_id'] : null, $filters['codigo'] ?? null, $filters['mercadoria_descricao'] ?? null);
            // codigo is selection context on this page; q is the query, so they never conflict.
            $searchFilters = $catalogue->searchFilters(\Illuminate\Support\Arr::only($filters, ['q', 'tipo', 'descricao']));
            $results = $search->search($searchFilters, (int) ($filters['per_page'] ?? 20));
            $listing = $catalogue->listing($results);
            $selection = $selected ? $catalogue->item($selected) : null;
            $directory = null;
            if ($selected) {
                try {
                    $directory = $experience->search($selected, \Illuminate\Support\Arr::except($filters, ['page']), 3);
                } catch (\Illuminate\Database\QueryException|\PDOException $exception) {
                    report($exception);
                    [$start, $end] = $experience->window((int) ($filters['months'] ?? config('marketplace.window_months')));
                    $directory = ['error' => true, 'start' => $start, 'end' => $end, 'profiles' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 3)];
                }
            }

            return response()->view('WebSite.consultar_pauta', compact('filters', 'results', 'listing', 'selection', 'directory'))->header('Cache-Control', 'no-store');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return $this->failure($request, $exception->errors(), 422);
        } catch (\Illuminate\Database\QueryException|\PDOException $exception) {
            report($exception);

            return $this->failure($request, ['consulta' => 'A consulta está temporariamente indisponível. Tente novamente.'], 503);
        }
    }

    private function failure(Request $request, array $messages, int $status)
    {
        $q = $request->query('q');
        $type = $request->query('tipo');
        $filters = ['q' => is_string($q) ? mb_substr($q, 0, 100) : '', 'tipo' => in_array($type, ['auto', 'codigo', 'descricao'], true) ? $type : 'auto', 'per_page' => 20];
        $listing = ['success' => false, 'data' => [], 'meta' => ['total' => 0, 'shown' => 0, 'current_page' => 1, 'last_page' => 1, 'catalogue_empty' => false]];
        $errors = (new \Illuminate\Support\ViewErrorBag)->put('default', new \Illuminate\Support\MessageBag($messages));

        return response()->view('WebSite.consultar_pauta', ['filters' => $filters, 'listing' => $listing, 'selection' => null, 'directory' => null, 'errors' => $errors], $status)->header('Cache-Control', 'no-store');
    }
}
