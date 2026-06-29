<?php

namespace App\Application\Processo\Actions;

use App\Application\Processo\Services\ProcessoJasperService;
use App\Application\Processo\Services\ProcessoTenantAccessService;
use App\Models\Processo;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class GerarExtratoMercadoriaProcessoAction
{
    public function __construct(
        private readonly ProcessoTenantAccessService $tenantAccess,
        private readonly ProcessoJasperService $jasper,
    ) {
    }

    public function execute(User $user, Processo $processo): array
    {
        abort_unless($this->tenantAccess->canAccess($user, $processo), 404);
        Gate::forUser($user)->authorize('print', $processo);

        $filename = 'extrato_mercadoria_' . $this->safeName($processo->NrProcesso) . '.pdf';
        $outputName = pathinfo($filename, PATHINFO_FILENAME);
        $outputDirectory = storage_path('app/reports/processos/' . $processo->id);

        $path = $this->jasper->generatePdf('extrato_mercadoria.jrxml', $outputDirectory, $outputName, [
            'id' => $processo->id,
        ]);

        return [
            'path' => $path,
            'filename' => $filename,
            'mime' => 'application/pdf',
        ];
    }

    private function safeName(?string $value): string
    {
        return preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) ($value ?: 'processo'));
    }
}
