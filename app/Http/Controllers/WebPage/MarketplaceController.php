<?php

namespace App\Http\Controllers\WebPage;

use App\Application\Marketplace\MarketplaceExperience;
use App\Application\PautaAduaneira\Services\PautaSearchService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    private function filters(Request $request): array
    {
        return $request->validate([
            'pauta_id' => 'nullable|integer|min:1', 'codigo' => ['nullable', 'string', 'max:50', 'regex:/^[0-9.]+$/'],
            'q' => 'nullable|string|max:100', 'location' => 'nullable|string|max:255',
            'months' => 'nullable|integer|min:1|max:'.(int) config('marketplace.window_months'),
            'recurrence' => 'nullable|integer|min:1|max:'.(int) config('marketplace.window_months'),
            'page' => 'nullable|integer|min:1|max:10000',
            'mercadoria_descricao' => 'nullable|string|max:1000', 'pauta_q' => 'nullable|string|min:2|max:100',
            'pauta_tipo' => 'nullable|in:auto,codigo,descricao,ambos', 'pauta_page' => 'nullable|integer|min:1|max:10000', 'pauta_per_page' => 'nullable|integer|min:1|max:100',
        ]);
    }

    public function index(Request $request, MarketplaceExperience $experience, PautaSearchService $pauta)
    {
        $filters = $this->filters($request);
        $selection = $experience->selection(isset($filters['pauta_id']) ? (int) $filters['pauta_id'] : null, $filters['codigo'] ?? null, $filters['mercadoria_descricao'] ?? null);
        $term = trim($filters['q'] ?? '');
        $type = preg_match('/^[0-9.]+$/', $term) ? 'codigo' : 'descricao';
        $choices = $term !== '' ? $pauta->search(['q' => $term, 'tipo' => $type], 10)->withQueryString() : null;
        $directory = $experience->search($selection, $filters);

        return view('WebSite.marketplace', compact('filters', 'selection', 'choices', 'directory'));
    }

    public function guide(Request $request, MarketplaceExperience $experience)
    {
        $filters = $this->filters($request);
        abort_unless(! empty($filters['pauta_id']) || ! empty($filters['codigo']), 422);
        $selection = $experience->selection(isset($filters['pauta_id']) ? (int) $filters['pauta_id'] : null, $filters['codigo'] ?? null, $filters['mercadoria_descricao'] ?? null);
        $directory = $experience->search($selection, $filters, 3);

        return response()->json([
            'selection' => ['id' => $selection->id, 'codigo' => (string) $selection->codigo, 'descricao' => $selection->descricao],
            'profiles' => $directory['profiles']->items(),
            'period' => ['start' => $directory['start']->toDateString(), 'end_exclusive' => $directory['end']->toDateString()],
            'marketplace_url' => route('marketplace', array_merge(\Illuminate\Support\Arr::only($filters, ['months', 'location', 'recurrence', 'pauta_q', 'pauta_tipo', 'pauta_page', 'pauta_per_page']), ['pauta_id' => $selection->id, 'codigo' => (string) $selection->codigo, 'mercadoria_descricao' => $selection->descricao])),
        ])->header('Cache-Control', 'no-store');
    }
}
