<?php

declare(strict_types=1);

namespace App\Livewire\Processo;

use App\Application\Processo\Actions\AtualizarExportacaoCrudeAction;
use App\Application\Processo\Actions\AtualizarProcessoAction;
use App\Application\Processo\DTOs\AtualizarProcessoDTO;
use App\Models\Processo;

final class ExportacaoCrudeEdit extends ProcessoEdit
{
    protected function isCrude(): bool
    {
        return true;
    }

    protected function persist(AtualizarProcessoDTO $dto, AtualizarProcessoAction $action): Processo
    {
        return app(AtualizarExportacaoCrudeAction::class)->execute($dto);
    }

    public function render()
    {
        return view('livewire.processo.exportacao-crude-edit', ['isCrude' => true, 'legacyExportData' => false]);
    }
}
