<?php

declare(strict_types=1);

namespace App\Application\Processo\Actions;

use App\Application\Processo\DTOs\CriarProcessoDTO;
use App\Domains\Processo\Enums\NaturezaOperacaoProcesso;
use App\Models\Processo;

final readonly class CriarExportacaoCrudeAction
{
    public function __construct(private CriarProcessoAction $criarProcesso) {}

    public function execute(CriarProcessoDTO $dto): Processo
    {
        return $this->criarProcesso->execute(CriarProcessoDTO::fromArray(array_replace(
            $dto->toArray(), ['natureza_operacao' => NaturezaOperacaoProcesso::EXPORTACAO_CRUDE->value]
        )));
    }
}
