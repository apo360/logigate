<?php

declare(strict_types=1);

namespace App\Domains\Processo\Services;

use App\Application\Processo\Actions\AtualizarProcessoAction;
use App\Application\Processo\Actions\CriarProcessoAction;
use App\Application\Processo\Actions\FinalizarProcessoAction;
use App\Application\Processo\DTOs\AtualizarProcessoDTO;
use App\Application\Processo\DTOs\CriarProcessoDTO;
use App\Domains\Processo\Repositories\ProcessoRepositoryInterface;
use App\Models\Processo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class ProcessoService
{
    public function __construct(
        private ProcessoRepositoryInterface $processos,
        private CriarProcessoAction $criarProcesso,
        private AtualizarProcessoAction $atualizarProcesso,
        private FinalizarProcessoAction $finalizarProcesso,
    ) {
    }

    public function criar(CriarProcessoDTO $dto): Processo
    {
        return $this->criarProcesso->execute($dto);
    }

    public function atualizar(AtualizarProcessoDTO $dto): Processo
    {
        return $this->atualizarProcesso->execute($dto);
    }

    public function finalizar(int $id): Processo
    {
        return $this->finalizarProcesso->execute($id);
    }

    public function obter(int $id): Processo
    {
        return $this->processos->findOrFail($id);
    }

    public function listar(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->processos->paginate($filters, $perPage);
    }
}
