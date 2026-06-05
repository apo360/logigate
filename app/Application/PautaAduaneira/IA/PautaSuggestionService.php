<?php

namespace App\Application\PautaAduaneira\IA;

use App\Application\PautaAduaneira\Services\PautaSearchService;
use App\Models\Subcategoria;
use Illuminate\Support\Collection;

final class PautaSuggestionService
{
    public function __construct(
        private readonly PautaSearchService $search,
        private readonly PautaSuggestionProviderInterface $provider,
    ) {
    }

    /**
     * @return array<int, PautaSuggestionResultDTO>
     */
    public function suggest(PautaSuggestionDTO $dto): array
    {
        $candidates = $this->localCandidates($dto);

        if ($candidates->isEmpty()) {
            return [];
        }

        return collect($this->provider->suggest($dto, $candidates))
            ->take($dto->limit)
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, \App\Models\PautaAduaneira>
     */
    private function localCandidates(PautaSuggestionDTO $dto): Collection
    {
        $queries = array_filter([
            $dto->codigoAtual,
            $dto->descricao,
            $dto->marca,
            $dto->modelo,
            $dto->subcategoriaId ? $this->subcategoriaPrefix($dto->subcategoriaId) : null,
        ]);

        $candidates = collect();

        foreach ($queries as $query) {
            $page = $this->search->search(['q' => $query], 15);
            $candidates = $candidates->merge($page->items());
        }

        return $candidates->unique('id')->take(20)->values();
    }

    private function subcategoriaPrefix(int $id): ?string
    {
        $subcategoria = Subcategoria::find($id);

        return $subcategoria?->cod_pauta ? (string) $subcategoria->cod_pauta : null;
    }
}
