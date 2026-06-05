<?php

namespace App\Application\Mercadoria\Services;

use App\Application\PautaAduaneira\Services\PautaSearchService;
use App\Models\Subcategoria;
use Illuminate\Support\Collection;

final class PautaAduaneiraLookupService
{
    public function __construct(
        private readonly PautaSearchService $search,
    ) {
    }

    public function bySubcategoriaId(int $subcategoriaId): Collection
    {
        $subcategoria = Subcategoria::find($subcategoriaId);

        if (! $subcategoria) {
            return collect();
        }

        return $this->byPrefix((string) $subcategoria->cod_pauta);
    }

    public function byPrefix(string $prefix): Collection
    {
        return $this->search
            ->search(['codigo' => $prefix], 100)
            ->getCollection();
    }
}
