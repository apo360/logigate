<?php

namespace App\Application\Mercadoria\Services;

use App\Models\PautaAduaneira;
use App\Models\Subcategoria;
use Illuminate\Support\Collection;

final class PautaAduaneiraLookupService
{
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
        return PautaAduaneira::query()
            ->where('codigo', 'like', $prefix . '%')
            ->orderBy('codigo')
            ->get();
    }
}
