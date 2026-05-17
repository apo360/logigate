<?php

namespace App\Application\Licenciamento\Actions;

use App\Domains\Licenciamento\Repositories\LicenciamentoRepositoryInterface;
use App\Application\Licenciamento\DTOs\AtualizarLicenciamentoDTO;
use App\Models\Licenciamento;
use Illuminate\Support\Facades\DB;

class AtualizarLicenciamentoAction
{
    public function __construct(
        private LicenciamentoRepositoryInterface $repository
    ) {}

    public function execute(AtualizarLicenciamentoDTO $dto): Licenciamento
    {
        return DB::transaction(function () use ($dto) {
            // Regras de negócio antes de atualizar (ex: não alterar moeda se já faturado)
            $licenciamento = $this->repository->find($dto->id);
            if (!$licenciamento) {
                throw new \Exception('Licenciamento não encontrado');
            }

            // Se já tem fatura, pode bloquear certas alterações (exemplo)
            if ($licenciamento->procLicenFaturas()->exists() && $licenciamento->moeda != $dto->moeda) {
                throw new \Exception('Não é possível alterar a moeda porque já existem faturas associadas.');
            }

            return $this->repository->update($dto->id, $dto);
        });
    }
}