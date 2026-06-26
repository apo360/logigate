<?php

namespace App\Application\Mercadoria\Repositories;

use App\Models\Mercadoria;
use Illuminate\Support\Collection;

interface MercadoriaRepositoryInterface
{
    public function findInContext(int $id, string $context, int $parentId): Mercadoria;

    public function create(array $attributes): Mercadoria;

    public function update(Mercadoria $mercadoria, array $attributes): Mercadoria;

    public function delete(Mercadoria $mercadoria): void;

    public function listForContext(string $context, int $parentId): Collection;

    public function groupedForContext(string $context, int $parentId): Collection;
}
