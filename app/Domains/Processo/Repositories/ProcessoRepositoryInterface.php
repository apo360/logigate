<?php

declare(strict_types=1);

namespace App\Domains\Processo\Repositories;

use App\Application\Processo\DTOs\AtualizarProcessoDTO;
use App\Application\Processo\DTOs\CriarProcessoDTO;
use App\Models\Processo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProcessoRepositoryInterface
{
    public function create(CriarProcessoDTO $dto): Processo;

    public function update(int $id, AtualizarProcessoDTO $dto): Processo;

    public function find(int $id): ?Processo;

    public function findOrFail(int $id): Processo;

    public function findByNumero(string $numero): ?Processo;

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function delete(int $id): bool;

    public function verificarCamposImportantes(array $camposImportantes): array;
}
