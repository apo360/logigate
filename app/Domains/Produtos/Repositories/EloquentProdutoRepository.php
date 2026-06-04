<?php

namespace App\Domains\Produtos\Repositories;

use App\Models\Empresa;
use App\Models\ProductPrice;
use App\Models\Produto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class EloquentProdutoRepository implements ProdutoRepositoryInterface
{
    public function findForEmpresa(int $id, Empresa $empresa): Produto
    {
        return Produto::query()
            ->with(['price', 'grupo', 'tipo', 'empresa', 'salesLines'])
            ->whereKey($id)
            ->where('empresa_id', $empresa->id)
            ->firstOrFail();
    }

    public function createForEmpresa(Empresa $empresa, array $attributes): Produto
    {
        return Produto::create(array_merge($attributes, [
            'empresa_id' => $empresa->id,
        ]));
    }

    public function update(Produto $produto, array $attributes): Produto
    {
        $produto->update($attributes);

        return $produto->refresh();
    }

    public function savePrice(Produto $produto, array $attributes): ProductPrice
    {
        $price = $this->currentPrice($produto);

        if ($price) {
            $price->update($attributes);

            return $price->refresh();
        }

        return ProductPrice::create(array_merge($attributes, [
            'fk_product' => $produto->id,
        ]));
    }

    public function currentPrice(Produto $produto): ?ProductPrice
    {
        return ProductPrice::query()
            ->where('fk_product', $produto->id)
            ->latest('created_at')
            ->first();
    }

    public function setStatus(Produto $produto, int $status): Produto
    {
        $produto->status = $status;
        $produto->discontinued_at = $status === 0 ? now() : null;
        $produto->saveQuietly();

        return $produto->refresh();
    }

    public function hasSales(Produto $produto): bool
    {
        return $produto->salesLines()->exists();
    }

    public function paginateForEmpresa(Empresa $empresa, array $filters = []): LengthAwarePaginator
    {
        $sortField = $this->allowedSortField($filters['sortField'] ?? 'created_at');
        $sortDirection = strtolower((string) ($filters['sortDirection'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
        $perPage = max(1, min((int) ($filters['perPage'] ?? 15), 100));
        $search = trim((string) ($filters['search'] ?? ''));
        $taxa = trim((string) ($filters['taxa'] ?? ''));
        $productType = trim((string) ($filters['productType'] ?? ''));

        return Produto::query()
            ->with(['price', 'grupo', 'salesLines'])
            ->where('empresa_id', $empresa->id)
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('ProductDescription', 'like', "%{$search}%")
                        ->orWhere('ProductCode', 'like', "%{$search}%");
                });
            })
            ->when($taxa !== '', fn (Builder $query) => $query->whereHas('price', fn (Builder $price) => $price->where('imposto', $taxa)))
            ->when($productType !== '', fn (Builder $query) => $query->where('ProductType', $productType))
            ->orderBy($sortField, $sortDirection)
            ->paginate($perPage);
    }

    private function allowedSortField(string $field): string
    {
        return in_array($field, ['ProductType', 'ProductDescription', 'ProductCode', 'created_at', 'status'], true)
            ? $field
            : 'created_at';
    }
}
