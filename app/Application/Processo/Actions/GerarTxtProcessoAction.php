<?php

namespace App\Application\Processo\Actions;

use App\Application\Processo\Services\ProcessoTenantAccessService;
use App\Application\Processo\Services\ProcessoTxtService;
use App\Models\Processo;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class GerarTxtProcessoAction
{
    public function __construct(
        private readonly ProcessoTenantAccessService $tenantAccess,
        private readonly ProcessoTxtService $txt,
    ) {
    }

    public function execute(User $user, Processo $processo): array
    {
        abort_unless($this->tenantAccess->canAccess($user, $processo), 404);
        Gate::forUser($user)->authorize('exportXml', $processo);

        $processo->loadMissing('mercadoriasAgrupadas');

        if ($processo->mercadoriasAgrupadas->isEmpty()) {
            throw new RuntimeException('Nenhuma mercadoria agrupada encontrada para este processo.');
        }

        $content = $this->txt->build($processo);
        $filename = 'processo_' . $this->safeName($processo->NrProcesso) . '_' . now()->format('Ymd_His') . '.txt';
        $relativePath = 'reports/processos/' . $processo->id . '/' . $filename;

        Storage::disk('local')->put($relativePath, $content);

        return [
            'path' => Storage::disk('local')->path($relativePath),
            'filename' => $filename,
            'mime' => 'text/plain',
        ];
    }

    private function safeName(?string $value): string
    {
        return preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) ($value ?: 'processo'));
    }
}
