<?php

namespace App\Domains\PautaAduaneira\Repositories;

use App\Domains\PautaAduaneira\ValueObjects\CodigoPautal;
use App\Models\PautaAduaneira;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class EloquentPautaAduaneiraRepository implements PautaAduaneiraRepositoryInterface
{
    public function find(int $id): ?PautaAduaneira
    {
        return PautaAduaneira::find($id);
    }

    public function findOrFail(int $id): PautaAduaneira
    {
        return PautaAduaneira::findOrFail($id);
    }

    public function findByCodigo(string $codigo): ?PautaAduaneira
    {
        $codigoPautal = new CodigoPautal($codigo);

        return PautaAduaneira::query()
            ->where('codigo', $codigoPautal->formatted())
            ->orWhereRaw("REPLACE(codigo, '.', '') = ?", [$codigoPautal->normalized()])
            ->first();
    }

    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = PautaAduaneira::query();
        $term = trim((string) ($filters['q'] ?? $filters['termo'] ?? ''));
        $codigo = trim((string) ($filters['codigo'] ?? ''));
        $descricao = trim((string) ($filters['descricao'] ?? ''));
        $tipo = (string) ($filters['tipo'] ?? 'ambos');

        if ($codigo !== '') {
            $this->applyCodigoFilter($query, $codigo);
        }

        if ($descricao !== '') {
            $this->applyDescricaoFilter($query, $descricao);
        }

        if ($term !== '') {
            $query->where(function (Builder $query) use ($term, $tipo) {
                if ($tipo === 'codigo') {
                    $this->applyCodigoFilter($query, $term);
                    return;
                }

                if ($tipo === 'descricao') {
                    $this->applyDescricaoFilter($query, $term);
                    return;
                }

                $this->applyCodigoFilter($query, $term);
                $this->applyDescricaoFilter($query, $term, 'or');
            });
        }

        if (! empty($filters['capitulo'])) {
            $query->where('codigo', 'like', $filters['capitulo'] . '%');
        }

        if (! empty($filters['posicao'])) {
            $query->where('codigo', 'like', $filters['posicao'] . '%');
        }

        return $query
            ->orderBy('codigo')
            ->paginate(max(1, min($perPage, 100)));
    }

    private function applyCodigoFilter(Builder $query, string $codigo, string $boolean = 'and'): void
    {
        $normalized = preg_replace('/\D+/', '', $codigo) ?? '';
        $like = '%' . $codigo . '%';
        $normalizedLike = '%' . $normalized . '%';

        $query->where(function (Builder $query) use ($like, $normalizedLike) {
            $query->where('codigo', 'like', $like)
                ->orWhereRaw("REPLACE(codigo, '.', '') like ?", [$normalizedLike]);
        }, null, null, $boolean);
    }

    private function applyDescricaoFilter(Builder $query, string $descricao, string $boolean = 'and'): void
    {
        $term = '%' . mb_strtolower($descricao) . '%';

        $query->whereRaw('LOWER(descricao) LIKE ?', [$term], $boolean);
    }
}
