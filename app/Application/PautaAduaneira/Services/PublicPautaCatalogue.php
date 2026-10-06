<?php

namespace App\Application\PautaAduaneira\Services;

use App\Models\PautaAduaneira;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** Public read contract only; operational calculation and catalogue records are untouched. */
final class PublicPautaCatalogue
{
    public function filters(Request $request): array
    {
        $filters = $request->validate([
            'q' => 'nullable|string|min:2|max:100', 'tipo' => 'nullable|in:auto,codigo,descricao,ambos',
            'codigo' => ['nullable', 'string', 'max:50', 'regex:/^[0-9]+(?:\.[0-9]+)*$/'],
            'descricao' => 'nullable|string|max:100', 'pauta_id' => 'nullable|integer|min:1',
            'page' => 'nullable|integer|min:1|max:10000', 'per_page' => 'nullable|integer|min:1|max:100',
            'months' => 'nullable|integer|min:1|max:'.(int) config('marketplace.window_months'),
            'location' => 'nullable|string|max:255', 'recurrence' => 'nullable|integer|min:1|max:'.(int) config('marketplace.window_months'),
            'mercadoria_descricao' => 'nullable|string|max:1000',
        ]);
        $term = trim($filters['q'] ?? '');
        $type = $filters['tipo'] ?? 'auto';
        if ($type === 'codigo' && $term !== '' && ! preg_match('/^[0-9]+(?:\.[0-9]+)*$/', $term)) {
            throw ValidationException::withMessages(['q' => 'Use apenas dígitos e pontos para pesquisar por código pautal.']);
        }
        $filters['q'] = $term;
        $filters['tipo'] = $type;

        return $filters;
    }

    public function searchFilters(array $filters): array
    {
        $type = $filters['tipo'] ?? 'auto';
        if ($type === 'auto') {
            $type = preg_match('/^[0-9.]+$/', $filters['q'] ?? '') ? 'codigo' : 'descricao';
        }

        return array_merge($filters, ['tipo' => $type]);
    }

    public function item(PautaAduaneira $item): array
    {
        $rate = static function (string $field) use ($item): int|float|string|null {
            $value = $item->getRawOriginal($field);
            if ($value === null || trim((string) $value) === '') {
                return null;
            }

            return is_numeric($value) ? (float) $value : (string) $value;
        };

        return [
            'id' => $item->id, 'codigo' => (string) $item->codigo, 'descricao' => (string) $item->descricao,
            'unidade' => $item->uq, 'regime_geral' => $rate('rg'), 'sadc' => $rate('sadc'), 'ua' => $rate('ua'),
            'impostos' => ['iva' => $rate('iva'), 'ieq' => $rate('ieq')],
            'iva' => $rate('iva'), // Retain the existing list contract; missing is now null.
            'nivel' => substr_count((string) $item->codigo, '.') + 1, 'nivel_confirmado' => false,
            'link' => url('/api/v1/pauta/detalhes/'.$item->id),
            'requisitos' => $item->requisitos, 'observacao' => $item->observacao,
            'fonte' => [
                'designacao' => 'Catálogo pautal registado no LogiGate', 'versao' => null, 'vigencia' => null,
                'atualizacao_registo' => $item->getRawOriginal('updated_at'),
                'limitacao' => 'A fonte e a versão normativa, a vigência e a hierarquia não estão identificadas no catálogo. A actualização do registo não comprova actualização da legislação.',
            ],
            'links' => ['self' => url('/api/v1/pauta/detalhes/'.$item->id)],
        ];
    }

    public function listing($paginator): array
    {
        return [
            'success' => true,
            'data' => $paginator->getCollection()->map(fn ($item) => $this->item($item)),
            'meta' => [
                'total' => $paginator->total(), 'shown' => $paginator->count(), 'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(), 'last_page' => $paginator->lastPage(),
                'catalogue_empty' => $paginator->total() === 0 && ! PautaAduaneira::query()->exists(),
            ],
        ];
    }
}
