<?php

namespace App\Application\Licenciamento\Actions;

use App\Domains\Licenciamento\Repositories\LicenciamentoRepositoryInterface;
use App\Domains\Licenciamento\Services\LicenciamentoFaturamentoRules;
use Illuminate\Support\Facades\DB;

final readonly class ExcluirLicenciamentoAction
{
    public function __construct(
        private LicenciamentoRepositoryInterface $repository,
        private LicenciamentoFaturamentoRules $rules,
    ) {
    }

    public function execute(int $id): bool
    {
        return DB::transaction(function () use ($id): bool {
            $licenciamento = $this->repository->find($id);

            if (! $licenciamento) {
                throw new \InvalidArgumentException('Licenciamento não encontrado.');
            }

            $this->rules->assertPodeExcluir($licenciamento);

            return $this->repository->delete($id);
        });
    }
}
