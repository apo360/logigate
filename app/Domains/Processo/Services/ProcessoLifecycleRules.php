<?php

declare(strict_types=1);

namespace App\Domains\Processo\Services;

use App\Domains\Processo\Enums\EstadoProcessoEnum;
use App\Models\Processo;
use InvalidArgumentException;

final readonly class ProcessoLifecycleRules
{
    public function assertDataFechoNaoAnterior(?string $dataAbertura, ?string $dataFecho): void
    {
        if ($dataFecho !== null && $dataAbertura !== null && $dataFecho < $dataAbertura) {
            throw new InvalidArgumentException('A data de fecho não pode ser anterior à data de abertura.');
        }
    }

    public function assertPodeTransicionar(Processo $processo, ?EstadoProcessoEnum $novoEstado): void
    {
        if ($novoEstado === null) {
            return;
        }

        $estadoAtual = EstadoProcessoEnum::tryFrom((string) $processo->Estado) ?? EstadoProcessoEnum::ABERTO;

        if ($estadoAtual->isFinalizado() && $novoEstado !== EstadoProcessoEnum::FINALIZADO) {
            throw new InvalidArgumentException('Processos finalizados não podem retornar para estados anteriores.');
        }

        if ($estadoAtual->isCancelado() && $novoEstado !== EstadoProcessoEnum::CANCELADO) {
            throw new InvalidArgumentException('Processos cancelados não podem ser reabertos.');
        }
    }

    public function assertPodeExcluir(Processo $processo): void
    {
        $estadoAtual = EstadoProcessoEnum::tryFrom((string) $processo->Estado) ?? EstadoProcessoEnum::ABERTO;

        if ($estadoAtual->isFinalizado() || $estadoAtual->isCancelado()) {
            throw new InvalidArgumentException('Processos finalizados ou cancelados não podem ser excluídos.');
        }
    }
}
