<?php

declare(strict_types=1);

namespace App\Application\Processo\Actions;

use App\Domains\Processo\Repositories\ProcessoRepositoryInterface;
use App\Domains\Processo\Services\ProcessoLifecycleRules;
use Illuminate\Support\Facades\DB;

final readonly class ExcluirProcessoAction
{
    public function __construct(
        private ProcessoRepositoryInterface $processos,
        private ProcessoLifecycleRules $rules,
    ) {
    }

    public function execute(int $id): bool
    {
        return DB::transaction(function () use ($id): bool {
            $processo = $this->processos->findOrFail($id);
            $this->rules->assertPodeExcluir($processo);

            return $this->processos->delete($id);
        });
    }
}
