<?php

namespace App\Domains\PautaAduaneira\Repositories;

use App\Models\PautaAduaneira;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PautaAduaneiraRepositoryInterface
{
    public function find(int $id): ?PautaAduaneira;

    public function findOrFail(int $id): PautaAduaneira;

    public function findByCodigo(string $codigo): ?PautaAduaneira;

    public function search(array $filters, int $perPage = 15): LengthAwarePaginator;
}
