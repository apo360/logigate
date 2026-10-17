<?php

declare(strict_types=1);

namespace App\Application\Processo\Actions;

use App\Application\Processo\DTOs\AtualizarProcessoDTO;
use App\Application\Processo\Services\ProcessoTenantAccessService;
use App\Models\Processo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

final readonly class AtualizarExportacaoCrudeAction
{
    public function __construct(private AtualizarProcessoAction $atualizarProcesso) {}

    public function execute(AtualizarProcessoDTO $dto): Processo
    {
        $processo = app(ProcessoTenantAccessService::class)->findForUserOrFail(Auth::user(), $dto->id);
        Gate::authorize('update', $processo);
        abort_unless($processo->isExportacaoCrude(), 404);

        return $this->atualizarProcesso->execute($dto);
    }
}
