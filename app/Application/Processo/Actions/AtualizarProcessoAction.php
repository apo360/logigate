<?php

declare(strict_types=1);

namespace App\Application\Processo\Actions;

use App\Application\Processo\DTOs\AtualizarProcessoDTO;
use App\Domains\Processo\Repositories\ProcessoRepositoryInterface;
use App\Domains\Processo\Services\ProcessoLifecycleRules;
use App\Models\Processo;
use Illuminate\Support\Facades\DB;

final readonly class AtualizarProcessoAction
{
    public function __construct(
        private ProcessoRepositoryInterface $processos,
        private ProcessoLifecycleRules $rules,
    )
    {
    }

    public function execute(AtualizarProcessoDTO $dto): Processo
    {
        return DB::transaction(function () use ($dto): Processo {
            $processo = $this->processos->findOrFail($dto->id);

            $this->rules->assertPodeTransicionar($processo, $dto->estado);
            $this->rules->assertDataFechoNaoAnterior($dto->dataAbertura, $dto->dataFecho);

            return $this->processos->update($dto->id, $dto);
        });
    }
}
