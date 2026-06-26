<?php

namespace App\Application\Mercadoria\Repositories;

use App\Models\Mercadoria;
use App\Models\MercadoriaAgrupada;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class EloquentMercadoriaRepository implements MercadoriaRepositoryInterface
{
    public function findInContext(int $id, string $context, int $parentId): Mercadoria
    {
        return Mercadoria::query()
            ->whereKey($id)
            ->when(true, fn (Builder $query) => $this->applyContext($query, $context, $parentId))
            ->firstOrFail();
    }

    public function create(array $attributes): Mercadoria
    {
        return Mercadoria::create($attributes);
    }

    public function update(Mercadoria $mercadoria, array $attributes): Mercadoria
    {
        $mercadoria->update($attributes);

        return $mercadoria->refresh();
    }

    public function delete(Mercadoria $mercadoria): void
    {
        $mercadoria->delete();
    }

    public function listForContext(string $context, int $parentId): Collection
    {
        return Mercadoria::query()
            ->when(true, fn (Builder $query) => $this->applyContext($query, $context, $parentId))
            ->orderBy('codigo_aduaneiro')
            ->get();
    }

    public function groupedForContext(string $context, int $parentId): Collection
    {
        return MercadoriaAgrupada::query()
            ->when($context === 'processo', fn (Builder $query) => $query->where('processo_id', $parentId))
            ->when($context === 'licenciamento', fn (Builder $query) => $query->where('licenciamento_id', $parentId))
            ->orderBy('codigo_aduaneiro')
            ->get();
    }

    private function applyContext(Builder $query, string $context, int $parentId): void
    {
        match ($context) {
            'processo' => $query->where('Fk_Importacao', $parentId),
            'licenciamento' => $query->where('licenciamento_id', $parentId),
            default => $query->whereRaw('1 = 0'),
        };
    }
}
