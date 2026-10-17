<?php

declare(strict_types=1);

namespace App\Livewire\Processo;

use App\Application\Processo\Actions\CriarExportacaoCrudeAction;
use App\Application\Processo\Actions\CriarProcessoAction;
use App\Application\Processo\DTOs\CriarProcessoDTO;
use App\Domains\Processo\Enums\NaturezaOperacaoProcesso;
use App\Models\Processo;

final class ExportacaoCrudeCreate extends ProcessoCreate
{
    protected function naturezaOperacao(): NaturezaOperacaoProcesso
    {
        return NaturezaOperacaoProcesso::EXPORTACAO_CRUDE;
    }

    protected function persist(CriarProcessoDTO $dto, CriarProcessoAction $action): Processo
    {
        return app(CriarExportacaoCrudeAction::class)->execute($dto);
    }

    public function render()
    {
        return view('livewire.processo.exportacao-crude-create', ['isCrude' => true]);
    }
}
