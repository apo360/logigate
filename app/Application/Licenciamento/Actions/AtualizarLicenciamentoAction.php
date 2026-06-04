<?php

namespace App\Application\Licenciamento\Actions;

use App\Domains\Licenciamento\Repositories\LicenciamentoRepositoryInterface;
use App\Application\Licenciamento\DTOs\AtualizarLicenciamentoDTO;
use App\Domains\Licenciamento\Services\CalcularCifLicenciamentoService;
use App\Domains\Licenciamento\Services\LicenciamentoFaturamentoRules;
use App\Models\Licenciamento;
use Illuminate\Support\Facades\DB;

class AtualizarLicenciamentoAction
{
    public function __construct(
        private LicenciamentoRepositoryInterface $repository,
        private LicenciamentoFaturamentoRules $faturamentoRules,
        private CalcularCifLicenciamentoService $calcularCif,
    ) {}

    public function execute(AtualizarLicenciamentoDTO $dto): Licenciamento
    {
        return DB::transaction(function () use ($dto) {
            // Regras de negócio antes de atualizar (ex: não alterar moeda se já faturado)
            $licenciamento = $this->repository->find($dto->id);
            if (!$licenciamento) {
                throw new \Exception('Licenciamento não encontrado');
            }

            $this->faturamentoRules->assertMoedaPodeSerAlterada($licenciamento, $dto->moeda);

            $payload = $dto->toArray();
            if ($dto->cif->getValor() == 0 && $dto->fob_total->getValor() > 0) {
                $payload['cif'] = $this->calcularCif
                    ->calcular($dto->fob_total, $dto->frete, $dto->seguro)
                    ->getValor();
            }

            return $this->repository->update($dto->id, new AtualizarLicenciamentoDTO(['id' => $dto->id] + $payload));
        });
    }
}
