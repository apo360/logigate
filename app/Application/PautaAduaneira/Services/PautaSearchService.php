<?php

namespace App\Application\PautaAduaneira\Services;

use App\Domains\PautaAduaneira\Repositories\PautaAduaneiraRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class PautaSearchService
{
    public function __construct(
        private readonly PautaAduaneiraRepositoryInterface $pautas,
    ) {
    }

    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->pautas->search($filters, $perPage);
    }
}
