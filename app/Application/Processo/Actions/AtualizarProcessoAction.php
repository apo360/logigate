<?php

declare(strict_types=1);

namespace App\Application\Processo\Actions;

use App\Application\Processo\DTOs\AtualizarProcessoDTO;
use App\Domains\Processo\Enums\EstadoProcessoEnum;
use App\Domains\Processo\Repositories\ProcessoRepositoryInterface;
use App\Models\Processo;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final readonly class AtualizarProcessoAction
{
    public function __construct(private ProcessoRepositoryInterface $processos)
    {
    }

    public function execute(AtualizarProcessoDTO $dto): Processo
    {
        return DB::transaction(function () use ($dto): Processo {
            $processo = $this->processos->findOrFail($dto->id);

            if ($this->estadoAtual($processo)->isFinalizado() && $dto->estado !== EstadoProcessoEnum::FINALIZADO) {
                throw new InvalidArgumentException('Processos finalizados não podem retornar para estados anteriores.');
            }

            if ($this->estadoAtual($processo)->isCancelado() && $dto->estado !== EstadoProcessoEnum::CANCELADO) {
                throw new InvalidArgumentException('Processos cancelados não podem ser reabertos.');
            }

            if ($dto->dataFecho !== null && $dto->dataAbertura !== null && $dto->dataFecho < $dto->dataAbertura) {
                throw new InvalidArgumentException('A data de fecho não pode ser anterior à data de abertura.');
            }

            return $this->processos->update($dto->id, $dto);
        });
    }

    private function estadoAtual(Processo $processo): EstadoProcessoEnum
    {
        return EstadoProcessoEnum::tryFrom((string) $processo->Estado) ?? EstadoProcessoEnum::ABERTO;
    }
}
