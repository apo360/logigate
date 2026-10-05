<?php

declare(strict_types=1);

namespace App\Domains\Processo\Services;

use App\Domains\Processo\Enums\EstadoProcessoEnum;
use App\Models\Processo;
use InvalidArgumentException;

final readonly class ProcessoLifecycleRules
{
    public function assertEdicaoComum(Processo $processo, array $attributes): void
    {
        if (array_key_exists('ContaDespacho', $attributes) || array_key_exists('DataFecho', $attributes)
            || (($attributes['Estado'] ?? null) === EstadoProcessoEnum::FINALIZADO->value
                && $processo->Estado !== EstadoProcessoEnum::FINALIZADO->value)) {
            throw new InvalidArgumentException('Use o comando de finalização para finalizar o processo e emitir a conta de despacho.');
        }

        if (array_key_exists('Estado', $attributes) && $attributes['Estado'] === null) {
            throw new InvalidArgumentException('O estado do processo não pode ser limpo.');
        }

        if ($this->isTerminal($processo)) {
            foreach ($attributes as $field => $value) {
                $original = $processo->getAttribute($field);
                $unchanged = $value === null || $original === null
                    ? $value === $original
                    : (string) $value === (string) $original;
                if ($field !== 'observacoes' && ! $unchanged) {
                    throw new InvalidArgumentException('Após finalização ou cancelamento apenas as observações podem ser alteradas.');
                }
            }
        }
    }

    public function assertMercadoriasEditaveis(Processo $processo): void
    {
        if ($this->isTerminal($processo)) {
            throw new InvalidArgumentException('As mercadorias de processos finalizados ou cancelados não podem ser alteradas.');
        }
    }

    public function assertPodeFinalizar(Processo $processo): void
    {
        if ($this->isTerminal($processo)) {
            throw new InvalidArgumentException('O processo já está finalizado ou cancelado.');
        }
    }

    private function isTerminal(Processo $processo): bool
    {
        return in_array($processo->Estado, [EstadoProcessoEnum::FINALIZADO->value, EstadoProcessoEnum::CANCELADO->value], true);
    }

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
